<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ExperiencePackage extends Model
{
    use HasFactory;

    protected $table = 'experience_packages';

    protected $fillable = [
        'uuid', 'experience_id', 'name', 'tier', 'tagline', 'description',
        'price', 'currency', 'badge_color', 'transport_type',
        'accommodation_type', 'accommodation_sharing', 'includes',
        'max_capacity', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'price'        => 'decimal:2',
        'includes'     => 'array',
        'is_active'    => 'boolean',
        'tier'         => 'integer',
        'max_capacity' => 'integer',
        'sort_order'   => 'integer',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($p) {
            if (empty($p->uuid)) $p->uuid = (string) Str::uuid();
        });
    }

    public function experience() { return $this->belongsTo(Experience::class); }
    public function bookings()   { return $this->hasMany(ExperienceBooking::class, 'package_id'); }

    public function bookedCount(): int
    {
        return (int) $this->bookings()->where('payment_status', 'paid')->sum('qty');
    }

    public function availableSpots(): ?int
    {
        if ($this->max_capacity === null) return null;
        return max(0, (int) $this->max_capacity - $this->bookedCount());
    }

    public function getRouteKeyName(): string { return 'uuid'; }
}