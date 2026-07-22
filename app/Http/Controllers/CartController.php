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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CartController extends Controller
{
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
     * Process checkout and initiate payment
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

            // Calculate total amount and prepare order items
            $totalAmount = 0;
            $totalQty = 0;
            $ticketDetails = [];
            $eventId = null;
            $orderItems = [];

            foreach ($cart as $ticketId => $ticket) {
                if (isset($ticket['price'], $ticket['quantity']) && $ticket['quantity'] > 0) {
                    $subtotal = $ticket['price'] * $ticket['quantity'];
                    $totalAmount += $subtotal;
                    $totalQty += $ticket['quantity'];
                    $eventId = $ticket['event_id'];
                    
                    $ticketDetails[] = [
                        'ticket_id' => $ticketId,
                        'name' => $ticket['name'] ?? 'Ticket',
                        'quantity' => $ticket['quantity'],
                        'price' => $ticket['price'],
                        'subtotal' => $subtotal
                    ];
                    
                    // Prepare order items
                    $orderItems[] = [
                        'ticket_id' => $ticketId,
                        'event' => $ticket['name'] ?? 'Ticket',
                        'qty' => $ticket['quantity'],
                        'price' => $ticket['price'],
                        'total_amount' => $subtotal,
                    ];
                }
            }

            if ($totalAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid total amount'
                ], 400);
            }

            // Create Order (Invoice)
            $order = Order::create([
                'order_no' => 'ORD-' . strtoupper(Str::random(10)),
                'uuid' => (string) Str::uuid(),
                'qty' => $totalQty,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'initiator' => $request->input('name'),
                'event_id' => $eventId,
                'transaction_code' => null, // Will be updated after payment
            ]);

            // Create Order Items (Line items for each ticket type)
            foreach ($orderItems as $item) {
                OrderItem::create([
                    'ticket_id' => $item['ticket_id'],
                    'order_id' => $order->id,
                    'event' => $item['event'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'total_amount' => $item['total_amount'],
                ]);
            }

            // Process payment
            $paymentResult = null;
            $phone = null;
            
            if ($request->payment_mode === 'Mobile Money') {
                $phone = preg_replace('/[^0-9]/', '', $request->input('phone'));
                
                if (!preg_match('/^254\d{9}$/', $phone)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid phone number format. Please enter number starting with 254'
                    ], 400);
                }
                
                $paymentResult = $this->callStkPushApi($phone, $totalAmount, $order->id);
                
                if (!$paymentResult['success']) {
                    // Delete order if payment fails
                    $order->delete();
                    return response()->json([
                        'success' => false,
                        'message' => $paymentResult['message']
                    ], 400);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $request->payment_mode . ' payments are coming soon. Please use Mobile Money.'
                ], 400);
            }

            // Create Attendees (Individual ticket holders)
            $attendees = [];
            $attendeeCounter = 1;

            foreach ($cart as $ticketId => $ticket) {
                $payMode = PaymentMode::where('name', 'LIKE', '%' . $request->payment_mode . '%')->first();
                
                // Get the ticket details for attendee info
                $ticketModel = Ticket::find($ticketId);
                
                for ($i = 0; $i < $ticket['quantity']; $i++) {
                    $attendeeName = $request->input('name');
                    
                    // If multiple tickets, add a number to differentiate
                    if ($ticket['quantity'] > 1) {
                        $attendeeName = $request->input('name') . ' (' . ($i + 1) . ')';
                    }
                    
                    $attendee = Attendee::create([
                        'ticket_id' => $ticketId,
                        'name' => $attendeeName,
                        'phone' => $request->input('phone'),
                        'email' => $request->input('email'),
                        'payment_mode' => $request->payment_mode,
                        'payment_mode_id' => $payMode->id ?? 1,
                        'transaction_code' => $paymentResult['checkout_request_id'] ?? 'TRX' . uniqid(),
                        'qty' => 1, // Each attendee gets 1 ticket
                        'total_price' => $ticket['price'], // Price per ticket
                        'gender' => $request->input('gender'),
                        'qr_code' => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                        'payment_status' => 'pending',
                        'order_id' => $order->id,
                        'status' => 'active', // Add this
                        'created_by' => auth()->id() ?? $request->input('name'),
                        // 'uuid' will be auto-generated by the model's boot method
                    ]);
                    
                    $attendees[] = $attendee;
                    $attendeeCounter++;
                }
            }

            // Update order with first attendee as reference
            $order->update([
                'attendee_id' => $attendees[0]->id ?? null,
            ]);

            // Clear the cart after successful checkout
            session()->forget('cart');

            return response()->json([
                'success' => true,
                'message' => 'STK Push sent successfully. Please check your phone.',
                'data' => [
                    'order_id' => $order->id,
                    'order_no' => $order->order_no,
                    'total' => $totalAmount,
                    'total_tickets' => $totalQty,
                    'payment_mode' => $request->payment_mode,
                    'checkout_request_id' => $paymentResult['checkout_request_id'] ?? null,
                    'attendees_count' => count($attendees),
                    'attendees' => $attendees,
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
     * Call the STK Push API endpoint
     */
    private function callStkPushApi($phone, $amount, $orderId)
    {
        try {
            $url = url('/api/stkpush');
            
            Log::info('Calling STK Push API', [
                'url' => $url,
                'phone' => $phone,
                'amount' => $amount,
                'order_id' => $orderId
            ]);

            $response = Http::withOptions([
                'verify' => false, // For testing only - remove in production
            ])->post($url, [
                'phone' => $phone,
                'amount' => $amount,
                'account_reference' => 'TicketPayment',
                'transaction_desc' => 'Event Ticket Payment',
                'order_id' => $orderId,
            ]);

            $result = $response->json();

            Log::info('STK Push API Response', [
                'status' => $response->status(),
                'result' => $result
            ]);

            if ($response->successful() && isset($result['success']) && $result['success']) {
                return [
                    'success' => true,
                    'checkout_request_id' => $result['data']['checkout_request_id'] ?? null,
                    'merchant_request_id' => $result['data']['merchant_request_id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'message' => $result['message'] ?? 'STK Push failed'
            ];

        } catch (\Exception $e) {
            Log::error('STK Push API call error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage()
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