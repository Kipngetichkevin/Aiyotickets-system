<?php

namespace App\Console\Commands;

use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Attendee;
use App\Models\PaymentMode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UpdateMpesaTransaction extends Command
{
    protected $signature = 'mpesa:update {checkout_id} {receipt}';
    protected $description = 'Manually reconcile an M-Pesa transaction with a receipt number (idempotent)';

    public function handle(): int
    {
        $checkoutId = $this->argument('checkout_id');
        $receipt    = $this->argument('receipt');

        $stk = MpesaStk::where('checkout_request_id', $checkoutId)->first();

        if (!$stk) {
            $this->error("Transaction not found: {$checkoutId}");
            return 1;
        }

        $this->info("Found MpesaStk #{$stk->id} (order_id: " . ($stk->order_id ?? 'NULL') . ")");

        // ── Idempotency guard ──────────────────────────────────────
        if ($stk->isSuccessful() && $stk->mpesa_receipt_number === $receipt && $stk->order_id) {
            $this->info("✅ Already reconciled with receipt {$receipt}. Nothing to do.");
            return 0;
        }

        try {
            DB::beginTransaction();

            // ── 1. Mark MpesaStk as successful ─────────────────────
            $stk->update([
                'result_code'          => '0',
                'result_desc'          => 'Payment successful (manual reconcile)',
                'mpesa_receipt_number' => $receipt,
                'transaction_date'     => $stk->transaction_date ?: now()->format('YmdHis'),
            ]);

            $order = Order::find($stk->order_id);

            // ── 2. No order linked — abort safely ──────────────────
            if (!$order) {
                $this->warn("No Order linked to MpesaStk #{$stk->id}. Cannot build attendees.");
                $this->line("  → Let the callback run, or set order_id manually, then re-run this command.");
                DB::commit();
                return 1;
            }

            // ── 3. Update the order ────────────────────────────────
            $order->update([
                'status'           => 'paid',
                'transaction_code' => $receipt,
            ]);
            $this->info("Order #{$order->id} ({$order->order_no}) marked paid");

            // ── 4. Idempotency: if attendees already exist, skip ───
            $existing = Attendee::where('order_id', $order->id)->count();
            if ($existing > 0) {
                $this->info("Attendees already exist for order #{$order->id} ({$existing}). Skipping creation.");
                DB::commit();
                return 0;
            }

            // ── 5. Create attendees ────────────────────────────────
            $orderItems = OrderItem::where('order_id', $order->id)->get();
            $payMode    = PaymentMode::where('name', 'LIKE', '%Mobile Money%')->first();
            $created    = 0;
            $firstId    = null;

            foreach ($orderItems as $orderItem) {
                for ($i = 0; $i < $orderItem->qty; $i++) {
                    $name = $order->initiator;
                    if ($orderItem->qty > 1) {
                        $name .= ' (' . ($i + 1) . ')';
                    }

                    $attendee = Attendee::create([
                        'ticket_id'        => $orderItem->ticket_id,
                        'order_id'         => $order->id,
                        'name'             => $name,
                        'phone'            => $stk->phone_number,
                        'email'            => $order->email ?? 'customer@example.com',
                        'gender'           => '1',
                        'payment_mode'     => 'Mobile Money',
                        'payment_mode_id'  => $payMode->id ?? 1,
                        'transaction_code' => $receipt,
                        'qty'              => 1,
                        'total_price'      => $orderItem->price,
                        'qr_code'          => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'payment_status'   => 'paid',
                        'status'           => 'active',
                        'created_by'       => $order->initiator,
                    ]);

                    $firstId = $firstId ?? $attendee->id;
                    $created++;
                }
            }

            // ── 6. Link the first attendee back ────────────────────
            if ($firstId) {
                $stk->update(['attendee_id' => $firstId]);
                $order->update(['attendee_id' => $firstId]);
            }

            DB::commit();

            $this->info("Created {$created} attendees");
            $this->info("✅ Transaction reconciled successfully!");

            return 0;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Reconcile failed: ' . $e->getMessage());
            return 1;
        }
    }
}