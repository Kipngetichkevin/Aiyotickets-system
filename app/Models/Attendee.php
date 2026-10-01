<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attendee extends Model
{
    protected $table = 'attendees';

    protected $fillable = [
        'user_id',
        'ticket_id',
        'name',
        'gender',
        'phone',
        'email',
        'qr_code',
        'order_id',
        'payment_status',
        'transaction_code',
        'payment_mode',
        'payment_mode_id',
        'qty',
        'total_price',
        'status',
        'uuid',
        'created_by',
        'checked_in',
        'checked_in_at',
        'checked_in_by',
    ];

    protected $casts = [
        'total_price'   => 'decimal:2',
        'checked_in'    => 'boolean',
        'checked_in_at' => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

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

    public function user()      { return $this->belongsTo(User::class); }
    public function order()     { return $this->belongsTo(Order::class); }
    public function ticket()    { return $this->belongsTo(Ticket::class); }
    public function paymentMode() { return $this->belongsTo(PaymentMode::class, 'payment_mode_id'); }
    public function checkedInBy() { return $this->belongsTo(User::class, 'checked_in_by'); }

    public function event()
    {
        return $this->hasOneThrough(Event::class, Ticket::class, 'id', 'id', 'ticket_id', 'event_id');
    }

    public function scopePaid($q)    { return $q->where('payment_status', 'paid'); }
    public function scopePending($q) { return $q->where('payment_status', 'pending'); }
    public function scopeActive($q)  { return $q->where('status', 'active'); }

    public function generateQrCode()
    {
        if (empty($this->qr_code)) {
            $this->update([
                'qr_code' => 'QR-' . ($this->order->order_no ?? 'ORD') . '-' . strtoupper(Str::random(6))
            ]);
        }
        return $this->qr_code;
    }

    public function getFullNameWithTicketAttribute()
    {
        return $this->name . ' (' . ($this->ticket->name ?? 'Ticket') . ')';
    }
}