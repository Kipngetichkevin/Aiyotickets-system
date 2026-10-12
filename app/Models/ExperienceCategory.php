<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExperienceCategory extends Model
{
    protected $table = 'experience_categories';

    protected $fillable = [
        'experience_id', 'name', 'distance_km', 'price_surcharge',
        'max_capacity', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'distance_km'     => 'integer',
        'price_surcharge' => 'decimal:2',
        'max_capacity'    => 'integer',
        'sort_order'      => 'integer',
        'is_active'       => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function experience() { return $this->belongsTo(Experience::class); }
    public function bookings()   { return $this->hasMany(ExperienceBooking::class, 'category_id'); }

    public function bookedCount(): int
    {
        return (int) $this->bookings()->where('payment_status', 'paid')->sum('qty');
    }

    public function availableSpots(): ?int
    {
        if ($this->max_capacity === null) return null;
        return max(0, (int) $this->max_capacity - $this->bookedCount());
    }
}