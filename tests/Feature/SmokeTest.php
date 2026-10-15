<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\Bill;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\FinanceCategory;
use App\Models\GatePass;
use App\Models\Merchant;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\Payee;
use App\Models\Ticket;
use App\Http\Controllers\TicketViewController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Opens every GET page as each kind of user over realistic data (the demo:data set plus
 * finance records, a gate link and a pending organiser) and fails on any server error.
 */
class SmokeTest extends TestCase
{
    /** Routes that aren't pages (downloads, JSON feeds) or need a one-time token. */
    private const SKIP = ['password.reset', 'verification.verify', 'system.clear-cache', 'cart.clear',
        'tickets.generatePDF', 'ticket.pdf', 'experience.booking.pdf'];   // PDFs are checked in their own test

    public function test_no_page_errors_for_any_role(): void
    {
        $merchant = $this->user('merchant', ['email' => 'smoke-merchant@example.com']);
        $buyer    = $this->user('user', ['email' => 'smoke-buyer@example.com']);
        $admin    = $this->user('admin');

        $this->artisan('demo:data', ['--merchant' => $merchant->email, '--buyer' => $buyer->email])->assertSuccessful();

        $event      = Event::where('user_id', $merchant->id)->whereHas('tickets')->latest('date')->firstOrFail();
        $ticket     = Ticket::where('event_id', $event->id)->firstOrFail();
        $attendee   = Attendee::whereIn('ticket_id', Ticket::where('event_id', $event->id)->pluck('id'))->first()
            ?? $this->attendee($ticket, $buyer);
        $experience = Experience::where('user_id', $merchant->id)->firstOrFail();
        $booking    = ExperienceBooking::where('experience_id', $experience->id)->first();
        $order      = Order::where('user_id', $buyer->id)->first();

        $payee = Payee::create(['type' => 'supplier', 'name' => 'Smoke Supplies', 'payment_method' => 'mpesa', 'mpesa_phone' => '0712345678', 'is_active' => true]);
        $bill  = new Bill(['payee_id' => $payee->id, 'category_id' => FinanceCategory::value('id'), 'description' => 'Tent hire',
            'bill_date' => today(), 'due_date' => today()->addWeek(), 'amount' => 5000]);
        $bill->forceFill(['status' => Bill::AWAITING])->save();
        $pass = GatePass::issue(['event_id' => $event->id, 'label' => 'Main gate', 'expires_at' => now()->addDay()], null, $merchant);
        Merchant::create(['user_id' => $this->user()->id, 'name' => 'Pending Promoters'])->forceFill(['status' => 'pending'])->save();
        $stk = MpesaStk::create(['checkout_request_id' => 'ws_CO_SMOKE', 'merchant_request_id' => 'MR', 'user_id' => $buyer->id, 'status' => 'pending', 'amount' => 100]);

        $params = [
            'id' => $event->id, 'event_id' => $event->id, 'event' => $event->id, 'ticket_id' => $ticket->id, 'attendee_id' => $attendee->id,
            'slug' => $experience->slug, 'experience_id' => $experience->id, 'booking' => $booking?->uuid,
            'bill' => $bill->id, 'payee' => $payee->id, 'type' => 'event', 'token' => $pass->token,
            'checkoutRequestId' => $stk->checkout_request_id,
        ];
        $experienceRoutes = ['experiences.bookings', 'experiences.bookings.feed', 'experiences.json'];

        $errors = [];
        foreach (['guest' => null, 'buyer' => $buyer, 'organiser' => $merchant, 'admin' => $admin] as $role => $user) {
            foreach (Route::getRoutes() as $route) {
                $name = $route->getName();
                if (!in_array('GET', $route->methods()) || !$name || in_array($name, self::SKIP)
                    || str_starts_with($route->uri(), '_') || str_starts_with($name, 'sanctum') || str_starts_with($name, 'storage')) {
                    continue;
                }

                $needed = $route->parameterNames();
                $values = [];
                foreach ($needed as $p) {
                    $values[$p] = $p === 'id' && in_array($name, $experienceRoutes) ? $experience->id : ($params[$p] ?? null);
                }
                if (in_array(null, $values, true)) {
                    continue;   // a parameter we have no data for (e.g. signed links, done below)
                }

                $this->refreshApplicationGuard($user);
                $response = $this->get(route($name, $values));
                $status   = $response->baseResponse->getStatusCode();
                if ($status >= 500) {
                    $errors[] = "{$role}: {$name} → {$status} " . $this->exceptionOf($response);
                }
            }
        }

        // Signed links buyers get by email/SMS
        $this->refreshApplicationGuard(null);
        $signed = [];
        if ($order) {
            $signed['tickets.view'] = TicketViewController::linkFor($order);
        }
        if ($code = Attendee::where('payment_status', 'paid')->value('qr_code')) {
            $signed['tickets.qr'] = TicketViewController::qrLinkFor($code);
        }
        if ($instalment = Order::where('payment_plan', 'instalments')->first()) {
            $signed['instalments.guest'] = \App\Http\Controllers\InstalmentController::payLinkFor($instalment);
        }
        foreach ($signed as $name => $url) {
            $response = $this->get($url);
            if (($status = $response->baseResponse->getStatusCode()) >= 400) {
                $errors[] = "guest: {$name} (signed) → {$status} " . $this->exceptionOf($response);
            }
        }

        $this->assertSame([], $errors, "Pages with server errors:\n" . implode("\n", $errors));
    }

    public function test_ticket_and_booking_pdfs_download(): void
    {
        $merchant = $this->user('merchant');
        $buyer    = $this->user();
        $attendee = $this->attendee($this->ticket($this->event($merchant)), $buyer);
        $exp      = Experience::forceCreate(['name' => 'PDF Trip', 'user_id' => $merchant->id, 'status' => 'active', 'start_date' => now()->addWeek()]);
        $booking  = ExperienceBooking::forceCreate(['user_id' => $buyer->id, 'experience_id' => $exp->id, 'name' => $buyer->name, 'qty' => 1,
            'total_price' => 1000, 'payment_status' => 'paid', 'status' => 'active', 'qr_code' => 'EXP-ORD-PDF-ABC123']);

        foreach ([
            route('ticket.pdf', $attendee->id),
            route('tickets.generatePDF', [$attendee->ticket->event_id, $attendee->ticket_id, $attendee->id]),
            route('experience.booking.pdf', $booking->fresh()->uuid),
        ] as $url) {
            ob_start();
            $response = $this->actingAs($buyer)->get($url);
            $leaked = ob_get_clean();

            $this->assertSame('', $leaked, "{$url} echoed output instead of returning it");
            $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
            $this->assertStringStartsWith('%PDF', $response->getContent());
        }
    }

    private function refreshApplicationGuard($user): void
    {
        auth()->forgetGuards();
        $user ? $this->actingAs($user) : auth()->logout();
        $this->flushSession();
    }

    private function exceptionOf($response): string
    {
        $e = $response->exception ?? null;

        return $e ? get_class($e) . ': ' . mb_strimwidth($e->getMessage(), 0, 160, '…') . ' @ ' . basename($e->getFile()) . ':' . $e->getLine() : '';
    }
}
