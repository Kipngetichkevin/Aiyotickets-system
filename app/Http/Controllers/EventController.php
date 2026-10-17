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
            ->where('status', '!=', 'inactive')
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

        // Inactive (draft) events are only visible to their organiser and admins
        if ($event->status === 'inactive') {
            $user = auth()->user();
            abort_unless($user && ($user->hasRole('admin') || (int) $event->user_id === (int) $user->id), 404);
        }

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
            ? Merchant::where('status', Merchant::APPROVED)->get()
            : Merchant::where('user_id', $user->id)->where('status', Merchant::APPROVED)->get();
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

        // Sales figures count paid tickets only (not deposits, cancelled or overdue ones)
        $paid        = Attendee::whereIn('ticket_id', $tickets->pluck('id'))->where('payment_status', 'paid');
        $ticketsSold = (clone $paid)->sum('qty');
        $ticketSales = (float) (clone $paid)->sum('total_price');
        $netSales    = round($ticketSales * (1 - \App\Services\SalesReport::feeRate()), 2);
        $soldQuantities = (clone $paid)->selectRaw('ticket_id, sum(qty) as sold')->groupBy('ticket_id')->pluck('sold', 'ticket_id')->all();

        // For the edit modal on this page
        $isAdmin    = $user->hasRole('admin');
        $merchants  = Merchant::where('status', Merchant::APPROVED)->when(!$isAdmin, fn ($q) => $q->where('user_id', $user->id))->get();
        $eventTypes = EventType::all();

        return view('events.show', compact(
            'event', 'tickets', 'attendees',
            'ticketsSold', 'ticketSales', 'netSales', 'soldQuantities', 'merchants', 'eventTypes'
        ));
    }

    /* =========================================================
     |  AUTH — WRITE
     |========================================================= */

    /** "Create event" opens the events list with the create form already showing. */
    public function create()
    {
        return redirect()->route('events.index', ['new' => 1]);
    }

    public const CURRENCIES = ['KES', 'USD', 'EUR', 'GBP'];

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('merchant') && !$user->hasRole('admin')) {
            return $this->respond($request, false, 'Only organisers and admins can create events.', status: 403);
        }

        $this->normaliseCurrency($request);
        $request->validate($this->rules());

        $merchant = $this->resolveMerchant($request, $user);
        if (is_string($merchant)) {
            return $this->respond($request, false, $merchant, status: 422);
        }

        try {
            $slug         = Str::slug($request->name) ?: 'event';
            $originalSlug = $slug;
            $count        = 1;
            while (Event::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count++;
            }

            $event = Event::create([
                'uuid'          => (string) Str::uuid(),
                'slug'          => $slug,
                'merchant_id'   => $merchant->id,
                // The event belongs to the organiser (sales, payouts, promo codes), even when an admin creates it
                'user_id'       => $merchant->user_id,
                'event_type_id' => $request->event_type_id,
                'name'          => $request->name,
                'description'   => HtmlSanitizer::clean($request->description),
                'date'          => $request->date,
                'end_date'      => $request->end_date,
                'location'      => $request->location,
                'currency'      => $request->currency,
                'status'        => $request->status,
                'image'         => $request->hasFile('image') ? $request->file('image')->store('images', 'public') : null,
                'banner'        => $request->hasFile('banner') ? $request->file('banner')->store('banners', 'public') : null,
            ]);

            return $this->respond($request, true, 'Event created. Now add ticket types so it can go on sale.',
                ['event' => $event], route('events.show', $event->id));

        } catch (\Throwable $e) {
            return $this->respond($request, false, $this->userError('Failed to create event', $e), status: 500);
        }
    }

    /** Validation shared by create and update. */
    private function rules(): array
    {
        return [
            'event_type_id' => 'required|exists:event_types,id',
            'merchant_id'   => 'nullable|exists:merchants,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'date'          => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:date',
            'location'      => 'required|string|max:255',
            'currency'      => 'required|in:' . implode(',', self::CURRENCIES),
            // Cancelling goes through the Cancel button, which refunds and tells buyers
            'status'        => 'required|string|in:active,inactive,completed',
            'image'         => 'nullable|image|max:2048',
            'banner'        => 'nullable|image|max:2048',
        ];
    }

    /** Older forms sent currency names; store ISO codes. */
    private function normaliseCurrency(Request $request): void
    {
        $map  = ['EUROS' => 'EUR', 'EURO' => 'EUR', 'POUND' => 'GBP', 'POUNDS' => 'GBP', 'USDT' => 'USD'];
        $code = strtoupper(trim((string) $request->input('currency', 'KES')));
        $request->merge(['currency' => $map[$code] ?? $code]);
    }

    /**
     * The approved organiser profile the event goes under: the one chosen, or the user's
     * own when they have exactly one. Returns an error message when it can't be decided.
     */
    private function resolveMerchant(Request $request, User $user, ?Event $current = null): Merchant|string
    {
        $isAdmin = $user->hasRole('admin');
        $mine    = Merchant::where('status', Merchant::APPROVED)->when(!$isAdmin, fn ($q) => $q->where('user_id', $user->id));

        if ($request->filled('merchant_id')) {
            return (clone $mine)->find($request->merchant_id)
                ?? 'You can only create events under your own approved organiser profile.';
        }
        if ($current?->merchant_id) {
            return Merchant::find($current->merchant_id) ?? "This event's organiser no longer exists. Choose one.";
        }

        $options = (clone $mine)->when($isAdmin, fn ($q) => $q->where('user_id', $user->id))->get();

        return $options->count() === 1 ? $options->first() : ($isAdmin
            ? 'Choose which organiser this event is for.'
            : "Your organiser profile isn't approved yet, so you can't create events.");
    }

    /** JSON for the modals, a redirect for plain form posts. */
    private function respond(Request $request, bool $ok, string $message, array $extra = [], ?string $redirect = null, int $status = 200)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => $ok, 'message' => $message, 'redirect' => $redirect] + $extra, $ok ? 200 : $status);
        }

        return $ok
            ? redirect($redirect ?? route('events.index'))->with('success', $message)
            : back()->withInput()->withErrors(['event' => $message]);
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
        $user  = auth()->user();
        $event = Event::findOrFail($id);

        if (!$user->hasRole('admin') && (int) $event->user_id !== (int) $user->id) {
            return $this->respond($request, false, 'Unauthorized action.', status: 403);
        }
        if ($event->cancelled_at) {
            return $this->respond($request, false, 'This event was cancelled and its buyers refunded, so it can no longer be edited.', status: 422);
        }

        $this->normaliseCurrency($request);
        $request->validate($this->rules());

        $merchant = $this->resolveMerchant($request, $user, $event);
        if (is_string($merchant)) {
            return $this->respond($request, false, $merchant, status: 422);
        }

        try {
            $data = [
                'merchant_id'   => $merchant->id,
                'event_type_id' => $request->event_type_id,
                'name'          => $request->name,
                'description'   => HtmlSanitizer::clean($request->description),
                'location'      => $request->location,
                'currency'      => $request->currency,
                'status'        => $request->status,
            ];
            if ($user->hasRole('admin')) {
                $data['user_id'] = $merchant->user_id;   // an admin moving it to another organiser moves its sales too
            }

            foreach (['image' => 'images', 'banner' => 'banners'] as $field => $folder) {
                if ($request->hasFile($field) || $request->input("remove_{$field}") == '1') {
                    if ($old = $event->getRawOriginal($field)) {
                        Storage::disk('public')->delete(str_replace('storage/', '', $old));
                    }
                    $data[$field] = $request->hasFile($field) ? $request->file($field)->store($folder, 'public') : null;
                }
            }

            if ($event->name !== $request->name) {
                $slug         = Str::slug($request->name) ?: 'event';
                $originalSlug = $slug;
                $count        = 1;
                while (Event::where('slug', $slug)->where('id', '!=', $event->id)->exists()) {
                    $slug = $originalSlug . '-' . $count++;
                }
                $data['slug'] = $slug;
            }

            // A new date for an event people have paid for is a postponement: they're told,
            // and may ask for a refund for a while
            $newStart  = \Illuminate\Support\Carbon::parse($request->date);
            $newEnd    = $request->end_date ? \Illuminate\Support\Carbon::parse($request->end_date) : null;
            $moved     = !$event->date || $event->date->format('Y-m-d H:i') !== $newStart->format('Y-m-d H:i');
            $hasBuyers = Attendee::whereHas('ticket', fn ($q) => $q->where('event_id', $event->id))
                ->whereIn('payment_status', ['paid', 'partial'])->exists();

            $event->update($data + ($moved && $hasBuyers ? [] : ['date' => $newStart, 'end_date' => $newEnd]));

            $message = 'Event updated.';
            if ($moved && $hasBuyers) {
                $notified = app(\App\Services\Cancellations::class)->postpone($event, $newStart, $newEnd, null);
                $message  = "Event updated and moved to {$newStart->format('d M Y, H:i')}. {$notified} buyer(s) told; they may ask for a refund for "
                    . \App\Support\RefundPolicy::POSTPONED_DAYS . ' days.';
            }

            $updated = Event::find($event->id);
            $out     = $updated->toArray();
            $out['image_url']  = $updated->image_url;
            $out['banner_url'] = $updated->banner_url;

            return $this->respond($request, true, $message, ['event' => $out], route('events.show', $event->id));

        } catch (\Throwable $e) {
            return $this->respond($request, false, $this->userError('Failed to update event', $e), status: 500);
        }
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $user  = auth()->user();

        if (!$user->hasRole('admin') && (int) $event->user_id !== (int) $user->id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        // Buyers hold (or held) tickets for it: deleting would erase their tickets and the
        // money trail. Includes deposits, cancelled/refunded tickets and payments in progress.
        $ticketIds   = Ticket::where('event_id', $event->id)->pluck('id');
        $soldTickets = Attendee::whereIn('ticket_id', $ticketIds)->whereIn('payment_status', ['paid', 'partial', 'cancelled', 'overdue'])->count();
        $inProgress  = $ticketIds->contains(fn ($id) => app(\App\Services\Inventory::class)->ticketsHeld((int) $id) > 0);

        if ($soldTickets > 0 || $inProgress) {
            $message = $inProgress && !$soldTickets
                ? 'Someone is paying for a ticket right now. Try again in a few minutes.'
                : "This event has {$soldTickets} ticket(s) sold, so it can't be deleted. Set it to inactive, or cancel it to refund buyers.";
            return response()->json(['error' => $message, 'message' => $message], 422);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully.',
        ]);
    }
}