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
use App\Services\DarajaService;

class CartController extends Controller
{
    protected DarajaService $darajaService;

    public function __construct(DarajaService $darajaService)
    {
        $this->darajaService = $darajaService;
    }

    /**
     * Add items to cart
     */
    public function addToCart(Request $request)
    {
        try {
            $cart = session()->get('cart', []);

            foreach ($request->cart as $item) {
                $ticketId = $item['ticket_id'];
                if (isset($cart[$ticketId])) {
                    $cart[$ticketId]['quantity'] += $item['quantity'];
                } else {
                    $cart[$ticketId] = [
                        'ticket_id' => $ticketId,
                        'event_id' => $item['event_id'],
                        'name' => $item['name'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'],
                    ];
                }
            }

            session(['cart' => $cart]);

            return response()->json([
                'success' => true,
                'message' => 'Items added to cart successfully!',
                'cart_count' => count($cart)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Add to cart error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add items to cart'
            ], 500);
        }
    }

    /**
     * View cart with event and ticket details
     */
    public function viewCart()
    {
        $cart = session()->get('cart', []);

        foreach ($cart as $ticket_id => &$item) {
            $event = Event::find($item['event_id']);
            $ticket = Ticket::find($ticket_id);
            
            if ($event) {
                $item['event_name'] = $event->name;
                $item['event_image'] = $event->image ?? 'images/default-event.jpg';
                $item['ticket_name'] = $ticket ? $ticket->name : 'Ticket';
            } else {
                $item['event_name'] = 'Unknown Event';
                $item['event_image'] = 'images/default-event.jpg';
                $item['ticket_name'] = 'Ticket';
            }
        }

        return view('cart.view', compact('cart'));
    }

    /**
     * Remove a ticket from cart
     */
    public function removeTicket($ticket_id)
    {
        try {
            $cart = session()->get('cart', []);

            if (isset($cart[$ticket_id])) {
                unset($cart[$ticket_id]);
                session()->put('cart', $cart);

                return redirect()->route('cart.view')
                    ->with('success', 'Ticket removed successfully.');
            }

            return redirect()->route('cart.view')
                ->with('error', 'Ticket not found in cart.');

        } catch (\Exception $e) {
            Log::error('Remove ticket error: ' . $e->getMessage());
            return redirect()->route('cart.view')
                ->with('error', 'Failed to remove ticket.');
        }
    }

    /**
     * Clear the entire cart
     */
    public function clearCart()
    {
        try {
            session()->forget('cart');

            return redirect()->route('cart.view')
                ->with('success', 'Cart has been cleared.');

        } catch (\Exception $e) {
            Log::error('Clear cart error: ' . $e->getMessage());
            return redirect()->route('cart.view')
                ->with('error', 'Failed to clear cart.');
        }
    }

    /**
     * Process checkout - Send STK Push first, then wait for callback
     * This only initiates the payment, does NOT create order yet
     */
    public function checkout(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'gender' => 'required|string|in:1,2,3',
                'payment_mode' => 'required|string|in:Mobile Money,Card,Crypto',
                'phone' => 'required_if:payment_mode,Mobile Money|string|max:15',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cart = session()->get('cart', []);
            
            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty'
                ], 400);
            }

            // Calculate total amount
            $totalAmount = 0;
            $totalQty = 0;
            $eventId = null;
            $cartItems = [];

            foreach ($cart as $ticketId => $ticket) {
                if (isset($ticket['price'], $ticket['quantity']) && $ticket['quantity'] > 0) {
                    $subtotal = $ticket['price'] * $ticket['quantity'];
                    $totalAmount += $subtotal;
                    $totalQty += $ticket['quantity'];
                    $eventId = $ticket['event_id'];
                    
                    $cartItems[] = [
                        'ticket_id' => $ticketId,
                        'event' => $ticket['name'] ?? 'Ticket',
                        'qty' => $ticket['quantity'],
                        'price' => $ticket['price'],
                        'subtotal' => $subtotal,
                    ];
                }
            }

            if ($totalAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid total amount'
                ], 400);
            }

            // Process payment
            $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
            
            if (!preg_match('/^254\d{9}$/', $phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format. Please enter number starting with 254'
                ], 400);
            }

            // Generate a unique reference for this transaction
            $tempOrderRef = 'TMP-' . strtoupper(Str::random(10));

            // Send STK Push via DarajaService
            $paymentResult = $this->sendStkPush($phone, $totalAmount, $tempOrderRef);

            if (!$paymentResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['message']
                ], 400);
            }

            // Store the checkout session data (to be used when callback comes)
            session()->put('pending_checkout', [
                'phone' => $phone,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'gender' => $request->input('gender'),
                'payment_mode' => $request->input('payment_mode'),
                'cart_items' => $cartItems,
                'total_amount' => $totalAmount,
                'total_qty' => $totalQty,
                'event_id' => $eventId,
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'merchant_request_id' => $paymentResult['merchant_request_id'],
                'temp_order_ref' => $tempOrderRef,
            ]);

            // Save MpesaStk record (waiting for callback - NO order yet)
            $mpesaStk = MpesaStk::create([
                'merchant_request_id' => $paymentResult['merchant_request_id'],
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'response_code' => '0',
                'response_description' => 'Request sent to M-Pesa',
                'customer_message' => 'Please check your phone to complete payment',
                'response' => $paymentResult['raw_response'] ?? [],
                'user_id' => auth()->id(),
                'order_id' => null, // IMPORTANT: No order yet!
                'attendee_id' => null,
                'result_code' => '1', // Pending
                'result_desc' => 'Request sent to M-Pesa',
                'amount' => $totalAmount,
                'phone_number' => $phone,
                'mpesa_receipt_number' => null,
                'transaction_date' => null,
            ]);

            Log::info('STK Push initiated, waiting for callback', [
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'mpesa_stk_id' => $mpesaStk->id,
                'temp_order_ref' => $tempOrderRef,
            ]);

            // ✅ DO NOT clear the cart - keep it until payment is confirmed
            // ✅ DO NOT create order - wait for callback

            return response()->json([
                'success' => true,
                'message' => 'STK Push sent successfully. Please check your phone and enter PIN to complete payment.',
                'data' => [
                    'checkout_request_id' => $paymentResult['checkout_request_id'],
                    'merchant_request_id' => $paymentResult['merchant_request_id'],
                    'amount' => $totalAmount,
                    'status' => 'pending_payment',
                ]
            ]);

        } catch (\Throwable $th) {
            Log::error('Checkout error: ' . $th->getMessage(), [
                'trace' => $th->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during checkout: ' . $th->getMessage()
            ], 500);
        }
    }

    /**
     * Send STK Push via DarajaService
     */
    private function sendStkPush($phone, $amount, $orderRef)
    {
        try {
            Log::info('Sending STK Push via DarajaService', [
                'phone' => $phone,
                'amount' => $amount,
                'order_ref' => $orderRef,
            ]);

            $response = $this->darajaService->stkPush(
                $amount,
                $phone,
                "ORDER-{$orderRef}",
                "Event Ticket Payment"
            );

            Log::info('DarajaService STK Push Response', [
                'response' => $response
            ]);

            if (isset($response['ResponseCode']) && $response['ResponseCode'] == '0') {
                return [
                    'success' => true,
                    'checkout_request_id' => $response['CheckoutRequestID'],
                    'merchant_request_id' => $response['MerchantRequestID'],
                    'raw_response' => $response,
                ];
            }

            return [
                'success' => false,
                'message' => $response['ResponseDescription'] ?? 'STK Push failed',
            ];

        } catch (\Throwable $e) {
            Log::error('STK Push Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Display thank you page
     */
    public function thankyou()
    {
        return view('cart.thankyou');
    }

    /**
     * Get cart count
     */
    public function getCartCount()
    {
        $cart = session()->get('cart', []);
        $count = 0;
        
        foreach ($cart as $item) {
            $count += $item['quantity'] ?? 0;
        }
        
        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get cart total
     */
    public function getCartTotal()
    {
        $cart = session()->get('cart', []);
        $total = 0;
        
        foreach ($cart as $item) {
            if (isset($item['price'], $item['quantity'])) {
                $total += $item['price'] * $item['quantity'];
            }
        }
        
        return response()->json([
            'success' => true,
            'total' => $total,
            'formatted_total' => 'KES ' . number_format($total, 2)
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateQuantity(Request $request, $ticket_id)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:1|max:10'
            ]);

            $cart = session()->get('cart', []);
            
            if (isset($cart[$ticket_id])) {
                $cart[$ticket_id]['quantity'] = $request->quantity;
                session()->put('cart', $cart);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Quantity updated successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found in cart'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Update quantity error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity'
            ], 500);
        }
    }
}