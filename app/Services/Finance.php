<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\FinancePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Money out: bill approval and payment, so amounts paid always add up. */
class Finance
{
    public function approve(Bill $bill, User $by): void
    {
        if ($bill->status !== Bill::AWAITING) {
            throw ValidationException::withMessages(['bill' => 'Only bills awaiting approval can be approved.']);
        }

        $bill->forceFill(['status' => Bill::APPROVED, 'approved_by' => $by->id, 'approved_at' => now()])->save();
    }

    /** Record a payment against an approved bill; it can't exceed what's left to pay. */
    public function payBill(Bill $bill, array $data, User $by): FinancePayment
    {
        return DB::transaction(function () use ($bill, $data, $by) {
            $bill = Bill::lockForUpdate()->findOrFail($bill->id);

            if ($bill->status !== Bill::APPROVED) {
                throw ValidationException::withMessages(['amount' => 'Approve the bill before paying it.']);
            }
            if ((float) $data['amount'] > $bill->balance()) {
                throw ValidationException::withMessages(['amount' => 'That is more than the KES ' . number_format($bill->balance(), 2) . ' left to pay.']);
            }

            $payment = new FinancePayment([
                'payee_id'    => $bill->payee_id,
                'bill_id'     => $bill->id,
                'category_id' => $bill->category_id,
                'kind'        => 'bill',
                'amount'      => $data['amount'],
                'method'      => $data['method'],
                'reference'   => $data['reference'] ?? null,
                'paid_on'     => $data['paid_on'],
                'notes'       => $data['notes'] ?? null,
            ]);
            $payment->forceFill(['created_by' => $by->id])->save();

            $bill->forceFill(['amount_paid' => round((float) $bill->amount_paid + (float) $data['amount'], 2)])->save();

            return $payment;
        });
    }

    public function void(Bill $bill, string $reason): void
    {
        if ((float) $bill->amount_paid > 0) {
            throw ValidationException::withMessages(['reason' => 'This bill has payments recorded, so it can no longer be voided.']);
        }

        $bill->forceFill(['status' => Bill::VOID, 'void_reason' => $reason])->save();
    }
}
