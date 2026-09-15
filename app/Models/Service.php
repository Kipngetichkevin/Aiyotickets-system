<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'event_id',
        'payee_id',
        'description',
        'category',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'contracted_at',
        'delivered_at',
        'notes',
        'invoice_number',
        'invoice_date',
        'invoice_path',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'contracted_at' => 'date',
        'delivered_at' => 'date',
        'invoice_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($service) {
            if (empty($service->uuid)) {
                $service->uuid = (string) Str::uuid();
            }
        });

        static::saving(function ($service) {
            // Always recalculate total_amount = quantity × unit_price
            $service->total_amount = round(
                (float) $service->quantity * (float) $service->unit_price,
                2
            );
        });
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function payee()
    {
        return $this->belongsTo(Payee::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payouts()->where('status', 'paid')->sum('amount');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, (float) $this->total_amount - $this->total_paid);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}