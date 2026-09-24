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
    public function frontendIndex()
    {
        $today = Carbon::today();
        $events = Event::where('date', '>=', $today)
                       ->has('tickets')
                       ->with('merchant')
                       ->get();

        return view('welcome', compact('events'));
    }

    public function publicShow($id)
    {
        $event = Event::with(['ticketTypes', 'merchant'])->findOrFail($id);
        return view('event.show', compact('event'));
    }

    /**
     * Public browse page — no login required.
     */
    public function browse()
    {
        $events = Event::whereDate('end_date', '>=', today())
            ->has('tickets')
            ->with(['ticketTypes', 'merchant'])
            ->orderBy('date', 'ASC')
            ->get();

        return view('browse.index', compact('events'));
    }

    public function eventPage($eventId)
    {
        $event = Event::with('ticketTypes')->findOrFail($eventId);

        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $tickets = Ticket::where('event_id', $eventId)->get();

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

    public function create()
    {
        $merchants = Merchant::all();
        $eventTypes = EventType::all();
        return view('events.create', compact('merchants', 'eventTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type_id' => 'required|exists:event_types,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'date'          => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'location'      => 'required|string|max:255',
            'currency'      => 'required|string|max:10',
            'status'        => 'required|string|in:active,inactive,cancelled,completed',
            'image'         => 'nullable|image|max:2048',
            'banner'        => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public');
        }

        $bannerPath = null;
        if ($request->hasFile('banner')) {
            $bannerPath = $request->file('banner')->store('banners', 'public');
        }

        $slug = Str::slug($request->name);
        $originalSlug = $slug;
        $count = 1;
        while (Event::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $merchant = Merchant::where('user_id', Auth::id())->first();
        if (!$merchant) {
            $merchant = Merchant::create([
                'user_id' => Auth::id(),
                'name'    => Auth::user()->name,
                'email'   => Auth::user()->email,
            ]);
        }

        $event = Event::create([
            'uuid'          => (string) Str::uuid(),
            'slug'          => $slug,
            'merchant_id'   => $merchant->id,
            'user_id'       => Auth::id(),
            'event_type_id' => $request->event_type_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'date'          => $request->date,
            'end_date'      => $request->end_date,
            'location'      => $request->location,
            'currency'      => $request->currency,
            'status'        => $request->status,
            'image'         => $imagePath,
            'banner'        => $bannerPath,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Event created successfully!',
                'event'    => $event,
                'redirect' => route('events.show', $event->id),
            ]);
        }

        return redirect()
            ->route('events.show', $event->id)
            ->with('success', 'Event created successfully!');
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);

        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $merchants = Merchant::all();
        $eventTypes = EventType::all();

        return view('events.edit', compact('event', 'merchants', 'eventTypes'));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'event_type_id' => 'required|exists:event_types,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'date'          => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'location'      => 'required|string|max:255',
            'currency'      => 'required|string|max:10',
            'status'        => 'required|string|in:active,inactive,cancelled,completed',
            'image'         => 'nullable|image|max:2048',
            'banner'        => 'nullable|image|max:2048',
        ]);

        $data = [
            'event_type_id' => $request->event_type_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'date'          => $request->date,
            'end_date'      => $request->end_date,
            'location'      => $request->location,
            'currency'      => $request->currency,
            'status'        => $request->status,
        ];

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = $request->file('image')->store('images', 'public');
        } elseif ($request->input('remove_image') == '1') {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = null;
        }

        if ($request->hasFile('banner')) {
            if ($event->banner) {
                Storage::disk('public')->delete($event->banner);
            }
            $data['banner'] = $request->file('banner')->store('banners', 'public');
        } elseif ($request->input('remove_banner') == '1') {
            if ($event->banner) {
                Storage::disk('public')->delete($event->banner);
            }
            $data['banner'] = null;
        }

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

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Event updated successfully!',
                'event'    => $event->fresh(),
                'redirect' => route('events.show', $event->id),
            ]);
        }

        return redirect()
            ->route('events.show', $event->id)
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        if ($event->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Current + upcoming events only.
     * "Current" = end_date is today or later.
     */
    public function userEvents()
    {
        $userId = auth()->id();

        if (!$userId || !User::find($userId)) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $events = Event::where('user_id', $userId)
            ->whereDate('end_date', '>=', today())
            ->orderBy('date', 'ASC')
            ->paginate(10);

        $merchants = Merchant::where('user_id', $userId)->get();
        $eventTypes = EventType::all();

        return view('events.index', compact('events', 'merchants', 'eventTypes'));
    }

    /**
     * Past events only.
     */
    public function pastUserEvents()
    {
        $userId = auth()->id();

        if (!$userId || !User::find($userId)) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $events = Event::where('user_id', $userId)
            ->whereDate('end_date', '<', today())
            ->orderBy('date', 'DESC')
            ->paginate(10);

        $merchants = Merchant::where('user_id', $userId)->get();

        return view('events.past', compact('events', 'merchants'));
    }
}