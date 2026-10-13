<?php

namespace App\Http\Controllers;

use App\Models\MpesaStk;
use App\Services\MpesaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function __construct(private MpesaPaymentService $payments)
    {
    }

    /**
     * Handle M-Pesa Callback
     */
    public function handleCallback(Request $request)
    {
        $payload = $request->all();

        file_put_contents(
            storage_path('logs/mpesa_callback.log'),
            now() . PHP_EOL .
            'RAW: ' . $request->getContent() . PHP_EOL .
            "----------------" . PHP_EOL,
            FILE_APPEND
        );

        try {
            $callbackData = $payload['Body']['stkCallback'] ?? $payload['stkCallback'] ?? null;

            if (!$callbackData) {
                Log::error('❌ Invalid callback structure');
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data'], 400);
            }

            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;
            $resultCode        = isset($callbackData['ResultCode']) ? (string) $callbackData['ResultCode'] : null;
            $resultDesc        = $callbackData['ResultDesc'] ?? null;

            Log::info('🔔 M-Pesa callback', [
                'checkout_request_id' => $checkoutRequestId,
                'result_code'         => $resultCode,
                'result_desc'         => $resultDesc,
                'ip'                  => $request->ip(),
            ]);

            if (!$checkoutRequestId && !$merchantRequestId) {
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

            $details = $this->callbackDetails($callbackData);

            // Already confirmed (e.g. via STK Query): only fill in the receipt if we lack it.
            if ($mpesaStk->status === MpesaPaymentService::COMPLETED) {
                if ($resultCode === '0') {
                    $this->payments->backfillReceipt($mpesaStk, $details);
                }
                $this->payments->fulfilSafely($mpesaStk);
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            if ($mpesaStk->status === MpesaPaymentService::FAILED) {
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            if ($resultCode === '0') {
                // The callback URL is public, so a "success" is only trusted once Safaricom confirms it.
                // If it can't be confirmed now, the payment stays pending and is re-checked by
                // status polling and the mpesa:reconcile command.
                $confirmed = $this->payments->querySafaricom($mpesaStk);

                if (!$confirmed || (string) $confirmed['ResultCode'] !== '0') {
                    Log::warning('⚠️ Success callback not confirmed by Safaricom — left pending', [
                        'mpesa_stk_id' => $mpesaStk->id,
                        'ip'           => $request->ip(),
                    ]);
                    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
                }

                $this->payments->markCompleted($mpesaStk, $details + ['result_desc' => $resultDesc]);
                $this->payments->fulfilSafely($mpesaStk);
            } else {
                $this->payments->markFailed($mpesaStk, $resultCode, $resultDesc);
                Log::info('❌ Payment failed', ['mpesa_stk_id' => $mpesaStk->id, 'result_code' => $resultCode]);
            }

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);

        } catch (\Throwable $e) {
            Log::error('❌ Callback processing error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
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
            if ($transaction->status === MpesaPaymentService::PENDING
                && $transaction->created_at->lt(now()->subSeconds(20))
                && Cache::add('mpesa-sync:' . $transaction->id, true, 15)
            ) {
                $this->payments->sync($transaction);
                $transaction->refresh();
            }

            // Paid but the order isn't there yet (e.g. creation failed earlier): try again.
            if ($transaction->status === MpesaPaymentService::COMPLETED && !$transaction->order_id && !$transaction->instalment_order_id) {
                $this->payments->fulfilSafely($transaction);
                $transaction->refresh();
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                 => $transaction->id,
                    'status'             => $transaction->status,
                    'result_code'        => $transaction->result_code,
                    'result_desc'        => $transaction->result_desc,
                    'receipt_number'     => $transaction->mpesa_receipt_number,
                    'amount'             => $transaction->amount,
                    'phone_number'       => $transaction->phone_number,
                    'transaction_date'   => $transaction->transaction_date,
                    'is_ready_for_order' => $transaction->status === MpesaPaymentService::COMPLETED,
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

            $this->payments->sync($mpesaStk);
            $mpesaStk->refresh();

            return response()->json([
                'success' => true,
                'data'    => [
                    'status'             => $mpesaStk->status,
                    'result_code'        => $mpesaStk->result_code,
                    'result_desc'        => $mpesaStk->result_desc,
                    'receipt_number'     => $mpesaStk->mpesa_receipt_number,
                    'is_ready_for_order' => $mpesaStk->status === MpesaPaymentService::COMPLETED,
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

    private function ownsTransaction(MpesaStk $mpesaStk): bool
    {
        $user = auth()->user();

        return (int) $mpesaStk->user_id === (int) $user->id || $user->hasRole('admin');
    }

    /**
     * Receipt, date and phone from a success callback's metadata.
     */
    private function callbackDetails(array $callbackData): array
    {
        $map = [
            'MpesaReceiptNumber' => 'mpesa_receipt_number',
            'TransactionDate'    => 'transaction_date',
            'PhoneNumber'        => 'phone_number',
        ];

        $details = [];
        foreach ($callbackData['CallbackMetadata']['Item'] ?? [] as $item) {
            $field = $map[$item['Name'] ?? ''] ?? null;
            if ($field && isset($item['Value'])) {
                $details[$field] = (string) $item['Value'];
            }
        }

        return $details;
    }
}
