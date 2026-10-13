<?php

namespace Tests;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Merchant;
use App\Models\MpesaStk;
use App\Models\Ticket;
use App\Models\User;
use App\Services\DarajaService;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /** Roles, payment modes and event types. */
    protected bool $seed = true;
    protected string $seeder = ReferenceDataSeeder::class;

    protected function user(string $role = 'user', array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        if ($role === 'merchant') {
            Merchant::create(['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email]);
        }

        return $user;
    }

    protected function event(User $owner, array $attributes = []): Event
    {
        $merchantId = Merchant::where('user_id', $owner->id)->value('id')
            ?? Merchant::create(['user_id' => $owner->id, 'name' => $owner->name])->id;

        return Event::forceCreate(array_merge([
            'event_type_id' => DB::table('event_types')->value('id'),
            'user_id'       => $owner->id,
            'merchant_id'   => $merchantId,
            'name'          => 'Test Event ' . Str::random(4),
            'date'          => now()->addDays(7),
            'end_date'      => now()->addDays(7)->addHours(6),
            'location'      => 'Nairobi',
            'currency'      => 'KES',
            'status'        => 'active',
        ], $attributes));
    }

    protected function ticket(Event $event, array $attributes = []): Ticket
    {
        return Ticket::forceCreate(array_merge([
            'event_id'    => $event->id,
            'merchant_id' => $event->merchant_id,
            'name'        => 'Regular',
            'price'       => 1000,
            'quantity'    => 10,
            'currency'    => 'KES',
        ], $attributes));
    }

    protected function attendee(Ticket $ticket, User $buyer, array $attributes = []): Attendee
    {
        return Attendee::forceCreate(array_merge([
            'ticket_id'      => $ticket->id,
            'user_id'        => $buyer->id,
            'name'           => $buyer->name,
            'qty'            => 1,
            'payment_status' => 'paid',
            'status'         => 'active',
            'qr_code'        => 'QR-ORD-TEST' . strtoupper(Str::random(6)) . '-' . strtoupper(Str::random(6)),
        ], $attributes));
    }

    /** A pending STK push for $qty of $ticket, as checkout creates it. */
    protected function pendingPayment(User $buyer, Ticket $ticket, int $qty = 1): MpesaStk
    {
        return MpesaStk::create([
            'checkout_request_id' => 'ws_CO_' . Str::random(12),
            'merchant_request_id' => 'MR-' . Str::random(8),
            'user_id'             => $buyer->id,
            'status'              => 'pending',
            'amount'              => $ticket->price * $qty,
            'pending_data'        => [
                'user_id'          => $buyer->id,
                'name'             => $buyer->name,
                'email'            => $buyer->email,
                'phone'            => '254700000000',
                'event_id'         => $ticket->event_id,
                'total_amount'     => $ticket->price * $qty,
                'total_qty'        => $qty,
                'cart_items'       => [[
                    'ticket_id' => $ticket->id, 'event' => $ticket->name, 'qty' => $qty,
                    'price' => (float) $ticket->price, 'subtotal' => $ticket->price * $qty,
                ]],
                'experience_items' => [],
            ],
        ]);
    }

    /**
     * Make Safaricom's STK Query answer with $resultCode ('0' = paid), or with
     * "still processing" (no ResultCode) when null.
     */
    protected function safaricomSays(?string $resultCode): void
    {
        $this->mock(DarajaService::class, function ($mock) use ($resultCode) {
            $mock->shouldReceive('queryStatus')->andReturn(
                $resultCode === null
                    ? ['errorCode' => '500.001.1001', 'errorMessage' => 'The transaction is being processed']
                    : ['ResultCode' => $resultCode, 'ResultDesc' => 'Result ' . $resultCode]
            );
        });
    }

    protected function mpesaCallback(MpesaStk|string $stk, int $resultCode, ?string $receipt = null): \Illuminate\Testing\TestResponse
    {
        $id   = $stk instanceof MpesaStk ? $stk->checkout_request_id : $stk;
        $body = ['CheckoutRequestID' => $id, 'MerchantRequestID' => 'MR', 'ResultCode' => $resultCode, 'ResultDesc' => 'desc'];

        if ($resultCode === 0) {
            $body['CallbackMetadata'] = ['Item' => [
                ['Name' => 'Amount', 'Value' => 1000],
                ['Name' => 'MpesaReceiptNumber', 'Value' => $receipt ?? 'RCPT' . strtoupper(Str::random(6))],
                ['Name' => 'PhoneNumber', 'Value' => 254700000000],
            ]];
        }

        return $this->postJson('/mpesa/callback', ['Body' => ['stkCallback' => $body]]);
    }
}
