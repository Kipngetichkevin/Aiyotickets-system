<?php

namespace Tests\Feature;

use App\Models\Order;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    public function test_guests_get_the_public_page_without_sidebar(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('class="sidebar', $html);
        $this->assertStringContainsString(route('login'), $html);
        $this->assertStringContainsString(route('experiences.browse'), $html);
        $this->assertStringNotContainsString(route('system.index'), $html);
        $this->assertStringNotContainsString(route('qrcode.index'), $html);

        // After logging in, the sidebar is back
        $this->actingAs($this->user())->get('/')->assertOk()->assertSee('class="sidebar', false);
    }

    public function test_home_slider_shows_upcoming_events(): void
    {
        $event = $this->event($this->user('merchant'), ['name' => 'Slider Test Gala']);
        $this->ticket($event, ['price' => 750]);

        $this->get('/')->assertOk()
            ->assertSee('aria-roledescription="slide"', false)
            ->assertSee('Slider Test Gala')
            ->assertSee('From <strong class="text-lg text-white">KES 750</strong>', false);
    }

    public function test_guest_must_log_in_to_pay(): void
    {
        $this->postJson('/cart/checkout')->assertUnauthorized();
        $this->getJson('/mpesa/status/anything')->assertUnauthorized();
    }

    public function test_merchant_cannot_manage_another_merchants_tickets(): void
    {
        $owner = $this->user('merchant');
        $event = $this->event($owner);

        $this->actingAs($this->user('merchant'))->get("/events/{$event->id}/tickets")->assertForbidden();
        $this->actingAs($owner)->get("/events/{$event->id}/tickets")->assertOk();
    }

    public function test_merchant_cannot_edit_another_merchants_ticket(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')), ['price' => 1000]);

        $this->actingAs($this->user('merchant'))
            ->putJson("/tickets/{$ticket->id}", ['name' => 'Free', 'price' => 0, 'quantity' => 999])
            ->assertForbidden();

        $this->assertEquals(1000, $ticket->fresh()->price);
    }

    public function test_only_admins_reach_system_settings(): void
    {
        $this->actingAs($this->user('merchant'))->get('/system')->assertForbidden();
        $this->actingAs($this->user('user'))->get('/system')->assertForbidden();
        $this->actingAs($this->user('admin'))->get('/system')->assertOk();
    }

    public function test_ticket_pdf_is_limited_to_holder_organiser_and_admin(): void
    {
        $owner    = $this->user('merchant');
        $buyer    = $this->user();
        $attendee = $this->attendee($this->ticket($this->event($owner)), $buyer);

        $this->actingAs($this->user())->get("/ticket/pdf/{$attendee->id}")->assertForbidden();
        $this->actingAs($this->user('merchant'))->get("/ticket/pdf/{$attendee->id}")->assertForbidden();
    }

    public function test_thank_you_page_shows_an_order_only_to_its_buyer(): void
    {
        $buyer = $this->user();
        $order = Order::forceCreate([
            'order_no' => 'ORD-TEST', 'uuid' => 'u-1', 'qty' => 1, 'total_amount' => 100,
            'status' => 'paid', 'initiator' => $buyer->name, 'user_id' => $buyer->id,
        ]);

        $this->actingAs($this->user())->get("/cart/thankyou?order={$order->id}")->assertRedirect(route('cart.view'));
        $this->actingAs($buyer)->get("/cart/thankyou?order={$order->id}")->assertOk();
    }

    public function test_registration_makes_a_client_account(): void
    {
        $this->post('/register', [
            'name' => 'New Buyer', 'email' => 'buyer@example.com', 'phone' => '0712345678',
            'password' => 'Str0ng!Passw0rd#2026', 'password_confirmation' => 'Str0ng!Passw0rd#2026',
        ])->assertRedirect();

        $user = \App\Models\User::where('email', 'buyer@example.com')->first();
        $this->assertTrue($user->hasRole('user'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertSame('0712345678', $user->phone);
    }
}
