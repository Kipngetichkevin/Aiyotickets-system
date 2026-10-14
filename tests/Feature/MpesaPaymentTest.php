<?php

namespace Tests\Feature;

use App\Models\Attendee;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Services\MpesaPaymentService;
use Tests\TestCase;

class MpesaPaymentTest extends TestCase
{
    public function test_forged_success_callback_is_not_trusted(): void
    {
        $this->safaricomSays(null); // Safaricom has no record of a completed payment
        $payment = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))));

        $this->mpesaCallback($payment, 0)->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame(0, Order::count());
    }

    public function test_confirmed_payment_creates_exactly_one_order(): void
    {
        $this->safaricomSays('0');
        $buyer   = $this->user();
        $payment = $this->pendingPayment($buyer, $this->ticket($this->event($this->user('merchant'))), 2);

        $this->mpesaCallback($payment, 0, 'RCPT123456')->assertOk();
        $this->mpesaCallback($payment, 0, 'RCPT123456')->assertOk();   // Safaricom retries
        app(MpesaPaymentService::class)->fulfil($payment->fresh()); // status polling / cart racing it

        $payment->refresh();
        $this->assertSame('completed', $payment->status);
        $this->assertSame('RCPT123456', $payment->mpesa_receipt_number);
        $this->assertSame(1, Order::count());

        $attendees = Attendee::where('order_id', $payment->order_id)->get();
        $this->assertCount(2, $attendees);
        $this->assertTrue($attendees->every(fn ($a) => $a->user_id === $buyer->id && $a->transaction_code === 'RCPT123456'));
        $this->assertCount(2, $attendees->pluck('qr_code')->unique());
    }

    public function test_buyer_gets_one_tickets_email_per_paid_order(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $this->safaricomSays('0');
        $buyer   = $this->user();
        $payment = $this->pendingPayment($buyer, $this->ticket($this->event($this->user('merchant'))), 2);

        $this->mpesaCallback($payment, 0)->assertOk();
        $this->mpesaCallback($payment, 0)->assertOk(); // retry must not re-send

        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\TicketsIssued::class, 1);
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\TicketsIssued::class,
            fn ($mail) => $mail->hasTo($buyer->email) && $mail->order->id === $payment->fresh()->order_id);
    }

    public function test_tickets_email_lists_every_ticket_with_its_code(): void
    {
        $this->safaricomSays('0');
        $payment = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))), 2);
        $this->mpesaCallback($payment, 0)->assertOk();

        $order = Order::find($payment->fresh()->order_id);
        $html  = (new \App\Mail\TicketsIssued($order))->render();

        foreach (Attendee::where('order_id', $order->id)->pluck('qr_code') as $code) {
            $this->assertStringContainsString($code, $html);
        }
    }

    public function test_insufficient_funds_marks_the_payment_failed(): void
    {
        $this->safaricomSays('1');
        $payment = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))));

        $this->mpesaCallback($payment, 1)->assertOk();

        $payment->refresh();
        $this->assertSame('failed', $payment->status);
        $this->assertSame('1', $payment->result_code);
        $this->assertSame(0, Order::count());
    }

    public function test_forged_failure_callback_is_not_trusted(): void
    {
        $this->safaricomSays(null); // Safaricom can't confirm a failure
        $payment = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))));

        $this->mpesaCallback($payment, 1032)->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_confirmed_success_revives_a_payment_marked_failed(): void
    {
        $this->safaricomSays('0'); // e.g. timed out on our side but the customer paid
        $payment = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))));
        $payment->update(['status' => 'failed', 'result_code' => '1037']);

        $this->mpesaCallback($payment, 0, 'LATEPAID01')->assertOk();

        $payment->refresh();
        $this->assertSame('completed', $payment->status);
        $this->assertNotNull($payment->order_id);
        $this->assertSame(1, Attendee::where('order_id', $payment->order_id)->count());
    }

    public function test_failure_never_overrides_a_completed_payment(): void
    {
        $payment = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))));
        $payment->update(['status' => 'completed', 'result_code' => '0']);

        app(MpesaPaymentService::class)->markFailed($payment, '1', 'late failure');

        $this->assertSame('completed', $payment->fresh()->status);
    }

    public function test_payment_after_the_hold_for_a_sold_out_ticket_becomes_a_refund(): void
    {
        $this->safaricomSays('0');
        $ticket  = $this->ticket($this->event($this->user('merchant')), ['quantity' => 1]);
        $payment = $this->pendingPayment($this->user(), $ticket);
        $payment->forceFill(['created_at' => now()->subMinutes(30)])->save();   // hold long gone
        $this->attendee($ticket, $this->user());                                 // last place sold meanwhile

        $this->mpesaCallback($payment, 0, 'SOLDOUT001')->assertOk();

        $order = Order::find($payment->fresh()->order_id);
        $this->assertSame('sold_out', $order->status);
        $this->assertEquals($payment->amount, $order->refund);
        $this->assertSame(0, Attendee::where('order_id', $order->id)->count());
    }

    public function test_callback_for_unknown_payment_creates_nothing(): void
    {
        $this->mpesaCallback('ws_CO_DOES_NOT_EXIST', 0)->assertOk();

        $this->assertSame(0, MpesaStk::count());
    }

    public function test_late_callback_fills_in_receipt_for_a_query_confirmed_payment(): void
    {
        $this->safaricomSays('0');
        $payment  = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))));
        $payments = app(MpesaPaymentService::class);

        $payments->sync($payment);             // confirmed via STK Query: no receipt yet
        $this->assertNull($payment->fresh()->mpesa_receipt_number);

        $this->mpesaCallback($payment, 0, 'LATE998877')->assertOk();

        $payment->refresh();
        $this->assertSame('LATE998877', $payment->mpesa_receipt_number);
        $this->assertSame('LATE998877', Order::find($payment->order_id)->transaction_code);
        $this->assertSame(1, Order::count());
    }

    public function test_status_endpoint_is_private_to_the_buyer(): void
    {
        $buyer   = $this->user();
        $payment = $this->pendingPayment($buyer, $this->ticket($this->event($this->user('merchant'))));

        $this->actingAs($this->user())->getJson("/mpesa/status/{$payment->checkout_request_id}")->assertNotFound();
        $this->actingAs($buyer)->getJson("/mpesa/status/{$payment->checkout_request_id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_reconciler_creates_missing_orders_and_expires_stale_prompts(): void
    {
        $this->safaricomSays(null);
        $ticket = $this->ticket($this->event($this->user('merchant')));

        $paidNoOrder = $this->pendingPayment($this->user(), $ticket);
        $paidNoOrder->update(['status' => 'completed', 'result_code' => '0']);

        $stale = $this->pendingPayment($this->user(), $ticket);
        $stale->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->artisan('mpesa:reconcile')->assertSuccessful();
        $this->artisan('mpesa:reconcile')->assertSuccessful();

        $this->assertNotNull($paidNoOrder->fresh()->order_id);
        $this->assertSame(1, Order::count());
        $this->assertSame('failed', $stale->fresh()->status);
    }
}
