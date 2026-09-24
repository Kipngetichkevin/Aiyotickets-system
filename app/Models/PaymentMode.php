<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    use HasFactory;

    protected $table = 'payment_mode';

    protected $fillable = [
        'name',
        'code',
        'description',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function attendees()
    {
        return $this->hasMany(Attendee::class, 'payment_mode_id');
    }
}