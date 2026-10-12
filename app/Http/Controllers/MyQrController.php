<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\ExperienceBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyQrController extends Controller
{
    public function index()
    {
        return view('user.my-qrs');
    }

    public function json(Request $request)
    {
        $user   = Auth::user();
        $filter = $request->input('filter', 'all');

        $tickets     = [];
        $experiences = [];

        if ($filter === 'all' || $filter === 'tickets') {
            $tickets = Attendee::with(['ticket.event'])
                ->where('user_id', $user->id)
                ->whereIn('payment_status', ['paid', 'active'])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($a) {
                    return [
                        'type'        => 'ticket',
                        'id'          => $a->id,
                        'qr_code'     => $a->qr_code,
                        'name'        => $a->name,
                        'title'       => $a->ticket?->event?->name ?? 'Event',
                        'subtitle'    => $a->ticket?->name ?? 'Ticket',
                        'location'    => $a->ticket?->event?->location,
                        'date'        => optional($a->ticket?->event?->date)->format('d M Y'),
                        'checked_in'  => (bool) $a->checked_in,
                        'pdf_url'     => route('ticket.pdf', $a->id),
                    ];
                })
                ->values()
                ->all();
        }

        if ($filter === 'all' || $filter === 'experiences') {
            $experiences = ExperienceBooking::with(['experience', 'package'])
                ->where('user_id', $user->id)
                ->where('payment_status', 'paid')
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($b) {
                    return [
                        'type'        => 'experience',
                        'id'          => $b->id,
                        'qr_code'     => $b->qr_code,
                        'name'        => $b->name,
                        'title'       => $b->experience?->name ?? 'Experience',
                        'subtitle'    => $b->package?->name ?? 'Package',
                        'location'    => $b->experience?->location,
                        'date'        => optional($b->experience?->start_date)->format('d M Y'),
                        'checked_in'  => (bool) $b->checked_in,
                        'pdf_url'     => route('experience.booking.pdf', $b),
                    ];
                })
                ->values()
                ->all();
        }

        return response()->json([
            'success'     => true,
            'tickets'     => $tickets,
            'experiences' => $experiences,
        ]);
    }
}