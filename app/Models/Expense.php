<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $table = 'expenses';

    protected $fillable = [
        'event_id',
        'category',
        'supplier_name',
        'description',
        'amount',
        'paid_amount',
        'status',
        'due_date',
        'paid_date',
        'payment_method',
        'supplier_invoice',
        'receipt_image',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getBalanceAttribute()
    {
        return max(0, (float) $this->amount - (float) $this->paid_amount);
    }
}