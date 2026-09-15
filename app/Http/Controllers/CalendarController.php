<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $events = Event::where('user_id', $userId)
            ->orderBy('date', 'asc')
            ->get();

        // Build FullCalendar-ready data
        $calendarEvents = $events->map(function ($event) {
            $ticketIds = Ticket::where('event_id', $event->id)->pluck('id');
            $ticketsSold = Attendee::whereIn('ticket_id', $ticketIds)->count();

            return [
                'id'    => (string) $event->id,
                'uuid'  => $event->uuid,
                'title' => $event->name,
                'start' => $event->date,
                'end'   => $event->end_date ?? $event->date,
                'url'   => route('backend.events.page', $event->id),
                'extendedProps' => [
                    'location'     => $event->location,
                    'status'       => $event->status,
                    'currency'     => $event->currency,
                    'tickets_sold' => $ticketsSold,
                    'description'  => $event->description,
                    'image'        => $event->image ? asset('storage/' . $event->image) : null,
                ],
            ];
        });

        return view('backend.calendar.index', compact('calendarEvents'));
    }
}