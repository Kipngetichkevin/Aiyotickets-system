<?php

namespace App\Http\Controllers;

use App\Services\DarajaService;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\Attendee;
use App\Models\ExperienceBooking;
use App\Models\PaymentMode;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Handle M-Pesa Callback
     */
    public function handleCallback(Request $request)
    {
        $rawContent = $request->getContent();
        $payload    = $request->all();

        Log::info('🔔 M-Pesa Callback Received', [
            'full_request' => $payload,
            'ip'           => $request->ip(),
            'content_type' => $request->header('Content-Type'),
        ]);

        file_put_contents(
            storage_path('logs/mpesa_callback.log'),
            now() . PHP_EOL .
            'RAW: ' . $rawContent . PHP_EOL .
            'JSON: ' . json_encode($payload, JSON_PRETTY_PRINT) . PHP_EOL .
            "----------------" . PHP_EOL,
            FILE_APPEND
        );

        try {
            $data         = $payload;
            $callbackData = $data['Body']['stkCallback'] ?? $data['stkCallback'] ?? null;

            if (!$callbackData) {
                Log::error('❌ Invalid callback structure', ['received' => $data]);
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data'], 400);
            }

            Log::info('========== CALLBACK FULL DATA ==========');
            Log::info(json_encode($callbackData, JSON_PRETTY_PRINT));

            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;
            $resultCode        = isset($callbackData['ResultCode']) ? (string) $callbackData['ResultCode'] : null;
            $resultDesc        = $callbackData['ResultDesc'] ?? null;

            Log::info('📊 Callback Details', [
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId,
                'result_code'         => $resultCode,
                'result_desc'         => $resultDesc,
            ]);

            if (!$checkoutRequestId && !$merchantRequestId) {
                Log::error('❌ No transaction identifiers in callback');
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing transaction identifiers'], 400);
            }

            // Only callbacks for an STK push we initiated are processed.
            $mpesaStk = $checkoutRequestId
                ? MpesaStk::where('checkout_request_id', $checkoutRequestId)->first()
                : MpesaStk::where('merchant_request_id', $merchantRequestId)->first();

            if (!$mpesaStk) {
                Log::warning('⚠️ Callback for unknown transaction ignored', compact('checkoutRequestId', 'merchantRequestId'));
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            if ((string) $mpesaStk->result_code === '0') {
                Log::info('↩️ Transaction already confirmed — callback ignored', ['mpesa_stk_id' => $mpesaStk->id]);
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            $updateData = [
                'result_code'          => $resultCode,
                'result_desc'          => $resultDesc,
                'response'             => array_merge($callbackData, ['raw' => $data]),
                'response_code'        => $resultCode,
                'response_description' => $resultDesc,
                'customer_message'     => $resultDesc ?? null,
            ];

            $receiptNumber = $amount = $transactionDate = $phoneNumber = null;

            // ── SUCCESS ────────────────────────────────────────
            if ($resultCode === '0' && isset($callbackData['CallbackMetadata']['Item'])) {
                // The callback URL is public, so a "success" is only trusted once Safaricom confirms it.
                // If it can't be confirmed now, the payment stays pending and status polling re-checks it.
                if (!$this->confirmedBySafaricom($mpesaStk)) {
                    Log::warning('⚠️ Success callback not confirmed by Safaricom — left pending', [
                        'mpesa_stk_id' => $mpesaStk->id,
                        'ip'           => $request->ip(),
                    ]);
                    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
                }

                Log::info('💰 Payment successful, extracting metadata...');

                foreach ($callbackData['CallbackMetadata']['Item'] as $item) {
                    $name  = $item['Name'] ?? null;
                    $value = $item['Value'] ?? null;

                    switch ($name) {
                        case 'MpesaReceiptNumber':
                            $receiptNumber = $value;
                            $updateData['mpesa_receipt_number'] = $value;
                            break;
                        case 'TransactionDate':
                            $transactionDate = $value;
                            $updateData['transaction_date'] = (string) $value;
                            break;
                        case 'Amount':
                            $amount = $value;
                            $updateData['amount'] = (float) $value;
                            break;
                        case 'PhoneNumber':
                            $phoneNumber = $value;
                            $updateData['phone_number'] = (string) $value;
                            break;
                    }
                }

                $mpesaStk->update($updateData);

                Log::info('✅ MpesaStk updated', [
                    'id'      => $mpesaStk->id,
                    'receipt' => $receiptNumber,
                    'amount'  => $amount,
                ]);

                if ($mpesaStk->order_id) {
                    Log::info('↩️ Order already exists — skipping creation', [
                        'mpesa_stk_id' => $mpesaStk->id,
                        'order_id'     => $mpesaStk->order_id,
                    ]);
                } else {
                    $this->createOrderFromPayment($mpesaStk, $receiptNumber, $phoneNumber);
                    $mpesaStk->refresh();
                }

            } else {
                // ── FAILURE ────────────────────────────────────
                $mpesaStk->update($updateData);

                Log::warning('❌ Payment failed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'result_code'         => $resultCode,
                    'reason'              => $resultDesc,
                ]);
            }

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);

        } catch (\Throwable $e) {
            Log::error('❌ Callback processing error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'data'  => $request->all(),
            ]);

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
        }
    }

    /**
     * Create Order + Attendees + ExperienceBookings after successful payment.
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

            $buyerUserId = $mpesaStk->user_id ?? ($pendingData['user_id'] ?? null);

            // 1. Order
            $order = Order::create([
                'user_id'          => $buyerUserId,
                'order_no'         => 'ORD-' . strtoupper(Str::random(10)),
                'uuid'             => (string) Str::uuid(),
                'qty'              => $pendingData['total_qty'] ?? 1,
                'total_amount'     => $pendingData['total_amount'],
                'status'           => 'paid',
                'initiator'        => $pendingData['name'],
                'email'            => $pendingData['email'],
                'event_id'         => $pendingData['event_id'] ?? null,
                'transaction_code' => $receiptNumber,
            ]);

            Log::info('✅ Order created', [
                'order_id' => $order->id,
                'receipt'  => $receiptNumber,
                'user_id'  => $buyerUserId,
            ]);

            // 2. Order Items (tickets only — experiences tracked separately)
            foreach (($pendingData['cart_items'] ?? []) as $item) {
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
            $bookings   = [];

            // 3. Attendees (tickets)
            foreach (($pendingData['cart_items'] ?? []) as $item) {
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
                        'gender'           => $pendingData['gender'] ?? null,
                        'payment_mode'     => 'Mobile Money',
                        'payment_mode_id'  => $payMode->id ?? 1,
                        'qty'              => 1,
                        'total_price'      => $item['price'],
                        'payment_status'   => 'paid',
                        'status'           => 'active',
                        'transaction_code' => $receiptNumber,
                        'qr_code'          => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'created_by'       => $buyerUserId,
                    ]);
                }
            }

            // 4. ExperienceBookings (experiences)
            foreach (($pendingData['experience_items'] ?? []) as $item) {
                $qty = (int) ($item['qty'] ?? 1);

                for ($i = 0; $i < $qty; $i++) {
                    $bookingName = $qty > 1
                        ? $buyerName . ' (' . ($i + 1) . ')'
                        : $buyerName;

                    $packagePrice      = (float) ($item['package_price']      ?? 0);
                    $categorySurcharge = (float) ($item['category_surcharge'] ?? 0);
                    $totalPrice        = (float) ($item['price'] ?? ($packagePrice + $categorySurcharge));

                    $bookings[] = ExperienceBooking::create([
                        'user_id'            => $buyerUserId,
                        'experience_id'      => $item['experience_id'],
                        'package_id'         => $item['package_id']     ?? null,
                        'category_id'        => $item['category_id']    ?? null,
                        'order_id'           => $order->id,
                        'name'               => $bookingName,
                        'gender'             => $pendingData['gender']  ?? null,
                        'phone'              => $buyerPhone,
                        'email'              => $buyerEmail,
                        'qty'                => 1,
                        'package_price'      => $packagePrice,
                        'category_surcharge' => $categorySurcharge,
                        'total_price'        => $totalPrice,
                        'payment_status'     => 'paid',
                        'payment_mode'       => 'Mobile Money',
                        'payment_mode_id'    => $payMode->id ?? 1,
                        'transaction_code'   => $receiptNumber,
                        'qr_code'            => 'EXP-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'status'             => 'active',
                        'created_by'         => (string) $buyerUserId,
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

            Log::info('🎉 Order + Attendees + ExperienceBookings created', [
                'order_id'       => $order->id,
                'order_no'       => $order->order_no,
                'attendee_count' => count($attendees),
                'booking_count'  => count($bookings),
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

    /**
     * Frontend polls this to check payment status.
     */
    public function checkTransactionStatus(Request $request, $checkoutRequestId)
    {
        try {
            $transaction = MpesaStk::where('checkout_request_id', $checkoutRequestId)
                ->orWhere('merchant_request_id', $checkoutRequestId)
                ->first();

            if (!$transaction || !$this->ownsTransaction($transaction)) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            // No (trusted) callback yet: ask Safaricom directly, at most every 15s per transaction.
            if ((string) $transaction->result_code === '1'
                && $transaction->created_at->lt(now()->subSeconds(20))
                && Cache::add('mpesa-sync:' . $transaction->id, true, 15)
            ) {
                $this->syncWithSafaricom($transaction);
                $transaction->refresh();
            }

            $status          = 'pending';
            $isReadyForOrder = false;

            if ((string) $transaction->result_code === '0') {
                $status          = 'completed';
                $isReadyForOrder = true;
            } elseif (in_array((string) $transaction->result_code, ['2', '1032'], true)) {
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

    /**
     * Query M-Pesa directly (for timeout recovery).
     */
    public function queryTransaction(Request $request, $checkoutRequestId)
    {
        try {
            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)->first();

            if (!$mpesaStk || !$this->ownsTransaction($mpesaStk)) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ((string) $mpesaStk->result_code !== '1') {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'result_code'        => $mpesaStk->result_code,
                        'result_desc'        => $mpesaStk->result_desc,
                        'receipt_number'     => $mpesaStk->mpesa_receipt_number,
                        'is_ready_for_order' => (string) $mpesaStk->result_code === '0',
                    ],
                ]);
            }

            $this->syncWithSafaricom($mpesaStk);

            if ((string) $mpesaStk->result_code === '0') {
                return response()->json([
                    'success' => true,
                    'data'    => [
                        'result_code'        => '0',
                        'result_desc'        => 'Payment confirmed',
                        'receipt_number'     => $mpesaStk->mpesa_receipt_number,
                        'is_ready_for_order' => true,
                    ],
                ]);
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

    /**
     * Handle M-Pesa C2B Confirmation
     */
    /**
     * Ask Safaricom whether this STK push was actually paid.
     */
    private function confirmedBySafaricom(MpesaStk $mpesaStk): bool
    {
        try {
            $result = $this->darajaService->queryStatus($mpesaStk->checkout_request_id);
        } catch (\Throwable $e) {
            Log::warning('STK query failed: ' . $e->getMessage(), ['mpesa_stk_id' => $mpesaStk->id]);
            return false;
        }

        return isset($result['ResultCode']) && (string) $result['ResultCode'] === '0';
    }

    /**
     * Pull the final result for a pending STK push from Safaricom and apply it,
     * creating the order when the payment succeeded.
     */
    private function syncWithSafaricom(MpesaStk $mpesaStk): void
    {
        try {
            $result = $this->darajaService->queryStatus($mpesaStk->checkout_request_id);
        } catch (\Throwable $e) {
            Log::warning('STK query failed: ' . $e->getMessage(), ['mpesa_stk_id' => $mpesaStk->id]);
            return;
        }

        // While the customer hasn't responded, Daraja returns an errorCode and no ResultCode.
        if (!isset($result['ResultCode'])) {
            return;
        }

        if ((string) $result['ResultCode'] === '0') {
            $mpesaStk->update([
                'result_code' => '0',
                'result_desc' => $result['ResultDesc'] ?? 'Success',
            ]);

            if (!$mpesaStk->order_id) {
                $this->createOrderFromPayment($mpesaStk, $mpesaStk->mpesa_receipt_number, null);
                $mpesaStk->refresh();
            }
        } else {
            $mpesaStk->update([
                'result_code' => (string) $result['ResultCode'],
                'result_desc' => $result['ResultDesc'] ?? null,
            ]);
        }
    }

    private function ownsTransaction(MpesaStk $mpesaStk): bool
    {
        $user = auth()->user();

        return (int) $mpesaStk->user_id === (int) $user->id || $user->hasRole('admin');
    }

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
     * Standalone STK push endpoint.
     */
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

            Log::info('STK Push Request', [
                'phone'             => $phone,
                'amount'            => $amount,
                'account_reference' => $accountReference,
                'callback_url'      => config('mpesa.callback_url'),
            ]);

            if (empty(config('mpesa.consumer_key')) || empty(config('mpesa.consumer_secret'))) {
                Log::error('M-Pesa credentials are missing');
                return response()->json(['success' => false, 'message' => 'M-Pesa is not properly configured.'], 500);
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

                Log::info('STK Push initiated successfully', [
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'mpesa_stk_id'        => $mpesaStk->id,
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

            Log::error('Unexpected STK Push response', ['response' => $response]);

            return response()->json([
                'success' => false,
                'message' => 'Unexpected response from M-Pesa',
                'data'    => $response,
            ], 400);

        } catch (\Throwable $e) {
            Log::error('STK Push Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}