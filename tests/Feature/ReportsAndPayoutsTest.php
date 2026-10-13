<?php

namespace Tests\Feature;

use App\Models\Payout;
use App\Services\SalesReport;
use Tests\TestCase;

class ReportsAndPayoutsTest extends TestCase
{
    /** A merchant with one ended event that sold $count paid tickets at $price, and one unpaid. */
    private function merchantWithEndedSales(int $count = 3, int $price = 1000): \App\Models\User
    {
        $merchant = $this->user('merchant');
        $ticket   = $this->ticket($this->event($merchant, [
            'date' => now()->subDays(3), 'end_date' => now()->subDays(2),
        ]), ['price' => $price]);

        for ($i = 0; $i < $count; $i++) {
            $this->attendee($ticket, $this->user(), ['total_price' => $price]);
        }
        $this->attendee($ticket, $this->user(), ['total_price' => $price, 'payment_status' => 'pending']);

        return $merchant;
    }

    public function test_report_counts_paid_sales_of_own_events_only(): void
    {
        $merchant = $this->merchantWithEndedSales(3, 1000);
        $this->merchantWithEndedSales(5, 500); // someone else's

        $totals = (new SalesReport($merchant))->totals();

        $this->assertSame(3, $totals['tickets']);
        $this->assertEquals(3000, $totals['gross']);
        $this->assertEquals(150, $totals['fee']);   // 5%
        $this->assertEquals(2850, $totals['net']);

        $this->assertSame(8, (new SalesReport($this->user('admin')))->totals()['tickets']);
    }

    public function test_only_ended_events_are_withdrawable(): void
    {
        $merchant = $this->merchantWithEndedSales(2, 1000);
        $upcoming = $this->ticket($this->event($merchant), ['price' => 1000]);
        $this->attendee($upcoming, $this->user(), ['total_price' => 1000]);

        $balance = SalesReport::own($merchant)->balance();

        $this->assertEquals(2850, $balance['earned']);     // 3 x 1000 less 5%
        $this->assertEquals(950, $balance['pending']);     // upcoming event
        $this->assertEquals(1900, $balance['withdrawable']);
    }

    public function test_merchant_cannot_request_more_than_withdrawable(): void
    {
        $merchant = $this->merchantWithEndedSales(2, 1000); // 1900 withdrawable

        $this->actingAs($merchant)->post('/payouts', ['amount' => 2000, 'method' => 'mpesa', 'destination' => '0712345678'])
            ->assertSessionHasErrors('amount');

        $this->actingAs($merchant)->post('/payouts', ['amount' => 1500, 'method' => 'mpesa', 'destination' => '0712345678'])
            ->assertRedirect(route('payouts.index'));

        // The pending request is reserved: only 400 left
        $this->actingAs($merchant)->post('/payouts', ['amount' => 500, 'method' => 'mpesa', 'destination' => '0712345678'])
            ->assertSessionHasErrors('amount');

        $this->assertEquals(400, SalesReport::own($merchant)->balance()['withdrawable']);
    }

    public function test_admin_pays_or_rejects_and_balance_follows(): void
    {
        $merchant = $this->merchantWithEndedSales(2, 1000);
        $admin    = $this->user('admin');
        $payout   = Payout::create(['user_id' => $merchant->id, 'amount' => 1000, 'method' => 'mpesa', 'destination' => '0712345678']);

        $this->actingAs($merchant)->post("/admin/payouts/{$payout->id}/paid", ['reference' => 'X'])->assertForbidden();

        $this->actingAs($admin)->post("/admin/payouts/{$payout->id}/paid", ['reference' => 'QJK12345'])->assertRedirect();
        $payout->refresh();
        $this->assertSame('paid', $payout->status);
        $this->assertSame('QJK12345', $payout->reference);
        $this->assertSame($admin->id, $payout->processed_by);

        $rejected = Payout::create(['user_id' => $merchant->id, 'amount' => 500, 'method' => 'mpesa', 'destination' => '0712345678']);
        $this->actingAs($admin)->post("/admin/payouts/{$rejected->id}/reject", ['note' => 'Wrong number'])->assertRedirect();

        $balance = SalesReport::own($merchant)->balance();
        $this->assertEquals(1000, $balance['paid_out']);
        $this->assertEquals(900, $balance['withdrawable']); // rejected request no longer reserved

        // A paid payout can't be changed again
        $this->actingAs($admin)->post("/admin/payouts/{$payout->id}/reject", ['note' => 'x']);
        $this->assertSame('paid', $payout->fresh()->status);
    }

    public function test_attendee_csv_is_owner_only_and_formula_safe(): void
    {
        $merchant = $this->user('merchant');
        $event    = $this->event($merchant);
        $this->attendee($this->ticket($event), $this->user(), ['name' => '=HYPERLINK("http://evil")']);

        $this->actingAs($this->user('merchant'))->get("/reports/events/{$event->id}/attendees.csv")->assertForbidden();

        $csv = $this->actingAs($merchant)->get("/reports/events/{$event->id}/attendees.csv")->assertOk()->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_reports_and_payouts_pages_render(): void
    {
        $merchant = $this->merchantWithEndedSales();

        $this->actingAs($merchant)->get('/reports')->assertOk()->assertSee('Net earnings');
        $this->actingAs($merchant)->get('/payouts')->assertOk()->assertSee('Withdrawable');
        $this->actingAs($this->user('admin'))->get('/admin/payouts')->assertOk();
        $this->actingAs($this->user())->get('/reports')->assertForbidden();
    }
}
