<?php

namespace Tests\Feature;

use App\Jobs\NotifyBuyer;
use App\Models\Attendee;
use App\Models\Order;
use App\Support\RefundPolicy;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Tests\TestCase;

class CancellationTest extends TestCase
{
    /** A paid order of $qty tickets for $ticket. */
    private function paidOrder($ticket, int $qty = 1, array $orderAttributes = []): Order
    {
        $buyer = $this->user();
        $order = Order::forceCreate(array_merge([
            'user_id' => $buyer->id, 'order_no' => 'ORD-' . strtoupper(Str::random(8)), 'uuid' => (string) Str::uuid(),
            'qty' => $qty, 'total_amount' => $ticket->price * $qty, 'status' => 'paid', 'initiator' => $buyer->name, 'email' => $buyer->email,
        ], $orderAttributes));

        for ($i = 0; $i < $qty; $i++) {
            $this->attendee($ticket, $buyer, ['order_id' => $order->id, 'total_price' => $ticket->price, 'phone' => '0712345678']);
        }

        return $order;
    }

    public function test_cancelling_an_event_refunds_every_buyer_and_stops_the_tickets(): void
    {
        Bus::fake([NotifyBuyer::class]);
        $owner  = $this->user('merchant');
        $event  = $this->event($owner);
        $ticket = $this->ticket($event, ['price' => 1500]);
        $order  = $this->paidOrder($ticket, 2);
        $instalment = $this->paidOrder($ticket, 1, ['payment_plan' => 'instalments', 'status' => 'partial', 'amount_paid' => 600, 'balance_due' => 900]);
        Attendee::where('order_id', $instalment->id)->update(['payment_status' => 'partial', 'qr_code' => null]);

        $this->actingAs($this->user('merchant'))->post(route('listings.cancel', ['event', $event->id]), ['reason' => 'x', 'confirm' => 1])->assertForbidden();
        $this->actingAs($owner)->post(route('listings.cancel', ['event', $event->id]), ['reason' => 'Venue flooded'])->assertSessionHasErrors('confirm');

        $this->actingAs($owner)->post(route('listings.cancel', ['event', $event->id]), ['reason' => 'Venue flooded', 'confirm' => 1])
            ->assertSessionHas('success');

        $event->refresh();
        $this->assertSame('cancelled', $event->status);
        $this->assertNotNull($event->cancelled_at);

        $order->refresh();
        $this->assertEquals(3000, $order->refund);
        $this->assertSame('cancelled', $order->status);
        $this->assertStringContainsString('Venue flooded', $order->refund_reason);
        $this->assertEquals(600, $instalment->fresh()->refund);              // everything they paid, deposit included
        $this->assertSame(0, Attendee::whereIn('order_id', [$order->id, $instalment->id])->where('payment_status', '!=', 'cancelled')->count());

        Bus::assertDispatchedTimes(NotifyBuyer::class, 2);

        // The gate refuses cancelled tickets, and the refund shows up for the admin
        $code = Attendee::where('order_id', $order->id)->value('qr_code');
        $this->actingAs($owner)->postJson("/qrcode/scan/{$event->id}", ['qr' => $code])->assertStatus(400);
        $this->actingAs($this->user('admin'))->get(route('admin.payouts.index'))->assertSee('Venue flooded')->assertSee($order->order_no);
    }

    public function test_postponing_tells_buyers_and_opens_refunds_for_a_while(): void
    {
        Bus::fake([NotifyBuyer::class]);
        $owner  = $this->user('merchant');
        $event  = $this->event($owner, ['refund_policy' => 'none']);
        $order  = $this->paidOrder($this->ticket($event, ['price' => 2000]));
        $this->assertFalse(RefundPolicy::refundableNow($event));

        $newDate = now()->addMonth()->setTime(18, 0);
        $this->actingAs($owner)->post(route('listings.postpone', ['event', $event->id]), ['start' => $newDate->format('Y-m-d\TH:i'), 'note' => 'Rain'])
            ->assertSessionHas('success');

        $event->refresh();
        $this->assertSame($newDate->format('Y-m-d H:i'), $event->date->format('Y-m-d H:i'));
        $this->assertSame($newDate->copy()->addHours(6)->format('Y-m-d H:i'), $event->end_date->format('Y-m-d H:i'));   // same length
        $this->assertTrue(RefundPolicy::refundableNow($event));
        Bus::assertDispatched(NotifyBuyer::class, fn ($job) => $job->kind === 'postponed' && $job->orderId === $order->id);

        // The buyer can now cancel from their private ticket page
        $this->get(\App\Http\Controllers\TicketViewController::linkFor($order))->assertOk()->assertSee('Request a refund (KES 2,000)');
        $this->post(URL::temporarySignedRoute('tickets.refund', now()->addHour(), ['order' => $order->uuid], absolute: false))->assertRedirect();

        $this->assertEquals(2000, $order->fresh()->refund);
        $this->assertSame('Refund requested by buyer', $order->fresh()->refund_reason);
    }

    public function test_buyer_refunds_follow_the_listing_policy(): void
    {
        Bus::fake([NotifyBuyer::class]);
        $owner = $this->user('merchant');

        $soon  = $this->event($owner, ['refund_policy' => '7_days', 'date' => now()->addDays(3), 'end_date' => now()->addDays(3)->addHours(5)]);
        $later = $this->event($owner, ['refund_policy' => '7_days', 'date' => now()->addDays(20), 'end_date' => now()->addDays(20)->addHours(5)]);
        $none  = $this->event($owner, ['refund_policy' => 'none']);

        $cancellations = app(\App\Services\Cancellations::class);
        $this->assertSame(0, $cancellations->requestRefund($this->paidOrder($this->ticket($soon)))['amount']);   // inside 7 days
        $this->assertSame(0, $cancellations->requestRefund($this->paidOrder($this->ticket($none)))['amount']);

        $order = $this->paidOrder($this->ticket($later, ['price' => 1000]), 2);
        Attendee::where('order_id', $order->id)->limit(1)->update(['checked_in' => true]);        // already used one
        $this->assertEquals(1000, $cancellations->requestRefund($order)['amount']);
        $this->assertSame(1, Attendee::where('order_id', $order->id)->where('payment_status', 'paid')->count());
    }

    public function test_a_payment_that_lands_after_cancellation_becomes_a_refund(): void
    {
        $this->safaricomSays('0');
        $owner   = $this->user('merchant');
        $event   = $this->event($owner);
        $payment = $this->pendingPayment($this->user(), $this->ticket($event));
        $event->forceFill(['cancelled_at' => now(), 'status' => 'cancelled'])->save();

        $this->mpesaCallback($payment, 0, 'LATE000001')->assertOk();

        $order = Order::find($payment->fresh()->order_id);
        $this->assertEquals($payment->amount, $order->refund);
        $this->assertStringContainsString('cancelled', $order->refund_reason);
        $this->assertSame(0, Attendee::where('order_id', $order->id)->count());
    }

    public function test_notices_render_and_policy_pages_load(): void
    {
        Mail::fake();
        $owner = $this->user('merchant');
        $order = $this->paidOrder($this->ticket($this->event($owner)));

        foreach (['cancelled' => ['name' => 'Gig', 'reason' => 'Rain', 'amount' => 1000], 'postponed' => ['name' => 'Gig', 'when' => 'Sat 1 Nov', 'note' => null, 'deadline' => '15 Nov'], 'refund_requested' => ['amount' => 1000]] as $kind => $details) {
            (new NotifyBuyer($order->id, $kind, $details))->handle(app(\App\Services\Messaging\AfricasTalkingSms::class));
            $this->assertNotEmpty((new \App\Mail\BookingNotice($order, $kind, $details))->render());
        }
        Mail::assertSent(\App\Mail\BookingNotice::class, 3);

        foreach (['terms', 'privacy', 'refund-policy'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('Last updated');
        }
    }

    public function test_organiser_sets_the_refund_policy_with_instalment_settings(): void
    {
        $owner = $this->user('merchant');
        $event = $this->event($owner);
        $this->ticket($event);   // the policy shows with the ticket picker

        $this->actingAs($owner)->post(route('instalments.update', ['event', $event->id]), [
            'instalment_deposit_percent' => 30, 'instalment_due_days' => 7, 'refund_policy' => '48_hours',
        ])->assertSessionHas('success');

        $this->assertSame('48_hours', $event->fresh()->refund_policy);
        $this->get(route('event.show', $event->id))->assertSee('48 hours before');
    }
}
