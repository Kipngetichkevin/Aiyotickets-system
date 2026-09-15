<?php

namespace App\Http\Controllers;

use App\Models\Payee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayeeController extends Controller
{
    /**
     * List all payees (filterable by category, status, search).
     * The same page serves Suppliers (?category=supplier),
     * Personnel (?category=personnel), and Creditors (?category=creditor).
     */
    public function index(Request $request)
    {
        $query = Payee::with(['creator', 'payouts']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('point_of_contact', 'like', "%{$search}%");
            });
        }

        $payees = $query->latest()->paginate(20);

        return view('backend.payees.index', compact('payees'));
    }

    /**
     * Create form is now a modal — redirect back if visited directly.
     */
    public function create()
    {
        return redirect()
            ->route('backend.payees.index')
            ->with('info', 'Use the "Add Payee" button to create a new payee.');
    }

    /**
     * Store a new payee.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category'          => 'required|in:supplier,personnel,creditor,debtor,other',
            'type'              => 'nullable|string|max:255',
            'name'              => 'required|string|max:255',
            'point_of_contact'  => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string',
            'id_number'         => 'nullable|string|max:255',
            'kra_pin'           => 'nullable|string|max:255',
            'mpesa_number'      => 'nullable|string|max:20',
            'bank_name'         => 'nullable|string|max:255',
            'bank_account'      => 'nullable|string|max:255',
            'bank_branch'       => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
            'status'            => 'required|in:active,inactive',
        ]);

        $validated['created_by'] = Auth::id();

        Payee::create($validated);

        return redirect()
            ->route('backend.payees.index')
            ->with('success', 'Payee created successfully.');
    }

    /**
     * Show is now a modal — redirect back if visited directly.
     */
    public function show(Payee $payee)
    {
        return redirect()
            ->route('backend.payees.index')
            ->with('info', "Use the table to view {$payee->name}.");
    }

    /**
     * Edit form is now a modal — redirect back if visited directly.
     */
    public function edit(Payee $payee)
    {
        return redirect()
            ->route('backend.payees.index')
            ->with('info', "Use the Edit button on the table to edit {$payee->name}.");
    }

    /**
     * Update an existing payee.
     */
    public function update(Request $request, Payee $payee)
    {
        $validated = $request->validate([
            'category'          => 'required|in:supplier,personnel,creditor,debtor,other',
            'type'              => 'nullable|string|max:255',
            'name'              => 'required|string|max:255',
            'point_of_contact'  => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'address'           => 'nullable|string',
            'id_number'         => 'nullable|string|max:255',
            'kra_pin'           => 'nullable|string|max:255',
            'mpesa_number'      => 'nullable|string|max:20',
            'bank_name'         => 'nullable|string|max:255',
            'bank_account'      => 'nullable|string|max:255',
            'bank_branch'       => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
            'status'            => 'required|in:active,inactive',
        ]);

        $payee->update($validated);

        return redirect()
            ->route('backend.payees.index')
            ->with('success', "Payee \"{$payee->name}\" updated successfully.");
    }

    /**
     * Soft-delete a payee.
     * Blocks deletion if the payee has already-received payouts.
     */
    public function destroy(Payee $payee)
    {
        if ($payee->payouts()->where('status', 'paid')->exists()) {
            return redirect()
                ->route('backend.payees.index')
                ->with('error', "Cannot delete \"{$payee->name}\" — they have paid payouts on record.");
        }

        $name = $payee->name;
        $payee->delete();

        return redirect()
            ->route('backend.payees.index')
            ->with('success', "Payee \"{$name}\" deleted successfully.");
    }
}