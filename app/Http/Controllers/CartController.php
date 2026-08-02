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
     * Process checkout - Send STK Push only
     * NO order created yet - wait for payment confirmation
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

            // Format phone
            $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
            
            if (!preg_match('/^254\d{9}$/', $phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number format. Please enter number starting with 254'
                ], 400);
            }

            // Generate a unique reference for this transaction
            $tempOrderRef = 'TMP-' . strtoupper(Str::random(10));

            // Send STK Push via DarajaService (NO order created yet)
            $paymentResult = $this->sendStkPush($phone, $totalAmount, $tempOrderRef);

            if (!$paymentResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['message']
                ], 400);
            }

            // Store ONLY in MpesaStk (NO order, NO attendees, NO order items)
            $mpesaStk = MpesaStk::create([
                'merchant_request_id' => $paymentResult['merchant_request_id'],
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'response_code' => '0',
                'response_description' => 'Request sent to M-Pesa',
                'customer_message' => 'Please check your phone to complete payment',
                'response' => $paymentResult['raw_response'] ?? [],
                'user_id' => auth()->id(),
                'order_id' => null, // NO order yet!
                'attendee_id' => null, // NO attendee yet!
                'result_code' => '1', // 1 = Pending
                'result_desc' => 'Awaiting payment confirmation',
                'amount' => $totalAmount,
                'phone_number' => $phone,
                'mpesa_receipt_number' => null,
                'transaction_date' => null,
            ]);

            // Store cart data in session for later use
            session()->put('pending_payment', [
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'phone' => $phone,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'gender' => $request->input('gender'),
                'payment_mode' => $request->input('payment_mode'),
                'cart_items' => $cartItems,
                'total_amount' => $totalAmount,
                'total_qty' => $totalQty,
                'event_id' => $eventId,
                'temp_order_ref' => $tempOrderRef,
                'mpesa_stk_id' => $mpesaStk->id,
            ]);

            Log::info('STK Push initiated, waiting for callback', [
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'mpesa_stk_id' => $mpesaStk->id,
            ]);

            // DO NOT clear the cart - keep it until payment is confirmed
            // DO NOT create order - wait for callback

            return response()->json([
                'success' => true,
                'message' => 'STK Push sent successfully. Please check your phone.',
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
     * Create order AFTER payment confirmation
     * This is called when the frontend detects successful payment
     */
    public function createOrderAfterPayment(Request $request)
    {
        try {
            $checkoutRequestId = $request->input('checkout_request_id');
            
            // Find the MpesaStk record
            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)->first();
            
            if (!$mpesaStk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            // Check if payment was successful
            if ($mpesaStk->result_code !== '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not confirmed yet or failed',
                    'data' => [
                        'result_code' => $mpesaStk->result_code,
                        'result_desc' => $mpesaStk->result_desc,
                    ]
                ], 400);
            }

            // Check if order already exists (prevent duplicate)
            if ($mpesaStk->order_id) {
                $order = Order::find($mpesaStk->order_id);
                if ($order) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order already created',
                        'data' => [
                            'order_id' => $order->id,
                            'order_no' => $order->order_no,
                            'redirect_url' => route('cart.thankyou', ['order' => $order->id]),
                        ]
                    ]);
                }
            }

            // Get pending payment data from session
            $pendingData = session()->get('pending_payment');
            
            if (!$pendingData || $pendingData['checkout_request_id'] != $checkoutRequestId) {
                Log::error('Pending payment data not found or mismatch', [
                    'checkout_request_id' => $checkoutRequestId,
                    'pending' => $pendingData ? 'exists' : 'not found',
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment data not found'
                ], 400);
            }

            // CREATE EVERYTHING NOW!
            DB::beginTransaction();

            try {
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
                    'transaction_code' => $mpesaStk->mpesa_receipt_number,
                ]);

                Log::info('✅ Order created after payment confirmation', [
                    'order_id' => $order->id,
                    'receipt' => $mpesaStk->mpesa_receipt_number
                ]);

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

                Log::info('✅ Order items created', [
                    'order_id' => $order->id,
                    'items_count' => count($pendingData['cart_items'])
                ]);

                // 3. Create Attendees
                $payMode = PaymentMode::where('name', 'LIKE', '%Mobile Money%')->first();
                $buyerName = $pendingData['name'];
                $buyerEmail = $pendingData['email'];
                $buyerPhone = $mpesaStk->phone_number ?? $pendingData['phone'];
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
                            'gender' => $pendingData['gender'],
                            'payment_mode' => 'Mobile Money',
                            'payment_mode_id' => $payMode->id ?? 1,
                            'qty' => 1,
                            'total_price' => $item['price'],
                            'payment_status' => 'paid',
                            'status' => 'active',
                            'transaction_code' => $mpesaStk->mpesa_receipt_number,
                            'qr_code' => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                            'created_by' => $buyerName,
                        ]);

                        $attendees[] = $attendee;
                    }
                }

                Log::info('✅ Attendees created', [
                    'order_id' => $order->id,
                    'attendees_count' => count($attendees)
                ]);

                // 4. Update order with first attendee
                if (!empty($attendees)) {
                    $order->update(['attendee_id' => $attendees[0]->id]);
                }

                // 5. Update MpesaStk with order_id and attendee_id
                $mpesaStk->update([
                    'order_id' => $order->id,
                    'attendee_id' => $attendees[0]->id ?? null,
                ]);

                // 6. Clear the cart
                session()->forget('cart');
                session()->forget('pending_payment');

                DB::commit();

                Log::info('🎉 Order creation completed successfully', [
                    'order_id' => $order->id,
                    'receipt' => $mpesaStk->mpesa_receipt_number,
                    'attendees_count' => count($attendees)
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data' => [
                        'order_id' => $order->id,
                        'order_no' => $order->order_no,
                        'redirect_url' => route('cart.thankyou', ['order' => $order->id]),
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('❌ Order creation failed: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order: ' . $e->getMessage()
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('❌ Order creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display thank you page
     */
    public function thankyou(Request $request)
    {
        $orderId = $request->input('order');
        $order = Order::with(['orderItems', 'attendees'])->find($orderId);
        
        if (!$order) {
            return redirect()->route('cart.view')->with('error', 'Order not found');
        }
        
        return view('cart.thankyou', compact('order'));
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