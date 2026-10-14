<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Accounts must verify their email: guest purchases are linked to the account
 * by email, so an unverified account must never see them.
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'bio',
        'avatar',
        'country',
        'city',
        'postal_code',
        'tax_id',
        'social',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'social' => 'array',
    ];

    /**
     * Get the events for the user.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /** Merchants and admins use the back office (sidebar, dashboard); clients use the public site. */
    public function isStaff(): bool
    {
        return $this->hasAnyRole(['admin', 'merchant']);
    }

    /**
     * Get the merchant for the user.
     */
    public function merchant()
    {
        return $this->hasOne(Merchant::class);
    }

    /**
     * Get the orders for the user.
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get social links with proper URLs
     */
    public function getSocialLinksAttribute()
    {
        $social = $this->social ?: [];
        
        $links = [];
        
        if (!empty($social['facebook'])) {
            $links['facebook'] = $this->getSocialUrl($social['facebook'], 'https://facebook.com/');
        }
        
        if (!empty($social['twitter'])) {
            $links['twitter'] = $this->getSocialUrl($social['twitter'], 'https://twitter.com/');
        }
        
        if (!empty($social['instagram'])) {
            $links['instagram'] = $this->getSocialUrl($social['instagram'], 'https://instagram.com/');
        }
        
        if (!empty($social['linkedin'])) {
            $links['linkedin'] = $this->getSocialUrl($social['linkedin'], 'https://linkedin.com/in/');
        }
        
        return $links;
    }

    /**
     * Get social usernames
     */
    public function getSocialUsernamesAttribute()
    {
        $social = $this->social ?: [];
        $usernames = [];
        
        foreach ($social as $platform => $value) {
            $usernames[$platform] = $this->extractUsername($value);
        }
        
        return $usernames;
    }

    /**
     * Helper to extract username from URL
     */
    private function extractUsername($url)
    {
        if (empty($url)) {
            return '';
        }
        
        // If it's already a username (no dots, no slashes), return it
        if (!str_contains($url, '.') && !str_contains($url, '/')) {
            return $url;
        }
        
        // Remove protocol and domain
        $patterns = [
            'facebook' => [
                '/^https?:\/\/(www\.)?facebook\.com\//',
                '/^https?:\/\/fb\.com\//'
            ],
            'twitter' => [
                '/^https?:\/\/(www\.)?twitter\.com\//',
                '/^https?:\/\/x\.com\//'
            ],
            'instagram' => [
                '/^https?:\/\/(www\.)?instagram\.com\//'
            ],
            'linkedin' => [
                '/^https?:\/\/(www\.)?linkedin\.com\/in\//'
            ]
        ];
        
        // Try to match known patterns
        foreach ($patterns as $platformPatterns) {
            foreach ($platformPatterns as $pattern) {
                if (preg_match($pattern, $url)) {
                    return preg_replace($pattern, '', $url);
                }
            }
        }
        
        // If no match, return as-is
        return $url;
    }

    /**
     * Helper to create full URL from username
     */
    private function getSocialUrl($value, $baseUrl)
    {
        if (empty($value)) {
            return null;
        }
        
        // If it's already a URL, return it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Otherwise, append to base URL
        return rtrim($baseUrl, '/') . '/' . ltrim($value, '/');
    }

    /**
     * Prepare social data for storage
     */
    public function prepareSocialData($data)
    {
        $social = [];
        
        foreach ($data as $platform => $value) {
            if (empty($value)) {
                continue;
            }
            
            // Clean the value
            $value = trim($value);
            
            // If it's a full URL, store as-is
            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $social[$platform] = $value;
            } else {
                // Store as username
                $social[$platform] = $this->cleanUsername($value);
            }
        }
        
        return $social;
    }

    /**
     * Clean username (remove @ symbol, trim)
     */
    private function cleanUsername($username)
    {
        return ltrim(trim($username), '@');
    }
}