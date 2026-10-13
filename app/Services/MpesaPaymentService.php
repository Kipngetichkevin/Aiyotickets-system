<?php

namespace App\Services;

use App\Helpers\SystemHelper;
use App\Jobs\SendTicketMessage;
use App\Mail\InstalmentReceived;
use App\Mail\TicketsIssued;
use App\Services\Messaging\AfricasTalkingSms;
use App\Services\Messaging\WhatsAppCloud;
use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMode;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * Payment state and order fulfilment for M-Pesa STK pushes.
 *
 * mpesa_stks.status is the app's view of a payment (pending → completed | failed);
 * result_code only ever holds what Safaricom returned. Every path that turns a paid
 * STK push into an order (callback, status polling, cart, reconciler) goes through
 * fulfil(), which locks the row so a payment produces exactly one order.
 */
class MpesaPaymentService
{
    public const PENDING   = 'pending';
    public const COMPLETED = 'completed';
    public const FAILED    = 'failed';

    public function __construct(private DarajaService $daraja)
    {
    }

    /**
     * Ask Safaricom for the final result of an STK push.
     * Returns null while the customer hasn't responded or the query failed.
     */
    public function querySafaricom(MpesaStk $stk): ?array
    {
        try {
            $result = $this->daraja->queryStatus($stk->checkout_request_id);
        } catch (\Throwable $e) {
            Log::warning('STK query failed: ' . $e->getMessage(), ['mpesa_stk_id' => $stk->id]);
            return null;
        }

        return isset($result['ResultCode']) ? $result : null;
    }

    /**
     * Pull the final result for a pending payment from Safaricom and apply it.
     */
    public function sync(MpesaStk $stk): void
    {
        if ($stk->status !== self::PENDING) {
            return;
        }

        $result = $this->querySafaricom($stk);
        if (!$result) {
            return;
        }

        if ((string) $result['ResultCode'] === '0') {
            $this->markCompleted($stk, ['result_desc' => $result['ResultDesc'] ?? 'Success']);
            $this->fulfilSafely($stk);
        } else {
            $this->markFailed($stk, (string) $result['ResultCode'], $result['ResultDesc'] ?? null);
        }
    }

    /**
     * Record a confirmed payment. Receipt details are only filled in where still empty.
     */
    public function markCompleted(MpesaStk $stk, array $details = []): void
    {
        $update = [
            'status'      => self::COMPLETED,
            'result_code' => '0',
            'result_desc' => $details['result_desc'] ?? $stk->result_desc ?? 'Success',
        ];

        foreach (['mpesa_receipt_number', 'transaction_date', 'phone_number'] as $field) {
            if (!empty($details[$field]) && empty($stk->{$field})) {
                $update[$field] = (string) $details[$field];
            }
        }

        $stk->update($update);
    }

    public function markFailed(MpesaStk $stk, ?string $resultCode, ?string $resultDesc): void
    {
        $stk->update([
            'status'      => self::FAILED,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
        ]);
    }

    /**
     * A late callback can carry the receipt for a payment already confirmed by STK Query.
     * Copy it onto the payment, its order, attendees and bookings where it is missing.
     */
    public function backfillReceipt(MpesaStk $stk, array $details): void
    {
        $receipt = $details['mpesa_receipt_number'] ?? null;
        if (!$receipt || !empty($stk->mpesa_receipt_number)) {
            return;
        }

        $this->markCompleted($stk, $details);

        if ($stk->order_id) {
            Order::where('id', $stk->order_id)->whereNull('transaction_code')->update(['transaction_code' => $receipt]);
            Attendee::where('order_id', $stk->order_id)->whereNull('transaction_code')->update(['transaction_code' => $receipt]);
            ExperienceBooking::where('order_id', $stk->order_id)->whereNull('transaction_code')->update(['transaction_code' => $receipt]);
        }
    }

    /**
     * Create the order, attendees and experience bookings for a completed payment.
     * Idempotent and safe to call concurrently: returns the existing order if one was made.
     */
    public function fulfil(MpesaStk $stk): ?Order
    {
        return DB::transaction(function () use ($stk) {
            $stk = MpesaStk::lockForUpdate()->find($stk->id);

            if (!$stk || $stk->status !== self::COMPLETED) {
                return null;
            }

            if ($stk->order_id) {
                return Order::find($stk->order_id);
            }

            if ($stk->instalment_order_id) {
                return Order::find($stk->instalment_order_id);   // instalment already applied
            }

            $pending = $stk->pending_data;
            if (!$pending) {
                Log::warning('⚠️ Paid STK push has no pending_data — cannot build order', ['mpesa_stk_id' => $stk->id]);
                return null;
            }

            // A later lipa mdogo mdogo payment towards an existing order
            if (!empty($pending['instalment_for'])) {
                return $this->applyInstalment($stk, (int) $pending['instalment_for']);
            }

            // A lipa mdogo mdogo deposit: the order starts partly paid, places held, no QR codes yet
            $plan    = $pending['plan'] ?? null;
            $partial = $plan !== null;

            $userId  = $stk->user_id ?? ($pending['user_id'] ?? null);
            $receipt = $stk->mpesa_receipt_number;
            $name    = $pending['name'] ?? 'Customer';
            $email   = $pending['email'] ?? null;
            $phone   = $stk->phone_number ?: ($pending['phone'] ?? null);
            $modeId  = PaymentMode::where('name', 'Mobile Money')->value('id') ?? 1;

            $order = Order::create([
                'user_id'          => $userId,
                'order_no'         => 'ORD-' . strtoupper(Str::random(10)),
                'uuid'             => (string) Str::uuid(),
                'qty'              => $pending['total_qty'] ?? 1,
                'total_amount'     => $pending['total_amount'],
                'status'           => $partial ? 'partial' : 'paid',
                'initiator'        => $name,
                'email'            => $email,
                'event_id'         => $pending['event_id'] ?? null,
                'transaction_code' => $receipt,
                'discount'         => $pending['discount'] ?? 0,
            ] + ($partial ? [
                'status'         => 'partial',
                'payment_plan'   => 'instalments',
                'seller_id'      => $plan['seller_id'],
                'experience_id'  => $plan['type'] === 'experience' ? $plan['id'] : ($pending['experience_items'][0]['experience_id'] ?? null),
                'deposit_amount' => $stk->amount,
                'amount_paid'    => $stk->amount,
                'balance_due'    => max(0, $pending['total_amount'] - $stk->amount),
                'due_date'       => $plan['due_date'],
            ] : []));

            // A promo code counts as used only once its order is paid
            if (!empty($pending['promo_code_id'])) {
                PromoCode::whereKey($pending['promo_code_id'])->increment('used_count');
            }

            $attendees = [];

            foreach (($pending['cart_items'] ?? []) as $item) {
                OrderItem::create([
                    'ticket_id'    => $item['ticket_id'],
                    'order_id'     => $order->id,
                    'event'        => $item['event'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                    'total_amount' => $item['subtotal'],
                ]);

                for ($i = 0; $i < $item['qty']; $i++) {
                    $attendees[] = Attendee::create([
                        'user_id'          => $userId,
                        'ticket_id'        => $item['ticket_id'],
                        'order_id'         => $order->id,
                        'name'             => $item['qty'] > 1 ? $name . ' (' . ($i + 1) . ')' : $name,
                        'phone'            => $phone,
                        'email'            => $email,
                        'gender'           => $pending['gender'] ?? null,
                        'payment_mode'     => 'Mobile Money',
                        'payment_mode_id'  => $modeId,
                        'qty'              => 1,
                        'total_price'      => $item['price'],
                        'payment_status'   => $partial ? 'partial' : 'paid',
                        'status'           => 'active',
                        'transaction_code' => $receipt,
                        'qr_code'          => $partial ? null : 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'created_by'       => $userId,
                    ]);
                }
            }

            $bookingCount = 0;

            foreach (($pending['experience_items'] ?? []) as $item) {
                $qty               = (int) ($item['qty'] ?? 1);
                $packagePrice      = (float) ($item['package_price'] ?? $item['price'] ?? 0);
                $categorySurcharge = (float) ($item['category_surcharge'] ?? 0);
                $totalPrice        = (float) ($item['price'] ?? ($packagePrice + $categorySurcharge));

                for ($i = 0; $i < $qty; $i++) {
                    ExperienceBooking::create([
                        'user_id'            => $userId,
                        'experience_id'      => $item['experience_id'],
                        'package_id'         => $item['package_id'] ?? null,
                        'category_id'        => $item['category_id'] ?? null,
                        'order_id'           => $order->id,
                        'name'               => $qty > 1 ? $name . ' (' . ($i + 1) . ')' : $name,
                        'gender'             => $pending['gender'] ?? null,
                        'phone'              => $phone,
                        'email'              => $email,
                        'qty'                => 1,
                        'package_price'      => $packagePrice,
                        'category_surcharge' => $categorySurcharge,
                        'total_price'        => $totalPrice,
                        'payment_status'     => $partial ? 'partial' : 'paid',
                        'payment_mode'       => 'Mobile Money',
                        'payment_mode_id'    => $modeId,
                        'transaction_code'   => $receipt,
                        'qr_code'            => $partial ? null : 'EXP-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'status'             => 'active',
                        'created_by'         => (string) $userId,
                    ]);
                    $bookingCount++;
                }
            }

            if ($attendees) {
                $order->update(['attendee_id' => $attendees[0]->id]);
            }

            $stk->update([
                'order_id'    => $order->id,
                'attendee_id' => $attendees[0]->id ?? null,
            ]);

            if ($partial) {
                $this->notifyInstalment($order, (float) $stk->amount, $phone);
            } else {
                $this->deliverTickets($order, $email ?: User::whereKey($userId)->value('email'), $phone);
            }

            Log::info('🎉 Order fulfilled', [
                'order_id'     => $order->id,
                'mpesa_stk_id' => $stk->id,
                'attendees'    => count($attendees),
                'bookings'     => $bookingCount,
            ]);

            return $order;
        });
    }

    /**
     * Apply a paid instalment to its order (inside fulfil's transaction). When the
     * balance reaches zero the order is completed and the tickets are issued.
     * Money that arrives for a lapsed or already-paid order, or beyond the total,
     * is recorded as a refund due.
     */
    private function applyInstalment(MpesaStk $stk, int $orderId): ?Order
    {
        $order = Order::lockForUpdate()->find($orderId);
        if (!$order) {
            Log::error('Instalment for a missing order', ['mpesa_stk_id' => $stk->id, 'order_id' => $orderId]);
            return null;
        }

        $stk->update(['instalment_order_id' => $order->id]);
        $amount = (float) $stk->amount;

        if ($order->status !== 'partial') {
            $order->update(['refund' => (float) $order->refund + $amount]);
            Log::warning('Instalment received for a closed order — refund due', ['order_id' => $order->id, 'amount' => $amount]);
            return $order;
        }

        $paid   = (float) $order->amount_paid + $amount;
        $excess = max(0, $paid - (float) $order->total_amount);

        $order->update([
            'amount_paid' => $paid - $excess,
            'balance_due' => max(0, (float) $order->total_amount - $paid),
            'refund'      => (float) $order->refund + $excess,
        ]);

        $phone = $stk->phone_number ?: ($stk->pending_data['phone'] ?? null);

        if ((float) $order->balance_due <= 0) {
            $this->completeInstalments($order, $phone);
        } else {
            $this->notifyInstalment($order, $amount, $phone);
        }

        return $order;
    }

    /** Fully paid: release the QR tickets and deliver them like a normal purchase. */
    private function completeInstalments(Order $order, ?string $phone): void
    {
        $order->update(['status' => 'paid', 'balance_due' => 0]);

        foreach (Attendee::where('order_id', $order->id)->get() as $a) {
            $a->update(['payment_status' => 'paid', 'qr_code' => $a->qr_code ?: 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6))]);
        }
        foreach (ExperienceBooking::where('order_id', $order->id)->get() as $b) {
            $b->update(['payment_status' => 'paid', 'qr_code' => $b->qr_code ?: 'EXP-' . $order->order_no . '-' . strtoupper(Str::random(6))]);
        }

        $this->deliverTickets($order, $order->email ?: User::whereKey($order->user_id)->value('email'), $phone);
    }

    /** Receipt for a deposit or instalment (email, and SMS when configured). */
    private function notifyInstalment(Order $order, float $amount, ?string $phone): void
    {
        $email = $order->email ?: User::whereKey($order->user_id)->value('email');
        $phone = $this->kenyanMsisdn($phone);

        DB::afterCommit(function () use ($order, $amount, $email, $phone) {
            try {
                if ($email) {
                    Mail::to($email)->queue(new InstalmentReceived($order->fresh(), $amount));
                }
                if ($phone && app(AfricasTalkingSms::class)->enabled()) {
                    app(AfricasTalkingSms::class)->send($phone, SystemHelper::appName() . ": received KES " . number_format($amount)
                        . " for order {$order->order_no}. Balance KES " . number_format($order->fresh()->balance_due)
                        . ' due by ' . $order->due_date->format('d M Y') . '. Pay: ' . \App\Http\Controllers\InstalmentController::payLinkFor($order));
                }
            } catch (\Throwable $e) {
                Log::error('Instalment notice failed: ' . $e->getMessage(), ['order_id' => $order->id]);
            }
        });
    }

    /**
     * Queue ticket delivery once the order is committed: email, plus SMS and WhatsApp
     * when those are configured. A delivery problem must never undo or block the
     * order, so failures are only logged.
     */
    private function deliverTickets(Order $order, ?string $email, ?string $phone): void
    {
        $phone = $this->kenyanMsisdn($phone);

        DB::afterCommit(function () use ($order, $email, $phone) {
            try {
                if ($email) {
                    Mail::to($email)->queue(new TicketsIssued($order));
                }
                if ($phone && app(AfricasTalkingSms::class)->enabled()) {
                    SendTicketMessage::dispatch($order, 'sms', $phone);
                }
                if ($phone && app(WhatsAppCloud::class)->enabled()) {
                    SendTicketMessage::dispatch($order, 'whatsapp', $phone);
                }
            } catch (\Throwable $e) {
                Log::error('Ticket delivery failed: ' . $e->getMessage(), ['order_id' => $order->id]);
            }
        });
    }

    /** 07XXXXXXXX / 7XXXXXXXX / +2547… → 2547XXXXXXXX, or null. */
    private function kenyanMsisdn(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = '254' . substr($digits, 1);
        } elseif (strlen($digits) === 9) {
            $digits = '254' . $digits;
        }

        return preg_match('/^254[17]\d{8}$/', $digits) ? $digits : null;
    }

    /**
     * fulfil() for paths that must not fail (callbacks, polling). Failures are logged
     * and picked up again by the mpesa:reconcile command.
     */
    public function fulfilSafely(MpesaStk $stk): ?Order
    {
        try {
            return $this->fulfil($stk);
        } catch (\Throwable $e) {
            Log::error('❌ Order fulfilment failed — will retry: ' . $e->getMessage(), ['mpesa_stk_id' => $stk->id]);
            return null;
        }
    }
}
