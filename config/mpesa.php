<?php

// Safaricom's public sandbox test credentials are only ever used in the sandbox:
// in production a missing shortcode or passkey stays empty (and `php artisan aiyo:check` says so).
$sandbox = env('MPESA_ENVIRONMENT', 'sandbox') !== 'production';

return [
    // Environment - sandbox or production
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    // The Mpesa Consumer Key
    'consumer_key' => env('MPESA_CONSUMER_KEY'),

    // The Mpesa Consumer Secret
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    // The Lipa na Mpesa Online Passkey
    'passkey' => env('MPESA_PASSKEY', env('SAFARICOM_PASSKEY', $sandbox ? 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919' : null)),

    // The Lipa na Mpesa Online ShortCode (Paybill number, or the Till's head-office/store number)
    'shortcode' => env('MPESA_BUSINESS_SHORTCODE', $sandbox ? '174379' : null),

    // 'paybill' (CustomerPayBillOnline) or 'till' (CustomerBuyGoodsOnline)
    'transaction_type' => env('MPESA_TRANSACTION_TYPE', 'paybill'),

    // Who receives the money: the Till number for 'till'; defaults to the shortcode (Paybill)
    'party_b' => env('MPESA_PARTY_B'),

    // Where Safaricom posts payment results: must be public HTTPS. Defaults to APP_URL/mpesa/callback.
    'callback_url' => env('MPESA_CALLBACK_URL') ?: rtrim((string) env('APP_URL'), '/') . '/mpesa/callback',

    // The Mpesa Initiator Name
    'initiator_name' => env('MPESA_INITIATOR_NAME', 'testapi'),

    // The Mpesa Initiator Password
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),

    // Mpesa B2C ShortCode
    'b2c_shortcode' => env('MPESA_B2C_SHORTCODE'),

    // Mpesa C2B Validation url
    'c2b_validation_url' => env('MPESA_C2B_VALIDATION_URL'),

    // Mpesa C2B Confirmation url
    'c2b_confirmation_url' => env('MPESA_C2B_CONFIRMATION_URL'),

    // Mpesa B2C Result url
    'b2c_result_url' => env('MPESA_B2C_RESULT_URL'),

    // Mpesa B2C Timeout url
    'b2c_timeout_url' => env('MPESA_B2C_TIMEOUT_URL'),

    // Mpesa Transaction Status Result url
    'status_result_url' => env('MPESA_STATUS_RESULT_URL'),

    // Mpesa Transaction Status Timeout url
    'status_timeout_url' => env('MPESA_STATUS_TIMEOUT_URL'),

    // Mpesa Account Balance Result url
    'balance_result_url' => env('MPESA_BALANCE_RESULT_URL'),

    // Mpesa Account Balance Timeout url
    'balance_timeout_url' => env('MPESA_BALANCE_TIMEOUT_URL'),

    // Mpesa Reversal Result url
    'reversal_result_url' => env('MPESA_REVERSAL_RESULT_URL'),

    // Mpesa Reversal Timeout url
    'reversal_timeout_url' => env('MPESA_REVERSAL_TIMEOUT_URL'),

    // Mpesa B2B urls
    'b2b_result_url' => env('MPESA_B2B_RESULT_URL'),
    'b2b_timeout_url' => env('MPESA_B2B_TIMEOUT_URL'),
];