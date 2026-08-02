<?php

namespace App\Http\Controllers;

use App\Services\DarajaService;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\Attendee;
use App\Models\PaymentMode;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class MpesaController extends Controller
{
    private $darajaService;

    public function __construct(DarajaService $darajaService)
    {
        $this->darajaService = $darajaService;
    }

    /**
     * Handle M-Pesa Callback - Update ONLY MpesaStk
     * NO order creation here - that happens when frontend polls
     */
    public function handleCallback(Request $request)
    {
        // Log EVERYTHING that comes in
        Log::info('🔔 M-Pesa Callback Received', [
            'full_request' => $request->all(),
            'headers' => $request->headers->all(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'content_type' => $request->header('Content-Type'),
        ]);

        // Log raw input for debugging
        $rawContent = $request->getContent();
        Log::info('🔔 M-Pesa Raw Input', ['raw' => $rawContent]);

        // Write to separate log file for debugging
        file_put_contents(
            storage_path('logs/mpesa_callback.log'),
            now() . PHP_EOL .
            'RAW: ' . $rawContent . PHP_EOL .
            'JSON: ' . json_encode($request->all(), JSON_PRETTY_PRINT) .
            PHP_EOL . "----------------" . PHP_EOL,
            FILE_APPEND
        );

        try {
            $data = $request->all();
            
            // Extract callback data
            $callbackData = $data['Body']['stkCallback'] ?? $data['stkCallback'] ?? null;
            
            if (!$callbackData) {
                Log::error('❌ Invalid callback structure', [
                    'received_data' => $data
                ]);
                return response()->json([
                    'ResultCode' => 1,
                    'ResultDesc' => 'Invalid callback data'
                ], 400);
            }

            // ============================================
            // 🔥 LOG THE FULL CALLBACK DATA FOR DEBUGGING
            // ============================================
            Log::info('========== CALLBACK FULL DATA ==========');
            Log::info(json_encode($callbackData, JSON_PRETTY_PRINT));
            
            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;
            $resultCode = $callbackData['ResultCode'] ?? null;
            $resultDesc = $callbackData['ResultDesc'] ?? null;
            
            Log::info('📊 Callback Details', [
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId,
                'result_code' => $resultCode,
                'result_desc' => $resultDesc,
            ]);
            
            if (!$checkoutRequestId && !$merchantRequestId) {
                Log::error('❌ No transaction identifiers in callback');
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing transaction identifiers'], 400);
            }
            
            // Find the transaction
            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)
                ->orWhere('merchant_request_id', $merchantRequestId)
                ->first();
                
            if (!$mpesaStk) {
                Log::warning('⚠️ Transaction not found in database', [
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $merchantRequestId
                ]);
                
                $mpesaStk = MpesaStk::create([
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $merchantRequestId,
                    'result_code' => (string) $resultCode,
                    'result_desc' => $resultDesc,
                    'response' => $callbackData,
                    'response_code' => (string) $resultCode,
                    'response_description' => $resultDesc,
                    'customer_message' => $resultDesc ?? 'Unknown transaction',
                ]);
                
                Log::info('✅ Created new MpesaStk record for unknown transaction', [
                    'id' => $mpesaStk->id
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
            
            $receiptNumber = null;
            $amount = null;
            $transactionDate = null;
            $phoneNumber = null;
            
            // ============================================
            // 🔥 PAYMENT SUCCESSFUL - Extract Metadata
            // ============================================
            if ($resultCode == 0 && isset($callbackData['CallbackMetadata'])) {
                Log::info('💰 Payment successful! Processing metadata...');
                
                // Log the metadata structure
                Log::info('📦 Metadata exists', [
                    'metadata' => $callbackData['CallbackMetadata'] ?? null
                ]);
                
                $metadata = $callbackData['CallbackMetadata'];
                
                // Check if Item exists and is an array
                if (isset($metadata['Item']) && is_array($metadata['Item'])) {
                    Log::info('📋 Items found', [
                        'items' => $metadata['Item'],
                        'count' => count($metadata['Item'])
                    ]);
                    
                    // Loop through each item
                    foreach ($metadata['Item'] as $item) {
                        $name = $item['Name'] ?? null;
                        $value = $item['Value'] ?? null;
                        
                        Log::info('🔍 Processing item', [
                            'name' => $name,
                            'value' => $value,
                            'has_value' => isset($item['Value'])
                        ]);
                        
                        switch ($name) {
                            case 'MpesaReceiptNumber':
                                $receiptNumber = $value;
                                $updateData['mpesa_receipt_number'] = $receiptNumber;
                                Log::info('✅ Receipt number found', ['receipt' => $receiptNumber]);
                                break;
                            case 'TransactionDate':
                                $transactionDate = $value;
                                $updateData['transaction_date'] = (string) $value;
                                Log::info('✅ Transaction date found', ['date' => $transactionDate]);
                                break;
                            case 'Amount':
                                $amount = $value;
                                $updateData['amount'] = (float) $value;
                                Log::info('✅ Amount found', ['amount' => $amount]);
                                break;
                            case 'PhoneNumber':
                                $phoneNumber = $value;
                                $updateData['phone_number'] = (string) $value;
                                Log::info('✅ Phone number found', ['phone' => $phoneNumber]);
                                break;
                            default:
                                Log::info('ℹ️ Unknown metadata item', ['name' => $name, 'value' => $value]);
                                break;
                        }
                    }
                } else {
                    Log::error('❌ No Item array found in metadata', [
                        'metadata' => $metadata
                    ]);
                }
                
                // ============================================
                // 🔥 UPDATE MPESA_STK WITH ALL DATA
                // ============================================
                Log::info('📝 Updating MpesaStk with data', $updateData);
                
                $mpesaStk->update($updateData);
                
                Log::info('✅ MpesaStk updated successfully', [
                    'mpesa_stk_id' => $mpesaStk->id,
                    'receipt_number' => $receiptNumber,
                    'amount' => $amount,
                    'transaction_date' => $transactionDate,
                    'checkout_request_id' => $checkoutRequestId
                ]);
                
                // ============================================
                // 🔥 CREATE ORDER (if not already created)
                // ============================================
                if ($mpesaStk->order_id) {
                    Log::info('Order already exists for this transaction', [
                        'order_id' => $mpesaStk->order_id
                    ]);
                } else {
                    $this->createOrderFromPayment($mpesaStk, $checkoutRequestId, $receiptNumber, $phoneNumber, $amount);
                }
                
            } else {
                // ============================================
                // ❌ PAYMENT FAILED
                // ============================================
                $mpesaStk->update($updateData);
                
                Log::warning('❌ Payment failed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'reason' => $resultDesc,
                    'result_code' => $resultCode
                ]);
                
                // Clean up pending payment data if exists
                $pendingData = session()->get('pending_payment');
                if ($pendingData && $pendingData['checkout_request_id'] == $checkoutRequestId) {
                    session()->forget('pending_payment');
                    Log::info('🧹 Cleared pending payment data due to failed payment');
                }
            }
            
            // ============================================
            // Always return success to M-Pesa
            // ============================================
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Success'
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Callback processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            // Even if we have an error, return success to M-Pesa to avoid retries
            return response()->json([
                'ResultCode' => 0,
                'ResultDesc' => 'Callback received'
            ]);
        }
    }

    /**
     * Create order after payment confirmation
     */
    private function createOrderFromPayment($mpesaStk, $checkoutRequestId, $receiptNumber, $phoneNumber, $amount)
    {
        try {
            DB::beginTransaction();

            $pendingData = session()->get('pending_payment');

            if (!$pendingData) {
                Log::warning('⚠️ No pending checkout found for order creation', [
                    'checkout_request_id' => $checkoutRequestId
                ]);
                DB::rollBack();
                return;
            }

            if ($pendingData['checkout_request_id'] != $checkoutRequestId) {
                Log::warning('⚠️ Checkout request ID mismatch', [
                    'expected' => $pendingData['checkout_request_id'],
                    'received' => $checkoutRequestId
                ]);
                DB::rollBack();
                return;
            }

            // 1. Create Order
            $order = Order::create([
                'order_no' => 'ORD-' . strtoupper(Str::random(10)),
                'uuid' => (string) Str::uuid(),
                'qty' => $pendingData['total_qty'],
                'total_amount' => $pendingData['total_amount'],
                'status' => 'paid',
                'initiator' => $pendingData['name'],
                'email' => $pendingData['email'],
                'event_id' => $pendingData['event_id'],
                'transaction_code' => $receiptNumber,
            ]);

            Log::info('✅ Order created', ['order_id' => $order->id, 'receipt' => $receiptNumber]);

            // 2. Create Order Items
            foreach ($pendingData['cart_items'] as $item) {
                OrderItem::create([
                    'ticket_id' => $item['ticket_id'],
                    'order_id' => $order->id,
                    'event' => $item['event'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total_amount' => $item['subtotal'],
                ]);
            }

            // 3. Create Attendees
            $payMode = PaymentMode::where('name', 'LIKE', '%Mobile Money%')->first();
            $buyerName = $pendingData['name'];
            $buyerEmail = $pendingData['email'];
            $buyerPhone = $phoneNumber ?? $pendingData['phone'];
            $attendees = [];

            foreach ($pendingData['cart_items'] as $item) {
                for ($i = 0; $i < $item['qty']; $i++) {
                    $attendeeName = $buyerName;
                    if ($item['qty'] > 1) {
                        $attendeeName = $buyerName . ' (' . ($i + 1) . ')';
                    }

                    $attendee = Attendee::create([
                        'ticket_id' => $item['ticket_id'],
                        'order_id' => $order->id,
                        'name' => $attendeeName,
                        'phone' => $buyerPhone,
                        'email' => $buyerEmail,
                        'payment_mode' => 'Mobile Money',
                        'payment_mode_id' => $payMode->id ?? 1,
                        'transaction_code' => $receiptNumber,
                        'qty' => 1,
                        'total_price' => $item['price'],
                        'gender' => $pendingData['gender'],
                        'qr_code' => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'payment_status' => 'paid',
                        'order_id' => $order->id,
                        'status' => 'active',
                        'created_by' => $buyerName,
                    ]);

                    $attendees[] = $attendee;
                }
            }

            // Update order with first attendee
            if (!empty($attendees)) {
                $order->update(['attendee_id' => $attendees[0]->id]);
            }

            // Update MpesaStk with order_id and attendee_id
            $mpesaStk->update([
                'order_id' => $order->id,
                'attendee_id' => $attendees[0]->id ?? null,
            ]);

            // Clear session
            session()->forget('cart');
            session()->forget('pending_payment');

            DB::commit();
            Log::info('✅ Order creation completed', ['order_id' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('❌ Order creation failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Check transaction status - Frontend polls this
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

            // Determine status
            $status = 'pending';
            $isReadyForOrder = false;
            
            if ($transaction->result_code === '0') {
                $status = 'completed';
                $isReadyForOrder = true;
            } elseif ($transaction->result_code === '2' || $transaction->result_code === '1032') {
                $status = 'failed';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'status' => $status,
                    'result_code' => $transaction->result_code,
                    'result_desc' => $transaction->result_desc,
                    'receipt_number' => $transaction->mpesa_receipt_number,
                    'amount' => $transaction->amount,
                    'phone_number' => $transaction->phone_number,
                    'transaction_date' => $transaction->transaction_date,
                    'is_ready_for_order' => $isReadyForOrder,
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

    /**
     * Query M-Pesa directly (for timeout recovery)
     */
    public function queryTransaction(Request $request, $checkoutRequestId)
    {
        try {
            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)->first();
            
            if (!$mpesaStk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // If already completed or failed, return cached result
            if ($mpesaStk->result_code !== '1') {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'result_code' => $mpesaStk->result_code,
                        'result_desc' => $mpesaStk->result_desc,
                        'receipt_number' => $mpesaStk->mpesa_receipt_number,
                        'is_ready_for_order' => $mpesaStk->result_code === '0',
                    ]
                ]);
            }

            // Query M-Pesa API directly
            $result = $this->darajaService->queryStatus($checkoutRequestId);
            
            if (isset($result['ResultCode'])) {
                $resultCode = $result['ResultCode'];
                
                if ($resultCode === '0' && isset($result['ResultDesc'])) {
                    // Payment successful - update MpesaStk
                    $mpesaStk->update([
                        'result_code' => '0',
                        'result_desc' => $result['ResultDesc'],
                        'mpesa_receipt_number' => $result['ReceiptNumber'] ?? null,
                        'transaction_date' => $result['TransactionDate'] ?? null,
                        'amount' => $result['Amount'] ?? null,
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'result_code' => '0',
                            'result_desc' => 'Payment confirmed',
                            'receipt_number' => $result['ReceiptNumber'] ?? null,
                            'is_ready_for_order' => true,
                        ]
                    ]);
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'result_code' => $mpesaStk->result_code,
                    'result_desc' => $mpesaStk->result_desc,
                    'is_ready_for_order' => false,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Query error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error querying transaction'
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
     * Initiate STK Push (Standalone endpoint)
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

            if (empty(config('mpesa.mpesa_consumer_key')) || empty(config('mpesa.mpesa_consumer_secret'))) {
                Log::error('MPESA credentials are missing');
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
                    'response' => $response,
                    'amount' => $amount,
                    'phone_number' => $phone,
                    'user_id' => auth()->id() ?? null,
                    'order_id' => $request->input('order_id'),
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

            if (isset($response['CheckoutRequestID'])) {
                $mpesaStk = MpesaStk::create([
                    'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'response' => $response,
                    'amount' => $amount,
                    'phone_number' => $phone,
                    'user_id' => auth()->id() ?? null,
                    'order_id' => $request->input('order_id'),
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
}