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
        // Paid tickets plus places held by instalment plans; computed once per model (available/is_locked reuse it)
        return $this->soldCountCache ??= (int) $this->attendees()->whereIn('payment_status', ['paid', 'partial'])->sum('qty');
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

    /**
     * Where the ticket is in its sale window (start_date/end_date = sales open/close):
     * 'upcoming', 'ended' or 'on_sale'.
     */
    public function saleStatus(): string
    {
        if ($this->start_date && now()->lt($this->start_date)) {
            return 'upcoming';
        }
        if ($this->end_date && now()->gt($this->end_date)) {
            return 'ended';
        }
        return 'on_sale';
    }

    /** Whether the ticket can be bought now (always true unless ENFORCE_SALE_DATES is on). */
    public function isPurchasable(): bool
    {
        return !config('aiyo.enforce_sale_dates') || $this->saleStatus() === 'on_sale';
    }

    /** Message for a ticket that can't be bought now. */
    public function notOnSaleMessage(): string
    {
        return $this->saleStatus() === 'upcoming'
            ? "{$this->name} goes on sale on " . $this->start_date->format('d M Y, H:i') . '.'
            : "Sales for {$this->name} have ended.";
    }
}