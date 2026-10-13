<?php

namespace App\Models;

use App\Models\Merchant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'price' => 'decimal:2',
    ];

    protected $fillable = [
        'ticket_no',
        'merchant_id',
        'event_id',
        'name',
        'currency',
        'price',
        'quantity',
        'start_date',
        'end_date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            // Generate unique ticket number
            do {
                $ticketNo = 'TKT-' . strtoupper(Str::random(10));
            } while (self::where('ticket_no', $ticketNo)->exists());

            $ticket->ticket_no = $ticketNo;
            
            // Set default currency if not provided
            if (empty($ticket->currency)) {
                $ticket->currency = 'KES';
            }
        });
    }

    /**
     * Define the relationship with the Event model.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Define the relationship with the Merchant model.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the attendees for this ticket.
     */
    public function attendees()
    {
        return $this->hasMany(Attendee::class);
    }

    /**
     * Get the total sold quantity for this ticket.
     */
    public function getSoldCountAttribute()
    {
        // Paid tickets only; computed once per model (available/is_locked reuse it)
        return $this->soldCountCache ??= (int) $this->attendees()->where('payment_status', 'paid')->sum('qty');
    }

    private ?int $soldCountCache = null;

    /**
     * Get the available quantity for this ticket.
     */
    public function getAvailableAttribute()
    {
        return $this->quantity - $this->sold_count;
    }

    /**
     * Check if ticket is locked (has sales).
     */
    public function getIsLockedAttribute()
    {
        return $this->sold_count > 0;
    }
}