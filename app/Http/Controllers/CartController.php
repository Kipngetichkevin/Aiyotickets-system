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
use App\Models\PromoCode;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Services\DarajaService;
use App\Services\Instalments;
use App\Services\Inventory;
use App\Services\MpesaPaymentService;

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
        return app(Inventory::class)->ticketsLeft($ticketId);
    }

    private function availableExperienceSpots(int $experienceId, ?int $packageId, ?int $categoryId): ?int
    {
        return app(Inventory::class)->experienceSpotsLeft($experienceId, $packageId, $categoryId);
    }

    /** Session cart tickets as checkout lines, priced from the database. */
    private function ticketLines(array $cart): array
    {
        $lines = [];
        foreach ($cart as $ticketId => $item) {
            $ticket = Ticket::find($ticketId);
            if (!$ticket || empty($item['quantity'])) continue;
            $lines[] = [
                'ticket_id' => $ticketId,
                'qty'       => (int) $item['quantity'],
                'price'     => (float) $ticket->price,
                'subtotal'  => (float) $ticket->price * (int) $item['quantity'],
            ];
        }
        return $lines;
    }

    /** The promo code in the session applied to the cart: [code, discount] or [error]. */
    private function sessionPromo(array $lines): ?array
    {
        $code = session('promo_code');
        if (!$code) return null;

        $promo = PromoCode::findByCode($code);
        if (!$promo) {
            session()->forget('promo_code');
            return ['error' => 'That promo code no longer exists.'];
        }

        return ['promo' => $promo] + $promo->apply($lines);
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

        if ($stk && $stk->status === MpesaPaymentService::COMPLETED) {
            session()->forget('cart');
            session()->forget('experiences');
            session()->forget('pending_payment');
        } elseif ($stk && $stk->status === MpesaPaymentService::FAILED) {
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
                    if (!$ticket->isPurchasable()) {
                        return response()->json(['success' => false, 'message' => $ticket->notOnSaleMessage()], 400);
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
    public function applyPromo(Request $request)
    {
        $request->validate(['code' => 'required|string|max:40']);

        $promo = PromoCode::findByCode($request->input('code'));
        if (!$promo) {
            return redirect()->route('cart.view')->with('promo_error', 'That promo code does not exist.');
        }

        $result = $promo->apply($this->ticketLines(session('cart', [])));
        if (isset($result['error'])) {
            return redirect()->route('cart.view')->with('promo_error', $result['error']);
        }

        session(['promo_code' => $promo->code]);

        return redirect()->route('cart.view')->with('promo_success', "{$promo->code} applied: {$promo->label()}.");
    }

    public function removePromo()
    {
        session()->forget('promo_code');

        return redirect()->route('cart.view');
    }

    public function viewCart()
    {
        $this->selfHealCart();

        $cart        = session()->get('cart', []);
        $experiences = session()->get('experiences', []);

        $promo = $this->sessionPromo($this->ticketLines($cart));
        if ($promo && isset($promo['error'])) {
            session()->forget('promo_code');
            session()->flash('promo_error', $promo['error']);
            $promo = null;
        }

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

        // Lipa mdogo mdogo option, when this cart qualifies
        $expLines = array_map(fn ($id) => ['experience_id' => $id], array_keys($experiences));
        $plan     = app(Instalments::class)->planFor($this->ticketLines($cart), $expLines);
        $instalment = null;
        if (!isset($plan['error'])) {
            $total = 0;
            foreach ($cart as $item) { $total += ($item['quantity'] ?? 0) * ($item['price'] ?? 0); }
            foreach ($experiences as $item) { $total += ($item['quantity'] ?? 0) * ($item['price'] ?? 0); }
            $total = (int) round($total - (float) ($promo['discount'] ?? 0));
            $instalment = $plan + ['deposit' => app(Instalments::class)->deposit($total, $plan['percent'])];
        }

        return view('cart.view', compact('cart', 'experiences', 'promo', 'instalment'));
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
                'payment_plan' => 'nullable|in:full,instalments',
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

                if (!$dbTicket->isPurchasable()) {
                    return response()->json(['success' => false, 'message' => $dbTicket->notOnSaleMessage() . ' Please remove it from your cart.'], 400);
                }

                if (strtoupper($dbTicket->currency ?? 'KES') !== 'KES') {
                    return response()->json([
                        'success' => false,
                        'message' => "{$dbTicket->name} is priced in {$dbTicket->currency}; only KES prices can be paid with M-Pesa.",
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

                if (strtoupper($package->currency ?? 'KES') !== 'KES') {
                    return response()->json([
                        'success' => false,
                        'message' => "{$package->name} is priced in {$package->currency}; only KES prices can be paid with M-Pesa.",
                    ], 400);
                }

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

            // Promo code: re-checked and applied here on database prices, never trusted from the page
            $promoInfo = null;
            if ($promo = $this->sessionPromo($cartItems)) {
                if (isset($promo['error'])) {
                    session()->forget('promo_code');
                    return response()->json(['success' => false, 'message' => $promo['error'] . ' It has been removed — please review your total.'], 400);
                }
                $cartItems    = $promo['lines'];
                $totalAmount -= $promo['discount'];
                $promoInfo    = ['promo_code_id' => $promo['promo']->id, 'promo_code' => $promo['promo']->code, 'discount' => $promo['discount']];
            }

            // M-Pesa takes whole shillings: round once, then charge and record the same amount
            $totalAmount = (int) round($totalAmount);

            if ($totalAmount < 1) {
                return response()->json(['success' => false, 'message' => 'Invalid total amount'], 400);
            }

            // Lipa mdogo mdogo: charge only the deposit now; the order is completed by later instalments
            $chargeAmount = $totalAmount;
            $planInfo     = null;
            if ($request->input('payment_plan') === 'instalments') {
                $plan = app(Instalments::class)->planFor($cartItems, $expItems);
                if (isset($plan['error'])) {
                    return response()->json(['success' => false, 'message' => $plan['error']], 400);
                }
                $chargeAmount = app(Instalments::class)->deposit($totalAmount, $plan['percent']);
                $planInfo = ['plan' => [
                    'type'      => $plan['type'],
                    'id'        => $plan['id'],
                    'seller_id' => $plan['seller_id'],
                    'due_date'  => $plan['due_date']->toDateTimeString(),
                    'deposit'   => $chargeAmount,
                ]];
            }

            $phone = $this->normalizePhone($request->input('phone'));
            if (!$phone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid phone number. Use format 254XXXXXXXXX or 07XXXXXXXX.',
                ], 400);
            }

            // Stock is locked from the availability check until the pending payment row
            // exists (that row holds the stock), so two buyers can't take the last place.
            $inventory = app(Inventory::class);
            try {
                $locks = $inventory->lock(array_column($cartItems, 'ticket_id'), array_column($expItems, 'experience_id'));
            } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
                return response()->json(['success' => false, 'message' => 'Checkout is busy for these items. Please try again.'], 409);
            }

            try {
                // Availability check — tickets
                foreach ($cartItems as $item) {
                    $available = $inventory->ticketsLeft((int) $item['ticket_id']);
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
                    $available = $inventory->experienceSpotsLeft((int) $item['experience_id'], $item['package_id'], $item['category_id']);
                    if ($available !== null && $item['qty'] > $available) {
                        return response()->json([
                            'success' => false,
                            'message' => $available > 0
                                ? "Only {$available} slot(s) left for {$item['name']}."
                                : "{$item['name']} is sold out.",
                        ], 400);
                    }
                }

                $tempOrderRef = 'TMP-' . strtoupper(Str::random(10));

                $paymentResult = $this->sendStkPush($phone, $chargeAmount, $tempOrderRef);

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
                ] + ($promoInfo ?? []) + ($planInfo ?? []);

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
                    'status'               => MpesaPaymentService::PENDING,
                    'result_code'          => null,
                    'result_desc'          => 'Awaiting payment confirmation',
                    'amount'               => $chargeAmount,
                    'phone_number'         => $phone,
                    'mpesa_receipt_number' => null,
                    'transaction_date'     => null,
                ]);
            } finally {
                $inventory->release($locks);
            }

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
                'total_amount'        => $chargeAmount,
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
                'message' => $this->userError('An error occurred during checkout', $th),
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
            return ['success' => false, 'message' => $this->userError('Could not reach M-Pesa', $e)];
        }
    }

    /* =========================================================
     |  CREATE ORDER AFTER PAYMENT
     |========================================================= */
    public function createOrderAfterPayment(Request $request, MpesaPaymentService $payments)
    {
        $mpesaStk = MpesaStk::where('checkout_request_id', $request->input('checkout_request_id'))->first();

        if (!$mpesaStk || (int) $mpesaStk->user_id !== (int) auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
        }

        if ($mpesaStk->status !== MpesaPaymentService::COMPLETED) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not confirmed yet or failed',
                'data'    => [
                    'status'      => $mpesaStk->status,
                    'result_code' => $mpesaStk->result_code,
                    'result_desc' => $mpesaStk->result_desc,
                ],
            ], 400);
        }

        // Same routine as the callback: locks the payment, so it never creates a second order.
        $order = $payments->fulfilSafely($mpesaStk);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Your payment was received but we could not create the order yet. It will be retried automatically.',
            ], 500);
        }

        session()->forget(['cart', 'experiences', 'pending_payment']);

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data'    => [
                'order_id'     => $order->id,
                'order_no'     => $order->order_no,
                'redirect_url' => route('cart.thankyou', ['order' => $order->id]),
            ],
        ]);
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
}