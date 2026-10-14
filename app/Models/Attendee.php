<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attendee extends Model
{
    protected $table = 'attendees';

    protected $fillable = [
        'ticket_id',
        'name',
        'gender',
        'phone',
        'email',
        'qr_code',
        'order_id',
        'user_id',
        'payment_status',
        'transaction_code',
        'payment_mode',
        'payment_mode_id',
        'qty',
        'total_price',
        'status',
        'uuid',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'checked_in' => 'boolean',
        'checked_in_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Boot method to auto-generate UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($attendee) {
            if (empty($attendee->uuid)) {
                $attendee->uuid = (string) Str::uuid();
            }
            if (empty($attendee->status)) {
                $attendee->status = 'active';
            }
            if (empty($attendee->payment_status)) {
                $attendee->payment_status = 'pending';
            }
        });
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function event()
    {
        return $this->hasOneThrough(Event::class, Ticket::class, 'id', 'id', 'ticket_id', 'event_id');
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class, 'payment_mode_id');
    }

    // Scope for paid attendees
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    // Scope for pending attendees
    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    // Scope for active attendees
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // The attendee's gate code; created if missing
    public function ensureQrCode(): string
    {
        if (empty($this->qr_code)) {
            $this->update([
                'qr_code' => 'QR-' . ($this->order?->order_no ?? 'A' . $this->id) . '-' . strtoupper(Str::random(6)),
            ]);
        }
        return $this->qr_code;
    }

    // Get full name with ticket type
    public function getFullNameWithTicketAttribute()
    {
        return $this->name . ' (' . ($this->ticket->name ?? 'Ticket') . ')';
    }
}