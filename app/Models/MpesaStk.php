<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaStk extends Model
{
    protected $table = 'mpesa_stks';

    protected $fillable = [
        'merchant_request_id',
        'checkout_request_id',
        'response_code',
        'response_description',
        'customer_message',
        'response',
        'pending_data',
        'user_id',
        'order_id',
        'attendee_id',
        'status',
        'result_code',
        'result_desc',
        'amount',
        'mpesa_receipt_number',
        'transaction_date',
        'phone_number',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'response'     => 'array',
        'pending_data' => 'array',
        'amount'       => 'decimal:2',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function attendee()
    {
        return $this->belongsTo(Attendee::class);
    }

    // Status helpers
    public function isSuccessful()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    // Get status label
    public function getStatusLabelAttribute()
    {
        if ($this->isSuccessful()) {
            return 'Success';
        } elseif ($this->isPending()) {
            return 'Pending';
        } else {
            return 'Failed';
        }
    }

    // Get status color class
    public function getStatusColorAttribute()
    {
        if ($this->isSuccessful()) {
            return 'green';
        } elseif ($this->isPending()) {
            return 'yellow';
        } else {
            return 'red';
        }
    }

    // Scope for successful transactions
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope for pending transactions
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for failed transactions
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Get formatted amount
    public function getFormattedAmountAttribute()
    {
        return 'KES ' . number_format($this->amount, 2);
    }
}
