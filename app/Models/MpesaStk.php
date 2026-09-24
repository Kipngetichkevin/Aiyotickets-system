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
        'result_code',
        'result_desc',
        'amount',
        'mpesa_receipt_number',
        'transaction_date',
        'phone_number',
    ];

    protected $casts = [
        'response'     => 'array',
        'pending_data' => 'array',
        'amount'       => 'decimal:2',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

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

    public function isSuccessful(): bool
    {
        return (string) $this->result_code === '0';
    }

    public function isPending(): bool
    {
        return (string) $this->result_code === '1';
    }

    public function isFailed(): bool
    {
        return !$this->isSuccessful() && !$this->isPending();
    }

    public function getStatusLabelAttribute(): string
    {
        return match (true) {
            $this->isSuccessful() => 'Success',
            $this->isPending()    => 'Pending',
            default               => 'Failed',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match (true) {
            $this->isSuccessful() => 'green',
            $this->isPending()    => 'yellow',
            default               => 'red',
        };
    }

    public function scopeSuccessful($query)
    {
        return $query->where('result_code', '0');
    }

    public function scopePending($query)
    {
        return $query->where('result_code', '1');
    }

    public function scopeFailed($query)
    {
        return $query->whereNotIn('result_code', ['0', '1']);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'KES ' . number_format((float) $this->amount, 2);
    }
}