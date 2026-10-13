<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\PromoCode;
use App\Services\DarajaService;
use Tests\TestCase;

class PromoCodeTest extends TestCase
{
    private function promo(int $userId, array $attributes = []): PromoCode
    {
        return PromoCode::create(array_merge([
            'user_id' => $userId, 'code' => 'SAVE20', 'type' => 'percent', 'value' => 20,
        ], $attributes));
    }

    private function lines($ticket, int $qty): array
    {
        return [['ticket_id' => $ticket->id, 'qty' => $qty, 'price' => (float) $ticket->price, 'subtotal' => $ticket->price * $qty]];
    }

    public function test_discount_applies_only_to_the_merchants_own_tickets(): void
    {
        $merchant = $this->user('merchant');
        $mine     = $this->ticket($this->event($merchant), ['price' => 1000]);
        $theirs   = $this->ticket($this->event($this->user('merchant')), ['price' => 1000]);
        $promo    = $this->promo($merchant->id);

        $result = $promo->apply(array_merge($this->lines($mine, 2), $this->lines($theirs, 1)));

        $this->assertEquals(400, $result['discount']);         // 20% of 2000
        $this->assertEquals(800, $result['lines'][0]['price']); // discounted unit price
        $this->assertEquals(1000, $result['lines'][1]['price']); // untouched

        $this->assertArrayHasKey('error', $promo->apply($this->lines($theirs, 1)));
    }

    public function test_codes_cover_experiences_too(): void
    {
        $merchant = $this->user('merchant');
        $exp      = \App\Models\Experience::forceCreate(['name' => 'Beach Trip', 'user_id' => $merchant->id, 'status' => 'active']);
        $ticket   = $this->ticket($this->event($merchant), ['price' => 1000]);
        $expLine  = [['experience_id' => $exp->id, 'qty' => 1, 'price' => 32000, 'subtotal' => 32000]];

        // Experience-only code: discounts the trip, not tickets
        $tripCode = $this->promo($merchant->id, ['code' => 'TRIP15', 'value' => 15, 'experience_id' => $exp->id]);
        $result   = $tripCode->apply($this->lines($ticket, 1), $expLine);
        $this->assertEquals(4800, $result['discount']);
        $this->assertEquals(27200, $result['exp_lines'][0]['price']);
        $this->assertEquals(1000, $result['lines'][0]['price']);
        $this->assertArrayHasKey('error', $tripCode->apply($this->lines($ticket, 1)));

        // A merchant-wide code covers both
        $all = $this->promo($merchant->id, ['code' => 'ALL10', 'value' => 10]);
        $this->assertEquals(3300, $all->apply($this->lines($ticket, 1), $expLine)['discount']);

        // Group code counts places
        $group = $this->promo($merchant->id, ['code' => 'GANG4', 'value' => 20, 'experience_id' => $exp->id, 'min_tickets' => 4]);
        $this->assertArrayHasKey('error', $group->apply([], $expLine));
        $this->assertEquals(25600, $group->apply([], [['experience_id' => $exp->id, 'qty' => 4, 'price' => 32000, 'subtotal' => 128000]])['discount']);
    }

    public function test_rules_minimum_tickets_expiry_usage_and_active(): void
    {
        $merchant = $this->user('merchant');
        $ticket   = $this->ticket($this->event($merchant), ['price' => 500]);

        $group = $this->promo($merchant->id, ['code' => 'GROUP5', 'min_tickets' => 5]);
        $this->assertArrayHasKey('error', $group->apply($this->lines($ticket, 4)));
        $this->assertEquals(500, $group->apply($this->lines($ticket, 5))['discount']);

        $this->assertArrayHasKey('error', $this->promo($merchant->id, ['code' => 'OLD', 'ends_at' => now()->subDay()])->apply($this->lines($ticket, 1)));
        $this->assertArrayHasKey('error', $this->promo($merchant->id, ['code' => 'USED', 'max_uses' => 3, 'used_count' => 3])->apply($this->lines($ticket, 1)));
        $this->assertArrayHasKey('error', $this->promo($merchant->id, ['code' => 'OFF', 'is_active' => false])->apply($this->lines($ticket, 1)));

        $fixed = $this->promo($merchant->id, ['code' => 'FLAT', 'type' => 'fixed', 'value' => 5000]);
        $this->assertEquals(500, $fixed->apply($this->lines($ticket, 1))['discount']); // never more than the tickets cost
    }

    public function test_discounted_checkout_charges_less_and_counts_use_once_paid(): void
    {
        $merchant = $this->user('merchant');
        $ticket   = $this->ticket($this->event($merchant), ['price' => 1000]);
        $promo    = $this->promo($merchant->id);
        $buyer    = $this->user();

        $this->mock(DarajaService::class, function ($mock) {
            $mock->shouldReceive('stkPush')->once()->withArgs(fn ($amount) => $amount === 1600)
                ->andReturn(['ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_PROMO', 'MerchantRequestID' => 'MR']);
            $mock->shouldReceive('queryStatus')->andReturn(['ResultCode' => '0', 'ResultDesc' => 'ok']);
        });

        $this->actingAs($buyer)
            ->withSession(['cart' => [$ticket->id => ['ticket_id' => $ticket->id, 'quantity' => 2]], 'promo_code' => 'save20'])
            ->postJson('/cart/checkout', ['name' => 'B', 'email' => 'b@example.com', 'gender' => '1', 'payment_mode' => 'Mobile Money', 'phone' => '0712345678'])
            ->assertOk();

        $this->assertSame(0, $promo->fresh()->used_count); // not paid yet

        $payment = MpesaStk::where('checkout_request_id', 'ws_CO_PROMO')->first();
        $this->mpesaCallback($payment, 0)->assertOk();
        $this->mpesaCallback($payment, 0)->assertOk();

        $order = Order::find($payment->fresh()->order_id);
        $this->assertEquals(400, $order->discount);
        $this->assertEquals(1600, $order->total_amount);
        $this->assertEquals([800.0, 800.0], Attendee::where('order_id', $order->id)->pluck('total_price')->map(fn ($p) => (float) $p)->all());
        $this->assertSame(1, $promo->fresh()->used_count);
    }

    public function test_deactivated_code_is_refused_at_checkout(): void
    {
        $merchant = $this->user('merchant');
        $ticket   = $this->ticket($this->event($merchant));
        $this->promo($merchant->id, ['is_active' => false]);
        $this->mock(DarajaService::class, fn ($mock) => $mock->shouldNotReceive('stkPush'));

        $this->actingAs($this->user())
            ->withSession(['cart' => [$ticket->id => ['ticket_id' => $ticket->id, 'quantity' => 1]], 'promo_code' => 'SAVE20'])
            ->postJson('/cart/checkout', ['name' => 'B', 'email' => 'b@example.com', 'gender' => '1', 'payment_mode' => 'Mobile Money', 'phone' => '0712345678'])
            ->assertStatus(400);
    }

    public function test_cart_shows_the_discount(): void
    {
        $merchant = $this->user('merchant');
        $ticket   = $this->ticket($this->event($merchant), ['price' => 1000]);
        $this->promo($merchant->id);

        $this->withSession(['cart' => [$ticket->id => ['ticket_id' => $ticket->id, 'event_id' => $ticket->event_id, 'quantity' => 1, 'price' => 1000]]])
            ->post('/cart/promo', ['code' => 'save20'])
            ->assertRedirect(route('cart.view'));

        $this->get('/cart')->assertOk()->assertSee('Discount (SAVE20)')->assertSee('KES 800.00');
    }

    public function test_merchants_manage_only_their_own_codes(): void
    {
        $merchant = $this->user('merchant');
        $other    = $this->user('merchant');
        $theirs   = $this->promo($other->id, ['code' => 'THEIRS']);

        $this->actingAs($merchant)->post("/promo-codes/{$theirs->id}/toggle")->assertForbidden();

        $this->actingAs($merchant)->post('/promo-codes', [
            'code' => 'x', 'type' => 'percent', 'value' => 10, 'event_id' => $this->event($other)->id,
        ])->assertSessionHasErrors('event_id');

        $this->actingAs($merchant)->post('/promo-codes', ['code' => 'theirs', 'type' => 'percent', 'value' => 10])
            ->assertSessionHasErrors('code'); // codes are unique platform-wide

        $this->actingAs($merchant)->post('/promo-codes', ['code' => 'early-bird', 'type' => 'percent', 'value' => 10])
            ->assertRedirect(route('promo-codes.index'));
        $this->assertDatabaseHas('promo_codes', ['code' => 'EARLY-BIRD', 'user_id' => $merchant->id]);

        $this->actingAs($merchant)->get('/promo-codes')->assertOk()->assertSee('EARLY-BIRD')->assertDontSee('THEIRS');
    }
}
