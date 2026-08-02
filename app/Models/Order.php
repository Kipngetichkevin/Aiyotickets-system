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
        'tax',
        'transaction_code',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'refund' => 'decimal:2',
        'tax' => 'decimal:2',
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