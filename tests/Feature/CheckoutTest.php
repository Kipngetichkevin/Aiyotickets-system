<?php

namespace Tests\Feature;

use App\Services\Inventory;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    public function test_cart_ignores_the_price_sent_by_the_browser(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')), ['price' => 3500, 'name' => 'VIP']);

        $this->postJson('/cart/add', ['cart' => [[
            'ticket_id' => $ticket->id, 'event_id' => 999, 'name' => 'HACKED', 'price' => 1, 'quantity' => 2,
        ]]])->assertOk();

        $item = session('cart')[$ticket->id];
        $this->assertEquals(3500, $item['price']);
        $this->assertSame('VIP', $item['name']);
        $this->assertSame($ticket->event_id, $item['event_id']);
    }

    public function test_pending_checkouts_hold_stock_until_they_expire_or_fail(): void
    {
        $ticket  = $this->ticket($this->event($this->user('merchant')), ['quantity' => 10]);
        $payment = $this->pendingPayment($this->user(), $ticket, 3);
        $left    = fn () => (new Inventory)->ticketsLeft($ticket->id);

        $this->assertSame(7, $left());

        $payment->forceFill(['created_at' => now()->subMinutes(Inventory::HOLD_MINUTES + 1)])->save();
        $this->assertSame(10, $left());

        $payment->forceFill(['created_at' => now(), 'status' => 'failed'])->save();
        $this->assertSame(10, $left());
    }

    private function checkout(): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/cart/checkout', [
            'name' => 'Buyer', 'email' => 'buyer@example.com', 'gender' => '1',
            'payment_mode' => 'Mobile Money', 'phone' => '0712345678',
        ]);
    }

    public function test_charges_and_records_the_same_whole_shilling_amount(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')), ['price' => 99.75]);
        $this->mock(\App\Services\DarajaService::class, function ($mock) {
            $mock->shouldReceive('stkPush')->once()->withArgs(fn ($amount) => $amount === 200)
                ->andReturn(['ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_ROUND', 'MerchantRequestID' => 'MR-1']);
        });

        $this->actingAs($this->user())
            ->withSession(['cart' => [$ticket->id => ['ticket_id' => $ticket->id, 'quantity' => 2, 'price' => 99.75]]])
            ->checkout()
            ->assertOk();

        $payment = \App\Models\MpesaStk::where('checkout_request_id', 'ws_CO_ROUND')->first();
        $this->assertEquals(200, $payment->amount);
        $this->assertEquals(200, $payment->pending_data['total_amount']);
    }

    public function test_non_kes_prices_are_refused_before_any_mpesa_prompt(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')), ['price' => 50, 'currency' => 'USD']);
        $this->mock(\App\Services\DarajaService::class, fn ($mock) => $mock->shouldNotReceive('stkPush'));

        $this->actingAs($this->user())
            ->withSession(['cart' => [$ticket->id => ['ticket_id' => $ticket->id, 'quantity' => 1, 'price' => 50]]])
            ->checkout()
            ->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_sale_dates_are_labelled_and_enforced_only_when_switched_on(): void
    {
        $event  = $this->event($this->user('merchant'));
        $ended  = $this->ticket($event, ['name' => 'Early Bird', 'start_date' => now()->subMonth(), 'end_date' => now()->subDay()]);
        $future = $this->ticket($event, ['name' => 'Late', 'start_date' => now()->addDay()]);
        $add    = fn ($ticket) => $this->postJson('/cart/add', ['cart' => [['ticket_id' => $ticket->id, 'event_id' => $event->id, 'quantity' => 1]]]);

        $this->get("/event/{$event->id}")->assertOk()->assertSee('Sales ended')->assertSee('Not on sale yet');

        config(['aiyo.enforce_sale_dates' => false]);
        $add($ended)->assertOk();

        config(['aiyo.enforce_sale_dates' => true]);
        $add($ended)->assertStatus(400)->assertJsonFragment(['message' => 'Sales for Early Bird have ended.']);
        $add($future)->assertStatus(400);

        // A ticket already in the cart is refused at checkout too, before any M-Pesa prompt
        $this->mock(\App\Services\DarajaService::class, fn ($mock) => $mock->shouldNotReceive('stkPush'));
        $this->actingAs($this->user())
            ->withSession(['cart' => [$ended->id => ['ticket_id' => $ended->id, 'quantity' => 1]]])
            ->checkout()
            ->assertStatus(400);
    }

    public function test_paid_tickets_reduce_stock(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')), ['quantity' => 2]);
        $this->attendee($ticket, $this->user());
        $this->attendee($ticket, $this->user());

        $this->assertSame(0, (new Inventory)->ticketsLeft($ticket->id));

        $this->postJson('/cart/add', ['cart' => [['ticket_id' => $ticket->id, 'event_id' => $ticket->event_id, 'quantity' => 1]]])
            ->assertStatus(400)
            ->assertJsonFragment(['success' => false]);
    }
}
