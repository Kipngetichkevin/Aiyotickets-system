<?php

namespace App\Http\Controllers;

use App\Services\DarajaService;
use App\Models\MpesaStk;
use App\Models\MpesaCallback;
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
    private DarajaService $darajaService;

    public function __construct(DarajaService $darajaService)
    {
        $this->darajaService = $darajaService;
    }

    public function handleCallback(Request $request)
    {
        $rawContent = $request->getContent();
        $payload    = $request->all();

        $callbackLog = MpesaCallback::create([
            'checkout_request_id' => $payload['Body']['stkCallback']['CheckoutRequestID'] ?? null,
            'merchant_request_id' => $payload['Body']['stkCallback']['MerchantRequestID'] ?? null,
            'result_code'         => isset($payload['Body']['stkCallback']['ResultCode'])
                                        ? (string) $payload['Body']['stkCallback']['ResultCode']
                                        : null,
            'result_desc'         => $payload['Body']['stkCallback']['ResultDesc'] ?? null,
            'raw_payload'         => $payload ?: ['_raw' => $rawContent],
            'headers'             => $request->headers->all(),
            'ip_address'          => $request->ip(),
            'user_agent'          => substr((string) $request->userAgent(), 0, 500),
            'processing_status'   => 'received',
        ]);

        Log::info('🔔 M-Pesa Callback Received', [
            'callback_id'  => $callbackLog->id,
            'ip'           => $request->ip(),
            'content_type' => $request->header('Content-Type'),
        ]);

        file_put_contents(
            storage_path('logs/mpesa_callback.log'),
            now() . PHP_EOL
            . 'CALLBACK_ID: ' . $callbackLog->id . PHP_EOL
            . 'RAW: ' . $rawContent . PHP_EOL
            . 'JSON: ' . json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL
            . "----------------" . PHP_EOL,
            FILE_APPEND
        );

        try {
            $data         = $payload;
            $callbackData = $data['Body']['stkCallback'] ?? $data['stkCallback'] ?? null;

            if (!$callbackData) {
                $callbackLog->update([
                    'processing_status' => 'failed',
                    'processing_error'  => 'Invalid callback structure',
                ]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data'], 400);
            }

            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;
            $resultCode        = (string) ($callbackData['ResultCode'] ?? '');
            $resultDesc        = $callbackData['ResultDesc'] ?? '';

            $receiptNumber = $amount = $transactionDate = $phoneNumber = null;

            if ($resultCode === '0' && !empty($callbackData['CallbackMetadata']['Item'])) {
                foreach ($callbackData['CallbackMetadata']['Item'] as $item) {
                    switch ($item['Name'] ?? null) {
                        case 'MpesaReceiptNumber': $receiptNumber   = $item['Value'] ?? null; break;
                        case 'TransactionDate':    $transactionDate = $item['Value'] ?? null; break;
                        case 'Amount':             $amount          = $item['Value'] ?? null; break;
                        case 'PhoneNumber':        $phoneNumber     = $item['Value'] ?? null; break;
                    }
                }
            }

            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)
                ->orWhere('merchant_request_id', $merchantRequestId)
                ->first();

            if (!$mpesaStk) {
                Log::warning('⚠️ Transaction not found, creating orphan record', compact('checkoutRequestId', 'merchantRequestId'));

                $mpesaStk = MpesaStk::create([
                    'checkout_request_id'  => $checkoutRequestId,
                    'merchant_request_id'  => $merchantRequestId,
                    'result_code'          => $resultCode,
                    'result_desc'          => $resultDesc,
                    'response'             => $callbackData,
                    'response_code'        => $resultCode,
                    'response_description' => $resultDesc,
                    'customer_message'     => $resultDesc ?: 'Unknown transaction',
                ]);
            }

            $updateData = [
                'result_code'          => $resultCode,
                'result_desc'          => $resultDesc,
                'response'             => array_merge($callbackData, ['raw' => $data]),
                'response_code'        => $resultCode,
                'response_description' => $resultDesc,
                'customer_message'     => $resultDesc ?: null,
            ];

            if ($resultCode === '0') {
                if ($receiptNumber !== null)   $updateData['mpesa_receipt_number'] = $receiptNumber;
                if ($transactionDate !== null) $updateData['transaction_date']     = (string) $transactionDate;
                if ($amount !== null)          $updateData['amount']               = (float) $amount;
                if ($phoneNumber !== null)     $updateData['phone_number']         = (string) $phoneNumber;

                $mpesaStk->update($updateData);

                Log::info('✅ MpesaStk updated', [
                    'id'      => $mpesaStk->id,
                    'receipt' => $receiptNumber,
                    'amount'  => $amount,
                ]);

                if ($mpesaStk->order_id) {
                    Log::info('↩️ Order already exists — skipping', [
                        'mpesa_stk_id' => $mpesaStk->id,
                        'order_id'     => $mpesaStk->order_id,
                    ]);
                } else {
                    $this->createOrderFromPayment($mpesaStk, $receiptNumber, $phoneNumber);
                    $mpesaStk->refresh();
                }
            } else {
                $mpesaStk->update($updateData);

                Log::warning('❌ Payment not successful', [
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code'         => $resultCode,
                    'reason'              => $resultDesc,
                ]);
            }

            $callbackLog->update([
                'mpesa_receipt_number' => $receiptNumber,
                'amount'               => $amount,
                'phone_number'         => $phoneNumber,
                'processing_status'    => $resultCode === '0' ? 'processed' : 'failed',
                'processing_error'     => $resultCode === '0' ? null : "M-Pesa ResultCode {$resultCode}: {$resultDesc}",
                'mpesa_stk_id'         => $mpesaStk->id,
                'order_id'             => $mpesaStk->order_id,
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);

        } catch (\Throwable $e) {
            $callbackLog->update([
                'processing_status' => 'failed',
                'processing_error'  => $e->getMessage(),
            ]);

            Log::error('❌ Callback processing error: ' . $e->getMessage(), [
                'trace'       => $e->getTraceAsString(),
                'callback_id' => $callbackLog->id,
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
        }
    }

    /**
     * Public so CartController@createOrderAfterPayment can call it as a
     * fallback. Idempotent — safe to call twice.
     */
    public function createOrderFromPayment(MpesaStk $mpesaStk, ?string $receiptNumber, ?string $phoneNumber): void
    {
        $pendingData = $mpesaStk->pending_data;

        if (!$pendingData) {
            Log::warning('⚠️ No pending_data on MpesaStk — cannot build order', [
                'mpesa_stk_id'        => $mpesaStk->id,
                'checkout_request_id' => $mpesaStk->checkout_request_id,
            ]);
            return;
        }

        $mpesaStk->refresh();
        if ($mpesaStk->order_id) {
            Log::info('↩️ Order already created — skipping (race guard)', [
                'mpesa_stk_id' => $mpesaStk->id,
                'order_id'     => $mpesaStk->order_id,
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            // Resolve the buyer user_id — trust the MpesaStk first, then pending_data
            $buyerUserId = $mpesaStk->user_id ?? ($pendingData['user_id'] ?? null);

            $order = Order::create([
                'user_id'          => $buyerUserId,
                'order_no'         => 'ORD-' . strtoupper(Str::random(10)),
                'uuid'             => (string) Str::uuid(),
                'qty'              => $pendingData['total_qty'],
                'total_amount'     => $pendingData['total_amount'],
                'status'           => 'paid',
                'initiator'        => $pendingData['name'],
                'email'            => $pendingData['email'],
                'event_id'         => $pendingData['event_id'],
                'transaction_code' => $receiptNumber,
            ]);

            Log::info('✅ Order created', [
                'order_id' => $order->id,
                'receipt'  => $receiptNumber,
                'user_id'  => $buyerUserId,
            ]);

            foreach ($pendingData['cart_items'] as $item) {
                OrderItem::create([
                    'ticket_id'    => $item['ticket_id'],
                    'order_id'     => $order->id,
                    'event'        => $item['event'],
                    'qty'          => $item['qty'],
                    'price'        => $item['price'],
                    'total_amount' => $item['subtotal'],
                ]);
            }

            $payMode    = PaymentMode::where('name', 'LIKE', '%Mobile Money%')->first();
            $buyerName  = $pendingData['name'];
            $buyerEmail = $pendingData['email'];
            $buyerPhone = $phoneNumber ?? ($pendingData['phone'] ?? null);
            $attendees  = [];

            foreach ($pendingData['cart_items'] as $item) {
                for ($i = 0; $i < $item['qty']; $i++) {
                    $attendeeName = $item['qty'] > 1
                        ? $buyerName . ' (' . ($i + 1) . ')'
                        : $buyerName;

                    $attendees[] = Attendee::create([
                        'user_id'          => $buyerUserId,
                        'ticket_id'        => $item['ticket_id'],
                        'order_id'         => $order->id,
                        'name'             => $attendeeName,
                        'phone'            => $buyerPhone,
                        'email'            => $buyerEmail,
                        'payment_mode'     => 'Mobile Money',
                        'payment_mode_id'  => $payMode->id ?? 1,
                        'transaction_code' => $receiptNumber,
                        'qty'              => 1,
                        'total_price'      => $item['price'],
                        'gender'           => $pendingData['gender'] ?? null,
                        'qr_code'          => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'payment_status'   => 'paid',
                        'status'           => 'active',
                        'created_by'       => $buyerUserId,   // ← stores user id, not name
                    ]);
                }
            }

            if (!empty($attendees)) {
                $order->update(['attendee_id' => $attendees[0]->id]);
            }

            $mpesaStk->update([
                'order_id'    => $order->id,
                'attendee_id' => $attendees[0]->id ?? null,
            ]);

            DB::commit();

            Log::info('✅ Order creation completed', [
                'order_id'       => $order->id,
                'order_no'       => $order->order_no,
                'attendee_count' => count($attendees),
                'mpesa_stk_id'   => $mpesaStk->id,
                'user_id'        => $buyerUserId,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('❌ Order creation failed: ' . $e->getMessage(), [
                'trace'        => $e->getTraceAsString(),
                'mpesa_stk_id' => $mpesaStk->id,
            ]);
        }
    }

    public function checkTransactionStatus(Request $request, $checkoutRequestId)
    {
        try {
            $transaction = MpesaStk::where('checkout_request_id', $checkoutRequestId)
                ->orWhere('merchant_request_id', $checkoutRequestId)
                ->first();

            if (!$transaction) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            $status          = 'pending';
            $isReadyForOrder = false;

            if ($transaction->isSuccessful()) {
                $status          = 'completed';
                $isReadyForOrder = true;
            } elseif ($transaction->isFailed()) {
                $status = 'failed';
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                 => $transaction->id,
                    'status'             => $status,
                    'result_code'        => $transaction->result_code,
                    'result_desc'        => $transaction->result_desc,
                    'receipt_number'     => $transaction->mpesa_receipt_number,
                    'amount'             => $transaction->amount,
                    'phone_number'       => $transaction->phone_number,
                    'transaction_date'   => $transaction->transaction_date,
                    'is_ready_for_order' => $isReadyForOrder,
                    'order_id'           => $transaction->order_id,
                    'created_at'         => $transaction->created_at,
                    'updated_at'         => $transaction->updated_at,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Status check error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error checking transaction status'], 500);
        }
    }

    public function queryTransaction(Request $request, $checkoutRequestId)
    {
        try {
            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)->first();

            if (!$mpesaStk) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if (!$mpesaStk->isPending()) {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'result_code'        => $mpesaStk->result_code,
                        'result_desc'        => $mpesaStk->result_desc,
                        'receipt_number'     => $mpesaStk->mpesa_receipt_number,
                        'is_ready_for_order' => $mpesaStk->isSuccessful(),
                    ],
                ]);
            }

            $result = $this->darajaService->queryStatus($checkoutRequestId);

            if (isset($result['ResultCode'])) {
                $resultCode = (string) $result['ResultCode'];

                if ($resultCode === '0') {
                    $mpesaStk->update([
                        'result_code'          => '0',
                        'result_desc'          => $result['ResultDesc'] ?? 'Success',
                        'mpesa_receipt_number' => $result['ReceiptNumber'] ?? null,
                        'transaction_date'     => $result['TransactionDate'] ?? null,
                        'amount'               => $result['Amount'] ?? null,
                    ]);

                    if (!$mpesaStk->order_id) {
                        $this->createOrderFromPayment($mpesaStk, $result['ReceiptNumber'] ?? null, null);
                    }

                    return response()->json([
                        'success' => true,
                        'data'    => [
                            'result_code'        => '0',
                            'result_desc'        => 'Payment confirmed',
                            'receipt_number'     => $result['ReceiptNumber'] ?? null,
                            'is_ready_for_order' => true,
                        ],
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'result_code'        => $mpesaStk->result_code,
                    'result_desc'        => $mpesaStk->result_desc,
                    'is_ready_for_order' => false,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('Query error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error querying transaction'], 500);
        }
    }

    public function handleConfirmation(Request $request)
    {
        Log::info('M-Pesa C2B Confirmation Received', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Confirmation received']);
    }

    public function handleValidation(Request $request)
    {
        Log::info('M-Pesa C2B Validation Received', $request->all());
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Validation successful']);
    }

    public function stkPush(Request $request)
    {
        try {
            $request->validate([
                'phone'             => 'required|regex:/^254\d{9}$/',
                'amount'            => 'required|numeric|min:1',
                'account_reference' => 'nullable|string|max:50',
                'transaction_desc'  => 'nullable|string|max:50',
                'order_id'          => 'nullable|exists:orders,id',
            ]);

            $phone            = $request->input('phone');
            $amount           = (int) round($request->input('amount'));
            $accountReference = $request->input('account_reference', 'TicketPayment');
            $transactionDesc  = $request->input('transaction_desc', 'Event Ticket Payment');

            if (empty(config('mpesa.consumer_key')) || empty(config('mpesa.consumer_secret'))) {
                Log::error('M-Pesa credentials are missing');
                return response()->json(['success' => false, 'message' => 'M-Pesa is not properly configured.'], 500);
            }

            $response = $this->darajaService->stkPush($amount, $phone, $accountReference, $transactionDesc);

            if (isset($response['errorCode'])) {
                Log::error('STK Push API Error', ['response' => $response]);

                MpesaStk::create([
                    'response'             => $response,
                    'amount'               => $amount,
                    'phone_number'         => $phone,
                    'user_id'              => auth()->id(),
                    'order_id'             => $request->input('order_id'),
                    'result_code'          => '2',
                    'result_desc'          => $response['errorMessage'] ?? 'Payment failed',
                    'response_code'        => $response['errorCode'] ?? 'ERROR',
                    'response_description' => $response['errorMessage'] ?? 'Payment failed',
                    'customer_message'     => $response['errorMessage'] ?? 'Payment failed',
                ]);

                return response()->json([
                    'success'    => false,
                    'message'    => $response['errorMessage'] ?? 'Payment initiation failed',
                    'error_code' => $response['errorCode'] ?? null,
                ], 400);
            }

            if (isset($response['CheckoutRequestID'])) {
                $mpesaStk = MpesaStk::create([
                    'merchant_request_id'  => $response['MerchantRequestID'] ?? null,
                    'checkout_request_id'  => $response['CheckoutRequestID'],
                    'response'             => $response,
                    'amount'               => $amount,
                    'phone_number'         => $phone,
                    'user_id'              => auth()->id(),
                    'order_id'             => $request->input('order_id'),
                    'result_code'          => '1',
                    'result_desc'          => 'Request sent to M-Pesa',
                    'response_code'        => '0',
                    'response_description' => 'Request sent to M-Pesa',
                    'customer_message'     => 'Please check your phone to complete payment',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'STK Push sent successfully',
                    'data'    => [
                        'checkout_request_id' => $response['CheckoutRequestID'],
                        'merchant_request_id' => $response['MerchantRequestID'] ?? null,
                        'transaction_id'      => $mpesaStk->id,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unexpected response from M-Pesa',
                'data'    => $response,
            ], 400);

        } catch (\Throwable $e) {
            Log::error('STK Push Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
}