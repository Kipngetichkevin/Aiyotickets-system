<?php

namespace App\Models;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'is_merchant', 'google_id', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'is_merchant'       => 'boolean',
    ];

    // ─── Roles ──────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isOrganizer(): bool
    {
        return $this->hasRole('merchant') || (bool) $this->is_merchant;
    }

    public function isMerchant(): bool
    {
        return $this->hasRole('merchant') || (bool) $this->is_merchant;
    }

    public function isBuyer(): bool
    {
        return $this->hasRole('user') && !$this->isAdmin() && !$this->isMerchant();
    }

    // ─── Ownership (as organizer) ───────────────────────────

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function merchants()
    {
        return $this->hasMany(Merchant::class);
    }

    // ─── Purchases (as buyer) ───────────────────────────────

    public function attendees()
    {
        return $this->hasMany(Attendee::class, 'user_id');
    }
}