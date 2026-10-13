<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'google' => [
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    // Ticket SMS. Off until AT_USERNAME and AT_API_KEY are set; username "sandbox" uses the sandbox API.
    'africastalking' => [
        'username'  => env('AT_USERNAME'),
        'api_key'   => env('AT_API_KEY'),
        'sender_id' => env('AT_SENDER_ID'), // approved alphanumeric sender, e.g. AIYOTICKET
    ],

    // Ticket messages on WhatsApp (Meta Cloud API). Off until token, phone number id and template are set.
    'whatsapp' => [
        'token'           => env('WHATSAPP_TOKEN'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
        'template'        => env('WHATSAPP_TEMPLATE'),            // approved template name
        'language'        => env('WHATSAPP_TEMPLATE_LANG', 'en'),
        'api_version'     => env('WHATSAPP_API_VERSION', 'v21.0'),
    ],


];
