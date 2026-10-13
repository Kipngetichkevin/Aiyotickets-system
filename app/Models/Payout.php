<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    public const REQUESTED = 'requested';
    public const APPROVED  = 'approved';
    public const PAID      = 'paid';
    public const REJECTED  = 'rejected';

    protected $fillable = [
        'user_id', 'amount', 'method', 'destination', 'status',
        'reference', 'note', 'processed_by', 'processed_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
