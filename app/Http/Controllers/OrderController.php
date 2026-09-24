<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $query = Order::with(['event', 'orderItems', 'attendees'])
            ->orderByDesc('created_at');

        if (!$isAdmin) {
            $eventIds = Event::where('user_id', $user->id)->pluck('id');
            $query->whereIn('event_id', $eventIds);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('order_no', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('initiator', 'like', "%{$s}%");
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // ---- Stats: source of truth = orders ----
        $statsQuery = Order::query();
        if (!$isAdmin) {
            $eventIds = $eventIds ?? Event::where('user_id', $user->id)->pluck('id');
            $statsQuery->whereIn('event_id', $eventIds);
        }

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'paid'     => (clone $statsQuery)->where('status', 'paid')->count(),
            'pending'  => (clone $statsQuery)->where('status', 'pending')->count(),
            'revenue'  => (clone $statsQuery)->where('status', 'paid')->sum('total_amount'),
        ];

        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('orders.index', compact('orders', 'events', 'isAdmin', 'stats'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $order = Order::with([
            'event',
            'orderItems.ticket',
            'attendees.ticket',
            'payments',
        ])->findOrFail($id);

        if (!$isAdmin) {
            $ownsEvent = Event::where('id', $order->event_id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$ownsEvent) {
                abort(403, 'You do not have access to this order.');
            }
        }

        $existingInvoice = Invoice::where('transaction_reference', $order->order_no)->first();

        return view('orders.show', compact('order', 'isAdmin', 'existingInvoice'));
    }

    public function generateInvoice($id)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $order = Order::with(['event', 'attendees', 'payments'])->findOrFail($id);

        if (!$isAdmin) {
            $ownsEvent = Event::where('id', $order->event_id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$ownsEvent) {
                abort(403, 'Unauthorized action.');
            }
        }

        $existing = Invoice::where('transaction_reference', $order->order_no)->first();
        if ($existing) {
            return redirect()->route('finance.index', ['tab' => 'invoices'])
                ->with('success', 'Invoice ' . $existing->invoice_number . ' already exists for this order.');
        }

        $firstAttendee = $order->attendees->first();
        if (!$firstAttendee) {
            return back()->with('error', 'Cannot generate invoice — this order has no attendees yet.');
        }

        $year = now()->year;
        $lastThisYear = Invoice::where('invoice_number', 'like', "INV-{$year}-%")
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($lastThisYear) {
            $parts = explode('-', $lastThisYear->invoice_number);
            $nextNumber = ((int) end($parts)) + 1;
        }

        $invoiceNumber = sprintf('INV-%d-%04d', $year, $nextNumber);

        $amount = (float) $order->total_amount;
        $vat    = (float) ($order->tax ?? 0);
        $total  = $amount + $vat;

        $payment = $order->payments->where('status', 'paid')->first();
        $paymentMode = $payment->payment_mode ?? null;

        $invoice = Invoice::create([
            'event_id'              => $order->event_id,
            'attendee_id'           => $firstAttendee->id,
            'invoice_number'        => $invoiceNumber,
            'amount'                => $amount,
            'vat_amount'            => $vat,
            'total_amount'          => $total,
            'currency'              => optional($order->event)->currency ?? 'KES',
            'status'                => 'paid',
            'issue_date'            => now()->toDateString(),
            'due_date'              => now()->toDateString(),
            'paid_date'             => $order->created_at?->toDateString() ?? now()->toDateString(),
            'payment_method'        => $paymentMode,
            'transaction_reference' => $order->order_no,
            'notes'                 => 'Generated from order ' . $order->order_no,
        ]);

        return redirect()->route('finance.index', ['tab' => 'invoices'])
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' generated successfully.');
    }
}