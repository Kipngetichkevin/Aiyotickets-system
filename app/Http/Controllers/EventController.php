<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Merchant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon; 
use Illuminate\Support\Str;

class EventController extends Controller
{
    /**
     * Display a listing of events for the frontend welcome page (PUBLIC).
     */
    public function frontendIndex()
    {
        $today = Carbon::today();
        $events = Event::where('date', '>=', $today)
                       ->has('tickets')
                       ->with('merchant')
                       ->get();
    
        return view('welcome', compact('events'));
    }

    /**
     * Display the specified event for the frontend (PUBLIC - No auth required).
     * Route: event.show
     */
    public function publicShow($id)
    {
        $event = Event::with(['ticketTypes', 'merchant'])->findOrFail($id);
        return view('event.show', compact('event'));
    }

    /**
     * Display the specified event for the backend (AUTH REQUIRED).
     * Route: events.show
     */
    public function eventPage($eventId)
    {
        $event = Event::with('ticketTypes')->findOrFail($eventId);
        
        // Check if user owns this event
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $tickets = Ticket::where('event_id', $eventId)->get();
        
        // Get all attendees without pagination (Alpine will handle pagination client-side)
        $attendees = Attendee::whereIn('ticket_id', $tickets->pluck('id'))
                            ->with(['ticket', 'order'])
                            ->get();

        $ticketIds = $tickets->pluck('id');
        $ticketsSold = Attendee::whereIn('ticket_id', $ticketIds)->sum('qty');
        $ticketSales = Attendee::whereIn('ticket_id', $ticketIds)->sum('total_price');
        $netSales = 0.95 * $ticketSales;

        $soldQuantities = [];
        foreach ($tickets as $ticket) {
            $soldQuantities[$ticket->id] = Attendee::where('ticket_id', $ticket->id)->sum('qty');
        }

        return view('events.show', compact('event', 'tickets', 'attendees', 'ticketsSold', 'ticketSales', 'netSales', 'soldQuantities'));
    }

    /**
     * Show the form for creating a new event (BACKEND).
     */
    public function create()
    {
        $merchants = Merchant::all();
        $eventTypes = EventType::all();
        return view('events.create', compact('merchants', 'eventTypes'));
    }

    /**
     * Store a newly created event in storage (BACKEND).
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'event_type_id' => 'required|exists:event_types,id',
                'merchant_id' => 'required|exists:merchants,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:date',
                'location' => 'required|string|max:255',
                'currency' => 'required|string|max:3',
                'status' => 'required|string|in:active,inactive,cancelled,completed',
                'image' => 'nullable|image|max:2048',
                'banner' => 'nullable|image|max:2048',
            ]);

            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images', 'public');
            }

            $bannerPath = null;
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('banners', 'public');
            }

            // Generate slug
            $slug = Str::slug($request->name);
            $originalSlug = $slug;
            $count = 1;
            while (Event::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $event = Event::create([
                'uuid' => (string) Str::uuid(),
                'slug' => $slug,
                'merchant_id' => $request->merchant_id,
                'user_id' => Auth::id(),
                'event_type_id' => $request->event_type_id,
                'name' => $request->name,
                'description' => $request->description,
                'date' => $request->date,
                'end_date' => $request->end_date,
                'location' => $request->location,
                'currency' => $request->currency,
                'status' => $request->status,
                'image' => $imagePath,
                'banner' => $bannerPath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully!',
                'event' => $event,
                'redirect' => route('events.show', $event->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified event (BACKEND).
     * Route: events.edit
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        
        // Check if user owns this event
        if ($event->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }
        
        $merchants = Merchant::all();
        $eventTypes = EventType::all();
        
        // Return event data with proper image URLs
        $eventData = $event->toArray();
        $eventData['image_url'] = $event->image_url;
        $eventData['banner_url'] = $event->banner_url;
        
        return response()->json($eventData);
    }

    /**
     * Update the specified event in storage (BACKEND).
     * Route: events.update
     */
    public function update(Request $request, $id)
    {
        try {
            $event = Event::findOrFail($id);
            
            // Check if user owns this event
            if ($event->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized action.'
                ], 403);
            }

            $request->validate([
                'event_type_id' => 'required|exists:event_types,id',
                'merchant_id' => 'required|exists:merchants,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'end_date' => 'nullable|date|after_or_equal:date',
                'location' => 'required|string|max:255',
                'currency' => 'required|string|max:3',
                'status' => 'required|string|in:active,inactive,cancelled,completed',
                'image' => 'nullable|image|max:2048',
                'banner' => 'nullable|image|max:2048',
            ]);

            $data = [
                'merchant_id' => $request->merchant_id,
                'user_id' => Auth::id(),
                'event_type_id' => $request->event_type_id,
                'name' => $request->name,
                'description' => $request->description,
                'date' => $request->date,
                'end_date' => $request->end_date,
                'location' => $request->location,
                'currency' => $request->currency,
                'status' => $request->status,
            ];

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($event->image) {
                    $imagePath = str_replace('storage/', '', $event->image);
                    Storage::disk('public')->delete($imagePath);
                }
                $data['image'] = $request->file('image')->store('images', 'public');
            } elseif ($request->input('remove_image') == '1') {
                // Remove image if requested
                if ($event->image) {
                    $imagePath = str_replace('storage/', '', $event->image);
                    Storage::disk('public')->delete($imagePath);
                }
                $data['image'] = null;
            }

            // Handle banner upload
            if ($request->hasFile('banner')) {
                // Delete old banner if exists
                if ($event->banner) {
                    $bannerPath = str_replace('storage/', '', $event->banner);
                    Storage::disk('public')->delete($bannerPath);
                }
                $data['banner'] = $request->file('banner')->store('banners', 'public');
            } elseif ($request->input('remove_banner') == '1') {
                // Remove banner if requested
                if ($event->banner) {
                    $bannerPath = str_replace('storage/', '', $event->banner);
                    Storage::disk('public')->delete($bannerPath);
                }
                $data['banner'] = null;
            }

            // Update slug if name changed
            if ($event->name !== $request->name) {
                $slug = Str::slug($request->name);
                $originalSlug = $slug;
                $count = 1;
                while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $data['slug'] = $slug;
            }

            $event->update($data);

            // Get updated event data
            $updatedEvent = Event::find($event->id);
            $eventData = $updatedEvent->toArray();
            $eventData['image_url'] = $updatedEvent->image_url;
            $eventData['banner_url'] = $updatedEvent->banner_url;

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully!',
                'event' => $eventData,
                'redirect' => route('events.show', $event->id)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified event from storage (BACKEND).
     * Route: events.destroy
     */
    public function destroy(Event $event)
    {
        // Check if user owns this event
        if ($event->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }
        
        $event->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.'
        ]);
    }

    public function userEvents()
    {
        $userId = auth()->id();

        if (!$userId || !User::find($userId)) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $events = Event::where('user_id', $userId)
            ->where('date', '>=', now())
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        $merchants = Merchant::where('user_id', $userId)->get();
        $eventTypes = EventType::all();

        return view('events.index', compact('events', 'merchants', 'eventTypes'));
    }
    
    /**
     * Display the user's past events (BACKEND).
     */
    public function pastUserEvents()
    {
        $userId = auth()->id();
    
        if (!$userId || !User::find($userId)) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }
    
        $events = Event::where('user_id', $userId)
            ->where('end_date', '<', now())
            ->orderBy('created_at', 'DESC')
            ->paginate(10);
    
        $merchants = Merchant::where('user_id', $userId)->get();
    
        return view('events.past', compact('events', 'merchants'));
    }
}