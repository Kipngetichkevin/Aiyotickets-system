<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Event;
use App\Models\Payee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    /**
     * List all services (filterable by event, payee, status, search).
     */
    public function index(Request $request)
    {
        $query = Service::with(['event', 'payee', 'creator']);

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('payee_id')) {
            $query->where('payee_id', $request->payee_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('payee', function ($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('event', function ($eq) use ($search) {
                      $eq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $services = $query->latest()->paginate(20);

        // Data for filters and the create form
        $events = Event::orderBy('date', 'desc')->get();
        $payees = Payee::active()->orderBy('name')->get();

        return view('backend.services.index', compact('services', 'events', 'payees'));
    }

    public function create()
    {
        return redirect()->route('backend.services.index')
            ->with('info', 'Use the "Add Service" button to create a new service.');
    }

    /**
     * Store a new service.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id'       => 'required|exists:events,id',
            'payee_id'       => 'required|exists:payees,id',
            'description'    => 'required|string',
            'category'       => 'required|in:equipment,labor,food,transport,venue,marketing,other',
            'quantity'       => 'required|numeric|min:0.01',
            'unit_price'     => 'required|numeric|min:0',
            'status'         => 'required|in:pending,approved,delivered,cancelled',
            'contracted_at'  => 'nullable|date',
            'delivered_at'   => 'nullable|date',
            'notes'          => 'nullable|string',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date'   => 'nullable|date',
            'invoice_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Handle invoice upload
        if ($request->hasFile('invoice_file')) {
            $validated['invoice_path'] = $request->file('invoice_file')
                ->store('invoices', 'public');
        }
        unset($validated['invoice_file']);

        $validated['created_by'] = Auth::id();

        Service::create($validated);

        return redirect()
            ->route('backend.services.index')
            ->with('success', 'Service created successfully.');
    }

    public function show(Service $service)
    {
        return redirect()->route('backend.services.index')
            ->with('info', "Use the table to view {$service->description}.");
    }

    public function edit(Service $service)
    {
        return redirect()->route('backend.services.index')
            ->with('info', "Use the Edit button on the table to edit this service.");
    }

    /**
     * Update a service.
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'event_id'       => 'required|exists:events,id',
            'payee_id'       => 'required|exists:payees,id',
            'description'    => 'required|string',
            'category'       => 'required|in:equipment,labor,food,transport,venue,marketing,other',
            'quantity'       => 'required|numeric|min:0.01',
            'unit_price'     => 'required|numeric|min:0',
            'status'         => 'required|in:pending,approved,delivered,cancelled',
            'contracted_at'  => 'nullable|date',
            'delivered_at'   => 'nullable|date',
            'notes'          => 'nullable|string',
            'invoice_number' => 'nullable|string|max:255',
            'invoice_date'   => 'nullable|date',
            'invoice_file'   => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Handle invoice upload
        if ($request->hasFile('invoice_file')) {
            // Delete old file if it exists
            if ($service->invoice_path && Storage::disk('public')->exists($service->invoice_path)) {
                Storage::disk('public')->delete($service->invoice_path);
            }
            $validated['invoice_path'] = $request->file('invoice_file')
                ->store('invoices', 'public');
        }
        unset($validated['invoice_file']);

        $service->update($validated);

        return redirect()
            ->route('backend.services.index')
            ->with('success', "Service \"{$service->description}\" updated successfully.");
    }

    /**
     * Soft-delete a service.
     */
    public function destroy(Service $service)
    {
        if ($service->payouts()->where('status', 'paid')->exists()) {
            return redirect()
                ->route('backend.services.index')
                ->with('error', 'Cannot delete a service that has paid payouts.');
        }

        $desc = $service->description;
        $service->delete();

        return redirect()
            ->route('backend.services.index')
            ->with('success', "Service \"{$desc}\" deleted successfully.");
    }
}