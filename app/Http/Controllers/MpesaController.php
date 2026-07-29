<?php

namespace App\Http\Controllers;

use App\Services\DarajaService;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\Attendee;
use App\Models\PaymentMode;
use App\Models\Ticket;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MpesaController extends Controller
{
    private $darajaService;

    public function __construct(DarajaService $darajaService)
    {
        $this->darajaService = $darajaService;
    }

    /**
     * Handle M-Pesa Callback
     * This is where the order is created ONLY after payment is confirmed
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
            
            $receiptNumber = null;
            $amount = null;
            $transactionDate = null;
            $phoneNumber = null;
            
            // IF PAYMENT SUCCESSFUL - Create Order and everything
            if ($resultCode == 0 && isset($callbackData['CallbackMetadata'])) {
                $metadata = $callbackData['CallbackMetadata']['Item'];
                
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
                
                // Update MpesaStk
                $mpesaStk->update($updateData);
                
                // 🔥 NOW CREATE THE ORDER (Only after payment confirmation)
                $pendingCheckout = session()->get('pending_checkout');
                
                if ($pendingCheckout && $pendingCheckout['checkout_request_id'] == $checkoutRequestId) {
                    // 1. Create Order
                    $order = Order::create([
                        'order_no' => 'ORD-' . strtoupper(Str::random(10)),
                        'uuid' => (string) Str::uuid(),
                        'qty' => $pendingCheckout['total_qty'],
                        'total_amount' => $pendingCheckout['total_amount'],
                        'status' => 'paid',
                        'initiator' => $pendingCheckout['name'],
                        'email' => $pendingCheckout['email'],
                        'event_id' => $pendingCheckout['event_id'],
                        'transaction_code' => $receiptNumber,
                    ]);
                    
                    Log::info('Order created after payment confirmation', [
                        'order_id' => $order->id,
                        'receipt' => $receiptNumber
                    ]);
                    
                    // 2. Create Order Items
                    foreach ($pendingCheckout['cart_items'] as $item) {
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
                    $buyerName = $pendingCheckout['name'];
                    $buyerEmail = $pendingCheckout['email'];
                    $buyerPhone = $phoneNumber ?? $pendingCheckout['phone'];
                    $attendees = [];
                    
                    foreach ($pendingCheckout['cart_items'] as $item) {
                        for ($i = 0; $i < $item['qty']; $i++) {
                            $attendeeName = $buyerName;
                            if ($item['qty'] > 1) {
                                $attendeeName = $buyerName . ' (' . ($i + 1) . ')';
                            }
                            
                            $attendee = Attendee::create([
                                'ticket_id' => $item['ticket_id'],
                                'name' => $attendeeName,
                                'phone' => $buyerPhone,
                                'email' => $buyerEmail,
                                'payment_mode' => 'Mobile Money',
                                'payment_mode_id' => $payMode->id ?? 1,
                                'transaction_code' => $receiptNumber,
                                'qty' => 1,
                                'total_price' => $item['price'],
                                'gender' => $pendingCheckout['gender'],
                                'qr_code' => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                                'payment_status' => 'paid',
                                'order_id' => $order->id,
                                'status' => 'active',
                                'created_by' => $buyerName,
                            ]);
                            
                            $attendees[] = $attendee;
                        }
                    }
                    
                    // Update order with first attendee as reference
                    if (!empty($attendees)) {
                        $order->update([
                            'attendee_id' => $attendees[0]->id,
                        ]);
                    }
                    
                    // Update MpesaStk with order_id and attendee_id
                    $mpesaStk->update([
                        'order_id' => $order->id,
                        'attendee_id' => $attendees[0]->id ?? null,
                    ]);
                    
                    Log::info('Attendees created for order', [
                        'order_id' => $order->id,
                        'attendees_count' => count($attendees)
                    ]);
                    
                    // 4. ✅ CLEAR THE CART (Only after successful payment)
                    session()->forget('cart');
                    session()->forget('pending_checkout');
                    
                    Log::info('Cart cleared after successful payment', [
                        'order_id' => $order->id,
                        'receipt' => $receiptNumber
                    ]);
                    
                } else {
                    Log::warning('Pending checkout data not found or mismatch', [
                        'checkout_request_id' => $checkoutRequestId,
                        'pending' => $pendingCheckout ? 'exists' : 'not found'
                    ]);
                }
                
                Log::info('Callback processed successfully - Payment confirmed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'receipt_number' => $receiptNumber
                ]);
                
            } else {
                // Payment failed - Just update the MpesaStk record
                $mpesaStk->update($updateData);
                
                Log::warning('Payment failed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'reason' => $resultDesc
                ]);
                
                // Clean up pending checkout data if exists
                $pendingCheckout = session()->get('pending_checkout');
                if ($pendingCheckout && $pendingCheckout['checkout_request_id'] == $checkoutRequestId) {
                    session()->forget('pending_checkout');
                    // ✅ DO NOT clear cart - user may want to retry
                }
            }
            
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