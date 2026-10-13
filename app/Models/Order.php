<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_no',
        'uuid',
        'qty',
        'total_amount',
        'status',
        'initiator',
        'email', 
        'event_id',
        'attendee_id',
        'discount',
        'refund',
        'payment_plan', 'seller_id', 'experience_id', 'deposit_amount', 'amount_paid', 'balance_due',
        'due_date', 'reminders_sent', 'lapsed_at', 'refund_reference', 'refunded_at',
        'tax',
        'transaction_code',
        'user_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'refund' => 'decimal:2',
        'tax' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'due_date' => 'datetime',
        'reminders_sent' => 'array',
        'lapsed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }
}