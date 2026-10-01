<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
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
        'discount'     => 'decimal:2',
        'refund'       => 'decimal:2',
        'tax'          => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'order_no', 'order_no');
    }
}