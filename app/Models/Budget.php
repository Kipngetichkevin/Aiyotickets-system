<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'budgets';

    protected $fillable = [
        'event_id',
        'name',
        'point_of_contact',
        'phone',
        'email',
        'address',
        'role',
        'category',
        'cost',
        'paid_amount',
        'status',
        'contract_signature',
        'profile_pic',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    /**
     * Include accessors in JSON output (needed for the edit modal).
     */
    protected $appends = ['balance', 'profile_pic_url'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function getBalanceAttribute()
    {
        return max(0, (float) $this->cost - (float) $this->paid_amount);
    }

    public function getProfilePicUrlAttribute()
    {
        if ($this->profile_pic) {
            return asset('storage/' . $this->profile_pic);
        }
        return null;
    }
}