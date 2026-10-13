<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends SMS through Africa's Talking. Disabled until AT_USERNAME and AT_API_KEY are set.
 */
class AfricasTalkingSms
{
    public function enabled(): bool
    {
        return filled(config('services.africastalking.username')) && filled(config('services.africastalking.api_key'));
    }

    public function send(string $phone, string $message): void
    {
        $username = config('services.africastalking.username');
        $host     = $username === 'sandbox' ? 'https://api.sandbox.africastalking.com' : 'https://api.africastalking.com';

        $response = Http::asForm()
            ->withHeaders(['apiKey' => config('services.africastalking.api_key'), 'Accept' => 'application/json'])
            ->timeout(20)
            ->post("{$host}/version1/messaging", array_filter([
                'username' => $username,
                'to'       => '+' . ltrim($phone, '+'),
                'message'  => $message,
                'from'     => config('services.africastalking.sender_id'),
            ]));

        $status = $response->json('SMSMessageData.Recipients.0.status');

        if (!$response->successful() || !in_array($status, ['Success', 'Sent'], true)) {
            throw new RuntimeException('Africa\'s Talking SMS failed: ' . ($status ?? $response->status()));
        }
    }
}
