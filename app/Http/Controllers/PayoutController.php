<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payout;
use App\Services\SalesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class PayoutController extends Controller
{
    /** Merchant: balance, request form and payout history. */
    public function index()
    {
        $user = Auth::user();

        return view('payouts.index', [
            'balance'    => SalesReport::own($user)->balance(),
            'payouts'    => Payout::where('user_id', $user->id)->latest()->get(),
            'minimum'    => config('aiyo.payout_minimum'),
            'feePercent' => config('aiyo.platform_fee_percent'),
            'afterEnd'   => config('aiyo.payout_after_end'),
        ]);
    }

    /** Merchant: request a payout from the withdrawable balance. */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'method'      => 'required|in:mpesa,bank',
            'destination' => 'required|string|max:255',
            'amount'      => 'required|numeric|min:' . config('aiyo.payout_minimum'),
        ]);

        if ($validated['method'] === 'mpesa' && !preg_match('/^(?:254|0)[17]\d{8}$/', preg_replace('/\s+/', '', $validated['destination']))) {
            return back()->withErrors(['destination' => 'Enter an M-Pesa number like 0712345678.'])->withInput();
        }

        // One request at a time per merchant, so two quick submits can't both pass the balance check
        return Cache::lock('payout-request:' . $user->id, 10)->block(5, function () use ($user, $validated) {
            $withdrawable = SalesReport::own($user)->balance()['withdrawable'];

            if ($validated['amount'] > $withdrawable) {
                return back()->withErrors(['amount' => 'You can withdraw up to KES ' . number_format($withdrawable, 2) . '.'])->withInput();
            }

            Payout::create([
                'user_id'     => $user->id,
                'amount'      => round($validated['amount'], 2),
                'method'      => $validated['method'],
                'destination' => trim($validated['destination']),
                'status'      => Payout::REQUESTED,
            ]);

            return redirect()->route('payouts.index')->with('success', 'Payout requested. You will be notified when it is paid.');
        });
    }

    /** Admin: all payout requests. */
    public function adminIndex(Request $request)
    {
        $status = $request->input('status', Payout::REQUESTED);

        return view('payouts.admin', [
            'status'  => $status,
            'payouts' => Payout::with(['user', 'processor'])
                ->when($status !== 'all', fn ($q) => $q->where('status', $status))
                ->latest()
                ->get(),
            'counts'  => Payout::selectRaw('status, count(*) n')->groupBy('status')->pluck('n', 'status'),
            'refunds' => Order::where('refund', '>', 0)->whereNull('refunded_at')->oldest('lapsed_at')->get(),
        ]);
    }

    /** Admin: record that a lipa mdogo mdogo refund was sent to the buyer. */
    public function markRefunded(Request $request, Order $order)
    {
        $reference = $request->validate(['reference' => 'required|string|max:100'])['reference'];

        if ($order->refund <= 0 || $order->refunded_at) {
            return back()->with('error', "Order {$order->order_no} has no refund outstanding.");
        }

        $order->update(['refund_reference' => $reference, 'refunded_at' => now()]);

        return back()->with('success', "Refund for {$order->order_no} recorded.");
    }

    public function approve(Payout $payout)
    {
        return $this->transition($payout, [Payout::REQUESTED], Payout::APPROVED);
    }

    public function reject(Request $request, Payout $payout)
    {
        $note = $request->validate(['note' => 'required|string|max:500'])['note'];

        return $this->transition($payout, [Payout::REQUESTED, Payout::APPROVED], Payout::REJECTED, ['note' => $note]);
    }

    public function markPaid(Request $request, Payout $payout)
    {
        $reference = $request->validate(['reference' => 'required|string|max:100'])['reference'];

        return $this->transition($payout, [Payout::REQUESTED, Payout::APPROVED], Payout::PAID, ['reference' => $reference]);
    }

    private function transition(Payout $payout, array $from, string $to, array $extra = [])
    {
        if (!in_array($payout->status, $from, true)) {
            return back()->with('error', "This payout is already {$payout->status}.");
        }

        $payout->update($extra + [
            'status'       => $to,
            'processed_by' => Auth::id(),
            'processed_at' => now(),
        ]);

        return back()->with('success', "Payout #{$payout->id} marked {$to}.");
    }
}
