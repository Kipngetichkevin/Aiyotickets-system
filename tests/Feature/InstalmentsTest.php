<?php

namespace Tests\Feature;

use App\Mail\InstalmentNotice;
use App\Mail\InstalmentReceived;
use App\Mail\TicketsIssued;
use App\Models\Attendee;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Services\DarajaService;
use App\Services\Instalments;
use App\Services\Inventory;
use App\Services\SalesReport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class InstalmentsTest extends TestCase
{
    private int $stkCount = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        // Every STK push is accepted; every query confirms payment
        $this->mock(DarajaService::class, function ($mock) {
            $mock->shouldReceive('stkPush')->andReturnUsing(fn () => [
                'ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_INST_' . (++$this->stkCount), 'MerchantRequestID' => 'MR',
            ]);
            $mock->shouldReceive('queryStatus')->andReturn(['ResultCode' => '0', 'ResultDesc' => 'ok']);
        });
    }

    /** Merchant event 30 days out with instalments on (30% deposit, due 7 days before). */
    private function instalmentTicket(int $price = 1000, int $quantity = 10)
    {
        $event = $this->event($this->user('merchant'), [
            'date' => now()->addDays(30), 'end_date' => now()->addDays(30)->addHours(5),
            'allow_instalments' => true, 'instalment_deposit_percent' => 30, 'instalment_due_days' => 7,
        ]);

        return $this->ticket($event, ['price' => $price, 'quantity' => $quantity]);
    }

    /** Buyer pays a deposit for $qty tickets; returns the partial order. */
    private function depositOrder($ticket, $buyer, int $qty = 2): Order
    {
        $this->actingAs($buyer)
            ->withSession(['cart' => [$ticket->id => ['ticket_id' => $ticket->id, 'quantity' => $qty]]])
            ->postJson('/cart/checkout', [
                'name' => $buyer->name, 'email' => $buyer->email, 'gender' => '1', 'payment_mode' => 'Mobile Money',
                'phone' => '0712345678', 'payment_plan' => 'instalments',
            ])->assertOk();

        $stk = MpesaStk::latest('id')->first();
        $this->mpesaCallback($stk, 0)->assertOk();

        return Order::find($stk->fresh()->order_id);
    }

    private function payInstalment(Order $order, $buyer, int $amount): void
    {
        $this->actingAs($buyer)->postJson("/my-instalments/{$order->id}/pay", ['amount' => $amount, 'phone' => '0712345678'])->assertOk();
        $this->mpesaCallback(MpesaStk::latest('id')->first(), 0)->assertOk();
    }

    public function test_eligibility_rules(): void
    {
        $service = app(Instalments::class);
        $ok      = $this->instalmentTicket();
        $line    = fn ($t) => [['ticket_id' => $t->id, 'qty' => 1, 'price' => 1000, 'subtotal' => 1000]];

        $plan = $service->planFor($line($ok), []);
        $this->assertSame(30, $plan['percent']);
        $this->assertTrue($plan['due_date']->isSameDay(now()->addDays(23)));

        $off = $this->ticket($this->event($this->user('merchant'), ['date' => now()->addDays(30)]));
        $this->assertArrayHasKey('error', $service->planFor($line($off), []));

        $soon = $this->ticket($this->event($this->user('merchant'), ['date' => now()->addDays(5), 'allow_instalments' => true]));
        $this->assertArrayHasKey('error', $service->planFor($line($soon), []));   // due date already passed

        $this->assertArrayHasKey('error', $service->planFor(array_merge($line($ok), $line($this->instalmentTicket())), []));
        $this->assertSame(300, $service->deposit(1000, 30));
    }

    public function test_cart_offers_the_plan_or_says_why_not(): void
    {
        $offered = $this->instalmentTicket(1000);
        $this->withSession(['cart' => [$offered->id => ['ticket_id' => $offered->id, 'event_id' => $offered->event_id, 'quantity' => 2, 'price' => 1000]]])
            ->get('/cart')->assertOk()
            ->assertSee('KES 600 deposit now (30%)');

        $notOffered = $this->ticket($this->event($this->user('merchant'), ['date' => now()->addDays(30)]), ['price' => 1000]);
        $this->withSession(['cart' => [$notOffered->id => ['ticket_id' => $notOffered->id, 'event_id' => $notOffered->event_id, 'quantity' => 1, 'price' => 1000]]])
            ->get('/cart')->assertOk()
            ->assertDontSee('deposit now')
            ->assertSee('This event does not offer lipa mdogo mdogo.');
    }

    public function test_deposit_holds_places_without_issuing_tickets(): void
    {
        $ticket = $this->instalmentTicket(1000, 10);
        $order  = $this->depositOrder($ticket, $this->user(), 2);

        $this->assertSame('partial', $order->status);
        $this->assertEquals(600, $order->amount_paid);
        $this->assertEquals(1400, $order->balance_due);
        $this->assertEquals(600, MpesaStk::where('order_id', $order->id)->value('amount'));

        $attendees = Attendee::where('order_id', $order->id)->get();
        $this->assertCount(2, $attendees);
        $this->assertTrue($attendees->every(fn ($a) => $a->payment_status === 'partial' && $a->qr_code === null));

        $this->assertSame(8, (new Inventory)->ticketsLeft($ticket->id));   // places held
        Mail::assertQueued(InstalmentReceived::class, 1);
        Mail::assertNotQueued(TicketsIssued::class);
    }

    public function test_paying_the_balance_issues_the_tickets(): void
    {
        $buyer = $this->user();
        $order = $this->depositOrder($this->instalmentTicket(1000), $buyer, 2);

        $this->payInstalment($order, $buyer, 500);
        $order->refresh();
        $this->assertSame('partial', $order->status);
        $this->assertEquals(900, $order->balance_due);

        $this->actingAs($buyer)->postJson("/my-instalments/{$order->id}/pay", ['amount' => 901, 'phone' => '0712345678'])
            ->assertStatus(422);                                            // more than the balance
        $this->actingAs($this->user())->postJson("/my-instalments/{$order->id}/pay", ['amount' => 100, 'phone' => '0712345678'])
            ->assertNotFound();                                             // not their order

        $this->payInstalment($order, $buyer, 900);
        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertEquals(2000, $order->amount_paid);

        $attendees = Attendee::where('order_id', $order->id)->get();
        $this->assertTrue($attendees->every(fn ($a) => $a->payment_status === 'paid' && Str::startsWith($a->qr_code, 'QR-')));
        Mail::assertQueued(TicketsIssued::class, 1);

        // A late duplicate payment for a closed order becomes a refund, not a second completion
        $stray = MpesaStk::create(['checkout_request_id' => 'ws_CO_STRAY', 'user_id' => $buyer->id, 'status' => 'completed', 'result_code' => '0',
            'amount' => 200, 'pending_data' => ['instalment_for' => $order->id]]);
        app(\App\Services\MpesaPaymentService::class)->fulfil($stray);
        $this->assertEquals(200, $order->fresh()->refund);
        Mail::assertQueued(TicketsIssued::class, 1);
    }

    public function test_reminders_are_sent_once_per_threshold(): void
    {
        $order = $this->depositOrder($this->instalmentTicket(), $this->user());
        $order->update(['due_date' => now()->addDays(6)]);

        $this->artisan('instalments:process')->assertSuccessful();
        $this->artisan('instalments:process')->assertSuccessful();

        Mail::assertQueued(InstalmentNotice::class, fn ($m) => $m->kind === 'reminder');
        Mail::assertQueued(InstalmentNotice::class, 1);
        $this->assertEqualsCanonicalizing([14, 7], $order->fresh()->reminders_sent);
    }

    public function test_missed_deadline_cancels_keeps_deposit_and_refunds_the_rest(): void
    {
        $buyer  = $this->user();
        $ticket = $this->instalmentTicket(1000, 10);
        $order  = $this->depositOrder($ticket, $buyer, 2);   // 600 deposit
        $this->payInstalment($order, $buyer, 400);           // 400 more
        $order->update(['due_date' => now()->subHour()]);

        $this->artisan('instalments:process')->assertSuccessful();

        $order->refresh();
        $this->assertSame('lapsed', $order->status);
        $this->assertEquals(400, $order->refund);
        $this->assertTrue(Attendee::where('order_id', $order->id)->get()->every(fn ($a) => $a->payment_status === 'overdue'));
        $this->assertSame(10, (new Inventory)->ticketsLeft($ticket->id));   // places released
        Mail::assertQueued(InstalmentNotice::class, fn ($m) => $m->kind === 'lapsed');

        // Merchant keeps the deposit (less the platform fee); admin records the refund
        $merchant = \App\Models\User::find($order->seller_id);
        $this->assertEquals(570, SalesReport::own($merchant)->balance()['withdrawable']);   // 600 - 5%

        $this->actingAs($this->user('admin'))->post("/admin/payouts/refunds/{$order->id}", ['reference' => 'RFND123'])->assertRedirect();
        $this->assertNotNull($order->fresh()->refunded_at);
    }

    public function test_merchant_settings_and_report_figures(): void
    {
        $merchant = $this->user('merchant');
        $event    = $this->event($merchant, ['date' => now()->addDays(30)]);

        $this->actingAs($this->user('merchant'))->post("/instalments/event/{$event->id}", [
            'allow_instalments' => 1, 'instalment_deposit_percent' => 30, 'instalment_due_days' => 7,
        ])->assertForbidden();

        $this->actingAs($merchant)->post("/instalments/event/{$event->id}", [
            'allow_instalments' => 1, 'instalment_deposit_percent' => 40, 'instalment_due_days' => 10,
        ])->assertRedirect();
        $this->assertTrue($event->fresh()->allow_instalments);
        $this->assertSame(40, $event->fresh()->instalment_deposit_percent);

        $order = $this->depositOrder($this->ticket($event->fresh(), ['price' => 1000]), $this->user(), 1);   // 400 deposit
        $totals = (new SalesReport($merchant))->totals();
        $this->assertSame(1, $totals['instalments_open']);
        $this->assertEquals(600, $totals['outstanding']);
        $this->assertSame(0, $totals['tickets']);   // not counted as sold until fully paid

        $this->actingAs($merchant)->get('/instalments')->assertOk()->assertSee($order->order_no);
        $this->actingAs(\App\Models\User::find($order->user_id))->get('/my-instalments')->assertOk()->assertSee('KES 400 of 1,000 paid');
    }
}
