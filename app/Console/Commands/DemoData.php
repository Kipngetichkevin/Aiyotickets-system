<?php

namespace App\Console\Commands;

use App\Http\Controllers\TicketViewController;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceActivity;
use App\Models\ExperienceBooking;
use App\Models\ExperienceDay;
use App\Models\ExperiencePackage;
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

            // Lipa mdogo mdogo: switched on for the upcoming event, and one plan half paid by the buyer
            $next->update(['allow_instalments' => true, 'instalment_deposit_percent' => 30, 'instalment_due_days' => 7]);
            $plan = Order::forceCreate([
                'order_no' => 'ORD-DEMOP' . strtoupper(Str::random(5)), 'uuid' => (string) Str::uuid(), 'qty' => 2,
                'total_amount' => 2000, 'status' => 'partial', 'initiator' => $buyer->name, 'email' => $buyer->email,
                'event_id' => $next->id, 'transaction_code' => 'DEMORCPT02', 'user_id' => $buyer->id,
                'payment_plan' => 'instalments', 'seller_id' => $merchant->id, 'deposit_amount' => 600,
                'amount_paid' => 1000, 'balance_due' => 1000, 'due_date' => $next->date->copy()->subDays(7),
            ]);
            for ($i = 1; $i <= 2; $i++) {
                Attendee::forceCreate([
                    'ticket_id' => $regular->id, 'order_id' => $plan->id, 'user_id' => $buyer->id,
                    'name' => $buyer->name . ($i > 1 ? " ({$i})" : ''), 'email' => $buyer->email, 'phone' => '254700000000',
                    'qty' => 1, 'total_price' => 1000, 'payment_status' => 'partial', 'status' => 'active', 'qr_code' => null,
                ]);
            }

            // A complete 3-day experience: photos, itinerary, packages, instalments and promo codes
            $getaway = $this->dianiGetaway($merchant);

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
            $this->line("  Instalment experience: " . url('/experience/' . $getaway->slug));
        });

        return self::SUCCESS;
    }

    /** "[DEMO] Diani Beach Getaway": a full 3-day coastal trip (photos from Unsplash, free licence). */
    private function dianiGetaway(User $merchant): Experience
    {
        $img   = fn (string $name) => "experiences/demo-diani/{$name}.jpg";
        $start = now()->addDays(40)->setTime(7, 0);

        $exp = Experience::forceCreate([
            'name'              => '[DEMO] Diani Beach Getaway',
            'user_id'           => $merchant->id,
            'merchant_id'       => \App\Models\Merchant::where('user_id', $merchant->id)->value('id'),
            'status'            => 'active',
            'featured'          => true,
            'location'          => 'Diani Beach, Kwale County',
            'short_description' => '3 days of white sand, dhow sailing, snorkelling and Swahili seafood on Kenya\'s south coast.',
            'description'       => "Swap the city for the Indian Ocean. Three relaxed but full days on Diani — regularly voted one of Africa's best beaches — with everything planned: travel, stays, meals and the best of the south coast.\n\n"
                . "Sail a traditional dhow to the Kisite-Mpunguti Marine Park to snorkel coral gardens (dolphins are often spotted on the way), feast on fresh seafood on Wasini Island, meet Diani's rare colobus monkeys, and end each day with the sun setting over the palms.\n\n"
                . "Pick the package that fits your budget — every package joins the same itinerary. Pay in full, or hold your place with a 30% deposit and pay the rest bit by bit with lipa mdogo mdogo.\n\n"
                . "Good to know: bring reef-safe sunscreen, a hat and water shoes; snorkel gear and life jackets are provided. Children 12+ welcome.",
            'image'             => $img('cover'),
            'banner'            => $img('banner'),
            'poster_image'      => $img('sunset'),
            'gallery'           => [$img('huts'), $img('sail'), $img('pool'), $img('palms-hill'), $img('sunset'), $img('boat')],
            'start_date'        => $start,
            'end_date'          => $start->copy()->addDays(2)->setTime(22, 30),
            'total_capacity'    => 30,
            'allow_instalments' => true, 'instalment_deposit_percent' => 30, 'instalment_due_days' => 7,
        ]);

        $days = [
            ['Arrival & Swahili welcome', 'Travel down to the coast, settle in and meet the group over a sunset dinner on the sand.', [
                ['07:00', 'Depart Nairobi', 'Board the Madaraka Express (SGR) at Nairobi Terminus — Luxe guests fly to Ukunda. Breakfast pack included.', ['Travel', 'SGR'], 'Arrive 30 minutes early: ticket and luggage checks at the terminus take time.', null],
                ['13:30', 'Transfer & check-in', 'Air-conditioned shuttle from Mombasa Terminus (or Ukunda airstrip) to Diani. Welcome drinks and room check-in.', ['Transfer', 'Check-in'], null, 'sand'],
                ['15:00', 'Pool & beach time', 'Unwind by the pool or take your first swim in the warm, shallow lagoon.', ['Relax', 'Swimming'], 'Low tide reveals rock pools full of starfish — ask the guide for tide times.', 'pool'],
                ['18:30', 'Sunset welcome dinner', 'Barbecue dinner on the beach with live Swahili music as the sun sets behind the palms.', ['Dinner', 'Music'], null, 'sunset'],
            ]],
            ['Ocean day: Kisite-Mpunguti Marine Park', 'The highlight: a full day at sea by traditional dhow, with snorkelling, dolphins and a seafood feast.', [
                ['07:30', 'Breakfast', 'Hearty breakfast at the hotel before the drive south to Shimoni jetty.', ['Meal'], null, null],
                ['08:30', 'Dhow sail & snorkelling', 'Sail a traditional dhow into Kisite-Mpunguti Marine Park. Snorkel the coral gardens with a guide — dolphins are often seen on the crossing.', ['Snorkelling', 'Dhow', 'Marine park'], 'Wear reef-safe sunscreen and a rash vest: the equatorial sun is strong on the water.', 'sail'],
                ['13:00', 'Seafood lunch on Wasini Island', 'Grilled fish, crab and coconut rice at a family-run restaurant on Wasini Island, then a short walk through the coral gardens.', ['Lunch', 'Seafood'], 'Not a seafood fan? Tell us when booking — a vegetarian Swahili option is always available.', 'boat'],
                ['16:30', 'Free afternoon', 'Back at the beach: nap in a banda, book a massage, or try paddle-boarding.', ['Free time'], null, 'huts'],
                ['19:30', 'Bonfire & taarab night', 'Dinner followed by a bonfire on the beach with a live taarab band.', ['Dinner', 'Culture'], null, null],
            ]],
            ['Colobus, coast walks & homeward', 'A gentle last morning with Diani\'s wildlife before travelling home.', [
                ['06:30', 'Sunrise beach walk', 'Walk the empty beach as the sun rises over the reef — the best light of the day for photos.', ['Walk', 'Photography'], 'This is the coolest part of the day: perfect for a last swim.', 'banner'],
                ['09:00', 'Colobus Conservation visit', 'Guided visit to the Colobus Conservation centre to meet the rare Angolan colobus monkeys and learn about the "colobridges" that keep them safe.', ['Wildlife', 'Conservation'], 'Keep food out of sight — the vervet monkeys are bold!', 'palms-hill'],
                ['12:00', 'Check-out & beach lunch', 'Final lunch under the makuti roof before checking out.', ['Lunch'], null, null],
                ['14:00', 'Transfer to Mombasa', 'Shuttle to Mombasa Terminus for the afternoon SGR (Luxe: flight from Ukunda).', ['Transfer'], null, null],
                ['22:30', 'Arrive Nairobi', 'Arrive back at Nairobi Terminus — tired, tanned and already planning the next trip.', ['Travel'], null, null],
            ]],
        ];

        foreach ($days as $d => [$title, $summary, $activities]) {
            $day = ExperienceDay::forceCreate([
                'experience_id' => $exp->id, 'day_number' => $d + 1, 'date' => $start->copy()->addDays($d)->toDateString(),
                'title' => $title, 'description' => $summary, 'sort_order' => $d + 1,
            ]);
            foreach ($activities as $a => [$time, $name, $text, $tags, $tip, $photo]) {
                ExperienceActivity::forceCreate([
                    'experience_id' => $exp->id, 'day_id' => $day->id, 'time_label' => $time, 'title' => $name,
                    'description' => $text, 'tags' => $tags, 'pro_tip' => $tip, 'image' => $photo ? $img($photo) : null, 'sort_order' => $a + 1,
                ]);
            }
        }

        $packages = [
            ['Explorer', 1, 'Budget-smart beach escape', 18500, '#10b981', 'SGR economy class (return)', 'Garden-view guest house, 5 min walk to the beach', 'Shared room (2–3 guests)', 15,
                'Everything you need for a great coast trip at the best price.',
                ['Return SGR economy tickets', 'Mombasa–Diani transfers', '2 nights accommodation', 'Daily breakfast & 2 dinners', 'Kisite dhow trip, snorkel gear & park fees', 'Wasini seafood lunch', 'Colobus Conservation visit']],
            ['Comfort', 2, 'Most popular — beachfront & first class', 32000, '#465fff', 'SGR first class (return)', '3★ beachfront resort with pool', 'Twin or double room (2 guests)', 12,
                'Wake up on the beach: beachfront resort, first-class train and all meals.',
                ['Return SGR first class tickets', 'Mombasa–Diani transfers', '2 nights beachfront resort', 'All meals (full board)', 'Kisite dhow trip, snorkel gear & park fees', 'Wasini seafood lunch', 'Colobus Conservation visit', 'Sunset welcome dinner']],
            ['Luxe', 3, 'Fly in, ocean-view suite, spa', 58000, '#f59e0b', 'Return flights Nairobi–Ukunda', '5★ ocean-view suite', 'Private room', 6,
                'The full treat: flights, a 5★ suite and a spa massage.',
                ['Return flights Nairobi–Ukunda', 'Private airport transfers', '2 nights 5★ ocean-view suite', 'All meals & selected drinks', 'Private dhow cabin & snorkel guide', 'Wasini seafood lunch', 'Colobus Conservation visit', '60-minute spa massage']],
        ];
        foreach ($packages as $i => [$name, $tier, $tagline, $price, $color, $transport, $stay, $sharing, $cap, $desc, $includes]) {
            ExperiencePackage::forceCreate([
                'experience_id' => $exp->id, 'name' => $name, 'tier' => $tier, 'tagline' => $tagline, 'description' => $desc,
                'price' => $price, 'currency' => 'KES', 'badge_color' => $color, 'transport_type' => $transport,
                'accommodation_type' => $stay, 'accommodation_sharing' => $sharing, 'includes' => $includes,
                'max_capacity' => $cap, 'sort_order' => $i + 1, 'is_active' => true,
            ]);
        }

        PromoCode::create(['user_id' => $merchant->id, 'experience_id' => $exp->id, 'code' => 'DIANI15', 'type' => 'percent', 'value' => 15]);
        PromoCode::create(['user_id' => $merchant->id, 'experience_id' => $exp->id, 'code' => 'BEACHGANG4', 'type' => 'percent', 'value' => 20, 'min_tickets' => 4]);

        return $exp;
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

            $experienceIds = Experience::where('name', 'like', '[DEMO]%')->pluck('id');
            $expOrderIds   = ExperienceBooking::whereIn('experience_id', $experienceIds)->whereNotNull('order_id')->pluck('order_id');
            ExperienceBooking::whereIn('experience_id', $experienceIds)->delete();
            Order::whereIn('id', $expOrderIds)->delete();
            ExperienceActivity::whereIn('experience_id', $experienceIds)->delete();
            ExperienceDay::whereIn('experience_id', $experienceIds)->delete();
            ExperiencePackage::whereIn('experience_id', $experienceIds)->delete();
            PromoCode::whereIn('experience_id', $experienceIds)->delete();
            Experience::whereIn('id', $experienceIds)->delete();
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
