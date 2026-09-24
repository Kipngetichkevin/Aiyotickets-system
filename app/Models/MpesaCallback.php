<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpesaCallback extends Model
{
    protected $table = 'mpesa_callbacks';

    protected $fillable = [
        'checkout_request_id',
        'merchant_request_id',
        'result_code',
        'result_desc',
        'mpesa_receipt_number',
        'amount',
        'phone_number',
        'raw_payload',
        'headers',
        'ip_address',
        'user_agent',
        'processing_status',
        'processing_error',
        'mpesa_stk_id',
        'order_id',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'headers'     => 'array',
        'amount'      => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function mpesaStk()
    {
        return $this->belongsTo(MpesaStk::class, 'mpesa_stk_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->processing_status) {
            'processed' => 'green',
            'failed'    => 'red',
            default     => 'yellow',
        };
    }
}