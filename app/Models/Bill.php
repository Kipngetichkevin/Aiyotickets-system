<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A bill we owe a payee (accounts payable). It must be approved before it can be
 * paid; it can be paid in parts. "Open" = approved and not fully paid.
 */
class Bill extends Model
{
    public const AWAITING = 'awaiting_approval';
    public const APPROVED = 'approved';
    public const VOID     = 'void';

    protected $fillable = [
        'payee_id', 'category_id', 'reference', 'description', 'bill_date', 'due_date', 'amount', 'attachment',
    ];

    protected $casts = [
        'bill_date'   => 'date',
        'due_date'    => 'date',
        'amount'      => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function payee()
    {
        return $this->belongsTo(Payee::class);
    }

    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }

    public function payments()
    {
        return $this->hasMany(FinancePayment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', self::APPROVED)->whereColumn('amount_paid', '<', 'amount');
    }

    public function balance(): float
    {
        return max(0, round((float) $this->amount - (float) $this->amount_paid, 2));
    }

    public function isPaid(): bool
    {
        return $this->status === self::APPROVED && $this->balance() <= 0;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::APPROVED && $this->balance() > 0 && $this->due_date->isPast() && !$this->due_date->isToday();
    }

    /** One label for lists: Awaiting approval, Unpaid, Part paid, Paid, Overdue, Void. */
    public function statusLabel(): string
    {
        return match (true) {
            $this->status === self::VOID     => 'Void',
            $this->status === self::AWAITING => 'Awaiting approval',
            $this->isPaid()                  => 'Paid',
            $this->isOverdue()               => 'Overdue',
            (float) $this->amount_paid > 0   => 'Part paid',
            default                          => 'Unpaid',
        };
    }
}
