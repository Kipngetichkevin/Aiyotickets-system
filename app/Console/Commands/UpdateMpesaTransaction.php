<?php

namespace App\Console\Commands;

use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Attendee;
use App\Models\PaymentMode;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateMpesaTransaction extends Command
{
    protected $signature = 'mpesa:update {checkout_id} {receipt}';
    protected $description = 'Update M-Pesa transaction with receipt number';

    public function handle()
    {
        $checkoutId = $this->argument('checkout_id');
        $receipt = $this->argument('receipt');

        // Find the transaction
        $stk = MpesaStk::where('checkout_request_id', $checkoutId)->first();

        if (!$stk) {
            $this->error("Transaction not found: {$checkoutId}");
            return 1;
        }

        $this->info("Found transaction for order #{$stk->order_id}");

        // Update MpesaStk
        $stk->update([
            'result_code' => '0',
            'result_desc' => 'Payment successful',
            'mpesa_receipt_number' => $receipt,
            'transaction_date' => now()->format('YmdHis'),
        ]);

        $this->info("MpesaStk updated with receipt: {$receipt}");

        // Update Order
        $order = Order::find($stk->order_id);
        if ($order) {
            $order->update([
                'status' => 'paid',
                'transaction_code' => $receipt,
            ]);
            $this->info("Order #{$order->id} updated to paid");
        }

        // Create Attendees
        $orderItems = OrderItem::where('order_id', $order->id)->get();
        $payMode = PaymentMode::where('name', 'LIKE', '%Mobile Money%')->first();
        $attendeesCreated = 0;

        foreach ($orderItems as $orderItem) {
            for ($i = 0; $i < $orderItem->qty; $i++) {
                $name = $order->initiator;
                if ($orderItem->qty > 1) {
                    $name = $order->initiator . ' (' . ($i + 1) . ')';
                }

                Attendee::create([
                    'ticket_id' => $orderItem->ticket_id,
                    'name' => $name,
                    'phone' => $stk->phone_number,
                    'email' => $order->email ?? 'customer@example.com',
                    'payment_mode' => 'Mobile Money',
                    'payment_mode_id' => $payMode->id ?? 1,
                    'transaction_code' => $receipt,
                    'qty' => 1,
                    'total_price' => $orderItem->price,
                    'gender' => '1',
                    'qr_code' => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                    'payment_status' => 'paid',
                    'order_id' => $order->id,
                    'status' => 'active',
                    'created_by' => $order->initiator,
                ]);
                $attendeesCreated++;
            }
        }

        $this->info("Created {$attendeesCreated} attendees");
        $this->info("✅ Transaction updated successfully!");

        return 0;
    }
}