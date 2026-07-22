<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;

class EventTypeController extends Controller
{
    /**
     * Display a listing of the event types.
     */
    public function index()
    {
        $eventTypes = EventType::all();
        return view('event_types.index', compact('eventTypes'));
    }

    /**
     * Show the form for creating a new event type.
     */
    public function create()
    {
        return view('event_types.create');
    }

    /**
     * Store a newly created event type in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        EventType::create($request->all());

        return redirect()->route('event_types.index')->with('success', 'Event Type created successfully.');
    }

    /**
     * Show the form for editing the specified event type.
     */
    public function edit(EventType $eventType)
    {
        return view('event_types.edit', compact('eventType'));
    }

    /**
     * Update the specified event type in storage.
     */
    public function update(Request $request, EventType $eventType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
        ]);

        $eventType->update($request->all());

        return redirect()->route('event_types.index')->with('success', 'Event Type updated successfully.');
    }

    /**
     * Remove the specified event type from storage.
     */
    public function destroy(EventType $eventType)
    {
        $eventType->delete();

        return redirect()->route('event_types.index')->with('success', 'Event Type deleted successfully.');
    }
}
