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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        $query = Event::where('date', '>=', $today)
                      ->has('tickets')
                      ->with(['eventType', 'merchant']);

        if ($request->filled('type')) {
            $query->where('event_type_id', $request->type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('date', 'ASC')->paginate(12);
        $eventTypes = EventType::all();

        return view('welcome', compact('events', 'eventTypes'));
    }

    public function show($id)
    {
        $event = Event::with(['ticketTypes', 'merchant', 'eventType'])->findOrFail($id);
        return view('event.show', compact('event'));
    }

    public function showTickets(int $id)
    {
        $event = Event::with('ticketTypes')->findOrFail($id);
        return view('event.show', compact('event'));
    }

    public function create()
    {
        $merchants = Merchant::all();
        $eventTypes = EventType::all();
        return view('backend.events.create', compact('merchants', 'eventTypes'));
    }

    public function edit(Request $request, Event $event)
    {
        $merchants = Merchant::all();
        $eventTypes = EventType::all();
        return view('backend.events.edit', compact('event', 'merchants', 'eventTypes'));
    }

    public function userEvents()
    {
        $userId = auth()->id();

        if (!$userId || !User::find($userId)) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $events = Event::where('user_id', $userId)
            ->where(function ($q) {
                $q->where('end_date', '>=', now())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('end_date')->where('date', '>=', now());
                  })
                  ->orWhere(function ($q3) {
                      $q3->whereNull('end_date')->whereNull('date');
                  });
            })
            ->orderBy('date', 'ASC')
            ->paginate(10);

        if ($events->total() === 0) {
            $events = Event::where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->paginate(10);
        }

        $merchants = Merchant::where('user_id', $userId)->get();

        return view('backend.events.show', compact('events', 'merchants'));
    }

    public function pastUserEvents()
    {
        $userId = auth()->id();

        if (!$userId || !User::find($userId)) {
            return redirect()->route('dashboard')->with('error', 'User not found.');
        }

        $events = Event::where('user_id', $userId)
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->orderBy('id', 'DESC')
            ->paginate(10);

        $merchants = Merchant::where('user_id', $userId)->get();

        return view('backend.events.past', compact('events', 'merchants'));
    }

    private function ensureMerchantForCurrentUser(): int
    {
        $user = Auth::user();

        $merchant = Merchant::firstOrCreate(
            ['user_id' => $user->id],
            [
                'name'        => $user->name . "'s Business",
                'phone'       => null,
                'email'       => $user->email,
                'description' => null,
                'location'    => null,
            ]
        );

        return $merchant->id;
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_type_id' => 'required',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'date'          => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'location'      => 'required|string|max:255',
            'currency'      => 'required|string|max:3',
            'status'        => 'required|string',
            'image'         => 'nullable|image|max:5120',
            'banner'        => 'nullable|image|max:5120',
        ]);

        $realMerchantId = $this->ensureMerchantForCurrentUser();

        if (!Storage::disk('public')->exists('images')) {
            Storage::disk('public')->makeDirectory('images');
        }
        if (!Storage::disk('public')->exists('banners')) {
            Storage::disk('public')->makeDirectory('banners');
        }

        $imagePath = null;
        $bannerPath = null;

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            try {
                $imagePath = $request->file('image')->store('images', 'public');
                Log::info('Event image uploaded', ['path' => $imagePath]);
            } catch (\Exception $e) {
                Log::error('Event image upload failed: ' . $e->getMessage());
            }
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            try {
                $bannerPath = $request->file('banner')->store('banners', 'public');
                Log::info('Event banner uploaded', ['path' => $bannerPath]);
            } catch (\Exception $e) {
                Log::error('Event banner upload failed: ' . $e->getMessage());
            }
        }

        $event = Event::create([
            'merchant_id'   => $realMerchantId,
            'user_id'       => Auth::id(),
            'name'          => $request->name,
            'event_type_id' => $request->event_type_id,
            'description'   => $request->description,
            'date'          => $request->date,
            'end_date'      => $request->end_date,
            'location'      => $request->location,
            'currency'      => $request->currency,
            'status'        => $request->status,
            'image'         => $imagePath,
            'banner'        => $bannerPath,
        ]);

        return redirect()->to("/backend/events/$event->id/page")->with([
            'success' => 'Event created successfully.',
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $request->validate([
            'event_type_id' => 'required',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'date'          => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'location'      => 'required|string|max:255',
            'currency'      => 'required|string|max:3',
            'status'        => 'required|string',
            'image'         => 'nullable|image|max:5120',
            'banner'        => 'nullable|image|max:5120',
        ]);

        $realMerchantId = $this->ensureMerchantForCurrentUser();

        $data = [
            'merchant_id'   => $realMerchantId,
            'user_id'       => Auth::id(),
            'name'          => $request->name,
            'event_type_id' => $request->event_type_id,
            'description'   => $request->description,
            'date'          => $request->date,
            'end_date'      => $request->end_date,
            'location'      => $request->location,
            'currency'      => $request->currency,
            'status'        => $request->status,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($event->image && Storage::disk('public')->exists($event->image)) {
                Storage::disk('public')->delete($event->image);
            }
            $data['image'] = $request->file('image')->store('images', 'public');
        }

        if ($request->hasFile('banner') && $request->file('banner')->isValid()) {
            if ($event->banner && Storage::disk('public')->exists($event->banner)) {
                Storage::disk('public')->delete($event->banner);
            }
            $data['banner'] = $request->file('banner')->store('banners', 'public');
        }

        $event->update($data);

        return redirect()
            ->route('backend.events.page', $event->id)
            ->with('success', 'Event updated successfully.');
    }

    public function eventPage($eventId)
    {
        $event = Event::findOrFail($eventId);

        $tickets = Ticket::where('event_id', $eventId)->get();

        $attendees = Attendee::whereIn('ticket_id', $tickets->pluck('id'))->get();

        $ticketsSold = $attendees->count();

        $ticketSales = 0;
        $soldQuantities = [];
        foreach ($tickets as $ticket) {
            $sold = Attendee::where('ticket_id', $ticket->id)->count();
            $soldQuantities[$ticket->id] = $sold;
            $ticketSales += $ticket->price * $sold;
        }

        $netSales = 0.95 * $ticketSales;

        return view('backend.events.page', compact(
            'event',
            'tickets',
            'attendees',
            'ticketsSold',
            'ticketSales',
            'netSales',
            'soldQuantities'
        ));
    }

    public function deleteEvent(Event $event)
    {
        if ($event->image && Storage::disk('public')->exists($event->image)) {
            Storage::disk('public')->delete($event->image);
        }
        if ($event->banner && Storage::disk('public')->exists($event->banner)) {
            Storage::disk('public')->delete($event->banner);
        }

        $event->delete();

        return back()->with('success', 'Event deleted successfully.');
    }
}