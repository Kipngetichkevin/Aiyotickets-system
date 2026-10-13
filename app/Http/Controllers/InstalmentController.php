<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Experience;
use App\Models\ExperienceBooking;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Services\DarajaService;
use App\Services\Instalments;
use App\Services\MpesaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Merchant side of lipa mdogo mdogo: switch instalments on per event/experience
 * and follow instalment orders.
 */
class InstalmentController extends Controller
{
    public function settings()
    {
        $user = Auth::user();

        return view('instalments.settings', [
            'events'      => Event::where('user_id', $user->id)->where('end_date', '>=', now())->orderBy('date')->get(),
            'experiences' => Experience::where('user_id', $user->id)
                ->where(fn ($q) => $q->whereNull('end_date')->orWhere('end_date', '>=', now()))
                ->orderBy('start_date')->get(),
            'orders'      => Order::where('payment_plan', 'instalments')->where('seller_id', $user->id)
                ->latest()->take(100)->get(),
        ]);
    }

    /** Buyer: their instalment orders, with a way to pay the balance. */
    public function mine()
    {
        $orders = Order::where('payment_plan', 'instalments')
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(function (Order $order) {
                $attendees = Attendee::with('ticket.event')->where('order_id', $order->id)->get();
                $bookings  = ExperienceBooking::with('experience')->where('order_id', $order->id)->get();

                $order->item_name  = $attendees->first()?->ticket?->event?->name ?? $bookings->first()?->experience?->name ?? 'Order';
                $order->item_count = $attendees->count() + $bookings->count();

                return $order;
            });

        return view('instalments.mine', ['orders' => $orders, 'minimum' => Instalments::MIN_INSTALMENT]);
    }

    /** Buyer: send an M-Pesa prompt for part (or all) of the balance. */
    public function pay(Request $request, Order $order, DarajaService $daraja)
    {
        abort_unless((int) $order->user_id === (int) Auth::id(), 404);

        if ($order->status !== 'partial') {
            return response()->json(['success' => false, 'message' => 'This order is not open for payments.'], 400);
        }

        $balance = (int) ceil((float) $order->balance_due);
        $minimum = min(Instalments::MIN_INSTALMENT, $balance);

        $data = $request->validate([
            'amount' => "required|integer|min:{$minimum}|max:{$balance}",
            'phone'  => 'required|string|max:20',
        ], ['amount.max' => "The balance is KES {$balance}.", 'amount.min' => "Pay at least KES {$minimum}."]);

        $phone = preg_replace('/\D/', '', $data['phone']);
        $phone = strlen($phone) === 10 && $phone[0] === '0' ? '254' . substr($phone, 1) : $phone;
        if (!preg_match('/^254[17]\d{8}$/', $phone)) {
            return response()->json(['success' => false, 'message' => 'Use an M-Pesa number like 0712345678.'], 422);
        }

        try {
            $response = $daraja->stkPush($data['amount'], $phone, 'ORDER-' . $order->order_no, 'Lipa mdogo mdogo');
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $this->userError('Could not reach M-Pesa', $e)], 502);
        }

        if (($response['ResponseCode'] ?? null) !== '0' || empty($response['CheckoutRequestID'])) {
            return response()->json(['success' => false, 'message' => $response['errorMessage'] ?? $response['ResponseDescription'] ?? 'M-Pesa did not accept the request.'], 400);
        }

        MpesaStk::create([
            'checkout_request_id'  => $response['CheckoutRequestID'],
            'merchant_request_id'  => $response['MerchantRequestID'] ?? null,
            'response'             => $response,
            'user_id'              => Auth::id(),
            'status'               => MpesaPaymentService::PENDING,
            'result_desc'          => 'Awaiting payment confirmation',
            'amount'               => $data['amount'],
            'phone_number'         => $phone,
            'pending_data'         => ['instalment_for' => $order->id, 'user_id' => Auth::id(), 'phone' => $phone],
            'response_code'        => '0',
            'response_description' => 'Request sent to M-Pesa',
            'customer_message'     => 'Please check your phone to complete payment',
        ]);

        return response()->json(['success' => true, 'checkout_request_id' => $response['CheckoutRequestID']]);
    }

    public function update(Request $request, string $type, int $id)
    {
        abort_unless(in_array($type, ['event', 'experience'], true), 404);

        $item = ($type === 'event' ? Event::class : Experience::class)::findOrFail($id);
        abort_unless((int) $item->user_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'allow_instalments'          => 'nullable|boolean',
            'instalment_deposit_percent' => 'required|integer|min:10|max:90',
            'instalment_due_days'        => 'required|integer|min:1|max:90',
        ]);

        $item->update([
            'allow_instalments'          => $request->boolean('allow_instalments'),
            'instalment_deposit_percent' => $data['instalment_deposit_percent'],
            'instalment_due_days'        => $data['instalment_due_days'],
        ]);

        return back()->with('success', "Lipa mdogo mdogo " . ($item->allow_instalments ? 'on' : 'off') . " for {$item->name}.");
    }
}
