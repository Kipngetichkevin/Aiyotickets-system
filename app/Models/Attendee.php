<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Attendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'name',
        'phone',
        'email',
        'qr_code',
        'status',
        'checked_in_at',
        'checked_in_by',
        'device_info',
        'uuid',
        'created_by',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'device_info'   => 'array',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Auto-generate UUID when creating a new attendee.
     */
    protected static function booted(): void
    {
        static::creating(function ($attendee) {
            if (empty($attendee->uuid)) {
                $attendee->uuid = (string) Str::uuid();
            }
        });
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    /**
     * The ticket this attendee bought.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    /**
     * The event (through the ticket).
     */
    public function event()
    {
        return $this->hasOneThrough(
            Event::class,
            Ticket::class,
            'id',         // tickets.id
            'id',         // events.id
            'ticket_id',  // attendees.ticket_id
            'event_id'    // tickets.event_id
        );
    }

    /**
     * The user account that owns this attendee record.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Staff member who checked this attendee in.
     */
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Who created this record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ============================================================
    // HELPERS
    // ============================================================

    /**
     * Has this attendee been checked in?
     */
    public function isCheckedIn(): bool
    {
        return !is_null($this->checked_in_at);
    }

    /**
     * Mark this attendee as checked in.
     */
    public function markCheckedIn(?int $userId = null, ?array $deviceInfo = null): void
    {
        $this->update([
            'status'        => 'used',
            'checked_in_at' => now(),
            'checked_in_by' => $userId ?? auth()->id(),
            'device_info'   => $deviceInfo ?? $this->device_info,
        ]);
    }
}