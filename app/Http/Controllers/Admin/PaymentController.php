<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MpesaStk;
use App\Services\MpesaPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Admin view of every M-Pesa STK push: what was asked for, what Safaricom said,
 * and which order it produced. Used to match payments against the M-Pesa statement.
 */
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:all,pending,completed,failed,unfulfilled',
            'q'      => 'nullable|string|max:100',
            'from'   => 'nullable|date',
            'to'     => 'nullable|date',
        ]);

        $status = $filters['status'] ?? 'all';
        $search = trim($filters['q'] ?? '');
        $from   = !empty($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $to     = !empty($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : null;

        $query = MpesaStk::query()
            ->when($from, fn ($q) => $q->where('created_at', '>=', $from))
            ->when($to, fn ($q) => $q->where('created_at', '<=', $to))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('mpesa_receipt_number', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', '%' . ltrim($search, '+0') . '%')
                ->orWhere('checkout_request_id', $search)
                ->orWhereHas('order', fn ($o) => $o->where('order_no', $search))
                ->orWhereHas('user', fn ($u) => $u->where('email', 'like', "%{$search}%"))));

        // Totals for the same filters, before the status tab narrows them
        $totals = (clone $query)
            ->selectRaw('status, count(*) n, coalesce(sum(amount), 0) total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $unfulfilled = (clone $query)->where('status', MpesaPaymentService::COMPLETED)
            ->whereNull('order_id')->whereNull('instalment_order_id')->count();

        $payments = $query
            ->when(in_array($status, ['pending', 'completed', 'failed'], true), fn ($q) => $q->where('status', $status))
            ->when($status === 'unfulfilled', fn ($q) => $q->where('status', MpesaPaymentService::COMPLETED)
                ->whereNull('order_id')->whereNull('instalment_order_id'))
            ->with(['user:id,name,email', 'order:id,order_no,status', 'instalmentOrder:id,order_no,status'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'totals', 'unfulfilled', 'status', 'search') + [
            'from' => $filters['from'] ?? null,
            'to'   => $filters['to'] ?? null,
        ]);
    }

    public function recheck(MpesaStk $payment, MpesaPaymentService $payments)
    {
        return back()->with('success', "{$payment->checkout_request_id}: " . $payments->recheck($payment));
    }
}
