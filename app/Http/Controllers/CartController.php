<?php

namespace App\Http\Controllers;

use Iankumu\Mpesa\Facades\Mpesa;
use Illuminate\Http\Request;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\MpesaStk;
use App\Models\Ticket;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Add a single ticket to the cart (after validating sales rules).
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'quantity'  => 'required|integer|min:1|max:20',
        ]);

        $ticket = Ticket::with('event')->findOrFail($request->ticket_id);
        $requestedQty = (int) $request->quantity;

        // ---- 1. Sales window check ----
        if ($ticket->isComingSoon()) {
            return response()->json([
                'success' => false,
                'message' => $ticket->name . ' sales open on ' . $ticket->start_date->format('d M Y, g:i A'),
            ], 422);
        }

        if ($ticket->isEnded()) {
            return response()->json([
                'success' => false,
                'message' => $ticket->name . ' sales ended on ' . $ticket->end_date->format('d M Y, g:i A'),
            ], 422);
        }

        if (!$ticket->isWithinSalesWindow()) {
            return response()->json([
                'success' => false,
                'message' => $ticket->name . ' is not currently on sale.',
            ], 422);
        }

        // ---- 2. Stock check ----
        if ($ticket->isSoldOut()) {
            return response()->json([
                'success' => false,
                'message' => $ticket->name . ' is sold out.',
            ], 422);
        }

        // ---- 3. Cart + stock check ----
        $cart = session()->get('cart', []);
        $existingQty = $cart[$ticket->id]['quantity'] ?? 0;
        $totalRequested = $existingQty + $requestedQty;

        if ($totalRequested > $ticket->available_quantity) {
            $remaining = $ticket->available_quantity;
            $msg = $remaining <= 0
                ? $ticket->name . ' is sold out.'
                : "Only {$remaining} " . \Illuminate\Support\Str::plural('ticket', $remaining) . " of {$ticket->name} remaining.";
            return response()->json([
                'success' => false,
                'message' => $msg,
            ], 422);
        }

        // ---- 4. Add to cart ----
        if (isset($cart[$ticket->id])) {
            $cart[$ticket->id]['quantity'] += $requestedQty;
        } else {
            $cart[$ticket->id] = [
                'ticket_id' => $ticket->id,
                'event_id'  => $ticket->event_id,
                'name'      => $ticket->name,
                'price'     => $ticket->price,
                'currency'  => $ticket->currency,
                'quantity'  => $requestedQty,
            ];
        }

        session(['cart' => $cart]);

        return response()->json([
            'success'    => true,
            'message'    => 'Ticket added to cart',
            'cart_count' => count($cart),
            'added'      => [
                'ticket'   => $ticket->name,
                'quantity' => $requestedQty,
            ],
        ]);
    }

    public function viewCart()
    {
        $cart = session()->get('cart', []);

        foreach ($cart as $ticket_id => &$item) {
            $event = Event::find($item['event_id']);
            $ticket = Ticket::find($ticket_id);
            if ($event) {
                $item['event_name']  = $event->name;
                $item['event_image'] = $event->image;
                $item['ticket_name'] = $ticket->name ?? 'Ticket';
            } else {
                $item['event_name']  = 'Unknown Event';
                $item['event_image'] = null;
                $item['ticket_name'] = 'Ticket';
            }
        }

        return view('cart.view', compact('cart'));
    }

    public function removeTicket($ticket_id)
    {
        $cart = session()->get('cart', []);

        if (isset($cart[$ticket_id])) {
            unset($cart[$ticket_id]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.view')->with('success', 'Ticket removed successfully.');
    }

    public function clearCart()
    {
        session()->forget('cart');
        return redirect()->route('cart.view')->with('success', 'Cart has been cleared.');
    }

    /**
     * Checkout — re-validates every ticket before launching STK push.
     */
    public function checkout(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'phone'         => 'required|string|max:15',
            'email'         => 'required|email|max:255',
            'gender'        => 'required|string',
            'payment_mode'  => 'required|string',
            'attendee_data' => 'required|string',
        ]);

        try {
            $attendeeData = json_decode($request->input('attendee_data'), true);

            if (!is_array($attendeeData) || empty($attendeeData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tickets found. Please add tickets to your cart before checkout.',
                ], 422);
            }

            $totalAmount = 0;
            $account = '';

            // ---- REVALIDATE EVERY TICKET ----
            foreach ($attendeeData as $ticketId => $ticket) {

                if (
                    !isset($ticket['ticket_id'], $ticket['quantity'], $ticket['price']) ||
                    $ticket['quantity'] <= 0 ||
                    $ticket['price'] <= 0
                ) {
                    continue;
                }

                $ticketModel = Ticket::find($ticket['ticket_id']);

                if (!$ticketModel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'One of your tickets no longer exists.',
                    ], 422);
                }

                if ($ticketModel->isComingSoon()) {
                    return response()->json([
                        'success' => false,
                        'message' => $ticketModel->name . ' sales have not opened yet.',
                    ], 422);
                }

                if ($ticketModel->isEnded()) {
                    return response()->json([
                        'success' => false,
                        'message' => $ticketModel->name . ' sales have ended.',
                    ], 422);
                }

                if ($ticketModel->isSoldOut()) {
                    return response()->json([
                        'success' => false,
                        'message' => $ticketModel->name . ' is now sold out.',
                    ], 422);
                }

                if ($ticket['quantity'] > $ticketModel->available_quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only ' . $ticketModel->available_quantity . ' of ' . $ticketModel->name . ' left.',
                    ], 422);
                }

                // Sum up
                $totalAmount += $ticket['quantity'] * $ticket['price'];
                $account = $ticketModel->name;
            }

            if ($totalAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total amount is invalid.',
                ], 422);
            }

            $phone = formatPhoneNumber($request->input('phone'));
            $this->launchStk($phone, $totalAmount, $account);

            return response()->json([
                'success' => true,
            ]);
        } catch (\Throwable $th) {
            Log::error('Checkout failed: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function thankyou()
    {
        return view('cart.thankyou');
    }

    public function launchStk($phone, $amount, $account)
    {
        $response = Mpesa::stkpush($phone, $amount, $account);
        $result = $response->json();

        MpesaStk::create([
            'response' => json_encode($result),
            'user_id'  => auth()->id(),
        ]);

        Log::info(json_encode($result));
    }
}