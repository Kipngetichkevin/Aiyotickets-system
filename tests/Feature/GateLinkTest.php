<?php

namespace Tests\Feature;

use App\Models\GatePass;
use Tests\TestCase;

class GateLinkTest extends TestCase
{
    private function link($owner, $event, array $data = []): GatePass
    {
        $this->actingAs($owner)->post(route('qrcode.gates.store', ['event', $event->id]), array_merge([
            'label' => 'Main gate — Brian', 'expires_at' => now()->addDay()->format('Y-m-d\TH:i'),
        ], $data))->assertRedirect(route('qrcode.gates', ['event', $event->id]));

        auth()->logout();

        return GatePass::latest('id')->first();
    }

    public function test_organiser_creates_links_for_their_own_events_only(): void
    {
        $owner = $this->user('merchant');
        $event = $this->event($owner);

        $this->actingAs($this->user('merchant'))->get(route('qrcode.gates', ['event', $event->id]))->assertForbidden();
        $this->actingAs($this->user('merchant'))->post(route('qrcode.gates.store', ['event', $event->id]), ['label' => 'x', 'expires_at' => now()->addDay()])->assertForbidden();

        $pass = $this->link($owner, $event, ['pin' => '4821']);
        $this->assertNotSame('4821', $pass->pin_hash);
        $this->assertSame(40, strlen($pass->token));

        $this->actingAs($owner)->get(route('qrcode.gates', ['event', $event->id]))->assertOk()->assertSee('Main gate — Brian')->assertSee($pass->url());
    }

    public function test_staff_scan_through_a_link_with_a_pin_and_it_is_recorded(): void
    {
        $owner    = $this->user('merchant');
        $event    = $this->event($owner);
        $attendee = $this->attendee($this->ticket($event, ['name' => 'VIP']), $this->user());
        $other    = $this->attendee($this->ticket($this->event($owner)), $this->user());
        $pass     = $this->link($owner, $event, ['pin' => '4821']);

        // Locked until the PIN is entered
        $this->get($pass->url())->assertOk()->assertSee('Enter the gate PIN');
        $this->postJson(route('gate.scan', $pass->token), ['qr' => $attendee->qr_code])->assertStatus(410);
        $this->post(route('gate.unlock', $pass->token), ['pin' => '0000'])->assertSessionHasErrors('pin');
        $this->post(route('gate.unlock', $pass->token), ['pin' => '4821'])->assertRedirect($pass->url());

        $this->get($pass->url())->assertOk()->assertSee('Find by name')->assertDontSee('Gate staff links');

        $this->postJson(route('gate.scan', $pass->token), ['qr' => $attendee->qr_code])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('detail', 'VIP');
        $this->assertSame($pass->id, $attendee->fresh()->gate_pass_id);
        $this->assertNull($attendee->fresh()->checked_in_by);
        $this->assertSame(1, $pass->fresh()->scans);

        // Only this event's tickets
        $this->postJson(route('gate.scan', $pass->token), ['qr' => $other->qr_code])->assertNotFound();
        $this->getJson(route('gate.search', [$pass->token, 'q' => $attendee->name]))->assertOk()->assertJsonCount(1, 'results');
    }

    public function test_a_link_without_a_pin_opens_straight_away(): void
    {
        $owner = $this->user('merchant');
        $pass  = $this->link($owner, $this->event($owner));

        $this->get($pass->url())->assertOk()->assertSee('Find by name')->assertSee('Take a photo of the QR');
    }

    public function test_switched_off_and_expired_links_stop_working(): void
    {
        $owner    = $this->user('merchant');
        $event    = $this->event($owner);
        $attendee = $this->attendee($this->ticket($event), $this->user());
        $pass     = $this->link($owner, $event);

        $this->actingAs($owner)->post(route('qrcode.gates.revoke', $pass))->assertSessionHas('success');
        auth()->logout();

        $this->get($pass->url())->assertStatus(410)->assertSee('This gate link is closed');
        $this->postJson(route('gate.scan', $pass->token), ['qr' => $attendee->qr_code])->assertStatus(410);

        $expired = $this->link($owner, $event);
        $expired->forceFill(['expires_at' => now()->subMinute()])->save();
        $this->get($expired->url())->assertStatus(410);

        $this->assertFalse((bool) $attendee->fresh()->checked_in);
    }

    public function test_another_organiser_cannot_switch_off_a_link(): void
    {
        $owner = $this->user('merchant');
        $pass  = $this->link($owner, $this->event($owner));

        $this->actingAs($this->user('merchant'))->post(route('qrcode.gates.revoke', $pass))->assertForbidden();
        $this->assertNull($pass->fresh()->revoked_at);
    }
}
