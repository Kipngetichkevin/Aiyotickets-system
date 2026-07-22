<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'budget_id',
        'service',
        'price',
        'qty',
        'delivery_date',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // Calculate the total cost for a service
    public function getTotalCostAttribute()
    {
        return $this->price * $this->qty;
    }
}
