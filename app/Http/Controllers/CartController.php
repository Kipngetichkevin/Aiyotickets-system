<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMode;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\DarajaService;

class CartController extends Controller
{
    protected DarajaService $darajaService;

    public function __construct(DarajaService $darajaService)
    {
        $this->darajaService = $darajaService;
    }

    /**
     * Compute how many units of a ticket are still purchasable.
     */
    private function availableFor(int $ticketId): ?int
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) {
            return null;
        }

        $sold = Attendee::where('ticket_id', $ticket->id)
            ->whereIn('payment_status', ['paid', 'active'])
            ->sum('qty');

        return max(0, (int) $ticket->quantity - (int) $sold);
    }

    /**
     * Drop a stale cart if the previous payment succeeded but the front-end
     * never got a chance to clear it (tab closed, network drop, etc.).
     */
    private function selfHealCart(): void
    {
        $pending = session('pending_payment');
        if (!$pending || empty($pending['checkout_request_id'])) {
            return;
        }

        $stk = MpesaStk::where('checkout_request_id', $pending['checkout_request_id'])->first();

        if ($stk && $stk->isSuccessful()) {
            session()->forget('cart');
            session()->forget('pending_payment');
        } elseif ($stk && $stk->isFailed()) {
            session()->forget('pending_payment');
        }
    }

    public function addToCart(Request $request)
    {
        try {
            $this->selfHealCart();

            $cart = session()->get('cart', []);

            foreach ($request->cart as $item) {
                $ticketId  = $item['ticket_id'];
                $requested = (int) $item['quantity'];

                if ($requested <= 0) {
                    continue;
                }

                $ticket = Ticket::find($ticketId);
                if (!$ticket) {
                    return response()->json([
                        'success' => false,
                        'message' => "Ticket #{$ticketId} not found.",
                    ], 400);
                }

                $available     = $this->availableFor((int) $ticketId);
                $alreadyInCart = $cart[$ticketId]['quantity'] ?? 0;

                if (($requested + $alreadyInCart) > $available) {
                    return response()->json([
                        'success' => false,
                        'message' => $available > 0
                            ? "Only {$available} ticket(s) left for {$ticket->name}."
                            : "{$ticket->name} is sold out.",
                    ], 400);
                }

                if (isset($cart[$ticketId])) {
                    $cart[$ticketId]['quantity'] += $requested;
                } else {
                    $cart[$ticketId] = [
                        'ticket_id' => $ticketId,
                        'event_id'  => $item['event_id'],
                        'name'      => $item['name'],
                        'price'     => $item['price'],
                        'quantity'  => $requested,
                    ];
                }
            }

            session(['cart' => $cart]);

            return response()->json([
                'success'    => true,
                'message'    => 'Items added to cart successfully!',
                'cart_count' => count($cart),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Add to cart error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add items to cart'], 500);
        }
    }

    public function viewCart()
    {
        $this->selfHealCart();

        $cart = session()->get('cart', []);

        foreach ($cart as $ticket_id => &$item) {
            $event  = Event::find($item['event_id']);
            $ticket = Ticket::find($ticket_id);

            if ($event) {
                $item['event_name']  = $event->name;
                $item['event_image'] = $event->image ?? 'images/default-event.jpg';
                $item['ticket_name'] = $ticket ? $ticket->name : 'Ticket';
            } else {
                $item['event_name']  = 'Unknown Event';
                $item['event_image'] = 'images/default-event.jpg';
                $item['ticket_name'] = 'Ticket';
            }
        }

        return view('cart.view', compact('cart'));
    }

    public function removeTicket($ticket_id)
    {
        try {
            $cart = session()->get('cart', []);
            if (isset($cart[$ticket_id])) {
                unset($cart[$ticket_id]);
                session()->put('cart', $cart);
                return redirect()->route('cart.view')->with('success', 'Ticket removed successfully.');
            }
            return redirect()->route('cart.view')->with('error', 'Ticket not found in cart.');
        } catch (\Throwable $e) {
            Log::error('Remove ticket error: ' . $e->getMessage());
            return redirect()->route('cart.view')->with('error', 'Failed to remove ticket.');
        }
    }

    public function clearCart()
    {
        try {
            session()->forget('cart');
            return redirect()->route('cart.view')->with('success', 'Cart has been cleared.');
        } catch (\Throwable $e) {
            Log::error('Clear cart error: ' . $e->getMessage());
            return redirect()->route('cart.view')->with('error', 'Failed to clear cart.');
        }
    }

    public function checkout(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'         => 'required|string|max:255',
                'email'        => 'required|email|max:255',
                'gender'       => 'required|string|in:1,2,3',
                'payment_mode' => 'required|string|in:Mobile Money,Card,Crypto',
                'phone'        => 'required_if:payment_mode,Mobile Money|string|max:15',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            $cart = session()->get('cart', []);

            if (empty($cart)) {
                return response()->json(['success' => false, 'message' => 'Your cart is empty'], 400);
            }

            $totalAmount = 0;
            $totalQty    = 0;
            $eventId     = null;
            $cartItems   = [];

            foreach ($cart as $ticketId => $ticket) {
                if (isset($ticket['price'], $ticket['quantity']) && $ticket['quantity'] > 0) {
                    $subtotal     = $ticket['price'] * $ticket['quantity'];
                    $totalAmount += $subtotal;
                    $totalQty    += $ticket['quantity'];
                    $eventId      = $ticket['event_id'];

                    $cartItems[] = [
                        'ticket_id' => $ticketId,
                        'event'     => $ticket['name'] ?? 'Ticket',
                        'qty'       => $ticket['quantity'],
                        'price'     => $ticket['price'],
                        'subtotal'  => $subtotal,
                    ];
                }
            }

            if ($totalAmount <= 0) {
                return response()->json(['success' => false, 'message' => 'Invalid total amount'], 400);
            }

            // Availability check
            foreach ($cartItems as $item) {
                $available = $this->availableFor((int) $item['ticket_id']);
                $ticket    = Ticket::find($item['ticket_id']);

                if ($available === null) {
                    return response()->json([
                        'success' => false,
                        'message' => "Ticket #{$item['ticket_id']} no longer exists.",
                    ], 400);
                }

                if ($item['qty'] > $available) {
                    $name = $ticket->name ?? 'this ticket';
                    return response()->json([
                        'success' => false,
                        'message' => $available > 0
                            ? "Only {$available} ticket(s) left for {$name}. Please adjust your cart."
                            : "{$name} is sold out.",
                    ], 400);
                }
            }

            $phone = $this->normalizePhone($request->input('phone'));

            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number. Use format 254XXXXXXXXX or 07XXXXXXXX.',
                ], 400);
            }

            $tempOrderRef = 'TMP-' . strtoupper(Str::random(10));

            $paymentResult = $this->sendStkPush($phone, $totalAmount, $tempOrderRef);

            if (!$paymentResult['success']) {
                return response()->json(['success' => false, 'message' => $paymentResult['message']], 400);
            }

            // ✅ pending_data now carries user_id + safe fallbacks for name/email
            $pendingData = [
                'user_id'        => auth()->id(),
                'name'           => $request->input('name') ?: auth()->user()->name,
                'email'          => $request->input('email') ?: auth()->user()->email,
                'gender'         => $request->input('gender'),
                'phone'          => $phone,
                'payment_mode'   => $request->input('payment_mode'),
                'cart_items'     => $cartItems,
                'total_amount'   => $totalAmount,
                'total_qty'      => $totalQty,
                'event_id'       => $eventId,
                'temp_order_ref' => $tempOrderRef,
            ];

            $mpesaStk = MpesaStk::create([
                'merchant_request_id'  => $paymentResult['merchant_request_id'],
                'checkout_request_id'  => $paymentResult['checkout_request_id'],
                'response_code'        => '0',
                'response_description' => 'Request sent to M-Pesa',
                'customer_message'     => 'Please check your phone to complete payment',
                'response'             => $paymentResult['raw_response'] ?? [],
                'pending_data'         => $pendingData,
                'user_id'              => auth()->id(),
                'order_id'             => null,
                'attendee_id'          => null,
                'result_code'          => '1',
                'result_desc'          => 'Awaiting payment confirmation',
                'amount'               => $totalAmount,
                'phone_number'         => $phone,
                'mpesa_receipt_number' => null,
                'transaction_date'     => null,
            ]);

            session()->put('pending_payment', [
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'mpesa_stk_id'        => $mpesaStk->id,
                'phone'               => $phone,
                'name'                => $pendingData['name'],
                'email'               => $pendingData['email'],
            ]);

            Log::info('STK Push initiated, waiting for callback', [
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'mpesa_stk_id'        => $mpesaStk->id,
                'user_id'             => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'STK Push sent successfully. Please check your phone.',
                'data'    => [
                    'checkout_request_id' => $paymentResult['checkout_request_id'],
                    'merchant_request_id' => $paymentResult['merchant_request_id'],
                    'mpesa_stk_id'        => $mpesaStk->id,
                    'amount'              => $totalAmount,
                    'status'              => 'pending_payment',
                ],
            ]);

        } catch (\Throwable $th) {
            Log::error('Checkout error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString(), 'request' => $request->all()]);
            return response()->json(['success' => false, 'message' => 'An error occurred during checkout: ' . $th->getMessage()], 500);
        }
    }

    private function normalizePhone(?string $raw): ?string
    {
        if (!$raw) return null;

        $digits = preg_replace('/\D/', '', $raw);

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = '254' . substr($digits, 1);
        } elseif (strlen($digits) === 9 && in_array($digits[0], ['7', '1'], true)) {
            $digits = '254' . $digits;
        } elseif (strlen($digits) === 12 && str_starts_with($digits, '254')) {
            // already good
        }

        return preg_match('/^254\d{9}$/', $digits) ? $digits : null;
    }

    private function sendStkPush($phone, $amount, $orderRef)
    {
        try {
            Log::info('Sending STK Push via DarajaService', compact('phone', 'amount', 'orderRef'));

            $response = $this->darajaService->stkPush(
                $amount,
                $phone,
                "ORDER-{$orderRef}",
                "Event Ticket Payment"
            );

            Log::info('DarajaService STK Push Response', ['response' => $response]);

            if (isset($response['ResponseCode']) && (string) $response['ResponseCode'] === '0') {
                return [
                    'success'             => true,
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'raw_response'        => $response,
                ];
            }

            return [
                'success' => false,
                'message' => $response['ResponseDescription'] ?? $response['errorMessage'] ?? 'STK Push failed',
            ];

        } catch (\Throwable $e) {
            Log::error('STK Push Error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function createOrderAfterPayment(Request $request)
    {
        try {
            $checkoutRequestId = $request->input('checkout_request_id');

            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)->first();

            if (!$mpesaStk) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ($mpesaStk->isPending()) {
                try {
                    $query = $this->darajaService->queryStatus($checkoutRequestId);

                    if (($query['ResultCode'] ?? null) === '0') {
                        $mpesaStk->update([
                            'result_code'          => '0',
                            'result_desc'          => $query['ResultDesc'] ?? 'Success',
                            'mpesa_receipt_number' => $query['ReceiptNumber'] ?? null,
                            'transaction_date'     => $query['TransactionDate'] ?? null,
                            'amount'               => $query['Amount'] ?? $mpesaStk->amount,
                        ]);

                        if (!$mpesaStk->order_id && $mpesaStk->pending_data) {
                            app(MpesaController::class)->createOrderFromPayment(
                                $mpesaStk,
                                $mpesaStk->mpesa_receipt_number,
                                $mpesaStk->phone_number
                            );
                            $mpesaStk->refresh();
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Query fallback failed: ' . $e->getMessage());
                }
            }

            $mpesaStk->refresh();

            if ($mpesaStk->isFailed()) {
                return response()->json([
                    'success' => false,
                    'message' => $mpesaStk->result_desc ?: 'Payment was not completed',
                    'data'    => ['result_code' => $mpesaStk->result_code, 'result_desc' => $mpesaStk->result_desc],
                ], 400);
            }

            if (!$mpesaStk->order_id) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment still pending',
                    'data'    => ['status' => 'pending', 'checkout_request_id' => $checkoutRequestId, 'result_code' => $mpesaStk->result_code],
                ]);
            }

            $order = Order::find($mpesaStk->order_id);

            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Order not found'], 404);
            }

            session()->forget('cart');
            session()->forget('pending_payment');

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data'    => [
                    'order_id'     => $order->id,
                    'order_no'     => $order->order_no,
                    'receipt'      => $mpesaStk->mpesa_receipt_number,
                    'redirect_url' => route('cart.thankyou', ['order' => $order->id]),
                ],
            ]);

        } catch (\Throwable $e) {
            Log::error('createOrderAfterPayment error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function thankyou(Request $request)
    {
        $orderId = $request->input('order');
        $order   = Order::with(['orderItems', 'attendees'])->find($orderId);

        if (!$order) {
            return redirect()->route('cart.view')->with('error', 'Order not found');
        }

        return view('cart.thankyou', compact('order'));
    }

    public function getCartCount()
    {
        $cart  = session()->get('cart', []);
        $count = 0;
        foreach ($cart as $item) {
            $count += $item['quantity'] ?? 0;
        }
        return response()->json(['success' => true, 'count' => $count]);
    }

    public function getCartTotal()
    {
        $cart  = session()->get('cart', []);
        $total = 0;
        foreach ($cart as $item) {
            if (isset($item['price'], $item['quantity'])) {
                $total += $item['price'] * $item['quantity'];
            }
        }
        return response()->json(['success' => true, 'total' => $total, 'formatted_total' => 'KES ' . number_format($total, 2)]);
    }

    public function updateQuantity(Request $request, $ticket_id)
    {
        try {
            $request->validate(['quantity' => 'required|integer|min:1|max:10']);
            $cart = session()->get('cart', []);

            if (isset($cart[$ticket_id])) {
                $available = $this->availableFor((int) $ticket_id);

                if ($available !== null && $request->quantity > $available) {
                    return response()->json([
                        'success' => false,
                        'message' => $available > 0
                            ? "Only {$available} ticket(s) left."
                            : "This ticket is sold out.",
                    ], 400);
                }

                $cart[$ticket_id]['quantity'] = $request->quantity;
                session()->put('cart', $cart);
                return response()->json(['success' => true, 'message' => 'Quantity updated successfully']);
            }

            return response()->json(['success' => false, 'message' => 'Ticket not found in cart'], 404);
        } catch (\Throwable $e) {
            Log::error('Update quantity error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update quantity'], 500);
        }
    }
}