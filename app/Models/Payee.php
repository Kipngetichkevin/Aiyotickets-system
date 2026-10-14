<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Someone the platform pays: a supplier, a staff member, a contractor. */
class Payee extends Model
{
    public const TYPES = [
        'supplier'   => 'Supplier',
        'staff'      => 'Staff',
        'contractor' => 'Contractor',
        'other'      => 'Other',
    ];

    public const METHODS = ['mpesa' => 'M-Pesa', 'bank' => 'Bank', 'cash' => 'Cash'];

    protected $fillable = [
        'type', 'name', 'phone', 'email', 'kra_pin', 'id_number', 'job_title', 'monthly_pay',
        'payment_method', 'mpesa_phone', 'bank_name', 'bank_account_name', 'bank_account_number',
        'default_category_id', 'notes', 'is_active',
    ];

    protected $casts = [
        'monthly_pay' => 'decimal:2',
        'is_active'   => 'boolean',
    ];

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function payments()
    {
        return $this->hasMany(FinancePayment::class);
    }

    public function defaultCategory()
    {
        return $this->belongsTo(FinanceCategory::class, 'default_category_id');
    }

    /** Where we send their money, for display. */
    public function payTo(): string
    {
        return match ($this->payment_method) {
            'mpesa' => 'M-Pesa ' . ($this->mpesa_phone ?: $this->phone),
            'bank'  => trim("{$this->bank_name} · {$this->bank_account_number}"),
            default => 'Cash',
        };
    }

    /** What we still owe them on approved bills. */
    public function owed(): float
    {
        return (float) $this->bills()->where('status', Bill::APPROVED)->sum(\Illuminate\Support\Facades\DB::raw('amount - amount_paid'));
    }
}
