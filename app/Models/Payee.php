<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Payee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'category',
        'type',
        'name',
        'point_of_contact',
        'phone',
        'email',
        'address',
        'id_number',
        'kra_pin',
        'mpesa_number',
        'bank_name',
        'bank_account',
        'bank_branch',
        'notes',
        'status',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Auto-generate UUID when creating a new payee.
     */
    protected static function booted(): void
    {
        static::creating(function ($payee) {
            if (empty($payee->uuid)) {
                $payee->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Services this payee has provided.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Payouts sent to this payee.
     */
    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    /**
     * The user account linked to this payee (if any).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Who created this payee record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: only active payees.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: filter by category (supplier, personnel, etc.).
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Total amount paid to this payee so far.
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payouts()->where('status', 'paid')->sum('amount');
    }

    /**
     * Total amount owed to this payee (services delivered but not fully paid).
     */
    public function getTotalOwedAttribute(): float
    {
        $delivered = (float) $this->services()->where('status', 'delivered')->sum('total_amount');
        $paid = $this->total_paid;

        return max(0, $delivered - $paid);
    }

    /**
     * Use UUID for route model binding (so URLs don't expose numeric IDs).
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}