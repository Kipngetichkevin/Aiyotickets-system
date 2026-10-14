<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An organiser. The row doubles as their application: anyone with a verified
 * account can apply (pending); an admin approves it, which grants the merchant role.
 */
class Merchant extends Model
{
    use HasFactory;

    public const PENDING   = 'pending';
    public const APPROVED  = 'approved';
    public const REJECTED  = 'rejected';
    public const SUSPENDED = 'suspended';

    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'description',
        'location',
        'business_type',
        'id_number',
        'kra_pin',
        'website',
        'payout_method',
        'payout_phone',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /** Where the organiser's payouts go, for display. */
    public function payoutDestination(): ?string
    {
        return match ($this->payout_method) {
            'mpesa' => 'M-Pesa ' . $this->payout_phone,
            'bank'  => trim("{$this->bank_name} · {$this->bank_account_number} ({$this->bank_account_name})"),
            default => null,
        };
    }
}
