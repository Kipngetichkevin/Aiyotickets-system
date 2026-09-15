<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DarajaService
{
    private $base_url;
    private $consumer_key;
    private $consumer_secret;
    private $shortcode;
    private $passkey;

    public function __construct()
    {
        // Determine environment from .env
        $environment = env('MPESA_ENVIRONMENT', 'sandbox');
        
        // Set the correct base URL
        $this->base_url = $environment === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
        
        // Get credentials - try both naming conventions
        $this->consumer_key = env('MPESA_CONSUMER_KEY') ?: env('DARAJA_CONSUMER_KEY');
        $this->consumer_secret = env('MPESA_CONSUMER_SECRET') ?: env('DARAJA_CONSUMER_SECRET');
        $this->shortcode = env('MPESA_BUSINESS_SHORTCODE') ?: env('DARAJA_SHORTCODE');
        $this->passkey = env('SAFARICOM_PASSKEY') ?: env('DARAJA_PASSKEY');
        
        // Log the configuration (remove in production)
        Log::info('DarajaService Configuration', [
            'environment' => $environment,
            'base_url' => $this->base_url,
            'shortcode' => $this->shortcode,
            'consumer_key_set' => !empty($this->consumer_key),
            'consumer_key_preview' => substr($this->consumer_key, 0, 10) . '...',
            'passkey_set' => !empty($this->passkey),
        ]);
    }

    public function getAccessToken()
    {
        try {
            Log::info('Requesting Access Token', [
                'url' => "{$this->base_url}/oauth/v1/generate?grant_type=client_credentials"
            ]);

            $response = Http::withBasicAuth($this->consumer_key, $this->consumer_secret)
                ->timeout(30)
                ->get("{$this->base_url}/oauth/v1/generate?grant_type=client_credentials");

            Log::info('Access Token Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['access_token'])) {
                    Log::info('Access Token obtained successfully');
                    return $data['access_token'];
                }
            }

            // Log the error details
            $errorData = $response->json();
            Log::error('Failed to obtain access token', [
                'status' => $response->status(),
                'error' => $errorData,
                'body' => $response->body()
            ]);

            // Check for specific error codes
            if (isset($errorData['errorCode'])) {
                if ($errorData['errorCode'] == '404.001.03') {
                    throw new \Exception('Invalid credentials. Please check your Consumer Key and Consumer Secret.');
                }
                throw new \Exception('M-Pesa Error: ' . ($errorData['errorMessage'] ?? $errorData['errorCode']));
            }

            throw new \Exception('Failed to obtain access token: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Access Token Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function stkPush($amount, $phone, $accountReference, $transactionDesc)
    {
        try {
            $timestamp = now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            Log::info('STK Push Request', [
                'amount' => $amount,
                'phone' => $phone,
                'account_reference' => $accountReference,
                'shortcode' => $this->shortcode,
                'timestamp' => $timestamp,
                'callback_url' => env('MPESA_CALLBACK_URL'),
            ]);

            $accessToken = $this->getAccessToken();

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => (int) $amount,
                'PartyA' => $phone,
                'PartyB' => $this->shortcode,
                'PhoneNumber' => $phone,
                'CallBackURL' => env('MPESA_CALLBACK_URL'),
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc,
            ];

            $response = Http::withToken($accessToken)
                ->timeout(60)
                ->post("{$this->base_url}/mpesa/stkpush/v1/processrequest", $payload);

            $responseData = $response->json();

            Log::info('STK Push Response', [
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if (!$response->successful()) {
                Log::error('STK Push Failed', [
                    'status' => $response->status(),
                    'response' => $responseData
                ]);
                
                if (isset($responseData['errorCode'])) {
                    throw new \Exception('M-Pesa Error: ' . ($responseData['errorMessage'] ?? $responseData['errorCode']));
                }
            }

            return $responseData;

        } catch (\Exception $e) {
            Log::error('STK Push Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    public function queryStatus($checkoutRequestId)
    {
        try {
            $timestamp = now()->format('YmdHis');
            $password = base64_encode($this->shortcode . $this->passkey . $timestamp);

            $accessToken = $this->getAccessToken();

            $payload = [
                'BusinessShortCode' => $this->shortcode,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'CheckoutRequestID' => $checkoutRequestId,
            ];

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->post("{$this->base_url}/mpesa/stkpushquery/v1/query", $payload);

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Query Status Error: ' . $e->getMessage());
            throw $e;
        }
    }
}