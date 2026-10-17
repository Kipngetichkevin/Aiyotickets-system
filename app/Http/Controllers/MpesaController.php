<?php

namespace App\Http\Controllers;

use App\Models\MpesaStk;
use App\Services\Buyers;
use App\Services\MpesaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    public function __construct(private MpesaPaymentService $payments)
    {
    }

    public function handleCallback(Request $request)
    {
        try {
            $callbackData = $request->input('Body.stkCallback') ?? $request->input('stkCallback');

            if (!$callbackData) {
                Log::warning('M-Pesa callback: invalid structure');
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback data'], 400);
            }

            $checkoutRequestId = $callbackData['CheckoutRequestID'] ?? null;
            $merchantRequestId = $callbackData['MerchantRequestID'] ?? null;
            $resultCode        = isset($callbackData['ResultCode']) ? (string) $callbackData['ResultCode'] : null;

            // PII-safe log: identifiers only, no phone/amount/name.
            Log::info('M-Pesa callback received', [
                'checkout_request_id' => $checkoutRequestId,
                'merchant_request_id' => $merchantRequestId,
                'result_code'         => $resultCode,
                'ip'                  => $request->ip(),
            ]);

            if (!$checkoutRequestId && !$merchantRequestId) {
                return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Missing transaction identifiers'], 400);
            }

            $mpesaStk = $checkoutRequestId
                ? MpesaStk::where('checkout_request_id', $checkoutRequestId)->first()
                : MpesaStk::where('merchant_request_id', $merchantRequestId)->first();

            if (!$mpesaStk) {
                Log::warning('M-Pesa callback for unknown transaction ignored', [
                    'checkout_request_id' => $checkoutRequestId,
                    'merchant_request_id' => $merchantRequestId,
                ]);
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            $details = $this->callbackDetails($callbackData);

            // Already confirmed — backfill receipt if missing, then reconcile order.
            if ($mpesaStk->status === MpesaPaymentService::COMPLETED) {
                if ($resultCode === '0') {
                    $this->payments->backfillReceipt($mpesaStk, $details);
                }
                $this->payments->fulfilSafely($mpesaStk);
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            // Already failed — only a success can change that.
            if ($mpesaStk->status === MpesaPaymentService::FAILED && $resultCode !== '0') {
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            // Callback URL is public — verify with Safaricom before trusting.
            // Cache the confirmation for 60s so retries don't hammer Daraja.
            $confirmed = Cache::remember(
                'mpesa-confirm:' . $mpesaStk->id,
                now()->addSeconds(60),
                fn () => $this->payments->querySafaricom($mpesaStk)
            );
            $confirmedCode = $confirmed ? (string) ($confirmed['ResultCode'] ?? '') : null;

            if ($resultCode === '0' && $confirmedCode === '0') {
                $this->payments->markCompleted($mpesaStk, $details);
                $this->payments->fulfilSafely($mpesaStk);
            } elseif ($resultCode !== '0' && $confirmedCode !== null && $confirmedCode !== '0') {
                $this->payments->markFailed($mpesaStk, $confirmedCode, $confirmed['ResultDesc'] ?? 'Payment failed');
                Log::info('M-Pesa payment failed', ['mpesa_stk_id' => $mpesaStk->id, 'result_code' => $confirmedCode]);
            } else {
                Log::warning('M-Pesa callback not confirmed by Safaricom — left as is', [
                    'mpesa_stk_id'   => $mpesaStk->id,
                    'callback_code'  => $resultCode,
                    'confirmed_code' => $confirmedCode,
                ]);
                return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
            }

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Success']);

        } catch (\Throwable $e) {
            Log::error('M-Pesa callback processing error', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile() . ':' . $e->getLine(),
                // trace omitted deliberately — full request PII would leak otherwise
            ]);
            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Callback received']);
        }
    }

    public function checkTransactionStatus(Request $request, $checkoutRequestId)
    {
        try {
            $transaction = MpesaStk::where('checkout_request_id', $checkoutRequestId)
                ->orWhere('merchant_request_id', $checkoutRequestId)
                ->first();

            if (!$transaction || !$this->ownsTransaction($transaction)) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($transaction->status === MpesaPaymentService::PENDING
                && $transaction->created_at->lt(now()->subSeconds(20))
                && Cache::add('mpesa-sync:' . $transaction->id, true, 15)
            ) {
                $this->payments->sync($transaction);
                $transaction->refresh();
            }

            if ($transaction->status === MpesaPaymentService::COMPLETED
                && !$transaction->order_id
                && !$transaction->instalment_order_id) {
                $this->payments->fulfilSafely($transaction);
                $transaction->refresh();
            }

            if ($transaction->order_id && $order = \App\Models\Order::find($transaction->order_id)) {
                app(Buyers::class)->rememberOrder($order);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'id'                 => $transaction->id,
                    'status'             => $transaction->status,
                    'result_code'        => $transaction->result_code,
                    'result_desc'        => $transaction->result_desc,
                    // Mask receipt in the response — full receipt only via PDF/email.
                    'receipt_number'     => $transaction->mpesa_receipt_number
                        ? substr($transaction->mpesa_receipt_number, 0, 4) . '****'
                        : null,
                    'amount'             => $transaction->amount,
                    'phone_number'       => $this->maskPhone($transaction->phone_number),
                    'transaction_date'   => $transaction->transaction_date,
                    'is_ready_for_order' => $transaction->status === MpesaPaymentService::COMPLETED,
                    'order_id'           => $transaction->order_id,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('M-Pesa status check error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error checking transaction status'], 500);
        }
    }

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
                    'is_ready_for_order' => $mpesaStk->status === MpesaPaymentService::COMPLETED,
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('M-Pesa query error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error querying transaction'], 500);
        }
    }

    public function handleConfirmation(Request $request)
    {
        // Identifier-only log — C2B confirmation currently accepted as-is.
        Log::info('M-Pesa C2B confirmation received', [
            'trans_id'   => $request->input('TransID'),
            'bill_ref'   => $request->input('BillRefNumber'),
            'ip'         => $request->ip(),
        ]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Confirmation received']);
    }

    public function handleValidation(Request $request)
    {
        Log::info('M-Pesa C2B validation received', [
            'trans_id' => $request->input('TransID'),
            'ip'       => $request->ip(),
        ]);
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Validation successful']);
    }

    private function ownsTransaction(MpesaStk $mpesaStk): bool
    {
        return app(Buyers::class)->canSeeCheckout($mpesaStk);
    }

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

    private function maskPhone(?string $phone): ?string
    {
        if (!$phone) return null;
        $digits = preg_replace('/\D/', '', $phone);
        return strlen($digits) >= 4
            ? substr($digits, 0, -4) . '****'
            : '****';
    }
}