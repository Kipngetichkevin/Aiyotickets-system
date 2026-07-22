<?php

namespace App\Http\Controllers;

use App\Services\DarajaService;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    private $darajaService;

    public function __construct(DarajaService $darajaService)
    {
        $this->darajaService = $darajaService;
    }

    /**
     * Handle M-Pesa Callback
     */
    public function handleCallback(Request $request)
    {
        Log::info('M-Pesa Callback Received', ['data' => $request->all()]);
        
        try {
            $data = $request->all();
            $callbackData = $data['Body']['stkCallback'] ?? $data['stkCallback'] ?? null;
            
            if (!$callbackData) {
                Log::error('Invalid callback structure', ['data' => $data]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data'], 400);
            }
            
            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;
            $resultCode = $callbackData['ResultCode'] ?? null;
            $resultDesc = $callbackData['ResultDesc'] ?? null;
            
            if (!$checkoutRequestId && !$merchantRequestId) {
                Log::error('No transaction identifiers in callback', ['data' => $callbackData]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing transaction identifiers'], 400);
            }
            
            // Find the transaction
            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)
                ->orWhere('merchant_request_id', $merchantRequestId)
                ->first();
                
            if (!$mpesaStk) {
                Log::warning('Transaction not found', [
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $merchantRequestId
                ]);
                
                $mpesaStk = MpesaStk::create([
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $merchantRequestId,
                    'result_code' => (string) $resultCode,
                    'result_desc' => $resultDesc,
                    'response' => $callbackData,
                ]);
            }
            
            // Prepare update data
            $updateData = [
                'result_code' => (string) $resultCode,
                'result_desc' => $resultDesc,
                'response' => array_merge($callbackData, ['raw' => $data]),
                'response_code' => (string) $resultCode,
                'response_description' => $resultDesc,
                'customer_message' => $resultDesc ?? null,
            ];
            
            // If successful, extract metadata
            if ($resultCode == 0 && isset($callbackData['CallbackMetadata'])) {
                $metadata = $callbackData['CallbackMetadata']['Item'];
                $receiptNumber = null;
                $amount = null;
                $transactionDate = null;
                $phoneNumber = null;
                
                foreach ($metadata as $item) {
                    switch ($item['Name']) {
                        case 'MpesaReceiptNumber':
                            $receiptNumber = $item['Value'];
                            $updateData['mpesa_receipt_number'] = $receiptNumber;
                            break;
                        case 'TransactionDate':
                            $transactionDate = $item['Value'];
                            $updateData['transaction_date'] = $transactionDate;
                            break;
                        case 'Amount':
                            $amount = $item['Value'];
                            $updateData['amount'] = $amount;
                            break;
                        case 'PhoneNumber':
                            $phoneNumber = $item['Value'];
                            $updateData['phone_number'] = $phoneNumber;
                            break;
                    }
                }
                
                // Update Order with transaction code
                if ($mpesaStk->order_id) {
                    $order = Order::find($mpesaStk->order_id);
                    if ($order) {
                        $order->update([
                            'status' => 'paid',
                            'transaction_code' => $receiptNumber,
                        ]);
                        Log::info('Order updated to paid', [
                            'order_id' => $order->id, 
                            'receipt' => $receiptNumber
                        ]);
                    }
                }
                
                // Update Attendees for this order
                if ($mpesaStk->order_id) {
                    $attendees = Attendee::where('order_id', $mpesaStk->order_id)->get();
                    foreach ($attendees as $attendee) {
                        $attendee->update([
                            'payment_status' => 'paid',
                            'transaction_code' => $receiptNumber,
                        ]);
                        Log::info('Attendee updated to paid', [
                            'attendee_id' => $attendee->id,
                            'receipt' => $receiptNumber
                        ]);
                    }
                }
                
                // Also update any attendees with matching transaction code
                if ($checkoutRequestId) {
                    $attendees = Attendee::where('transaction_code', $checkoutRequestId)->get();
                    foreach ($attendees as $attendee) {
                        $attendee->update([
                            'payment_status' => 'paid',
                            'transaction_code' => $receiptNumber,
                        ]);
                    }
                }
            } else {
                // Payment failed - update order status
                if ($mpesaStk->order_id) {
                    $order = Order::find($mpesaStk->order_id);
                    if ($order) {
                        $order->update(['status' => 'failed']);
                        Log::warning('Order marked as failed', [
                            'order_id' => $order->id, 
                            'reason' => $resultDesc
                        ]);
                    }
                }
            }
            
            $mpesaStk->update($updateData);
            
            Log::info('Callback processed successfully', [
                'checkout_request_id' => $checkoutRequestId,
                'result_code' => $resultCode,
                'receipt_number' => $mpesaStk->mpesa_receipt_number ?? null
            ]);
            
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Callback processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            return response()->json([
                'ResultCode' => 1,
                'ResultDesc' => 'Error processing callback: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle M-Pesa C2B Confirmation
     */
    public function handleConfirmation(Request $request)
    {
        Log::info('M-Pesa C2B Confirmation Received', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Confirmation received']);
    }

    /**
     * Handle M-Pesa C2B Validation
     */
    public function handleValidation(Request $request)
    {
        Log::info('M-Pesa C2B Validation Received', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Validation successful']);
    }

    /**
     * Initiate STK Push
     */
    public function stkPush(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|regex:/^254\d{9}$/',
                'amount' => 'required|numeric|min:1',
                'account_reference' => 'nullable|string|max:50',
                'transaction_desc' => 'nullable|string|max:50',
                'order_id' => 'nullable|exists:orders,id',
                'attendee_id' => 'nullable|exists:attendees,id',
            ]);

            $phone = $request->input('phone');
            $amount = (int) round($request->input('amount'));
            $accountReference = $request->input('account_reference', 'TicketPayment');
            $transactionDesc = $request->input('transaction_desc', 'Event Ticket Payment');

            Log::info('STK Push Request', [
                'phone' => $phone,
                'amount' => $amount,
                'account_reference' => $accountReference,
                'callback_url' => config('mpesa.callback_url')
            ]);

            // Check if credentials are set
            if (empty(config('mpesa.mpesa_consumer_key')) || empty(config('mpesa.mpesa_consumer_secret'))) {
                Log::error('MPESA credentials are missing');
                return response()->json([
                    'success' => false,
                    'message' => 'M-Pesa is not properly configured. Please contact support.'
                ], 500);
            }

            // Call DarajaService to initiate STK Push
            $response = $this->darajaService->stkPush(
                $amount,
                $phone,
                $accountReference,
                $transactionDesc
            );

            // Check for errors in response
            if (isset($response['errorCode'])) {
                Log::error('STK Push API Error', ['response' => $response]);
                
                MpesaStk::create([
                    'response' => $response,
                    'amount' => $amount,
                    'phone_number' => $phone,
                    'user_id' => auth()->id() ?? null,
                    'order_id' => $request->input('order_id'),
                    'attendee_id' => $request->input('attendee_id'),
                    'result_code' => '2',
                    'result_desc' => $response['errorMessage'] ?? 'Payment failed',
                    'response_code' => $response['errorCode'] ?? 'ERROR',
                    'response_description' => $response['errorMessage'] ?? 'Payment failed',
                    'customer_message' => $response['errorMessage'] ?? 'Payment failed',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $response['errorMessage'] ?? 'Payment initiation failed',
                    'error_code' => $response['errorCode'] ?? null
                ], 400);
            }

            // Store the successful request
            if (isset($response['CheckoutRequestID'])) {
                $mpesaStk = MpesaStk::create([
                    'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'response' => $response,
                    'amount' => $amount,
                    'phone_number' => $phone,
                    'user_id' => auth()->id() ?? null,
                    'order_id' => $request->input('order_id'),
                    'attendee_id' => $request->input('attendee_id'),
                    'result_code' => '1',
                    'result_desc' => 'Request sent to M-Pesa',
                    'response_code' => '0',
                    'response_description' => 'Request sent to M-Pesa',
                    'customer_message' => 'Please check your phone to complete payment',
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
                        'transaction_id' => $mpesaStk->id,
                    ]
                ]);
            }

            Log::error('Unexpected STK Push response', ['response' => $response]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unexpected response from M-Pesa',
                'data' => $response
            ], 400);

        } catch (\Exception $e) {
            Log::error('STK Push Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check transaction status
     */
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

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'status' => $transaction->result_code == '0' ? 'completed' : 
                               ($transaction->result_code == '1' ? 'pending' : 'failed'),
                    'result_code' => $transaction->result_code,
                    'result_desc' => $transaction->result_desc,
                    'receipt_number' => $transaction->mpesa_receipt_number,
                    'amount' => $transaction->amount,
                    'phone_number' => $transaction->phone_number,
                    'transaction_date' => $transaction->transaction_date,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
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
}