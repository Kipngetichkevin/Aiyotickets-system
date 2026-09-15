<?php

namespace App\Http\Controllers;

use App\Services\DarajaService;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\Attendee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    private $darajaService;

    public function __construct(DarajaService $darajaService)
    {
        $this->darajaService = $darajaService;
    }

    public function stkPush(Request $request)
    {
        $request->validate([
            'phone'             => 'required|regex:/^254\d{9}$/',
            'amount'            => 'required|numeric|min:1',
            'account_reference' => 'nullable|string|max:50',
            'transaction_desc'  => 'nullable|string|max:50',
            'order_id'          => 'nullable|exists:orders,id',
            'attendee_id'       => 'nullable|exists:attendees,id',
            'email'             => 'nullable|email',
        ]);

        try {
            $phone = $request->input('phone');
            $amount = $request->input('amount');
            $accountReference = $request->input('account_reference', 'TicketPayment');
            $transactionDesc = $request->input('transaction_desc', 'Event Ticket Payment');

            if (empty(env('MPESA_CONSUMER_KEY')) || empty(env('MPESA_CONSUMER_SECRET'))) {
                Log::error('MPESA credentials are missing in .env file');
                return response()->json([
                    'success' => false,
                    'message' => 'M-Pesa is not properly configured. Please contact support.'
                ], 500);
            }

            $response = $this->darajaService->stkPush(
                $amount,
                $phone,
                $accountReference,
                $transactionDesc
            );

            if (isset($response['errorCode'])) {
                Log::error('STK Push API Error', ['response' => $response]);

                MpesaStk::create([
                    'response'             => $response,
                    'amount'               => $amount,
                    'phonenumber'          => $phone,
                    'email'                => $request->input('email'),
                    'user_id'              => auth()->id() ?? null,
                    'order_id'             => $request->input('order_id'),
                    'attendee_id'          => $request->input('attendee_id'),
                    'result_code'          => '2',
                    'result_desc'          => $response['errorMessage'] ?? 'Payment failed',
                    'response_code'        => $response['errorCode'] ?? 'ERROR',
                    'response_description' => $response['errorMessage'] ?? 'Payment failed',
                    'customer_message'     => $response['errorMessage'] ?? 'Payment failed',
                ]);

                return response()->json([
                    'success'    => false,
                    'message'    => $response['errorMessage'] ?? 'Payment initiation failed',
                    'error_code' => $response['errorCode'] ?? null
                ], 400);
            }

            if (isset($response['CheckoutRequestID'])) {
                $mpesaStk = MpesaStk::create([
                    'merchant_request_id'  => $response['MerchantRequestID'] ?? null,
                    'checkout_request_id'  => $response['CheckoutRequestID'],
                    'response'             => $response,
                    'amount'               => $amount,
                    'phonenumber'          => $phone,
                    'email'                => $request->input('email'),
                    'user_id'              => auth()->id() ?? null,
                    'order_id'             => $request->input('order_id'),
                    'attendee_id'          => $request->input('attendee_id'),
                    'result_code'          => '1',
                    'result_desc'          => 'Request sent to M-Pesa',
                    'response_code'        => '0',
                    'response_description' => 'Request sent to M-Pesa',
                    'customer_message'     => 'Please check your phone to complete payment',
                ]);

                Log::info('STK Push initiated successfully', [
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'mpesa_stk_id' => $mpesaStk->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'STK Push sent successfully',
                    'data' => [
                        'checkout_request_id' => $response['CheckoutRequestID'],
                        'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                        'transaction_id'      => $mpesaStk->id,
                    ]
                ]);
            }

            Log::error('Unexpected STK Push response', ['response' => $response]);

            return response()->json([
                'success' => false,
                'message' => 'Unexpected response from M-Pesa',
                'data'    => $response
            ], 400);

        } catch (\Exception $e) {
            Log::error('STK Push Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            try {
                MpesaStk::create([
                    'response'             => ['error' => $e->getMessage()],
                    'amount'               => $request->input('amount'),
                    'phonenumber'          => $request->input('phone'),
                    'email'                => $request->input('email'),
                    'user_id'              => auth()->id() ?? null,
                    'order_id'             => $request->input('order_id'),
                    'attendee_id'          => $request->input('attendee_id'),
                    'result_code'          => '2',
                    'result_desc'          => $e->getMessage(),
                    'response_code'        => 'ERROR',
                    'response_description' => $e->getMessage(),
                    'customer_message'     => 'Payment failed',
                ]);
            } catch (\Exception $dbError) {
                Log::error('Failed to store error in database: ' . $dbError->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your request. Please try again later.',
                'error'   => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        Log::info('M-Pesa Callback Received', ['data' => $request->all()]);

        try {
            $data = $request->all();

            if (!isset($data['Body']['stkCallback'])) {
                Log::error('Invalid callback structure', ['data' => $data]);
                return response()->json(['message' => 'Invalid callback data'], 400);
            }

            $callbackData = $data['Body']['stkCallback'];
            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;

            if (!$checkoutRequestId && !$merchantRequestId) {
                return response()->json(['message' => 'Missing transaction identifiers'], 400);
            }

            $mpesaStk = null;
            if ($checkoutRequestId) {
                $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)->first();
            }
            if (!$mpesaStk && $merchantRequestId) {
                $mpesaStk = MpesaStk::where('merchant_request_id', $merchantRequestId)->first();
            }

            if (!$mpesaStk) {
                $mpesaStk = MpesaStk::create([
                    'checkout_request_id'  => $checkoutRequestId,
                    'merchant_request_id'  => $merchantRequestId,
                    'response'             => $callbackData,
                    'result_code'          => $callbackData['ResultCode'] ?? '1',
                    'result_desc'          => $callbackData['ResultDesc'] ?? 'Unknown',
                    'response_code'        => $callbackData['ResultCode'] ?? '1',
                    'response_description' => $callbackData['ResultDesc'] ?? 'Unknown',
                    'customer_message'     => $callbackData['ResultDesc'] ?? 'Processing',
                ]);
            }

            $updateData = [
                'result_code'          => (string) $callbackData['ResultCode'],
                'result_desc'          => $callbackData['ResultDesc'],
                'response'             => array_merge($callbackData, ['raw' => $data]),
                'response_code'        => (string) $callbackData['ResultCode'],
                'response_description' => $callbackData['ResultDesc'],
                'customer_message'     => $callbackData['ResultDesc'] ?? null,
            ];

            if ($callbackData['ResultCode'] == 0 && isset($callbackData['CallbackMetadata'])) {
                $metadata = $callbackData['CallbackMetadata']['Item'];

                foreach ($metadata as $item) {
                    switch ($item['Name']) {
                        case 'MpesaReceiptNumber':
                            $updateData['mpesa_receipt_number'] = $item['Value'];
                            break;
                        case 'TransactionDate':
                            $updateData['transaction_date'] = $item['Value'];
                            break;
                        case 'Amount':
                            $updateData['amount'] = $item['Value'];
                            break;
                        case 'PhoneNumber':
                            $updateData['phonenumber'] = $item['Value'];
                            break;
                    }
                }

                // ============ MARK ORDER PAID + AWARD POINTS ============
                if ($mpesaStk->order_id) {
                    $order = Order::find($mpesaStk->order_id);
                    if ($order && $order->status !== 'paid') {
                        $order->update(['status' => 'paid']);

                        // Award loyalty points (5% of amount)
                        $buyerEmail = $mpesaStk->email;
                        if ($buyerEmail) {
                            $buyer = User::where('email', $buyerEmail)->first();
                            if ($buyer) {
                                $pointsEarned = (int) floor(((float) $order->total_amount) * 0.05);
                                $buyer->increment('points', $pointsEarned);
                                $buyer->increment('total_spent', (float) $order->total_amount);

                                Log::info('Loyalty points awarded', [
                                    'user'   => $buyer->email,
                                    'points' => $pointsEarned,
                                    'order'  => $order->order_no,
                                ]);
                            }
                        }
                    }
                }

                // ============ MARK ATTENDEE PAID ============
                if ($mpesaStk->attendee_id) {
                    $attendee = Attendee::find($mpesaStk->attendee_id);
                    if ($attendee) {
                        $attendee->update(['status' => 'paid']);
                    }
                }
            }

            $mpesaStk->update($updateData);

            Log::info('Callback processed successfully', [
                'checkout_request_id' => $checkoutRequestId,
                'result_code'         => $callbackData['ResultCode'],
                'receipt_number'      => $mpesaStk->mpesa_receipt_number ?? null
            ]);

            return response()->json([
                'message'             => 'Callback processed successfully',
                'checkout_request_id' => $checkoutRequestId,
                'result_code'         => $callbackData['ResultCode']
            ]);

        } catch (\Exception $e) {
            Log::error('Callback processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $request->all()
            ]);

            return response()->json([
                'message' => 'Error processing callback',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function checkTransactionStatus(Request $request, $checkoutRequestId)
    {
        try {
            $transaction = MpesaStk::where('checkout_request_id', $checkoutRequestId)
                ->orWhere('merchant_request_id', $checkoutRequestId)
                ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            if ($transaction->result_code == '1') {
                try {
                    $statusResponse = $this->darajaService->queryStatus($checkoutRequestId);

                    if (isset($statusResponse['ResultCode'])) {
                        $transaction->update([
                            'result_code'          => (string) $statusResponse['ResultCode'],
                            'result_desc'          => $statusResponse['ResultDesc'] ?? $transaction->result_desc,
                            'response_code'        => (string) $statusResponse['ResultCode'],
                            'response_description' => $statusResponse['ResultDesc'] ?? $transaction->response_description,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to query transaction status', [
                        'checkout_request_id' => $checkoutRequestId,
                        'error' => $e->getMessage()
                    ]);
                }

                $transaction->refresh();
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id'               => $transaction->id,
                    'status'           => $transaction->result_code == '0' ? 'completed'
                                        : ($transaction->result_code == '1' ? 'pending' : 'failed'),
                    'result_code'      => $transaction->result_code,
                    'result_desc'      => $transaction->result_desc,
                    'receipt_number'   => $transaction->mpesa_receipt_number,
                    'amount'           => $transaction->amount,
                    'phonenumber'      => $transaction->phonenumber,
                    'transaction_date' => $transaction->transaction_date,
                    'created_at'       => $transaction->created_at,
                    'updated_at'       => $transaction->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Status check error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking transaction status'
            ], 500);
        }
    }

    public function getByReceipt($receiptNumber)
    {
        try {
            $transaction = MpesaStk::where('mpesa_receipt_number', $receiptNumber)->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => $transaction
            ]);

        } catch (\Exception $e) {
            Log::error('Get by receipt error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching transaction'
            ], 500);
        }
    }
}