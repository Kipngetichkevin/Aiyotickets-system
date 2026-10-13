<?php

namespace App\Console\Commands;

use App\Models\Ticket;
use Illuminate\Console\Command;

/**
 * Tickets of upcoming or running events that can't be bought right now because of
 * their sales window — fix these before setting ENFORCE_SALE_DATES=true.
 */
class TicketSaleDates extends Command
{
    protected $signature = 'tickets:sale-dates';

    protected $description = 'List tickets of current events whose sales window is closed';

    public function handle(): int
    {
        $rows = Ticket::with('event.merchant')
            ->whereHas('event', fn ($q) => $q->where('status', 'active')->where('end_date', '>=', now()))
            ->get()
            ->filter(fn (Ticket $t) => $t->saleStatus() !== 'on_sale')
            ->map(fn (Ticket $t) => [
                $t->id,
                $t->event->name,
                $t->name,
                $t->event->merchant?->name ?? '—',
                optional($t->start_date)->format('Y-m-d H:i') ?? '—',
                optional($t->end_date)->format('Y-m-d H:i') ?? '—',
                $t->saleStatus() === 'upcoming' ? 'not open yet' : 'ENDED',
            ]);

        if ($rows->isEmpty()) {
            $this->info('All tickets of current events are on sale. Safe to set ENFORCE_SALE_DATES=true.');
            return self::SUCCESS;
        }

        $this->warn($rows->count() . ' ticket(s) would not be sellable with ENFORCE_SALE_DATES=true:');
        $this->table(['Ticket #', 'Event', 'Ticket', 'Merchant', 'Sales start', 'Sales end', 'Status'], $rows->values()->all());
        $this->line('Update their sales dates in the event\'s ticket editor, then run this again.');

        return self::SUCCESS;
    }
}
