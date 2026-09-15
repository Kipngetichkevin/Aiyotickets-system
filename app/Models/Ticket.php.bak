<?php

namespace App\Models;

use App\Models\Attendee;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Ticket extends Model
{
    protected $table = 'tickets';

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
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
            do {
                $ticketNo = 'TKT-' . strtoupper(Str::random(10));
            } while (self::where('ticket_no', $ticketNo)->exists());

            $ticket->ticket_no = $ticketNo;
        });
    }

    // ============================================================
    // RELATIONSHIPS
    // ============================================================

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class, 'ticket_id');
    }

    // ============================================================
    // QUANTITY HELPERS
    // ============================================================

    /**
     * Number of tickets already sold (counts each attendee row as one ticket).
     * Uses withCount('attendees') if available to avoid N+1.
     */
    public function getSoldQuantityAttribute(): int
    {
        return $this->attendees_count ?? $this->attendees()->count();
    }

    /**
     * Number of tickets still available.
     */
    public function getAvailableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - (int) $this->sold_quantity);
    }

    /**
     * Are all tickets sold?
     */
    public function isSoldOut(): bool
    {
        return (int) $this->quantity > 0 && $this->available_quantity <= 0;
    }

    // ============================================================
    // SALES WINDOW HELPERS
    // ============================================================

    /**
     * Is the current moment within the sales window?
     */
    public function isWithinSalesWindow(): bool
    {
        $now = now();

        if ($this->start_date && $now->lt($this->start_date)) {
            return false;   // Sales haven't started yet
        }

        if ($this->end_date && $now->gt($this->end_date)) {
            return false;   // Sales have ended
        }

        return true;
    }

    /**
     * Sales will open in the future.
     */
    public function isComingSoon(): bool
    {
        return $this->start_date && now()->lt($this->start_date);
    }

    /**
     * Sales window has passed.
     */
    public function isEnded(): bool
    {
        return $this->end_date && now()->gt($this->end_date);
    }

    // ============================================================
    // SALE STATUS — single source of truth
    // ============================================================

    /**
     * Returns one of:
     *  - 'on_sale'      : within window AND has stock
     *  - 'coming_soon'  : start_date in the future
     *  - 'ended'        : end_date has passed
     *  - 'sold_out'     : out of stock
     *  - 'unavailable'  : any other reason
     */
    public function getSaleStatusAttribute(): string
    {
        if ($this->isEnded()) {
            return 'ended';
        }

        if ($this->isSoldOut()) {
            return 'sold_out';
        }

        if ($this->isComingSoon()) {
            return 'coming_soon';
        }

        if (!$this->isWithinSalesWindow()) {
            return 'unavailable';
        }

        return 'on_sale';
    }

    /**
     * Human-readable label.
     */
    public function getSaleStatusLabelAttribute(): string
    {
        return match ($this->sale_status) {
            'on_sale'     => 'On Sale',
            'coming_soon' => 'Coming Soon',
            'ended'       => 'Sales Ended',
            'sold_out'    => 'Sold Out',
            default       => 'Unavailable',
        };
    }

    /**
     * Badge colors: ['bg' => '#...', 'fg' => '#...']
     */
    public function getSaleStatusColorAttribute(): array
    {
        return match ($this->sale_status) {
            'on_sale'     => ['bg' => '#d1fae5', 'fg' => '#065f46'],
            'coming_soon' => ['bg' => '#dbeafe', 'fg' => '#1e40af'],
            'ended'       => ['bg' => '#f3f4f6', 'fg' => '#6b7280'],
            'sold_out'    => ['bg' => '#fee2e2', 'fg' => '#991b1b'],
            default       => ['bg' => '#f3f4f6', 'fg' => '#6b7280'],
        };
    }

    /**
     * A short message explaining why the ticket can't be bought.
     */
    public function getSaleStatusMessageAttribute(): string
    {
        return match ($this->sale_status) {
            'coming_soon' => 'Sales open on ' . $this->start_date->format('d M Y, g:i A'),
            'ended'       => 'Sales closed on ' . $this->end_date->format('d M Y, g:i A'),
            'sold_out'    => 'This ticket type is sold out',
            'unavailable' => 'Not currently available',
            default       => '',
        };
    }

    // ============================================================
    // SCOPES
    // ============================================================

    /**
     * Only tickets within their sales window (start <= now <= end).
     */
    public function scopeOnSale($query)
    {
        $now = now();

        return $query
            ->where(function ($q) use ($now) {
                $q->whereNull('start_date')->orWhere('start_date', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $now);
            });
    }

    /**
     * Only tickets that still have stock.
     */
    public function scopeInStock($query)
    {
        return $query->whereRaw('quantity > (SELECT COUNT(*) FROM attendees WHERE attendees.ticket_id = tickets.id)');
    }
}