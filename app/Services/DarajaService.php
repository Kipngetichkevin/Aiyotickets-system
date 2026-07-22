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
    private $environment;

    public function __construct()
    {
        $this->environment = config('mpesa.environment', 'sandbox');
        
        // Set the correct base URL based on environment
        $this->base_url = $this->environment === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
        
        // Get credentials
        $this->consumer_key = config('mpesa.mpesa_consumer_key');
        $this->consumer_secret = config('mpesa.mpesa_consumer_secret');
        $this->shortcode = config('mpesa.shortcode');
        $this->passkey = config('mpesa.passkey');
        
        // Log configuration (remove in production if sensitive)
        Log::info('DarajaService Configuration', [
            'environment' => $this->environment,
            'base_url' => $this->base_url,
            'shortcode' => $this->shortcode,
            'consumer_key_set' => !empty($this->consumer_key),
            'passkey_set' => !empty($this->passkey),
        ]);
    }

    public function getAccessToken()
    {
        try {
            Log::info('Requesting Access Token', [
                'environment' => $this->environment,
                'url' => "{$this->base_url}/oauth/v1/generate?grant_type=client_credentials"
            ]);

            $response = Http::withBasicAuth($this->consumer_key, $this->consumer_secret)
                ->timeout(30)
                ->get("{$this->base_url}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['access_token'])) {
                    Log::info('Access Token obtained successfully', [
                        'environment' => $this->environment,
                        'expires_in' => $data['expires_in'] ?? null
                    ]);
                    return $data['access_token'];
                }
            }

            // Log the error details
            $errorData = $response->json();
            Log::error('Failed to obtain access token', [
                'environment' => $this->environment,
                'status' => $response->status(),
                'error' => $errorData,
                'body' => $response->body()
            ]);

            // Check for specific error codes
            if (isset($errorData['errorCode'])) {
                if ($errorData['errorCode'] == '404.001.03') {
                    throw new \Exception('Invalid M-Pesa credentials. Please check your Consumer Key and Consumer Secret.');
                }
                if ($errorData['errorCode'] == '500.001.1001') {
                    throw new \Exception('M-Pesa service is currently unavailable. Please try again later.');
                }
                throw new \Exception('M-Pesa Error: ' . ($errorData['errorMessage'] ?? $errorData['errorCode']));
            }

            throw new \Exception('Failed to obtain access token: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Access Token Error: ' . $e->getMessage(), [
                'environment' => $this->environment,
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
            $callbackUrl = config('mpesa.callback_url');

            Log::info('STK Push Request', [
                'environment' => $this->environment,
                'amount' => $amount,
                'phone' => $phone,
                'account_reference' => $accountReference,
                'shortcode' => $this->shortcode,
                'timestamp' => $timestamp,
                'callback_url' => $callbackUrl,
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
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc,
            ];

            $response = Http::withToken($accessToken)
                ->timeout(60)
                ->post("{$this->base_url}/mpesa/stkpush/v1/processrequest", $payload);

            $responseData = $response->json();

            Log::info('STK Push Response', [
                'environment' => $this->environment,
                'status' => $response->status(),
                'response' => $responseData
            ]);

            if (!$response->successful()) {
                Log::error('STK Push Failed', [
                    'environment' => $this->environment,
                    'status' => $response->status(),
                    'response' => $responseData
                ]);
                
                if (isset($responseData['errorCode'])) {
                    throw new \Exception('M-Pesa Error: ' . ($responseData['errorMessage'] ?? $responseData['errorCode']));
                }
                throw new \Exception('STK Push failed with status: ' . $response->status());
            }

            return $responseData;

        } catch (\Exception $e) {
            Log::error('STK Push Exception: ' . $e->getMessage(), [
                'environment' => $this->environment,
                'trace' => $e->getTraceAsString()
            ]);
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