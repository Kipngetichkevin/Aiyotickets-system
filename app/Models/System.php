<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class System extends Model
{
    protected $table = 'system';

    protected $fillable = [
        'name',
        'logo',
        'logo_dark',
        'logo_icon',
        'favicon',
        'slogan',
        'timezone',
        'date_format',
        'time_format',
        'currency',
        'currency_symbol',
        'primary_color',
        'secondary_color',
        'contact_email',
        'contact_phone',
        'address',
        'location',
        'meta_description',
        'meta_keywords',
        'maintenance_mode',
        'pagination_limit',
        'custom_css',
        'custom_js',
        'settings',
        'website_pages',
        'social_media',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'pagination_limit' => 'integer',
        'location'         => 'array',
        'settings'         => 'array',
        'website_pages'    => 'array',
        'social_media'     => 'array',
    ];

    /**
     * Return the singleton settings row (creates it if missing).
     */
    public static function settings(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'name'             => 'AiyoTickets',
                'timezone'         => 'Africa/Nairobi',
                'date_format'      => 'd-m-Y',
                'time_format'      => 'H:i:s',
                'currency'         => 'KES',
                'currency_symbol'  => 'KSh',
                'primary_color'    => '#0d6efd',
                'secondary_color'  => '#6c757d',
                'pagination_limit' => 15,
                'maintenance_mode' => 0,
            ]
        );
    }

    /**
     * Logo accessor — full URL or null.
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function getLogoDarkUrlAttribute(): ?string
    {
        return $this->logo_dark ? asset('storage/' . $this->logo_dark) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? asset('storage/' . $this->favicon) : null;
    }
}