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

    public function test_a_ticket_for_another_event_is_called_out(): void
    {
        $owner    = $this->user('merchant');
        $attendee = $this->attendee($this->ticket($this->event($owner)), $this->user());
        $other    = $this->event($owner);

        $this->actingAs($owner)->postJson("/qrcode/scan/{$other->id}", ['qr' => $attendee->qr_code])
            ->assertNotFound()->assertJsonPath('message', 'Valid ticket, but for a different event — send them to the right gate.');
        $this->actingAs($owner)->postJson("/qrcode/scan/{$other->id}", ['qr' => 'QR-FAKE-123'])
            ->assertNotFound()->assertJsonPath('message', fn ($m) => str_contains($m, 'not recognised'));
    }

    public function test_search_finds_paid_holders_by_name_phone_or_order_and_checks_them_in(): void
    {
        $owner  = $this->user('merchant');
        $event  = $this->event($owner);
        $ticket = $this->ticket($event, ['name' => 'VIP']);
        $order  = \App\Models\Order::forceCreate(['order_no' => 'ORD-FINDME1', 'uuid' => (string) \Illuminate\Support\Str::uuid(), 'qty' => 1, 'total_amount' => 1000, 'status' => 'paid', 'initiator' => 'Wanjiku']);
        $holder = $this->attendee($ticket, $this->user(), ['name' => 'Wanjiku Kamau', 'phone' => '254712345678', 'order_id' => $order->id]);
        $this->attendee($ticket, $this->user(), ['name' => 'Wanjiku Unpaid', 'payment_status' => 'pending']);
        $this->attendee($this->ticket($this->event($this->user('merchant'))), $this->user(), ['name' => 'Wanjiku Elsewhere']);

        foreach (['wanjiku', '0712345678', '+254 712 345 678', 'ord-findme1'] as $q) {
            $this->actingAs($owner)->getJson(route('qrcode.search', [$event->id, 'q' => $q]))
                ->assertOk()->assertJsonCount(1, 'results')
                ->assertJsonPath('results.0.name', 'Wanjiku Kamau')
                ->assertJsonPath('results.0.detail', 'VIP')
                ->assertJsonPath('results.0.phone', '…5678');
        }

        $this->actingAs($this->user('merchant'))->getJson(route('qrcode.search', [$event->id, 'q' => 'wanjiku']))->assertForbidden();

        $this->actingAs($owner)->postJson("/qrcode/scan/{$event->id}", ['qr' => $holder->qr_code])
            ->assertOk()->assertJsonPath('id', $holder->id)->assertJsonPath('stats.by_type.0.name', 'VIP')->assertJsonPath('stats.by_type.0.checked_in', 1);
    }

    public function test_a_mistaken_check_in_can_be_undone_by_the_organiser_only(): void
    {
        $owner    = $this->user('merchant');
        $event    = $this->event($owner);
        $attendee = $this->attendee($this->ticket($event), $this->user());
        $this->actingAs($owner)->postJson("/qrcode/scan/{$event->id}", ['qr' => $attendee->qr_code])->assertOk();

        $this->actingAs($this->user('merchant'))->postJson(route('qrcode.undo', [$event->id, $attendee->id]))->assertForbidden();
        $this->actingAs($owner)->postJson(route('qrcode.undo', [$this->event($owner)->id, $attendee->id]))->assertForbidden();

        $this->actingAs($owner)->postJson(route('qrcode.undo', [$event->id, $attendee->id]))
            ->assertOk()->assertJsonPath('stats.checked_in', 0);
        $this->assertFalse($attendee->fresh()->checked_in);

        // And they can come in again
        $this->actingAs($owner)->postJson("/qrcode/scan/{$event->id}", ['qr' => $attendee->qr_code])->assertOk();
    }

    public function test_hub_lists_events_and_experiences_with_progress(): void
    {
        $owner = $this->user('merchant');
        $event = $this->event($owner, ['name' => 'Gate Test Night']);
        $this->attendee($this->ticket($event), $this->user(), ['checked_in' => true]);
        $this->attendee($this->ticket($event), $this->user());
        \App\Models\Experience::forceCreate(['name' => 'Hell\'s Gate Hike', 'user_id' => $owner->id, 'status' => 'active', 'start_date' => now()->addDays(3), 'end_date' => now()->addDays(4)]);

        $this->actingAs($owner)->get(route('qrcode.index'))
            ->assertOk()->assertSee('Gate Test Night')->assertSee('Hell&#039;s Gate Hike', false)->assertSee('of 2 checked in');

        $this->actingAs($owner)->get(route('qrcode.show', $event->id))->assertOk()->assertSee('Find by name');
    }

    public function test_experience_gate_searches_checks_in_and_undoes(): void
    {
        $owner = $this->user('merchant');
        $exp   = \App\Models\Experience::forceCreate(['name' => 'Diani Weekend', 'user_id' => $owner->id, 'status' => 'active', 'start_date' => now()->addDay()]);
        $booking = \App\Models\ExperienceBooking::forceCreate([
            'user_id' => $this->user()->id, 'experience_id' => $exp->id, 'name' => 'Otieno Omondi', 'phone' => '0722111222',
            'qty' => 1, 'total_price' => 30000, 'payment_status' => 'paid', 'status' => 'active', 'qr_code' => 'EXP-ORD-TEST-ABC123',
        ]);

        $this->actingAs($owner)->getJson(route('qrcode.experience-scanner.search', [$exp->id, 'q' => '0722 111 222']))
            ->assertOk()->assertJsonPath('results.0.name', 'Otieno Omondi');

        $this->actingAs($owner)->postJson(route('qrcode.experience-scanner.verify'), ['experience_id' => $exp->id, 'qr' => $booking->qr_code])
            ->assertOk()->assertJsonPath('stats.checked_in', 1);
        $this->actingAs($owner)->postJson(route('qrcode.experience-scanner.verify'), ['experience_id' => $exp->id, 'qr' => $booking->qr_code])
            ->assertStatus(409);

        $this->actingAs($owner)->postJson(route('qrcode.experience-scanner.undo', [$exp->id, $booking->id]))->assertOk();
        $this->assertFalse((bool) $booking->fresh()->checked_in);
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
