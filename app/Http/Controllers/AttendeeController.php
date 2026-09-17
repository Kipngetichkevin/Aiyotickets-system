<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendeeController extends Controller
{
    /**
     * Display all attendees across the user's events.
     * Admins see everyone; merchants see only attendees for their own events.
     */
    public function allAttendees(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $query = Attendee::with(['ticket.event', 'order'])
            ->orderByDesc('created_at');

        // Scope to the user's own events if not admin
        if (!$isAdmin) {
            $eventIds = Event::where('user_id', $user->id)->pluck('id');
            $ticketIds = Ticket::whereIn('event_id', $eventIds)->pluck('id');
            $query->whereIn('ticket_id', $ticketIds);
        }

        // Filter: event
        if ($request->filled('event_id')) {
            $ticketIds = Ticket::where('event_id', $request->event_id)->pluck('id');
            $query->whereIn('ticket_id', $ticketIds);
        }

        // Filter: payment status
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Search: name / email / phone / qr_code / transaction_code
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('qr_code', 'like', "%{$s}%")
                  ->orWhere('transaction_code', 'like', "%{$s}%");
            });
        }

        $attendees = $query->paginate(20)->withQueryString();

        // Stats — scoped the same way as the list
        $statsQuery = Attendee::query();
        if (!$isAdmin) {
            $eventIds = $eventIds ?? Event::where('user_id', $user->id)->pluck('id');
            $ticketIds = Ticket::whereIn('event_id', $eventIds)->pluck('id');
            $statsQuery->whereIn('ticket_id', $ticketIds);
        }

        $stats = [
            'total'   => (clone $statsQuery)->count(),
            'paid'    => (clone $statsQuery)->where('payment_status', 'paid')->count(),
            'pending' => (clone $statsQuery)->where('payment_status', 'pending')->count(),
            'revenue' => (clone $statsQuery)->where('payment_status', 'paid')->sum('total_price'),
        ];

        // Events for filter dropdown
        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('attendees.all', compact('attendees', 'events', 'isAdmin', 'stats'));
    }

    public function checkout(Request $request)
    {
        $cart = session('cart', []);

        $validated = $request->validate([
            'name' => 'required|string',
            'gender' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'payment_mode' => 'required|string',
            'transaction_code' => 'required|string',
        ]);

        $attendees = [];

        foreach ($cart as $item) {
            $qrCode = QrCode::generate($item['ticket_id']);

            $attendees[] = Attendee::create([
                'ticket_id' => $item['ticket_id'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'qr_code' => $qrCode,
                'payment_mode' => $validated['payment_mode'],
                'transaction_code' => $validated['transaction_code'],
                'qty' => $item['quantity'],
                'total_price' => $item['quantity'] * $item['price'],
            ]);
        }

        session()->forget('cart');

        return redirect()->route('checkout.confirmation')->with('attendees', $attendees);
    }

    public function index($ticketId)
    {
        $attendees = Attendee::where('ticket_id', $ticketId)->get();
        return view('attendees.index', compact('attendees', 'ticketId'));
    }

    public function create($ticketId)
    {
        return view('attendees.create', compact('ticketId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'name' => 'required|string',
            'gender' => 'required|string',
            'transaction_code' => 'nullable|string',
            'payment_mode' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'qty' => 'required|integer|min:1',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $totalPrice = $ticket->price * $request->qty;

        $qrCode = $this->generateQRCode($ticket->id, $request->name);

        Attendee::create([
            'ticket_id' => $request->ticket_id,
            'name' => $request->name,
            'gender' => $request->gender,
            'transaction_code' => $request->transaction_code,
            'payment_mode' => $request->payment_mode,
            'phone' => $request->phone,
            'email' => $request->email,
            'qty' => $request->qty,
            'total_price' => $totalPrice,
            'qr_code' => $qrCode,
        ]);

        return redirect()->route('attendees.index', $request->ticket_id)->with('success', 'Attendee added successfully.');
    }

    public function edit($id)
    {
        $attendee = Attendee::findOrFail($id);
        return view('attendees.edit', compact('attendee'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string',
            'gender' => 'required|string',
            'transaction_code' => 'nullable|string',
            'payment_mode' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|email',
            'qty' => 'required|integer|min:1',
        ]);

        $attendee = Attendee::findOrFail($id);

        $ticket = Ticket::findOrFail($attendee->ticket_id);
        $totalPrice = $ticket->price * $request->qty;

        $attendee->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'transaction_code' => $request->transaction_code,
            'payment_mode' => $request->payment_mode,
            'phone' => $request->phone,
            'email' => $request->email,
            'qty' => $request->qty,
            'total_price' => $totalPrice,
        ]);

        return redirect()->route('attendees.index', $attendee->ticket_id)->with('success', 'Attendee updated successfully.');
    }

    public function destroy($id)
    {
        $attendee = Attendee::findOrFail($id);
        $attendee->delete();

        return redirect()->route('attendees.index', $attendee->ticket_id)->with('success', 'Attendee deleted successfully.');
    }

    /**
     * Generate a unique QR code string for an attendee.
     */
    private function generateQRCode($ticketId, $name)
    {
        return 'QR-' . strtoupper(substr(md5($ticketId . $name . microtime()), 0, 12));
    }
}