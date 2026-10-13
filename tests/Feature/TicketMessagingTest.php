<?php

namespace Tests\Feature;

use App\Http\Controllers\TicketViewController;
use App\Jobs\SendTicketMessage;
use App\Models\Attendee;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TicketMessagingTest extends TestCase
{
    /** A paid order (through the real callback flow) with two tickets. */
    private function paidOrder(): Order
    {
        $this->safaricomSays('0');
        $payment = $this->pendingPayment($this->user(), $this->ticket($this->event($this->user('merchant'))), 2);
        $this->mpesaCallback($payment, 0)->assertOk();

        return Order::find($payment->fresh()->order_id);
    }

    private function enableSms(): void
    {
        config(['services.africastalking.username' => 'sandbox', 'services.africastalking.api_key' => 'test-key', 'services.africastalking.sender_id' => 'AIYOTICKET']);
    }

    private function enableWhatsApp(): void
    {
        config(['services.whatsapp.token' => 'test-token', 'services.whatsapp.phone_number_id' => '123456', 'services.whatsapp.template' => 'ticket_ready']);
    }

    public function test_no_sms_or_whatsapp_until_configured(): void
    {
        Queue::fake();
        $this->paidOrder();

        Queue::assertNotPushed(SendTicketMessage::class);
    }

    public function test_configured_channels_get_one_job_each(): void
    {
        Queue::fake();
        $this->enableSms();
        $this->enableWhatsApp();

        $order = $this->paidOrder();

        Queue::assertPushed(SendTicketMessage::class, 2);
        Queue::assertPushed(SendTicketMessage::class, fn ($job) => $job->channel === 'sms' && $job->phone === '254700000000' && $job->order->is($order));
        Queue::assertPushed(SendTicketMessage::class, fn ($job) => $job->channel === 'whatsapp');
    }

    public function test_sms_contains_codes_and_a_signed_ticket_link(): void
    {
        $order = $this->paidOrder();
        $this->enableSms();
        Http::fake(['*africastalking.com/*' => Http::response(['SMSMessageData' => ['Recipients' => [['status' => 'Success']]]])]);

        (new SendTicketMessage($order, 'sms', '254700000000'))->handle(app(\App\Services\Messaging\AfricasTalkingSms::class), app(\App\Services\Messaging\WhatsAppCloud::class));

        Http::assertSent(function ($request) use ($order) {
            $codes = Attendee::where('order_id', $order->id)->pluck('qr_code');
            return str_starts_with($request->url(), 'https://api.sandbox.africastalking.com/version1/messaging')
                && $request->header('apiKey')[0] === 'test-key'
                && $request['to'] === '+254700000000'
                && $request['from'] === 'AIYOTICKET'
                && $codes->every(fn ($c) => str_contains($request['message'], $c))
                && str_contains($request['message'], '/my-tickets/' . $order->uuid . '?expires=');
        });
    }

    public function test_whatsapp_sends_one_template_per_ticket_with_qr_image(): void
    {
        $order = $this->paidOrder();
        $this->enableWhatsApp();
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);

        (new SendTicketMessage($order, 'whatsapp', '254700000000'))->handle(app(\App\Services\Messaging\AfricasTalkingSms::class), app(\App\Services\Messaging\WhatsAppCloud::class));

        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => str_contains($request->url(), '/123456/messages')
            && $request['template']['name'] === 'ticket_ready'
            && str_contains($request['template']['components'][0]['parameters'][0]['image']['link'], '/qr/QR-')
            && count($request['template']['components'][1]['parameters']) === 4);
    }

    public function test_signed_ticket_page_and_qr_image(): void
    {
        $order = $this->paidOrder();
        $code  = Attendee::where('order_id', $order->id)->value('qr_code');

        $this->get(TicketViewController::linkFor($order))->assertOk()->assertSee($code);
        $this->get(route('tickets.view', ['order' => $order->uuid]))->assertForbidden();                   // unsigned
        // A valid link edited to point at someone else's order is refused
        $other = $this->paidOrder();
        $this->get(str_replace($order->uuid, $other->uuid, TicketViewController::linkFor($order)))->assertForbidden();
        $this->get(str_replace($order->uuid, $other->uuid, TicketViewController::linkFor($order)))->assertDontSee(
            (string) Attendee::where('order_id', $other->id)->value('qr_code')
        );

        $this->get(TicketViewController::qrLinkFor($code))->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->get(TicketViewController::qrLinkFor('QR-NOT-A-REAL-CODE'))->assertNotFound();
    }
}
