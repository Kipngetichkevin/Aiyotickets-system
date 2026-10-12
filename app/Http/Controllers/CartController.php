<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\ExperienceCategory;
use App\Models\ExperiencePackage;
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

    /* =========================================================
     |  HELPERS
     |========================================================= */

    private function availableFor(int $ticketId): ?int
    {
        $ticket = Ticket::find($ticketId);
        if (!$ticket) return null;

        $sold = Attendee::where('ticket_id', $ticket->id)
            ->whereIn('payment_status', ['paid', 'active'])
            ->sum('qty');

        return max(0, (int) $ticket->quantity - (int) $sold);
    }

    /**
     * Spots left for an experience cart item: the tightest of the experience's total
     * capacity and the chosen package's and category's capacity. Null = unlimited.
     */
    private function availableExperienceSpots(int $experienceId, ?int $packageId, ?int $categoryId): ?int
    {
        $limits = [
            Experience::find($experienceId)?->availableSpots(),
            $packageId ? ExperiencePackage::find($packageId)?->availableSpots() : null,
            $categoryId ? ExperienceCategory::find($categoryId)?->availableSpots() : null,
        ];

        $limits = array_filter($limits, fn ($limit) => $limit !== null);

        return $limits ? min($limits) : null;
    }

    private function normalizePhone(?string $raw): ?string
    {
        if (!$raw) return null;

        $digits = preg_replace('/\D/', '', $raw);

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = '254' . substr($digits, 1);
        } elseif (strlen($digits) === 9 && in_array($digits[0], ['7', '1'], true)) {
            $digits = '254' . $digits;
        }

        return preg_match('/^254\d{9}$/', $digits) ? $digits : null;
    }

    private function selfHealCart(): void
    {
        $pending = session('pending_payment');
        if (!$pending || empty($pending['checkout_request_id'])) return;

        $stk = MpesaStk::where('checkout_request_id', $pending['checkout_request_id'])->first();

        if ($stk && (string) $stk->result_code === '0') {
            session()->forget('cart');
            session()->forget('experiences');
            session()->forget('pending_payment');
        } elseif ($stk && !in_array((string) $stk->result_code, ['0', '1'], true)) {
            session()->forget('pending_payment');
        }
    }

    /* =========================================================
     |  ADD TO CART (tickets + experiences)
     |========================================================= */
    public function addToCart(Request $request)
    {
        try {
            $this->selfHealCart();

            $cart        = session()->get('cart', []);
            $experiences = session()->get('experiences', []);

            // ---------- TICKETS ----------
            if ($request->has('cart')) {
                foreach ($request->cart as $item) {
                    $ticketId  = $item['ticket_id'];
                    $requested = (int) $item['quantity'];

                    if ($requested <= 0) continue;

                    $ticket = Ticket::find($ticketId);
                    if (!$ticket) {
                        return response()->json(['success' => false, 'message' => "Ticket #{$ticketId} not found."], 400);
                    }

                    $available     = $this->availableFor((int) $ticketId);
                    $alreadyInCart = $cart[$ticketId]['quantity'] ?? 0;

                    if ($available !== null && ($requested + $alreadyInCart) > $available) {
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
                        // Price, name and event come from the database, never the request.
                        $cart[$ticketId] = [
                            'ticket_id' => $ticketId,
                            'event_id'  => $ticket->event_id,
                            'name'      => $ticket->name,
                            'price'     => (float) $ticket->price,
                            'quantity'  => $requested,
                        ];
                    }
                }
            }

            session(['cart'        => $cart]);
            session(['experiences' => $experiences]);

            return response()->json([
                'success'          => true,
                'message'          => 'Items added to cart successfully!',
                'cart_count'       => count($cart),
                'experience_count' => count($experiences),
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Add to cart error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to add items to cart'], 500);
        }
    }

    /* =========================================================
     |  VIEW CART
     |========================================================= */
    public function viewCart()
    {
        $this->selfHealCart();

        $cart        = session()->get('cart', []);
        $experiences = session()->get('experiences', []);

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
        unset($item);

        foreach ($experiences as $exp_id => &$item) {
            $event      = Event::find($item['event_id'] ?? null);
            $experience = Experience::find($exp_id);

            $item['event_name']      = $event ? $event->name : '—';
            $item['experience_name'] = $experience ? $experience->name : 'Experience';
            $item['duration']        = $experience?->duration_minutes;
            $item['image']           = $experience?->image;
        }
        unset($item);

        return view('cart.view', compact('cart', 'experiences'));
    }

    /* =========================================================
     |  REMOVE ITEM (ticket or experience)
     |========================================================= */
    public function removeTicket($ticket_id)
    {
        try {
            if (is_string($ticket_id) && str_starts_with($ticket_id, 'exp-')) {
                $expId       = (int) substr($ticket_id, 4);
                $experiences = session()->get('experiences', []);

                if (isset($experiences[$expId])) {
                    unset($experiences[$expId]);
                    session()->put('experiences', $experiences);
                    return redirect()->route('cart.view')->with('success', 'Experience removed successfully.');
                }
                return redirect()->route('cart.view')->with('error', 'Experience not found in cart.');
            }

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

    /* =========================================================
     |  CLEAR CART
     |========================================================= */
    public function clearCart()
    {
        try {
            session()->forget('cart');
            session()->forget('experiences');
            return redirect()->route('cart.view')->with('success', 'Cart has been cleared.');
        } catch (\Throwable $e) {
            Log::error('Clear cart error: ' . $e->getMessage());
            return redirect()->route('cart.view')->with('error', 'Failed to clear cart.');
        }
    }

    /* =========================================================
     |  CHECKOUT
     |========================================================= */
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
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            $cart        = session()->get('cart', []);
            $experiences = session()->get('experiences', []);

            if (empty($cart) && empty($experiences)) {
                return response()->json(['success' => false, 'message' => 'Your cart is empty'], 400);
            }

            $totalAmount = 0;
            $totalQty    = 0;
            $eventId     = null;
            $cartItems   = [];
            $expItems    = [];

            // Tickets — re-priced from the database so the charge always matches the current ticket price
            foreach ($cart as $ticketId => $ticket) {
                if (empty($ticket['quantity']) || $ticket['quantity'] <= 0) continue;

                $dbTicket = Ticket::find($ticketId);
                if (!$dbTicket) {
                    return response()->json([
                        'success' => false,
                        'message' => 'A ticket in your cart is no longer available. Please remove it and try again.',
                    ], 400);
                }

                $price        = (float) $dbTicket->price;
                $subtotal     = $price * $ticket['quantity'];
                $totalAmount += $subtotal;
                $totalQty    += $ticket['quantity'];
                $eventId      = $dbTicket->event_id;

                $cartItems[] = [
                    'ticket_id' => $ticketId,
                    'event'     => $dbTicket->name,
                    'qty'       => $ticket['quantity'],
                    'price'     => $price,
                    'subtotal'  => $subtotal,
                ];
            }

            // Experiences — re-priced from the database (package price + category surcharge)
            foreach ($experiences as $expId => $exp) {
                if (empty($exp['quantity']) || $exp['quantity'] <= 0) continue;

                $package = ExperiencePackage::where('experience_id', $expId)
                    ->where('is_active', true)
                    ->find($exp['package_id'] ?? null);

                if (!$package) {
                    return response()->json([
                        'success' => false,
                        'message' => 'An experience package in your cart is no longer available. Please remove it and try again.',
                    ], 400);
                }

                $category = !empty($exp['category_id'])
                    ? ExperienceCategory::where('experience_id', $expId)->where('is_active', true)->find($exp['category_id'])
                    : null;

                $packagePrice      = (float) $package->price;
                $categorySurcharge = (float) ($category?->price_surcharge ?? 0);
                $price             = $packagePrice + $categorySurcharge;
                $subtotal          = $price * $exp['quantity'];
                $totalAmount      += $subtotal;
                $totalQty         += $exp['quantity'];

                $expItems[] = [
                    'experience_id'      => $expId,
                    'package_id'         => $package->id,
                    'category_id'        => $category?->id,
                    'event_id'           => null,
                    'name'               => $exp['name'] ?? 'Experience',
                    'qty'                => $exp['quantity'],
                    'package_price'      => $packagePrice,
                    'category_surcharge' => $categorySurcharge,
                    'price'              => $price,
                    'subtotal'           => $subtotal,
                ];
            }

            if ($totalAmount <= 0) {
                return response()->json(['success' => false, 'message' => 'Invalid total amount'], 400);
            }

            // Availability check — tickets
            foreach ($cartItems as $item) {
                $available = $this->availableFor((int) $item['ticket_id']);
                $ticket    = Ticket::find($item['ticket_id']);

                if ($available !== null && $item['qty'] > $available) {
                    $name = $ticket->name ?? 'this ticket';
                    return response()->json([
                        'success' => false,
                        'message' => $available > 0
                            ? "Only {$available} ticket(s) left for {$name}."
                            : "{$name} is sold out.",
                    ], 400);
                }
            }

            // Availability check — experiences
            foreach ($expItems as $item) {
                $available = $this->availableExperienceSpots((int) $item['experience_id'], $item['package_id'], $item['category_id']);
                if ($available !== null && $item['qty'] > $available) {
                    return response()->json([
                        'success' => false,
                        'message' => $available > 0
                            ? "Only {$available} slot(s) left for {$item['name']}."
                            : "{$item['name']} is sold out.",
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

            $pendingData = [
                'user_id'          => auth()->id(),
                'name'             => $request->input('name') ?: auth()->user()->name,
                'email'            => $request->input('email') ?: auth()->user()->email,
                'gender'           => $request->input('gender'),
                'phone'            => $phone,
                'payment_mode'     => $request->input('payment_mode'),
                'cart_items'       => $cartItems,
                'experience_items' => $expItems,
                'total_amount'     => $totalAmount,
                'total_qty'        => $totalQty,
                'event_id'         => $eventId,
                'temp_order_ref'   => $tempOrderRef,
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
                'gender'              => $pendingData['gender'],
                'payment_mode'        => $pendingData['payment_mode'],
                'cart_items'          => $cartItems,
                'experience_items'    => $expItems,
                'total_amount'        => $totalAmount,
                'total_qty'           => $totalQty,
                'event_id'            => $eventId,
                'temp_order_ref'      => $tempOrderRef,
            ]);

            Log::info('STK Push initiated (tickets + experiences)', [
                'checkout_request_id' => $paymentResult['checkout_request_id'],
                'mpesa_stk_id'        => $mpesaStk->id,
                'user_id'             => auth()->id(),
                'ticket_items'        => count($cartItems),
                'experience_items'    => count($expItems),
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
            Log::error('Checkout error: ' . $th->getMessage(), [
                'trace'   => $th->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during checkout: ' . $th->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  SEND STK PUSH
     |========================================================= */
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

    /* =========================================================
     |  CREATE ORDER AFTER PAYMENT
     |========================================================= */
    public function createOrderAfterPayment(Request $request)
    {
        try {
            $checkoutRequestId = $request->input('checkout_request_id');

            $mpesaStk = MpesaStk::where('checkout_request_id', $checkoutRequestId)->first();

            if (!$mpesaStk) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            if ((string) $mpesaStk->result_code !== '0') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not confirmed yet or failed',
                    'data'    => [
                        'result_code' => $mpesaStk->result_code,
                        'result_desc' => $mpesaStk->result_desc,
                    ],
                ], 400);
            }

            if ($mpesaStk->order_id) {
                $order = Order::find($mpesaStk->order_id);
                if ($order) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order already created',
                        'data'    => [
                            'order_id'     => $order->id,
                            'order_no'     => $order->order_no,
                            'redirect_url' => route('cart.thankyou', ['order' => $order->id]),
                        ],
                    ]);
                }
            }

            $pendingData = session()->get('pending_payment');

            if (!$pendingData || $pendingData['checkout_request_id'] != $checkoutRequestId) {
                Log::error('Pending payment data not found or mismatch', [
                    'checkout_request_id' => $checkoutRequestId,
                    'pending'             => $pendingData ? 'exists' : 'not found',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Payment data not found',
                ], 400);
            }

            DB::beginTransaction();

            try {
                // 1. Create Order
                $order = Order::create([
                    'order_no'         => 'ORD-' . strtoupper(Str::random(10)),
                    'uuid'             => (string) Str::uuid(),
                    'qty'              => $pendingData['total_qty'],
                    'total_amount'     => $pendingData['total_amount'],
                    'status'           => 'paid',
                    'initiator'        => $pendingData['name'],
                    'email'            => $pendingData['email'],
                    'event_id'         => $pendingData['event_id'],
                    'transaction_code' => $mpesaStk->mpesa_receipt_number,
                    'user_id'          => auth()->id(),
                ]);

                // 2. Order Items (tickets only — experiences tracked via ExperienceBooking)
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
                $buyerPhone = $mpesaStk->phone_number ?? $pendingData['phone'];
                $attendees  = [];
                $bookings   = [];

                // 3. Attendees (tickets)
                foreach (($pendingData['cart_items'] ?? []) as $item) {
                    for ($i = 0; $i < $item['qty']; $i++) {
                        $attendeeName = $item['qty'] > 1
                            ? $buyerName . ' (' . ($i + 1) . ')'
                            : $buyerName;

                        $attendees[] = Attendee::create([
                            'user_id'          => auth()->id(),
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
                            'transaction_code' => $mpesaStk->mpesa_receipt_number,
                            'qr_code'          => 'QR-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                            'created_by'       => auth()->id(),
                        ]);
                    }
                }

                // 4. ExperienceBookings
                foreach (($pendingData['experience_items'] ?? []) as $item) {
                    for ($i = 0; $i < $item['qty']; $i++) {
                        $bookingName = $item['qty'] > 1
                            ? $buyerName . ' (' . ($i + 1) . ')'
                            : $buyerName;

                        $packagePrice      = (float) ($item['package_price'] ?? $item['price'] ?? 0);
                        $categorySurcharge = (float) ($item['category_surcharge'] ?? 0);
                        $totalPrice        = (float) ($item['price'] ?? ($packagePrice + $categorySurcharge));

                        $bookings[] = ExperienceBooking::create([
                            'user_id'            => auth()->id(),
                            'experience_id'      => $item['experience_id'],
                            'package_id'         => $item['package_id'] ?? null,
                            'category_id'        => $item['category_id'] ?? null,
                            'order_id'           => $order->id,
                            'name'               => $bookingName,
                            'phone'              => $buyerPhone,
                            'email'              => $buyerEmail,
                            'gender'             => $pendingData['gender'] ?? null,
                            'qty'                => 1,
                            'package_price'      => $packagePrice,
                            'category_surcharge' => $categorySurcharge,
                            'total_price'        => $totalPrice,
                            'payment_status'     => 'paid',
                            'payment_mode'       => 'Mobile Money',
                            'payment_mode_id'    => $payMode->id ?? 1,
                            'transaction_code'   => $mpesaStk->mpesa_receipt_number,
                            'qr_code'            => 'EXP-' . $order->order_no . '-' . strtoupper(Str::random(6)),
                            'status'             => 'active',
                            'created_by'         => (string) auth()->id(),
                        ]);
                    }
                }

                // 5. Link order to first attendee (if any)
                if (!empty($attendees)) {
                    $order->update(['attendee_id' => $attendees[0]->id]);
                }

                // 6. Update MpesaStk
                $mpesaStk->update([
                    'order_id'    => $order->id,
                    'attendee_id' => $attendees[0]->id ?? null,
                ]);

                // 7. Clear cart
                session()->forget('cart');
                session()->forget('experiences');
                session()->forget('pending_payment');

                DB::commit();

                Log::info('🎉 Order + Attendees + ExperienceBookings created', [
                    'order_id'         => $order->id,
                    'receipt'          => $mpesaStk->mpesa_receipt_number,
                    'attendees_count'  => count($attendees),
                    'bookings_count'   => count($bookings),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Order created successfully',
                    'data'    => [
                        'order_id'     => $order->id,
                        'order_no'     => $order->order_no,
                        'redirect_url' => route('cart.thankyou', ['order' => $order->id]),
                    ],
                ]);

            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('❌ Order creation failed: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order: ' . $e->getMessage(),
                ], 500);
            }

        } catch (\Throwable $e) {
            Log::error('❌ Order creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* =========================================================
     |  THANK YOU PAGE
     |========================================================= */
    public function thankyou(Request $request)
    {
        $orderId = $request->input('order');

        $order = Order::with(['orderItems', 'attendees'])->find($orderId);

        // Only the buyer (or an admin) may see an order — it shows the entry QR codes.
        $user = auth()->user();
        if (!$order || ((int) $order->user_id !== (int) $user->id && !$user->hasRole('admin'))) {
            return redirect()->route('cart.view')->with('error', 'Order not found');
        }

        $bookings = ExperienceBooking::with(['experience', 'package', 'category'])
            ->where('order_id', $order->id)
            ->get();

        return view('cart.thankyou', compact('order', 'bookings'));
    }

    /* =========================================================
     |  CART COUNT
     |========================================================= */
    public function getCartCount()
    {
        $cart        = session()->get('cart', []);
        $experiences = session()->get('experiences', []);
        $count       = 0;

        foreach ($cart as $item) {
            $count += $item['quantity'] ?? 0;
        }
        foreach ($experiences as $item) {
            $count += $item['quantity'] ?? 0;
        }

        return response()->json(['success' => true, 'count' => $count]);
    }

    /* =========================================================
     |  CART TOTAL
     |========================================================= */
    public function getCartTotal()
    {
        $cart        = session()->get('cart', []);
        $experiences = session()->get('experiences', []);
        $total       = 0;

        foreach ($cart as $item) {
            if (isset($item['price'], $item['quantity'])) {
                $total += $item['price'] * $item['quantity'];
            }
        }
        foreach ($experiences as $item) {
            if (isset($item['price'], $item['quantity'])) {
                $total += $item['price'] * $item['quantity'];
            }
        }

        return response()->json([
            'success'         => true,
            'total'           => $total,
            'formatted_total' => 'KES ' . number_format($total, 2),
        ]);
    }

    /* =========================================================
     |  UPDATE QUANTITY
     |========================================================= */
    public function updateQuantity(Request $request, $ticket_id)
    {
        try {
            $request->validate(['quantity' => 'required|integer|min:1|max:10']);

            if (is_string($ticket_id) && str_starts_with($ticket_id, 'exp-')) {
                $expId       = (int) substr($ticket_id, 4);
                $experiences = session()->get('experiences', []);

                if (!isset($experiences[$expId])) {
                    return response()->json(['success' => false, 'message' => 'Experience not found in cart'], 404);
                }

                $available = $this->availableExperienceSpots($expId, $experiences[$expId]['package_id'] ?? null, $experiences[$expId]['category_id'] ?? null);
                if ($available !== null && $request->quantity > $available) {
                    return response()->json([
                        'success' => false,
                        'message' => $available > 0
                            ? "Only {$available} slot(s) left."
                            : "This experience is sold out.",
                    ], 400);
                }

                $experiences[$expId]['quantity'] = $request->quantity;
                session()->put('experiences', $experiences);

                return response()->json(['success' => true, 'message' => 'Quantity updated successfully']);
            }

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