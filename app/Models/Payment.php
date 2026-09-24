<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'order_no',
        'payment_mode',
        'amount_paid',
        'balance',
        'transaction_id',
        'name',
        'phone',
        'email',
        'paid_at',
        'status',
        'description',
        'reference_id',
        'currency',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_no', 'order_no');
    }
}