<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'uuid',
        'slug',
        'merchant_id',
        'user_id',
        'event_type_id',
        'name',
        'description',
        'date',
        'end_date',
        'location',
        'currency',
        'status',
        'image',
        'banner',
    ];

    protected $casts = [
        'date' => 'datetime',
        'end_date' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            $event->uuid = (string) Str::uuid();

            $slug = Str::slug($event->name);

            $originalSlug = $slug;
            $count = 1;

            while (self::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $event->slug = $slug;
        });
    }

    // In Event.php - Add these accessors
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

    public function getBannerUrlAttribute()
    {
        if ($this->banner) {
            return asset('storage/' . $this->banner);
        }
        return null;
    }

    // Keep the original accessor for backward compatibility
    public function getImageAttribute($value)
    {
        if ($value) {
            if (strpos($value, 'storage/') === 0) {
                return asset($value);
            }
            return asset('storage/' . $value);
        }
        return null;
    }

    public function getBannerAttribute($value)
    {
        if ($value) {
            if (strpos($value, 'storage/') === 0) {
                return asset($value);
            }
            return asset('storage/' . $value);
        }
        return null;
    }

    public function ticketTypes()
    {
        return $this->hasMany(Ticket::class);
    }

    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function eventType()
    {
        return $this->belongsTo(EventType::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    // ============================================
    // FINANCE MODULE RELATIONSHIPS
    // ============================================

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}