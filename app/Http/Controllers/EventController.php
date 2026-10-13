<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Experience;
use App\Models\Merchant;
use App\Models\Ticket;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class EventController extends Controller
{
    /* =========================================================
     |  PUBLIC
     |========================================================= */

    public function frontendIndex()
    {
        $today = Carbon::today();

        // Upcoming and running events: starts today or later, or hasn't ended yet
        // (multi-day events stay listed until their end date). Only active ones.
        $upcoming = Event::where(function ($q) use ($today) {
                $q->where('date', '>=', $today)
                  ->orWhere('end_date', '>=', now());
            })
            ->where('status', 'active')
            ->has('tickets')
            ->with('merchant')
            ->withMin('tickets', 'price')
            ->orderBy('date', 'asc')
            ->get();

        // Past / finished events (end date before today, OR marked completed/cancelled)
        $past = Event::where(function ($q) use ($today) {
                $q->where('end_date', '<', $today)
                  ->orWhere(function ($q2) use ($today) {
                      $q2->whereNull('end_date')->where('date', '<', $today);
                  })
                  ->orWhereIn('status', ['completed', 'cancelled']);
            })
            ->has('tickets')
            ->with('merchant')
            ->withMin('tickets', 'price')
            ->orderBy('date', 'desc')
            ->get();

        // Public active experiences (for the home page section)
        $experiences = Experience::with(['packages', 'categories'])
            ->where('status', 'active')
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        // Hero slides: soonest upcoming events, then featured experiences
        $minPrices = \App\Models\Ticket::whereIn('event_id', $upcoming->pluck('id'))
            ->selectRaw('event_id, min(price) as min_price')->groupBy('event_id')->pluck('min_price', 'event_id');

        $slides = $upcoming->take(4)->map(fn ($e) => [
            'kind'  => 'Event',
            'title' => $e->name,
            'meta'  => collect([optional($e->date)->format('D, d M Y · H:i'), $e->location])->filter()->implode(' · '),
            'price' => $minPrices[$e->id] ?? null,
            'image' => $e->image_url,
            'url'   => route('event.show', $e->id),
            'cta'   => 'Get tickets',
        ])->concat($experiences->where('featured', true)->take(2)->map(fn ($x) => [
            'kind'  => 'Experience',
            'title' => $x->name,
            'meta'  => collect([optional($x->start_date)->format('d M Y'), $x->location])->filter()->implode(' · '),
            'price' => $x->packages->min('price'),
            'image' => $x->image ? asset('storage/' . ltrim($x->image, '/')) : null,
            'url'   => route('experience.show', $x->slug),
            'cta'   => 'Book now',
        ]))->values();

        return view('welcome', compact('upcoming', 'past', 'experiences', 'slides'));
    }

    public function publicShow($id)
    {
        $event = Event::with(['ticketTypes', 'merchant'])->findOrFail($id);
        return view('event.show', compact('event'));
    }

    /* =========================================================
     |  AUTH — READ
     |========================================================= */

    public function userEvents()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('dashboard')->with('error', 'Please log in.');
        }

        if (!$user->hasRole('admin') && !$user->hasRole('merchant')) {
            return redirect()->route('events.browse');
        }

        $isAdmin = $user->hasRole('admin');

        $query = Event::query()->orderByDesc('created_at');

        if (!$isAdmin) {
            $query->where('user_id', $user->id);
        }

        $events     = $query->paginate(10);
        $merchants  = $isAdmin
            ? Merchant::all()
            : Merchant::where('user_id', $user->id)->get();
        $eventTypes = EventType::all();

        return view('events.index', compact('events', 'merchants', 'eventTypes'));
    }

    public function eventPage($eventId)
    {
        $event = Event::with('ticketTypes')->findOrFail($eventId);
        $user  = auth()->user();

        if (!$user->hasRole('admin') && (int) $event->user_id !== (int) $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $tickets = Ticket::where('event_id', $eventId)->get();

        $attendees = Attendee::whereIn('ticket_id', $tickets->pluck('id'))
                            ->with(['ticket', 'order'])
                            ->get();

        $ticketIds   = $tickets->pluck('id');
        $ticketsSold = Attendee::whereIn('ticket_id', $ticketIds)->sum('qty');
        $ticketSales = Attendee::whereIn('ticket_id', $ticketIds)->sum('total_price');
        $netSales    = 0.95 * $ticketSales;

        $soldQuantities = [];
        foreach ($tickets as $ticket) {
            $soldQuantities[$ticket->id] = Attendee::where('ticket_id', $ticket->id)->sum('qty');
        }

        return view('events.show', compact(
            'event', 'tickets', 'attendees',
            'ticketsSold', 'ticketSales', 'netSales', 'soldQuantities'
        ));
    }

    /* =========================================================
     |  AUTH — WRITE
     |========================================================= */

    public function create()
    {
        $user = auth()->user();

        if (!$user->hasRole('merchant') && !$user->hasRole('admin')) {
            abort(403, 'Only merchants and admins can create events.');
        }

        $isAdmin = $user->hasRole('admin');

        $merchants = $isAdmin
            ? Merchant::all()
            : Merchant::where('user_id', $user->id)->get();

        $eventTypes = EventType::all();

        return view('events.create', compact('merchants', 'eventTypes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('merchant') && !$user->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Only merchants and admins can create events.',
            ], 403);
        }

        $request->validate([
            'event_type_id' => 'required|exists:event_types,id',
            'merchant_id'   => 'required|exists:merchants,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'date'          => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'location'      => 'required|string|max:255',
            'currency'      => 'required|string|max:3',
            'status'        => 'required|string|in:active,inactive,cancelled,completed',
            'image'         => 'nullable|image|max:2048',
            'banner'        => 'nullable|image|max:2048',
        ]);

        if (!$user->hasRole('admin')) {
            $ownsMerchant = Merchant::where('id', $request->merchant_id)
                                    ->where('user_id', $user->id)
                                    ->exists();
            if (!$ownsMerchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only create events under your own merchant account.',
                ], 403);
            }
        }

        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('images', 'public');
            }

            $bannerPath = null;
            if ($request->hasFile('banner')) {
                $bannerPath = $request->file('banner')->store('banners', 'public');
            }

            $slug         = Str::slug($request->name);
            $originalSlug = $slug;
            $count        = 1;
            while (Event::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $event = Event::create([
                'uuid'          => (string) Str::uuid(),
                'slug'          => $slug,
                'merchant_id'   => $request->merchant_id,
                'user_id'       => $user->id,
                'event_type_id' => $request->event_type_id,
                'name'          => $request->name,
                'description'   => HtmlSanitizer::clean($request->description),
                'date'          => $request->date,
                'end_date'      => $request->end_date,
                'location'      => $request->location,
                'currency'      => $request->currency,
                'status'        => $request->status,
                'image'         => $imagePath,
                'banner'        => $bannerPath,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Event created successfully!',
                'event'    => $event,
                'redirect' => route('events.show', $event->id),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $this->userError('Failed to create event', $e),
            ], 500);
        }
    }

    /* =========================================================
     |  EDIT / UPDATE / DELETE
     |========================================================= */

    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $user  = auth()->user();

        if (!$user->hasRole('admin') && (int) $event->user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $eventData               = $event->toArray();
        $eventData['image_url']  = $event->image_url;
        $eventData['banner_url'] = $event->banner_url;

        return response()->json($eventData);
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();

        $event = Event::findOrFail($id);

        if (!$user->hasRole('admin') && (int) $event->user_id !== (int) $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.',
            ], 403);
        }

        $request->validate([
            'event_type_id' => 'required|exists:event_types,id',
            'merchant_id'   => 'required|exists:merchants,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'date'          => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'location'      => 'required|string|max:255',
            'currency'      => 'required|string|max:3',
            'status'        => 'required|string|in:active,inactive,cancelled,completed',
            'image'         => 'nullable|image|max:2048',
            'banner'        => 'nullable|image|max:2048',
        ]);

        if (!$user->hasRole('admin')) {
            $ownsMerchant = Merchant::where('id', $request->merchant_id)
                                    ->where('user_id', $user->id)
                                    ->exists();
            if (!$ownsMerchant) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only assign events to your own merchant account.',
                ], 403);
            }
        }

        try {
            $data = [
                'merchant_id'   => $request->merchant_id,
                'event_type_id' => $request->event_type_id,
                'name'          => $request->name,
                'description'   => HtmlSanitizer::clean($request->description),
                'date'          => $request->date,
                'end_date'      => $request->end_date,
                'location'      => $request->location,
                'currency'      => $request->currency,
                'status'        => $request->status,
            ];

            if ($request->hasFile('image')) {
                if ($event->image) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $event->image));
                }
                $data['image'] = $request->file('image')->store('images', 'public');
            } elseif ($request->input('remove_image') == '1') {
                if ($event->image) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $event->image));
                }
                $data['image'] = null;
            }

            if ($request->hasFile('banner')) {
                if ($event->banner) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $event->banner));
                }
                $data['banner'] = $request->file('banner')->store('banners', 'public');
            } elseif ($request->input('remove_banner') == '1') {
                if ($event->banner) {
                    Storage::disk('public')->delete(str_replace('storage/', '', $event->banner));
                }
                $data['banner'] = null;
            }

            if ($event->name !== $request->name) {
                $slug         = Str::slug($request->name);
                $originalSlug = $slug;
                $count        = 1;
                while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $data['slug'] = $slug;
            }

            $event->update($data);

            $updated = Event::find($event->id);
            $out     = $updated->toArray();
            $out['image_url']  = $updated->image_url;
            $out['banner_url'] = $updated->banner_url;

            return response()->json([
                'success'  => true,
                'message'  => 'Event updated successfully!',
                'event'    => $out,
                'redirect' => route('events.show', $event->id),
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $this->userError('Failed to update event', $e),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $user  = auth()->user();

        if (!$user->hasRole('admin') && (int) $event->user_id !== (int) $user->id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        // Buyers hold tickets for it — deleting would orphan their orders and QR codes.
        $soldTickets = Attendee::where('payment_status', 'paid')
            ->whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
            ->count();

        if ($soldTickets > 0) {
            return response()->json([
                'error' => "This event has {$soldTickets} paid ticket(s) and can't be deleted. Set it to inactive instead.",
            ], 422);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.',
        ]);
    }
}