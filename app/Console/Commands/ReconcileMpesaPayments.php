<?php

namespace App\Console\Commands;

use App\Models\MpesaStk;
use App\Services\MpesaPaymentService;
use Illuminate\Console\Command;

/**
 * Safety net for payments the callback and polling didn't finish:
 *  - pending STK pushes are re-checked with Safaricom
 *  - pending ones nobody answered within 24h are marked failed
 *  - paid ones without an order get their order created
 */
class ReconcileMpesaPayments extends Command
{
    protected $signature = 'mpesa:reconcile';

    protected $description = 'Re-check pending M-Pesa payments and create missing orders for paid ones';

    public function handle(MpesaPaymentService $payments): int
    {
        $pending = MpesaStk::where('status', MpesaPaymentService::PENDING)
            ->whereNotNull('checkout_request_id')
            ->whereBetween('created_at', [now()->subDay(), now()->subMinutes(2)])
            ->get();

        foreach ($pending as $stk) {
            $payments->sync($stk);
        }

        $expired = MpesaStk::where('status', MpesaPaymentService::PENDING)
            ->where('created_at', '<', now()->subDay())
            ->update(['status' => MpesaPaymentService::FAILED, 'result_desc' => 'Expired: no response within 24 hours']);

        $unfulfilled = MpesaStk::where('status', MpesaPaymentService::COMPLETED)
            ->whereNull('order_id')->whereNull('instalment_order_id')
            ->whereNotNull('pending_data')
            ->get();

        $created = 0;
        foreach ($unfulfilled as $stk) {
            if ($payments->fulfilSafely($stk)) {
                $created++;
            }
        }

        $this->info("Checked {$pending->count()} pending, expired {$expired}, created {$created} missing order(s).");

        return self::SUCCESS;
    }
}
