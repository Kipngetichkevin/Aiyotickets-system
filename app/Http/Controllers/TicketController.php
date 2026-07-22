<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Attendee;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade as PDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Str;
use FPDF;

class TicketController extends Controller
{
    public function index($event_id)
    {
        $event = Event::find($event_id);
        $tickets = Ticket::where('event_id', $event_id)->get();
        return view('tickets.index', compact('tickets', 'event', 'event_id'));
    }

    public function create($event_id)
    {
        $event = Event::find($event_id);
        return view('tickets.create', compact('event'));
    }

    public function store(Request $request)
    {
        try {
            Log::info('Ticket store attempt', ['request' => $request->all()]);
            
            // Validate only fields that exist in your table
            $request->validate([
                'event_id' => 'required|exists:events,id',
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'currency' => 'nullable|in:USD,USDT,EUROS,POUND,KES',
            ]);

            // Get or create merchant for the user
            $merchant = Merchant::firstOrCreate(
                ['user_id' => auth()->id()],
                [
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                    'phone' => auth()->user()->phone ?? null,
                    'status' => 'active'
                ]
            );

            $ticket = Ticket::create([
                'event_id' => $request->event_id,
                'merchant_id' => $merchant->id,
                'name' => $request->name,
                'currency' => $request->currency ?? 'KES',
                'price' => $request->price,
                'quantity' => $request->quantity,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            Log::info('Ticket created successfully', ['ticket_id' => $ticket->id]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket created successfully!',
                'ticket' => $ticket
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ticket validation error', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ticket store error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit($event_id, $id)
    {
        try {
            $ticket = Ticket::where('event_id', $event_id)->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'ticket' => [
                    'id' => $ticket->id,
                    'event_id' => $ticket->event_id,
                    'name' => $ticket->name,
                    'price' => $ticket->price,
                    'quantity' => $ticket->quantity,
                    'currency' => $ticket->currency,
                    'start_date' => $ticket->start_date,
                    'end_date' => $ticket->end_date,
                    'ticket_no' => $ticket->ticket_no,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Ticket edit error', [
                'event_id' => $event_id,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load ticket data'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('Ticket update attempt', [
                'id' => $id, 
                'request' => $request->all(),
                'files' => $request->files->all()
            ]);
            
            // Be more lenient with validation - allow null values
            $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date',
                'currency' => 'nullable|in:USD,USDT,EUROS,POUND,KES',
            ]);

            $ticket = Ticket::findOrFail($id);
            
            // Get the current values
            $updateData = [
                'name' => $request->name,
                'price' => $request->price,
                'quantity' => $request->quantity,
                'currency' => $request->currency ?? $ticket->currency ?? 'KES',
            ];
            
            // Only set dates if they are provided
            if ($request->has('start_date') && !empty($request->start_date)) {
                $updateData['start_date'] = $request->start_date;
            }
            
            if ($request->has('end_date') && !empty($request->end_date)) {
                $updateData['end_date'] = $request->end_date;
            }
            
            $ticket->update($updateData);

            Log::info('Ticket updated successfully', ['ticket_id' => $ticket->id]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket updated successfully!',
                'ticket' => $ticket
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Ticket update validation error', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Ticket update error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('Ticket delete attempt', ['id' => $id]);
            
            $ticket = Ticket::findOrFail($id);
            
            // Check if ticket has sales
            $soldCount = Attendee::where('ticket_id', $id)->sum('qty');
            
            if ($soldCount > 0) {
                Log::warning('Ticket delete blocked - has sales', [
                    'ticket_id' => $id,
                    'sold_count' => $soldCount
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete ticket. It has ' . $soldCount . ' ticket(s) sold.'
                ], 422);
            }
            
            $ticket->delete();

            Log::info('Ticket deleted successfully', ['ticket_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Ticket deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Ticket delete error', [
                'id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete ticket: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateTicketPDF($attendee_id)
    {
        try {
            // Fetch the attendee and related ticket information
            $attendee = Attendee::with(['ticket.event'])->findOrFail($attendee_id);

            // Generate unique values
            $eventId = $attendee->ticket->event->id;
            $ticketId = $attendee->ticket->id;
            $attendeeId = $attendee->id;

            $ticketNumber = 'TIC-' . $eventId . '-' . $ticketId . '-' . $attendeeId . '-' . time();
            $ticketValue = $attendee->ticket->price;
            $paymentReference = $attendee->transaction_code;

            // Create a new PDF instance with DL size
            $pdf = new FPDF('P', 'mm', [110, 220]);
            $pdf->AddPage();

            // Set margins
            $pdf->SetMargins(10, 10, 10);

            // Header Section
            $logoPath = public_path('public/images/logo.svg');
            $pdf->Image($logoPath, 20, -27.5, 70);

            $pdf->SetFont('Arial', 'B', 20);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetY(50);
            $pdf->Cell(0, -65, 'Event Ticket', 0, 1, 'C');

            // Horizontal Line
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, 25, 100, 25);

            // Event Details (Centered)
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetY(25);
            $pdf->Cell(0, 10, $attendee->ticket->event->name, 0, 1, 'C');

            // Horizontal Line
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, 45, 100, 45);

            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 8, 'Venue: ' . $attendee->ticket->event->location, 0, 1, 'C');

            // Start Date (Left) and End Date (Right)
            $pdf->Cell(0, 8, '', 0, 1, 'C'); // Spacer

            // Date (Bold)
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 8, $attendee->ticket->event->date->format('D, d M Y'), 0, 0, 'L');
            $pdf->Cell(0, 8, $attendee->ticket->event->end_date->format('D, d M Y'), 0, 1, 'R');

            // Reset Font to Regular for Time
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(50, 8, $attendee->ticket->event->date->format('h:i A'), 0, 0, 'L');
            $pdf->Cell(0, 8, $attendee->ticket->event->end_date->format('h:i A'), 0, 1, 'R');

            // Horizontal Line
            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, 72.5, 100, 72.5);

            // Attendee Details (Right and Left-aligned)
            $pdf->Ln(8);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Attendee Information', 0, 1);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $attendee->name, 0, 1, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 8, $attendee->ticket->name . ' TICKET', 0, 1, 'L');
            $pdf->Cell(0, 8, 'KES ' . number_format($attendee->ticket->price, 2), 0, 1, 'L');

            // QR Code (Right-aligned)
            $qrCodeData = "Ticket name: " . $attendee->ticket->name . "\n" .
                "Ticket number: " . $ticketNumber . "\n" .
                "Ticket value: " . number_format($ticketValue, 2) . "\n" .
                "Payment Reference: " . $paymentReference;

            $qrCode = new QrCode($qrCodeData);
            $writer = new PngWriter();
            $qrCodePath = public_path('storage/qr_codes/ticket_' . $ticketNumber . '.png');
            
            // Ensure directory exists
            if (!file_exists(public_path('storage/qr_codes'))) {
                mkdir(public_path('storage/qr_codes'), 0755, true);
            }
            
            $result = $writer->write($qrCode);
            file_put_contents($qrCodePath, $result->getString());

            $pdf->Image($qrCodePath, 70, $pdf->GetY() - 25, 30, 30);

            // Share Warning (Centered)
            $pdf->Ln(10);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 8, 'Do not share this ticket with anyone!', 0, 1, 'C');

            // Terms and Conditions (Justified)
            $pdf->Ln(20);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(0, 8, "This ticket is valid for the " . $attendee->ticket->event->name . " on " . $attendee->ticket->event->date->format('D, d M Y') . ". By purchasing this ticket, you agree to the terms and conditions.", 0, 'J');

            // Purple Footer
            $pdf->SetFillColor(128, 0, 128);
            $pdf->Rect(0, 190, 110, 30, 'F');

            // Website Link (on top of footer)
            $pdf->SetY(189.5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 10, 'www.aiyotickets.com', 0, 1, 'C');

            // Output the PDF to browser
            $pdf->Output('D', 'ticket-' . $attendee->name . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('PDF generation error', [
                'attendee_id' => $attendee_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}