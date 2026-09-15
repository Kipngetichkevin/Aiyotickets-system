<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\Attendee;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TicketController extends Controller
{
    /**
     * List all tickets for an event.
     */
    public function index($event_id)
    {
        $event = Event::findOrFail($event_id);
        $tickets = Ticket::where('event_id', $event_id)->get();
        return view('backend.tickets.index', compact('tickets', 'event', 'event_id'));
    }

    /**
     * Show the form to create a new ticket.
     */
    public function create($event_id)
    {
        $event = Event::findOrFail($event_id);
        return view('backend.tickets.create', compact('event'));
    }

    /**
     * Store a new ticket.
     */
    public function store(Request $request, $event_id)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric',
            'currency'   => 'required|in:KES,USD,USDT,EUROS,POUND',
            'quantity'   => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $event = Event::findOrFail($event_id);

        // Ensure merchant exists for the current user (safety net)
        $merchant = Merchant::firstOrCreate(
            ['user_id' => Auth::id()],
            [
                'name'  => Auth::user()->name . "'s Business",
                'email' => Auth::user()->email,
            ]
        );

        // Use the event's merchant if set, otherwise fall back to the current user's merchant
        $merchantId = $event->merchant_id ?: $merchant->id;

        Ticket::create([
            'event_id'    => $event_id,
            'merchant_id' => $merchantId,
            'name'        => $request->name,
            'currency'    => $request->currency,
            'price'       => $request->price,
            'quantity'    => $request->quantity,
            'start_date'  => $request->start_date,
            'end_date'    => $request->end_date,
        ]);

        return redirect()
            ->to('/backend/events/' . $event_id . '/page')
            ->with('success', 'Ticket created successfully!');
    }

    /**
     * Show the form to edit a ticket.
     */
    public function edit($event_id, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $event = Event::findOrFail($event_id);
        return view('backend.tickets.edit', compact('ticket', 'event', 'event_id'));
    }

    /**
     * Update an existing ticket.
     */
    public function update(Request $request, $event_id, $id)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'price'      => 'required|numeric',
            'currency'   => 'required|in:KES,USD,USDT,EUROS,POUND',
            'quantity'   => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
        ]);

        $ticket = Ticket::findOrFail($id);

        $ticket->update([
            'name'       => $request->name,
            'price'      => $request->price,
            'currency'   => $request->currency,
            'quantity'   => $request->quantity,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
        ]);

        return redirect()
            ->to('/backend/events/' . $event_id . '/page')
            ->with('success', 'Ticket updated successfully!');
    }

    /**
     * Delete a ticket.
     */
    public function destroy($event_id, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $ticket->delete();

        return redirect()
            ->to('/backend/events/' . $event_id . '/page')
            ->with('success', 'Ticket deleted successfully.');
    }

    /**
     * Generate a printable PDF ticket for an attendee.
     * Route provides {event_id}, {ticket_id}, {attendee_id}.
     */
    public function generateTicketPDF($event_id, $ticket_id = null, $attendee_id = null)
    {
        // Handle both route formats:
        // /events/{event_id}/tickets/{ticket_id}/attendees/{attendee_id}/generate-pdf
        // /ticket/pdf/{attendeeId}

        if ($attendee_id === null) {
            // Called from the short route: $event_id is actually the attendee ID
            $attendee = Attendee::with(['ticket.event'])->findOrFail($event_id);
        } else {
            $attendee = Attendee::with(['ticket.event'])->findOrFail($attendee_id);
        }

        if (!$attendee->ticket || !$attendee->ticket->event) {
            abort(404, 'Ticket or event not found for this attendee.');
        }

        // Unique values
        $eventId          = $attendee->ticket->event->id;
        $ticketId         = $attendee->ticket->id;
        $attendeeId       = $attendee->id;
        $ticketNumber     = 'TIC-' . $eventId . '-' . $ticketId . '-' . $attendeeId . '-' . time();
        $ticketValue      = $attendee->ticket->price;
        $paymentReference = $attendee->transaction_code ?? 'N/A';

        // ============ BUILD THE PDF ============
        $pdf = new \FPDF('P', 'mm', [110, 220]);
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);

        // Logo (optional — silently skip if not found)
        $logoPath = public_path('images/logo.svg');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 20, -27.5, 70);
        }

        $pdf->SetFont('Arial', 'B', 20);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->SetY(50);
        $pdf->Cell(0, -65, 'Event Ticket', 0, 1, 'C');

        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, 25, 100, 25);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetY(25);
        $pdf->Cell(0, 10, $attendee->ticket->event->name, 0, 1, 'C');

        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, 45, 100, 45);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, 'Venue: ' . $attendee->ticket->event->location, 0, 1, 'C');
        $pdf->Cell(0, 8, '', 0, 1, 'C');

        // Dates
        $eventDate    = \Carbon\Carbon::parse($attendee->ticket->event->date);
        $eventEndDate = $attendee->ticket->event->end_date
            ? \Carbon\Carbon::parse($attendee->ticket->event->end_date)
            : $eventDate;

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(50, 8, $eventDate->format('D, d M Y'), 0, 0, 'L');
        $pdf->Cell(0, 8, $eventEndDate->format('D, d M Y'), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(50, 8, $eventDate->format('h:i A'), 0, 0, 'L');
        $pdf->Cell(0, 8, $eventEndDate->format('h:i A'), 0, 1, 'R');

        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, 72.5, 100, 72.5);

        // Attendee details
        $pdf->Ln(8);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Attendee Information', 0, 1);

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, $attendee->name, 0, 1, 'L');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 8, $attendee->ticket->name . ' TICKET', 0, 1, 'L');
        $pdf->Cell(0, 8, 'KES ' . number_format($attendee->ticket->price, 2), 0, 1, 'L');

        // ============ QR CODE ============
        $qrCodeData = "Ticket name: " . $attendee->ticket->name . "\n" .
                      "Ticket number: " . $ticketNumber . "\n" .
                      "Ticket value: " . number_format($ticketValue, 2) . "\n" .
                      "Payment Reference: " . $paymentReference;

        try {
            $qrCode = new QrCode($qrCodeData);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Ensure the qr_codes directory exists
            $qrDir = storage_path('app/public/qr_codes');
            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0755, true);
            }
            $qrCodePath = $qrDir . '/ticket_' . $ticketNumber . '.png';
            file_put_contents($qrCodePath, $result->getString());

            $pdf->Image($qrCodePath, 70, $pdf->GetY() - 25, 30, 30);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('QR code generation failed: ' . $e->getMessage());
        }

        // Warning
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(255, 0, 0);
        $pdf->Cell(0, 8, 'Do not share this ticket with anyone!', 0, 1, 'C');

        // Terms
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->MultiCell(
            0,
            8,
            "This ticket is valid for the " . $attendee->ticket->event->name . " on " . $eventDate->format('D, d M Y') . ". By purchasing this ticket, you agree to the terms and conditions.",
            0,
            'J'
        );

        // Purple Footer
        $pdf->SetFillColor(128, 0, 128);
        $pdf->Rect(0, 190, 110, 30, 'F');

        $pdf->SetY(189.5);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(0, 10, 'www.aiyotickets.com', 0, 1, 'C');

        // Output
        $pdf->Output('D', 'ticket-' . Str::slug($attendee->name) . '.pdf');
        exit;
    }
}