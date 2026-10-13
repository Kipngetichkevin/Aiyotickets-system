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
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use FPDF;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets for an event.
     */
    public function index($event_id)
    {
        $event = Event::findOrFail($event_id);
        $this->authorizeEventOwner($event);
        $tickets = Ticket::where('event_id', $event_id)->get();
        
        return view('tickets.index', compact('event', 'tickets'));
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create($event_id)
    {
        $event = Event::findOrFail($event_id);
        $this->authorizeEventOwner($event);
        
        return view('tickets.create', compact('event'));
    }

    /**
     * Store a newly created ticket in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeEventOwner($request->input('event_id'));

        try {
            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'currency' => 'nullable|in:KES,USD,USDT,EUROS,POUND',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            $ticket = Ticket::create([
                'event_id' => $validated['event_id'],
                // A ticket belongs to its event's merchant (users have no merchant_id column)
                'merchant_id' => Event::whereKey($validated['event_id'])->value('merchant_id'),
                'name' => $validated['name'],
                'price' => $validated['price'],
                'quantity' => $validated['quantity'],
                'currency' => $validated['currency'] ?? 'KES',
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]);

            // 🔥 Check if request expects JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket created successfully!',
                    'data' => $ticket->load('event')
                ], 201);
            }

            return redirect()->route('events.show', $validated['event_id'])
                ->with('success', 'Ticket created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Ticket creation error: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->userError('Failed to create ticket', $e)
                ], 500);
            }
            
            return back()->with('error', $this->userError('Failed to create ticket', $e));
        }
    }

    /**
     * Show the form for editing the specified ticket.
     */
    public function edit($event_id, $id)
    {
        $event = Event::findOrFail($event_id);
        $this->authorizeEventOwner($event);
        $ticket = Ticket::where('event_id', $event_id)->findOrFail($id);
        
        return view('tickets.edit', compact('event', 'ticket'));
    }

    /**
     * Update the specified ticket in storage.
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorizeEventOwner($ticket->event_id);

        try {

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'currency' => 'nullable|in:KES,USD,USDT,EUROS,POUND',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

            // Check if ticket is locked (has sales)
            if ($ticket->sold_count > 0) {
                $errorMessage = 'Cannot update ticket with existing sales.';
                
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                
                return back()->with('error', $errorMessage);
            }

            $ticket->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'quantity' => $validated['quantity'],
                'currency' => $validated['currency'] ?? 'KES',
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]);

            // 🔥 Check if request expects JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket updated successfully!',
                    'data' => $ticket->load('event')
                ]);
            }

            return redirect()->route('events.show', $ticket->event_id)
                ->with('success', 'Ticket updated successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Ticket update error: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->userError('Failed to update ticket', $e)
                ], 500);
            }
            
            return back()->with('error', $this->userError('Failed to update ticket', $e));
        }
    }

    /**
     * Remove the specified ticket from storage.
     */
    public function destroy(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $this->authorizeEventOwner($ticket->event_id);

        try {

            // Check if ticket is locked (has sales)
            if ($ticket->sold_count > 0) {
                $errorMessage = 'Cannot delete ticket with existing sales.';
                
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                
                return back()->with('error', $errorMessage);
            }

            $eventId = $ticket->event_id;
            $ticket->delete();

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket deleted successfully!',
                    'data' => ['event_id' => $eventId]
                ]);
            }

            return redirect()->route('events.show', $eventId)
                ->with('success', 'Ticket deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Ticket deletion error: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $this->userError('Failed to delete ticket', $e)
                ], 500);
            }
            
            return back()->with('error', $this->userError('Failed to delete ticket', $e));
        }
    }

    /**
     * Generate PDF ticket for an attendee.
     */
    public function generateTicketPDF($attendee_id)
    {
        // Ticket holder, the event's owner, or an admin
        $holder = Attendee::with('ticket.event')->findOrFail($attendee_id);
        if ((int) $holder->user_id !== (int) auth()->id()) {
            $this->authorizeEventOwner($holder->ticket?->event);
        }

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

            // Header Section - Use PNG from the logo directory
            $logoPath = public_path('images/logo/logo.png');
            
            // If PNG doesn't exist, try to use SVG or create a fallback
            if (!file_exists($logoPath)) {
                $svgPath = public_path('images/logo/logo.svg');
                if (file_exists($svgPath)) {
                    $logoPath = $this->convertSvgToPng($svgPath);
                }
            }
            
            // If we have a logo file, display it
            if ($logoPath && file_exists($logoPath)) {
                $pdf->Image($logoPath, 20, -27.5, 70);
            } else {
                // Fallback to text logo
                $pdf->SetFont('Arial', 'B', 24);
                $pdf->SetTextColor(50, 50, 50);
                $pdf->SetY(10);
                $pdf->Cell(0, 10, 'AIYO TICKETS', 0, 1, 'C');
            }

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

            // QR Code (Right-aligned) - Using Endroid QR Code
            // The gate scanner looks attendees up by this code — encode nothing else.
            $qrCodeData = $attendee->ensureQrCode();

            // Create QR Code directory if it doesn't exist (recursive)
            $qrCodeDir = public_path('storage/qr_codes');
            if (!file_exists($qrCodeDir)) {
                mkdir($qrCodeDir, 0755, true);
            }

            // Generate QR Code using Endroid
            $qrCode = new QrCode($qrCodeData);
            $writer = new PngWriter();
            $qrCodePath = $qrCodeDir . '/ticket_' . $ticketNumber . '.png';
            
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

            // Clean up temporary QR code file
            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
            }

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
                'message' => $this->userError('Failed to generate PDF', $e)
            ], 500);
        }
    }

    /**
     * Download ticket PDF (alias for generateTicketPDF).
     */
    public function downloadTicket($attendee_id)
    {
        return $this->generateTicketPDF($attendee_id);
    }

    /**
     * Convert SVG to PNG using ImageMagick or GD.
     */
    private function convertSvgToPng($svgPath, $width = 200, $height = 200)
    {
        try {
            // Create the directory with proper recursive creation
            $logoDir = public_path('images');
            if (!file_exists($logoDir)) {
                mkdir($logoDir, 0755, true);
            }

            $logoSubDir = public_path('images/logo');
            if (!file_exists($logoSubDir)) {
                mkdir($logoSubDir, 0755, true);
            }

            $pngPath = public_path('images/logo/logo.png');

            // Try using ImageMagick if available
            if (extension_loaded('imagick')) {
                try {
                    $imagick = new \Imagick();
                    $imagick->readImage($svgPath);
                    $imagick->setImageFormat('png');
                    $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
                    $imagick->writeImage($pngPath);
                    $imagick->clear();
                    $imagick->destroy();
                    return $pngPath;
                } catch (\Exception $e) {
                    Log::warning('Imagick conversion failed: ' . $e->getMessage());
                }
            }

            // Try using GD with simple XML parsing
            try {
                $svgContent = file_get_contents($svgPath);
                $svgXml = simplexml_load_string($svgContent);
                
                if ($svgXml) {
                    $attributes = $svgXml->attributes();
                    $width = isset($attributes->width) ? (int)$attributes->width : 200;
                    $height = isset($attributes->height) ? (int)$attributes->height : 200;
                    
                    $image = imagecreatetruecolor($width, $height);
                    
                    // Set background to white
                    $white = imagecolorallocate($image, 255, 255, 255);
                    imagefilledrectangle($image, 0, 0, $width, $height, $white);
                    
                    // Find text content from SVG
                    $text = '';
                    foreach ($svgXml->children() as $child) {
                        if ($child->getName() === 'text') {
                            $text = (string)$child;
                            break;
                        }
                    }
                    
                    if (empty($text)) {
                        // Try to find text in any element
                        foreach ($svgXml->children() as $child) {
                            if (isset($child->text)) {
                                $text = (string)$child->text;
                                break;
                            }
                        }
                    }
                    
                    if (empty($text)) {
                        $text = 'AIYO TICKETS';
                    }
                    
                    // Draw text on image
                    $black = imagecolorallocate($image, 0, 0, 0);
                    $fontSize = 5;
                    $textWidth = imagefontwidth($fontSize) * strlen($text);
                    $textHeight = imagefontheight($fontSize);
                    $x = ($width - $textWidth) / 2;
                    $y = ($height - $textHeight) / 2;
                    imagestring($image, $fontSize, $x, $y, $text, $black);
                    
                    imagepng($image, $pngPath);
                    imagedestroy($image);
                    
                    return $pngPath;
                }
            } catch (\Exception $e) {
                Log::warning('GD SVG conversion failed: ' . $e->getMessage());
            }

            // Fallback: create a simple text image
            try {
                $image = imagecreatetruecolor(200, 80);
                $white = imagecolorallocate($image, 255, 255, 255);
                $black = imagecolorallocate($image, 0, 0, 0);
                imagefilledrectangle($image, 0, 0, 200, 80, $white);
                
                $text = 'AIYO TICKETS';
                $fontSize = 5;
                $textWidth = imagefontwidth($fontSize) * strlen($text);
                $textHeight = imagefontheight($fontSize);
                $x = (200 - $textWidth) / 2;
                $y = (80 - $textHeight) / 2;
                imagestring($image, $fontSize, $x, $y, $text, $black);
                
                imagepng($image, $pngPath);
                imagedestroy($image);
                
                return $pngPath;
            } catch (\Exception $e) {
                Log::warning('Fallback image creation failed: ' . $e->getMessage());
                return null;
            }

        } catch (\Exception $e) {
            Log::warning('Failed to convert SVG to PNG: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get ticket status (API endpoint).
     */
    public function getStatus($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $ticket->id,
                    'name' => $ticket->name,
                    'price' => $ticket->price,
                    'quantity' => $ticket->quantity,
                    'sold' => $ticket->sold_count,
                    'available' => $ticket->available,
                    'is_locked' => $ticket->is_locked,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ticket not found'
            ], 404);
        }
    }
}