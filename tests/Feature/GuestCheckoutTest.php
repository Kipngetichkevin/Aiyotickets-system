<?php

namespace Tests\Feature;

use App\Http\Controllers\InstalmentController;
use App\Mail\TicketsIssued;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\User;
use App\Services\DarajaService;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GuestCheckoutTest extends TestCase
{
    private int $n = 0;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->mock(DarajaService::class, function ($mock) {
            $mock->shouldReceive('stkPush')->andReturnUsing(fn () => ['ResponseCode' => '0', 'CheckoutRequestID' => 'ws_CO_GUEST_' . (++$this->n), 'MerchantRequestID' => 'MR']);
            $mock->shouldReceive('queryStatus')->andReturn(['ResultCode' => '0', 'ResultDesc' => 'ok']);
        });
    }

    private function guestCheckout($ticket, string $email, array $extra = []): \Illuminate\Testing\TestResponse
    {
        return $this->withSession(['cart' => [$ticket->id => ['ticket_id' => $ticket->id, 'event_id' => $ticket->event_id, 'quantity' => 1]]])
            ->postJson('/cart/checkout', array_merge([
                'name' => 'Wanjiku Guest', 'email' => $email, 'gender' => '2', 'payment_mode' => 'Mobile Money', 'phone' => '0712345678',
            ], $extra));
    }

    public function test_guest_buys_without_an_account_and_sees_their_order(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')), ['price' => 500]);

        $this->guestCheckout($ticket, 'Wanjiku@Example.com')->assertOk();
        $this->assertGuest();

        // An account was created for the email and owns the payment
        $buyer = User::where('email', 'wanjiku@example.com')->first();
        $this->assertNotNull($buyer);
        $this->assertTrue($buyer->hasRole('user'));
        $payment = MpesaStk::latest('id')->first();
        $this->assertSame($buyer->id, $payment->user_id);

        // This browser can follow its payment and open its order
        $this->getJson("/mpesa/status/{$payment->checkout_request_id}")->assertOk();
        $this->mpesaCallback($payment, 0)->assertOk();
        $order = $this->postJson('/cart/create-order', ['checkout_request_id' => $payment->checkout_request_id])
            ->assertOk()->json('data.order_id');
        $this->get("/cart/thankyou?order={$order}")->assertOk();

        Mail::assertQueued(TicketsIssued::class, fn ($m) => $m->hasTo('wanjiku@example.com'));
    }

    public function test_another_browser_cannot_see_a_guest_payment_or_order(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')));
        $this->guestCheckout($ticket, 'private@example.com')->assertOk();
        $payment = MpesaStk::latest('id')->first();
        $this->mpesaCallback($payment, 0)->assertOk();
        $orderId = $payment->fresh()->order_id;

        $this->flushSession();   // a different visitor
        $this->getJson("/mpesa/status/{$payment->checkout_request_id}")->assertNotFound();
        $this->postJson('/cart/create-order', ['checkout_request_id' => $payment->checkout_request_id])->assertNotFound();
        $this->get("/cart/thankyou?order={$orderId}")->assertRedirect(route('cart.view'));
    }

    public function test_existing_email_links_the_order_without_logging_in(): void
    {
        $existing = $this->user('user', ['email' => 'returning@example.com']);
        $ticket   = $this->ticket($this->event($this->user('merchant')));

        $this->guestCheckout($ticket, 'returning@example.com')->assertOk();

        $this->assertGuest();
        $this->assertSame(1, User::where('email', 'returning@example.com')->count());
        $this->assertSame($existing->id, MpesaStk::latest('id')->first()->user_id);
    }

    public function test_unverified_account_cannot_see_tickets_bought_with_its_email(): void
    {
        // Someone registers a buyer's email before the buyer checks out as a guest
        $squatter = $this->user('user', ['email' => 'victim@example.com', 'email_verified_at' => null]);

        $this->actingAs($squatter)->get(route('my-qrs'))->assertRedirect(route('verification.notice'));
    }

    public function test_password_reset_verifies_a_guest_account(): void
    {
        $buyer = $this->user('user', ['email' => 'guest@example.com', 'email_verified_at' => null]);
        $token = \Illuminate\Support\Facades\Password::createToken($buyer);

        $this->post('/reset-password', [
            'token' => $token, 'email' => 'guest@example.com',
            'password' => 'new-Password-123', 'password_confirmation' => 'new-Password-123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($buyer->fresh()->hasVerifiedEmail());
    }

    public function test_guest_can_book_an_experience_directly(): void
    {
        $merchant   = $this->user('merchant');
        $experience = \App\Models\Experience::forceCreate(['name' => 'Guest Safari', 'user_id' => $merchant->id, 'status' => 'active', 'start_date' => now()->addDays(20)]);
        $package    = \App\Models\ExperiencePackage::forceCreate(['experience_id' => $experience->id, 'name' => 'Std', 'price' => 900, 'currency' => 'KES', 'is_active' => true]);

        $this->postJson("/experiences/{$experience->slug}/register", [
            'package_id' => $package->id, 'name' => 'Otieno', 'email' => 'otieno@example.com', 'phone' => '0712345678', 'gender' => '1',
        ])->assertOk();

        $payment = MpesaStk::latest('id')->first();
        $this->assertSame(User::where('email', 'otieno@example.com')->value('id'), $payment->user_id);
        $this->getJson("/mpesa/status/{$payment->checkout_request_id}")->assertOk();
    }

    public function test_guest_pays_lipa_mdogo_mdogo_balance_from_the_private_link(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant'), [
            'date' => now()->addDays(30), 'end_date' => now()->addDays(30)->addHours(4),
            'allow_instalments' => true, 'instalment_deposit_percent' => 30, 'instalment_due_days' => 7,
        ]), ['price' => 1000]);

        $this->guestCheckout($ticket, 'layaway@example.com', ['payment_plan' => 'instalments'])->assertOk();
        $this->mpesaCallback(MpesaStk::latest('id')->first(), 0)->assertOk();
        $order = Order::where('email', 'layaway@example.com')->first();
        $this->assertSame('partial', $order->status);

        $this->flushSession();   // later, from the link in the receipt, on any device
        $link = InstalmentController::payLinkFor($order);
        $page = $this->get($link)->assertOk()->assertSee('KES 300 of 1,000 paid');
        $this->get(route('instalments.guest', ['order' => $order->uuid]))->assertForbidden();   // unsigned

        preg_match("/instalmentPay\\(\\d+, \\d+, '([^']+)'\\)/", $page->getContent(), $m);
        $payUrl = html_entity_decode($m[1]);
        $this->postJson($payUrl, ['amount' => 700, 'phone' => '0712345678'])->assertOk();
        $this->mpesaCallback(MpesaStk::latest('id')->first(), 0)->assertOk();

        $this->assertSame('paid', $order->fresh()->status);
        Mail::assertQueued(TicketsIssued::class, 1);
    }

    public function test_ticket_email_links_to_the_private_ticket_page(): void
    {
        $ticket = $this->ticket($this->event($this->user('merchant')));
        $this->guestCheckout($ticket, 'link@example.com')->assertOk();
        $this->mpesaCallback(MpesaStk::latest('id')->first(), 0)->assertOk();

        $order = Order::where('email', 'link@example.com')->first();
        $html  = (new TicketsIssued($order))->render();

        $this->assertStringContainsString('/my-tickets/' . $order->uuid . '?expires=', $html);
        $this->assertStringNotContainsString(route('my-qrs'), $html);
    }
}
