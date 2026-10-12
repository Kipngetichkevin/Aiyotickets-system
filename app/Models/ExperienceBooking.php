<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ExperienceBooking extends Model
{
    use HasFactory;

    protected $table = 'experience_bookings';

    protected $fillable = [
        'uuid', 'user_id', 'experience_id', 'package_id', 'category_id', 'order_id',
        'name', 'gender', 'phone', 'email',
        'qty', 'package_price', 'category_surcharge', 'total_price',
        'payment_status', 'payment_mode', 'payment_mode_id', 'transaction_code',
        'qr_code', 'status',
        'checked_in', 'checked_in_at', 'checked_in_by', 'created_by',
    ];

    protected $casts = [
        'package_price'      => 'decimal:2',
        'category_surcharge' => 'decimal:2',
        'total_price'        => 'decimal:2',
        'qty'                => 'integer',
        'checked_in'         => 'boolean',
        'checked_in_at'      => 'datetime',
        'created_at'         => 'datetime',
        'updated_at'         => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($b) {
            if (empty($b->uuid)) $b->uuid = (string) Str::uuid();
            if (empty($b->qr_code)) $b->qr_code = 'EXP-' . strtoupper(Str::random(12));
        });
    }

    public function user()       { return $this->belongsTo(User::class); }
    public function experience() { return $this->belongsTo(Experience::class); }
    public function package()    { return $this->belongsTo(ExperiencePackage::class, 'package_id'); }
    public function category()   { return $this->belongsTo(ExperienceCategory::class, 'category_id'); }
    public function order()      { return $this->belongsTo(Order::class); }

    public function getRouteKeyName(): string { return 'uuid'; }
}