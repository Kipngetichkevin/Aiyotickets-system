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
        'response' => 'array',
        'amount' => 'decimal:2',
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

    public function isSuccessful()
    {
        return $this->result_code == '0';
    }
}