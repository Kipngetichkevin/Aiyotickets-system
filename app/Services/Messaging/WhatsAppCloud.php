<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Sends approved template messages through the WhatsApp Cloud API.
 * Disabled until WHATSAPP_TOKEN, WHATSAPP_PHONE_NUMBER_ID and WHATSAPP_TEMPLATE are set.
 *
 * Expected template: an IMAGE header (the ticket QR) and body variables
 * {{1}} buyer name, {{2}} event, {{3}} ticket code, {{4}} link to all tickets.
 */
class WhatsAppCloud
{
    public function enabled(): bool
    {
        return filled(config('services.whatsapp.token'))
            && filled(config('services.whatsapp.phone_number_id'))
            && filled(config('services.whatsapp.template'));
    }

    public function sendTicket(string $phone, string $imageUrl, array $bodyParams): void
    {
        $cfg = config('services.whatsapp');

        $response = Http::withToken($cfg['token'])
            ->timeout(20)
            ->post("https://graph.facebook.com/{$cfg['api_version']}/{$cfg['phone_number_id']}/messages", [
                'messaging_product' => 'whatsapp',
                'to'                => ltrim($phone, '+'),
                'type'              => 'template',
                'template'          => [
                    'name'       => $cfg['template'],
                    'language'   => ['code' => $cfg['language']],
                    'components' => [
                        ['type' => 'header', 'parameters' => [['type' => 'image', 'image' => ['link' => $imageUrl]]]],
                        ['type' => 'body', 'parameters' => array_map(
                            fn ($text) => ['type' => 'text', 'text' => (string) $text],
                            array_values($bodyParams)
                        )],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new RuntimeException('WhatsApp send failed: ' . ($response->json('error.message') ?? $response->status()));
        }
    }
}
