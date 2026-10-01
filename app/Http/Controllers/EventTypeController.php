<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class EventTypeController extends Controller
{
    public function index()
    {
        $eventTypes = EventType::query()
            ->withCount('events')
            ->ordered()
            ->paginate(20);

        $stats = [
            'total'          => EventType::count(),
            'active'         => EventType::where('is_active', true)->count(),
            'total_events'   => EventType::withCount('events')->get()->sum('events_count'),
            'upcoming_count' => EventType::query()
                ->withCount(['events as upcoming_count' => fn ($q) => $q->whereDate('end_date', '>=', today())])
                ->get()
                ->sum('upcoming_count'),
        ];

        return view('event_types.index', compact('eventTypes', 'stats'));
    }

    public function create()
    {
        return view('event_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:event_types,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'color'       => ['nullable', 'string', 'max:20'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        $validated['slug']      = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $eventType = EventType::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Event Type created successfully.',
                'event_type' => $eventType,
            ]);
        }

        return redirect()
            ->route('event_types.index')
            ->with('success', 'Event Type created successfully.');
    }

    public function show(EventType $eventType)
    {
        $eventType->loadCount('events');

        $upcomingEvents = $eventType->events()
            ->whereDate('end_date', '>=', today())
            ->with(['merchant:id,name', 'tickets:id,event_id,price'])
            ->orderBy('date', 'ASC')
            ->paginate(12);

        return view('event_types.show', compact('eventType', 'upcomingEvents'));
    }

    public function edit(EventType $eventType)
    {
        return view('event_types.edit', compact('eventType'));
    }

    public function update(Request $request, EventType $eventType)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255',
                              Rule::unique('event_types', 'name')->ignore($eventType->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon'        => ['nullable', 'string', 'max:255'],
            'color'       => ['nullable', 'string', 'max:20'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        if ($eventType->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $eventType->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => 'Event Type updated successfully.',
                'event_type' => $eventType->fresh(),
            ]);
        }

        return redirect()
            ->route('event_types.index')
            ->with('success', 'Event Type updated successfully.');
    }

    public function destroy(EventType $eventType)
    {
        // Guard: don't let admins nuke a category with events attached.
        $count = $eventType->events()->count();

        if ($count > 0) {
            $message = "Cannot delete \"{$eventType->name}\": {$count} event(s) are still attached. "
                     . 'Reassign or delete those events first.';

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        }

        $eventType->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Event Type deleted successfully.',
            ]);
        }

        return redirect()
            ->route('event_types.index')
            ->with('success', 'Event Type deleted successfully.');
    }
}