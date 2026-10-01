<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventType extends Model
{
    use HasFactory;

    protected $table = 'event_types';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($eventType) {
            if (empty($eventType->slug)) {
                $eventType->slug = static::generateUniqueSlug($eventType->name);
            }
            if ($eventType->sort_order === null) {
                $eventType->sort_order = (static::max('sort_order') ?? 0) + 1;
            }
        });

        static::updating(function ($eventType) {
            if ($eventType->isDirty('name') && ! $eventType->isDirty('slug')) {
                $eventType->slug = static::generateUniqueSlug($eventType->name, $eventType->id);
            }
        });
    }

    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug     = Str::slug($name);
        $original = $slug;
        $count    = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    protected static function slugExists(string $slug, ?int $ignoreId): bool
    {
        $query = static::where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    // ─────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    // ─────────────────────────────────────────────
    // Accessors
    // ─────────────────────────────────────────────

    public function getEventsCountAttribute(): int
    {
        return $this->events()->count();
    }

    public function getUpcomingEventsCountAttribute(): int
    {
        return $this->events()
            ->whereDate('end_date', '>=', today())
            ->count();
    }

    public function getIconClassAttribute(): string
    {
        return $this->icon ?: 'heroicon-o-tag';
    }

    public function getColorClassAttribute(): string
    {
        return $this->color ?: 'blue';
    }

    // ─────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ─────────────────────────────────────────────
    // Route binding
    // ─────────────────────────────────────────────

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}