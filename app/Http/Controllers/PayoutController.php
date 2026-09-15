<?php

namespace App\Http\Controllers;

use App\Models\Payee;
use App\Models\Payout;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    public function index(Request $request)
    {
        $query = Payout::with(['payee', 'service', 'payer', 'approver'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('payee_id')) {
            $query->where('payee_id', $request->payee_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_ref', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('payee', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payouts = $query->paginate(20)->withQueryString();

        $counts = [
            'all'        => Payout::count(),
            'pending'    => Payout::where('status', 'pending')->count(),
            'approved'   => Payout::where('status', 'approved')->count(),
            'processing' => Payout::where('status', 'processing')->count(),
            'paid'       => Payout::where('status', 'paid')->count(),
            'failed'     => Payout::where('status', 'failed')->count(),
        ];

        $totals = [
            'paid'    => Payout::where('status', 'paid')->sum('amount'),
            'pending' => Payout::whereIn('status', ['pending', 'approved', 'processing'])->sum('amount'),
        ];

        $payees = Payee::active()->orderBy('name')->get();

        $services = Service::with(['payee', 'event'])
            ->whereIn('status', ['approved', 'delivered'])
            ->orderBy('created_at', 'desc')
            ->get();

        // ✅ Compute admin flag ONCE — no per-row DB queries
        $isAdmin = auth()->user()->hasRole('admin');

        return view('backend.payouts.index', compact(
            'payouts', 'counts', 'totals', 'payees', 'services', 'isAdmin'
        ));
    }

    // Modal-driven pages — redirect direct URL visits safely
    public function create() { return redirect()->route('backend.payouts.index'); }
    public function show(Payout $payout) { return redirect()->route('backend.payouts.index'); }
    public function edit(Payout $payout) { return redirect()->route('backend.payouts.index'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payee_id'    => 'required|exists:payees,id',
            'service_id'  => 'nullable|exists:services,id',
            'amount'      => 'required|numeric|min:1',
            'currency'    => 'required|string|max:3',
            'method'      => 'required|in:mpesa,bank,cash,cheque',
            'phone'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $validated['status'] = 'pending';

        $threshold = (float) env('PAYOUT_APPROVAL_THRESHOLD', 50000);
        if ((float) $validated['amount'] < $threshold) {
            $validated['status']      = 'approved';
            $validated['approved_by'] = Auth::id();
            $validated['approved_at'] = now();
        }

        Payout::create($validated);

        $msg = $validated['status'] === 'approved'
            ? 'Payout created and auto-approved (under KES ' . number_format($threshold) . ').'
            : 'Payout created — pending approval.';

        return redirect()->route('backend.payouts.index')->with('success', $msg);
    }

    public function update(Request $request, Payout $payout)
    {
        if ($payout->status === 'paid') {
            return back()->with('error', 'Paid payouts cannot be edited.');
        }

        $validated = $request->validate([
            'amount'      => 'required|numeric|min:1',
            'method'      => 'required|in:mpesa,bank,cash,cheque',
            'phone'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $payout->update($validated);

        return redirect()->route('backend.payouts.index')->with('success', 'Payout updated.');
    }

    public function destroy(Payout $payout)
    {
        if ($payout->status === 'paid') {
            return redirect()->route('backend.payouts.index')->with('error', 'Cannot delete a paid payout.');
        }

        $payout->delete();

        return redirect()->route('backend.payouts.index')->with('success', 'Payout deleted.');
    }

    public function approve(Payout $payout)
    {
        // ✅ Check role once — this is a POST action, not a per-page loop
        if (!auth()->user()->hasRole('admin')) {
            abort(403, 'Only admins can approve payouts.');
        }

        if ($payout->status !== 'pending') {
            return back()->with('error', 'Only pending payouts can be approved.');
        }

        $payout->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Payout approved.');
    }

    public function markPaid(Request $request, Payout $payout)
    {
        $request->validate([
            'transaction_ref' => 'nullable|string|max:255',
        ]);

        if (!in_array($payout->status, ['approved', 'processing'])) {
            return back()->with('error', 'Only approved payouts can be marked as paid.');
        }

        $payout->update([
            'status'          => 'paid',
            'transaction_ref' => $request->transaction_ref,
            'paid_at'         => now(),
            'paid_by'         => Auth::id(),
        ]);

        return back()->with('success', 'Payout marked as paid.');
    }
}