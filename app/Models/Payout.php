<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Payout extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'payee_id', 'service_id', 'amount', 'currency', 'method',
        'status', 'transaction_ref', 'reference_id', 'phone', 'paid_at',
        'paid_by', 'approved_by', 'approved_at', 'description', 'attachments',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'attachments' => 'array',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($payout) {
            if (empty($payout->uuid)) {
                $payout->uuid = (string) Str::uuid();
            }
        });
    }

    public function payee()
    {
        return $this->belongsTo(Payee::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function needsApproval(): bool
    {
        $threshold = (float) env('PAYOUT_APPROVAL_THRESHOLD', 50000);
        return (float) $this->amount >= $threshold;
    }

    public function canBePaid(): bool
    {
        if ($this->status === 'pending' && $this->needsApproval()) {
            return false;
        }
        return in_array($this->status, ['pending', 'approved']);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}