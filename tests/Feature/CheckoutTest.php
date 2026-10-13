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
