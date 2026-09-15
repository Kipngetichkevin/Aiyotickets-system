<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    // protected $table = 'ticket_orders'; // Specify the custom table name

    protected $fillable = [
        'user_id',
        'ticket_id',
        'merchant_id',
        'event_id',
        'name',
        'price',
        'quantity',
        'total',
    ];
}
