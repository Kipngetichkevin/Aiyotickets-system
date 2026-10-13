<?php

namespace Tests\Feature;

use Tests\TestCase;

class GateScanTest extends TestCase
{
    public function test_scan_checks_in_the_attendee_once(): void
    {
        $owner    = $this->user('merchant');
        $event    = $this->event($owner);
        $attendee = $this->attendee($this->ticket($event), $this->user());

        $this->actingAs($owner)->postJson("/qrcode/scan/{$event->id}", ['qr' => $attendee->qr_code])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('stats.checked_in', 1);

        $this->assertTrue((bool) $attendee->fresh()->checked_in);

        $this->actingAs($owner)->postJson("/qrcode/scan/{$event->id}", ['qr' => $attendee->qr_code])
            ->assertStatus(409)
            ->assertJsonPath('already', true);
    }

    public function test_scan_accepts_the_code_inside_old_multi_line_pdf_text(): void
    {
        $owner    = $this->user('merchant');
        $event    = $this->event($owner);
        $attendee = $this->attendee($this->ticket($event), $this->user());

        $this->actingAs($owner)
            ->postJson("/qrcode/scan/{$event->id}", ['qr' => "Ticket name: VIP\nRef: {$attendee->qr_code}\nTotal: 100"])
            ->assertOk();
    }

    public function test_scan_rejects_wrong_event_unpaid_and_other_merchants(): void
    {
        $owner    = $this->user('merchant');
        $event    = $this->event($owner);
        $ticket   = $this->ticket($event);
        $attendee = $this->attendee($ticket, $this->user());
        $unpaid   = $this->attendee($ticket, $this->user(), ['payment_status' => 'pending']);

        $this->actingAs($owner)->postJson('/qrcode/scan/' . $this->event($owner)->id, ['qr' => $attendee->qr_code])->assertNotFound();
        $this->actingAs($owner)->postJson("/qrcode/scan/{$event->id}", ['qr' => $unpaid->qr_code])->assertStatus(400);
        $this->actingAs($this->user('merchant'))->postJson("/qrcode/scan/{$event->id}", ['qr' => $attendee->qr_code])->assertForbidden();

        $this->assertFalse((bool) $attendee->fresh()->checked_in);
    }

    public function test_event_with_paid_tickets_cannot_be_deleted(): void
    {
        $owner = $this->user('merchant');
        $event = $this->event($owner);
        $this->attendee($this->ticket($event), $this->user());
        $empty = $this->event($owner);

        $this->actingAs($owner)->deleteJson("/events/{$event->id}")->assertStatus(422);
        $this->actingAs($owner)->deleteJson("/events/{$empty->id}")->assertOk();

        $this->assertModelExists($event);
        $this->assertModelMissing($empty);
    }
}
