<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Experience extends Model
{
    use HasFactory;

    protected $table = 'experiences';

    protected $fillable = [
        'uuid',
        'slug',
        'user_id',
        'merchant_id',
        'name',
        'short_description',
        'description',
        'location',
        'start_date',
        'end_date',
        'image',
        'poster_image',
        'banner',
        'gallery',
        'total_capacity',
        'status',
        'featured',
    ];

    protected $casts = [
        'gallery'        => 'array',
        'start_date'     => 'datetime',
        'end_date'       => 'datetime',
        'featured'       => 'boolean',
        'total_capacity' => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    /* =========================================================
     |  BOOT — uuid + slug
     |========================================================= */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($experience) {
            if (empty($experience->uuid)) {
                $experience->uuid = (string) Str::uuid();
            }

            if (empty($experience->slug)) {
                $experience->slug = static::makeUniqueSlug($experience->name);
            }

            if (empty($experience->status)) {
                $experience->status = 'active';
            }
        });

        static::updating(function ($experience) {
            if ($experience->isDirty('name') && !$experience->isDirty('slug')) {
                $experience->slug = static::makeUniqueSlug(
                    $experience->name,
                    $experience->id
                );
            }
        });
    }

    protected static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i    = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    /* =========================================================
     |  RELATIONSHIPS
     |========================================================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function packages()
    {
        return $this->hasMany(ExperiencePackage::class)
                    ->orderBy('sort_order')
                    ->orderBy('tier');
    }

    public function days()
    {
        return $this->hasMany(ExperienceDay::class)
                    ->orderBy('day_number');
    }

    public function activities()
    {
        return $this->hasMany(ExperienceActivity::class);
    }

    public function categories()
    {
        return $this->hasMany(ExperienceCategory::class)
                    ->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->hasMany(ExperienceBooking::class);
    }

    /* =========================================================
     |  SCOPES
     |========================================================= */

    public function scopeActive($q)
    {
        return $q->where('status', 'active');
    }

    public function scopeFeatured($q)
    {
        return $q->where('featured', true);
    }

    public function scopeUpcoming($q)
    {
        return $q->where(function ($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    /* =========================================================
     |  HELPERS
     |========================================================= */

    public function bookedCount(): int
    {
        return (int) $this->bookings()
            ->where('payment_status', 'paid')
            ->sum('qty');
    }

    public function availableSpots(): ?int
    {
        if ($this->total_capacity === null) {
            return null;
        }
        return max(0, (int) $this->total_capacity - $this->bookedCount());
    }

    public function activePackages()
    {
        return $this->packages()->where('is_active', true)->get();
    }

    public function activeCategories()
    {
        return $this->categories()->where('is_active', true)->get();
    }

    /**
     * Shape used by the create/edit modal.
     * Dates are preformatted for <input type="datetime-local">.
     * Activities include image + image_url so the modal can preview them.
     */
    public function toFormArray(): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            'location'          => $this->location,
            'start_date'        => optional($this->start_date)->format('Y-m-d\TH:i'),
            'end_date'          => optional($this->end_date)->format('Y-m-d\TH:i'),
            'total_capacity'    => $this->total_capacity,
            'status'            => $this->status ?? 'active',
            'featured'          => (bool) $this->featured,

            'image'             => $this->image,
            'image_url'         => $this->image ? asset('storage/' . $this->image) : null,
            'poster_image'      => $this->poster_image,
            'poster_image_url'  => $this->poster_image ? asset('storage/' . $this->poster_image) : null,
            'banner'            => $this->banner,
            'banner_url'        => $this->banner ? asset('storage/' . $this->banner) : null,

            'packages' => $this->packages->map(fn ($p) => [
                'id'                    => $p->id,
                'name'                  => $p->name,
                'tier'                  => $p->tier,
                'badge_color'           => $p->badge_color,
                'tagline'               => $p->tagline,
                'price'                 => (float) $p->price,
                'currency'              => $p->currency,
                'transport_type'        => $p->transport_type,
                'accommodation_type'    => $p->accommodation_type,
                'accommodation_sharing' => $p->accommodation_sharing,
                'max_capacity'          => $p->max_capacity,
                'includes'              => is_array($p->includes) ? $p->includes : [],
            ])->values()->toArray(),

            'days' => $this->days->map(fn ($d) => [
                'id'          => $d->id,
                'day_number'  => $d->day_number,
                'date'        => optional($d->date)->format('Y-m-d'),
                'title'       => $d->title,
                'description' => $d->description,
                'activities'  => $d->activities->map(fn ($a) => [
                    'id'          => $a->id,
                    'title'       => $a->title,
                    'time_label'  => $a->time_label,
                    'description' => $a->description,
                    'tags'        => is_array($a->tags) ? $a->tags : [],
                    'pro_tip'     => $a->pro_tip,
                    'image'       => $a->image,
                    'image_url'   => $a->image ? asset('storage/' . $a->image) : null,
                ])->values()->toArray(),
            ])->values()->toArray(),

            'categories' => $this->categories->map(fn ($c) => [
                'id'              => $c->id,
                'name'            => $c->name,
                'distance_km'     => $c->distance_km,
                'price_surcharge' => (float) $c->price_surcharge,
                'max_capacity'    => $c->max_capacity,
            ])->values()->toArray(),
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}