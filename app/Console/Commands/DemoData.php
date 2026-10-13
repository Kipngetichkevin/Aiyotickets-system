<?php

namespace App\Console\Commands;

use App\Http\Controllers\TicketViewController;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Payout;
use App\Models\PromoCode;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Local demo records so every feature has something to show. Never run in production.
 * Everything created is named "[DEMO] …" (events) or DEMO… (codes, references) and
 * `--remove` deletes it again.
 */
class DemoData extends Command
{
    protected $signature = 'demo:data
                            {--merchant=kipkorir.soi@gmail.com : Merchant who owns the demo events}
                            {--buyer=kevin@gmail.com : Client who owns the demo tickets}
                            {--remove : Delete the demo records instead}';

    protected $description = 'Create (or remove) demo events, sales, promo codes and payouts for local testing';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('Refusing to run in production.');
            return self::FAILURE;
        }

        if ($this->option('remove')) {
            return $this->remove();
        }

        $merchant = User::where('email', $this->option('merchant'))->first();
        $buyer    = User::where('email', $this->option('buyer'))->first();
        if (!$merchant || !$buyer) {
            $this->error('Merchant or buyer account not found.');
            return self::FAILURE;
        }

        $this->remove(quiet: true);
        $merchantId = Merchant::firstOrCreate(['user_id' => $merchant->id], ['name' => $merchant->name])->id;

        DB::transaction(function () use ($merchant, $buyer, $merchantId) {
            $event = fn (array $a) => Event::forceCreate($a + [
                'event_type_id' => DB::table('event_types')->value('id'),
                'user_id' => $merchant->id, 'merchant_id' => $merchantId,
                'location' => 'Nairobi', 'currency' => 'KES', 'status' => 'active',
                'description' => '<p>Demo event for testing. Safe to delete.</p>',
            ]);

            // Ended event with sales: Reports + withdrawable payout balance
            $past = $event(['name' => '[DEMO] Sunset Jazz Night', 'date' => now()->subDays(5), 'end_date' => now()->subDays(5)->addHours(5)]);
            $pastTicket = Ticket::forceCreate(['event_id' => $past->id, 'merchant_id' => $merchantId, 'name' => 'Regular', 'price' => 1500, 'quantity' => 100, 'currency' => 'KES']);
            for ($i = 1; $i <= 6; $i++) {
                Attendee::forceCreate([
                    'ticket_id' => $pastTicket->id, 'name' => "Demo Guest {$i}", 'email' => "guest{$i}@example.com",
                    'phone' => '254700000000', 'qty' => 1, 'total_price' => 1500, 'payment_status' => 'paid', 'status' => 'active',
                    'qr_code' => 'QR-DEMO-PAST-' . strtoupper(Str::random(6)),
                    'checked_in' => $i <= 3, 'checked_in_at' => $i <= 3 ? now()->subDays(5) : null,
                    'created_at' => now()->subDays(20 - $i), 'updated_at' => now()->subDays(20 - $i),
                ]);
            }

            // Upcoming event: sale-window labels, buyer's tickets, scanner
            $next = $event(['name' => '[DEMO] Nairobi Comedy Live', 'date' => now()->addDays(10), 'end_date' => now()->addDays(10)->addHours(4)]);
            $regular = Ticket::forceCreate(['event_id' => $next->id, 'merchant_id' => $merchantId, 'name' => 'Regular', 'price' => 1000, 'quantity' => 200, 'currency' => 'KES', 'end_date' => now()->addDays(5)]);
            Ticket::forceCreate(['event_id' => $next->id, 'merchant_id' => $merchantId, 'name' => 'VIP', 'price' => 2500, 'quantity' => 50, 'currency' => 'KES', 'start_date' => now()->addDay()]);

            $order = Order::forceCreate([
                'order_no' => 'ORD-DEMO' . strtoupper(Str::random(6)), 'uuid' => (string) Str::uuid(), 'qty' => 2,
                'total_amount' => 2000, 'status' => 'paid', 'initiator' => $buyer->name, 'email' => $buyer->email,
                'event_id' => $next->id, 'transaction_code' => 'DEMORCPT01', 'user_id' => $buyer->id,
            ]);
            for ($i = 1; $i <= 2; $i++) {
                Attendee::forceCreate([
                    'ticket_id' => $regular->id, 'order_id' => $order->id, 'user_id' => $buyer->id,
                    'name' => $buyer->name . ($i > 1 ? " ({$i})" : ''), 'email' => $buyer->email, 'phone' => '254700000000',
                    'qty' => 1, 'total_price' => 1000, 'payment_status' => 'paid', 'status' => 'active',
                    'transaction_code' => 'DEMORCPT01', 'qr_code' => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                ]);
            }

            PromoCode::create(['user_id' => $merchant->id, 'code' => 'DEMO10', 'type' => 'percent', 'value' => 10]);
            PromoCode::create(['user_id' => $merchant->id, 'event_id' => $next->id, 'code' => 'GROUP4', 'type' => 'percent', 'value' => 15, 'min_tickets' => 4]);

            Payout::create(['user_id' => $merchant->id, 'amount' => 2000, 'method' => 'mpesa', 'destination' => '0712345678',
                'status' => Payout::PAID, 'reference' => 'DEMO-QJK123', 'processed_at' => now()->subDay()]);
            Payout::create(['user_id' => $merchant->id, 'amount' => 1000, 'method' => 'mpesa', 'destination' => '0712345678',
                'status' => Payout::REQUESTED]);

            $this->info('Demo data created.');
            $this->line("  Upcoming event page:   " . url("/event/{$next->id}"));
            $this->line("  Buyer's ticket link:   " . TicketViewController::linkFor($order));
            $this->line("  Gate code to scan:     " . Attendee::where('order_id', $order->id)->value('qr_code'));
        });

        return self::SUCCESS;
    }

    private function remove(bool $quiet = false): int
    {
        $eventIds  = Event::where('name', 'like', '[DEMO]%')->pluck('id');
        $ticketIds = Ticket::whereIn('event_id', $eventIds)->pluck('id');

        DB::transaction(function () use ($eventIds, $ticketIds) {
            $orderIds = Attendee::whereIn('ticket_id', $ticketIds)->whereNotNull('order_id')->pluck('order_id')->unique();
            Attendee::whereIn('ticket_id', $ticketIds)->delete();
            Order::whereIn('id', $orderIds)->delete();
            Ticket::whereIn('id', $ticketIds)->delete();
            Event::whereIn('id', $eventIds)->delete();
            PromoCode::whereIn('code', ['DEMO10', 'GROUP4'])->delete();
            Payout::where('destination', '0712345678')->where(fn ($q) => $q->where('reference', 'DEMO-QJK123')->orWhereNull('reference'))
                ->whereIn('amount', [2000, 1000])->delete();
        });

        if (!$quiet) {
            $this->info('Demo data removed.');
        }

        return self::SUCCESS;
    }
}
