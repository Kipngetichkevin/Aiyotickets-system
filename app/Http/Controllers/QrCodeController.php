<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Event;
use App\Models\Ticket;  // Import the Ticket model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // For authenticated user

class QrCodeController extends Controller
{
    // Show all current events for the authenticated user
    public function index()
    {
        // Fetch all current events for the user (e.g., events that are not finished or past)
        $events = Event::where('user_id', Auth::id()) // Only fetch events for the authenticated user
                       ->where('date', '>=', now()) // Event date should be in the past or present
                       ->where('end_date', '>=', now()) // Event end date should be in the future or present
                       ->get();

        return view('backend.qrcode.index', compact('events'));
    }    

    public function showQrCodePage($event_id)
    {
        // Fetch the specific event for the user by event_id
        $event = Event::where('user_id', Auth::id()) // Only fetch events for the authenticated user
                      ->where('id', $event_id) // Match the specific event ID
                      ->where('date', '>=', now()) // Event date should be in the past or present
                      ->where('end_date', '>=', now()) // Event end date should be in the future or present
                      ->first(); // Get the first matching event
    
        // Check if the event exists
        if (!$event) {
            // Handle the case where the event is not found (e.g., redirect or show an error)
            return redirect()->back()->with('error', 'Event not found or not accessible.');
        }
    
        return view('backend.qrcode.show', compact('event'));
    }

    // Handle scanning the QR code and checking in attendees
    public function scanQRCode(Request $request, $eventId)
    {
        // Ensure the event_id matches the authenticated user's event_id
        $userId = auth()->user()->id;
    
        // Fetch the event using the authenticated user's user_id
        $event = Event::where('user_id', $userId)
                      ->where('id', $eventId)
                      ->first();
    
        if (!$event) {
            return response()->json([
                'success' => false,
                'message' => 'Event ID does not match the authenticated user.',
            ]);
        }
    
        // Get the ticket_id from the request
        $ticketId = $request->input('ticket_id');
    
        if (!$ticketId) {
            return response()->json([
                'success' => false,
                'message' => 'No valid ticket ID provided.',
            ]);
        }
    
        // Query ticket by ticket_id
        $ticket = Ticket::where('event_id', $eventId)
                        ->where('id', $ticketId)
                        ->with(['user', 'event'])  // Eager load related data (user, event)
                        ->first();
    
        // Check if ticket exists for the provided event
        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found or does not belong to the specified event.',
            ]);
        }
    
        // Check if the ticket is already checked in
        if ($ticket->checked_in) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket has already been checked in.',
            ]);
        }
    
        // If not checked in, proceed with marking the ticket as checked-in
        $ticket->checked_in = true;
        $ticket->save();
    
        // Return success message and attendee data
        return response()->json([
            'success' => true,
            'message' => 'Ticket successfully checked in.',
            'attendee' => $ticket,  // Include ticket details (user and event data)
        ]);
    }
    

}
