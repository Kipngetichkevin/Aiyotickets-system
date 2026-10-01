<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Ticket;
use App\Models\Attendee;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $tickets = Ticket::where('event_id', $event_id)->get();

        return view('tickets.index', compact('event', 'tickets'));
    }

    /**
     * Display all tickets across the user's events.
     */
    public function allTickets(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');

        $query = Ticket::with('event')->orderByDesc('created_at');

        if (!$isAdmin) {
            $eventIds = Event::where('user_id', $user->id)->pluck('id');
            $query->whereIn('event_id', $eventIds);
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->whereRaw('(SELECT COALESCE(SUM(qty),0) FROM attendees WHERE attendees.ticket_id = tickets.id) < tickets.quantity');
            } elseif ($request->status === 'soldout') {
                $query->whereRaw('(SELECT COALESCE(SUM(qty),0) FROM attendees WHERE attendees.ticket_id = tickets.id) >= tickets.quantity');
            }
        }

        $tickets = $query->paginate(20)->withQueryString();

        $statsQuery = Ticket::query();
        if (!$isAdmin) {
            $eventIds = $eventIds ?? Event::where('user_id', $user->id)->pluck('id');
            $statsQuery->whereIn('event_id', $eventIds);
        }

        $ticketIds = (clone $statsQuery)->pluck('id');

        $stats = [
            'total'    => (clone $statsQuery)->count(),
            'capacity' => (clone $statsQuery)->sum('quantity'),
            'sold'     => Attendee::whereIn('ticket_id', $ticketIds)->sum('qty'),
        ];
        $stats['available'] = max(0, $stats['capacity'] - $stats['sold']);

        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::where('user_id', $user->id)->orderBy('name')->get(['id', 'name']);

        return view('tickets.all', compact('tickets', 'events', 'isAdmin', 'stats'));
    }

    public function create($event_id)
    {
        $event = Event::findOrFail($event_id);
        return view('tickets.create', compact('event'));
    }

    public function store(Request $request)
    {
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

            $event = Event::findOrFail($validated['event_id']);

            if (!$event->merchant_id) {
                $merchant = Merchant::where('user_id', $event->user_id)->first();
                if (!$merchant) {
                    $owner = \App\Models\User::find($event->user_id);
                    $merchant = Merchant::create([
                        'user_id' => $event->user_id,
                        'name'    => $owner->name ?? 'Merchant',
                        'email'   => $owner->email ?? null,
                    ]);
                }
                $event->update(['merchant_id' => $merchant->id]);
            }

            $ticket = Ticket::create([
                'event_id'    => $event->id,
                'merchant_id' => $event->merchant_id,
                'name'        => $validated['name'],
                'price'       => $validated['price'],
                'quantity'    => $validated['quantity'],
                'currency'    => $validated['currency'] ?? 'KES',
                'start_date'  => $validated['start_date'] ?? null,
                'end_date'    => $validated['end_date'] ?? null,
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Ticket created successfully!',
                    'data' => $ticket->load('event')
                ], 201);
            }

            return redirect()->route('events.show', $event->id)
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
                    'message' => 'Failed to create ticket: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to create ticket: ' . $e->getMessage());
        }
    }

    public function edit($event_id, $id)
    {
        $event = Event::findOrFail($event_id);
        $ticket = Ticket::where('event_id', $event_id)->findOrFail($id);
        return view('tickets.edit', compact('event', 'ticket'));
    }

    public function update(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'currency' => 'nullable|in:KES,USD,USDT,EUROS,POUND',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after:start_date',
            ]);

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
                    'message' => 'Failed to update ticket: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to update ticket: ' . $e->getMessage());
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $ticket = Ticket::findOrFail($id);

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
                    'message' => 'Failed to delete ticket: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Failed to delete ticket: ' . $e->getMessage());
        }
    }

    /**
     * Generate PDF ticket for an attendee.
     */
    public function generateTicketPDF($attendee_id)
    {
        try {
            $attendee = Attendee::with(['ticket.event'])->findOrFail($attendee_id);

            $eventId    = $attendee->ticket->event->id;
            $ticketId   = $attendee->ticket->id;
            $attendeeId = $attendee->id;

            $ticketNumber     = 'TIC-' . $eventId . '-' . $ticketId . '-' . $attendeeId . '-' . time();
            $ticketValue      = $attendee->ticket->price;
            $paymentReference = $attendee->transaction_code;

            $pdf = new FPDF('P', 'mm', [110, 220]);
            $pdf->AddPage();
            $pdf->SetMargins(10, 10, 10);

            // ---- Logo ----
            $logoPath = public_path('images/logo/logo.png');
            if (!file_exists($logoPath)) {
                $svgPath = public_path('images/logo/logo.svg');
                if (file_exists($svgPath)) {
                    $logoPath = $this->convertSvgToPng($svgPath);
                }
            }

            if ($logoPath && file_exists($logoPath)) {
                $pdf->Image($logoPath, 20, -27.5, 70);
            } else {
                $pdf->SetFont('Arial', 'B', 24);
                $pdf->SetTextColor(50, 50, 50);
                $pdf->SetY(10);
                $pdf->Cell(0, 10, 'AIYO TICKETS', 0, 1, 'C');
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

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 8, $attendee->ticket->event->date->format('D, d M Y'), 0, 0, 'L');
            $pdf->Cell(0, 8, $attendee->ticket->event->end_date->format('D, d M Y'), 0, 1, 'R');

            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(50, 8, $attendee->ticket->event->date->format('h:i A'), 0, 0, 'L');
            $pdf->Cell(0, 8, $attendee->ticket->event->end_date->format('h:i A'), 0, 1, 'R');

            $pdf->SetDrawColor(200, 200, 200);
            $pdf->Line(10, 72.5, 100, 72.5);

            $pdf->Ln(8);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'Attendee Information', 0, 1);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 8, $attendee->name, 0, 1, 'L');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 8, $attendee->ticket->name . ' TICKET', 0, 1, 'L');
            $pdf->Cell(0, 8, 'KES ' . number_format($attendee->ticket->price, 2), 0, 1, 'L');

            // ── QR encodes the attendee's UUID ─────────────────────────
            if (empty($attendee->uuid)) {
                $attendee->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
                $attendee->refresh();
            }
            $qrCodeData = $attendee->uuid;

            $qrCodeDir = public_path('storage/qr_codes');
            if (!file_exists($qrCodeDir)) {
                mkdir($qrCodeDir, 0755, true);
            }

            // Build QR with high resolution + robust error correction
            $qrCode = new QrCode($qrCodeData);
            $qrCode->setSize(700);
            $qrCode->setMargin(10);

            // Support both Endroid v4 (classes) and v5 (enum)
            if (method_exists($qrCode, 'setErrorCorrectionLevel')) {
                try {
                    if (enum_exists('Endroid\\QrCode\\ErrorCorrectionLevel')) {
                        $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::High);
                    } elseif (class_exists('Endroid\\QrCode\\ErrorCorrectionLevel\\ErrorCorrectionLevelHigh')) {
                        $qrCode->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh());
                    }
                } catch (\Throwable $e) {
                    Log::warning('QR error correction not set: ' . $e->getMessage());
                }
            }

            $writer     = new PngWriter();
            $qrCodePath = $qrCodeDir . '/ticket_' . $ticketNumber . '.png';

            $result = $writer->write($qrCode);
            file_put_contents($qrCodePath, $result->getString());

            // ── Draw QR: bigger (50×50mm), centered under attendee block ──
            $pdf->Ln(6);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->Cell(0, 6, 'Scan at entrance:', 0, 1, 'C');

            // Center horizontally: (page width 110) - (QR width 50) = 30mm offset
            $qrX = 30;
            $qrY = $pdf->GetY() + 2;
            $pdf->Image($qrCodePath, $qrX, $qrY, 50, 50);

            // Advance cursor below the QR
            $pdf->SetY($qrY + 54);

            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 0, 0);
            $pdf->Cell(0, 8, 'Do not share this ticket with anyone!', 0, 1, 'C');

            $pdf->Ln(8);
            $pdf->SetFont('Arial', 'I', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->MultiCell(
                0,
                8,
                "This ticket is valid for the " . $attendee->ticket->event->name
                    . " on " . $attendee->ticket->event->date->format('D, d M Y')
                    . ". By purchasing this ticket, you agree to the terms and conditions.",
                0,
                'J'
            );

            // Footer bar
            $pdf->SetFillColor(128, 0, 128);
            $pdf->Rect(0, 190, 110, 30, 'F');

            $pdf->SetY(189.5);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 10, 'www.aiyotickets.com', 0, 1, 'C');

            if (file_exists($qrCodePath)) {
                unlink($qrCodePath);
            }

            $pdf->Output('D', 'ticket-' . $attendee->name . '.pdf');

        } catch (\Exception $e) {
            Log::error('PDF generation error', [
                'attendee_id' => $attendee_id,
                'message'     => $e->getMessage(),
                'trace'       => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTicket($attendee_id)
    {
        return $this->generateTicketPDF($attendee_id);
    }

    private function convertSvgToPng($svgPath, $width = 200, $height = 200)
    {
        try {
            $logoDir = public_path('images');
            if (!file_exists($logoDir)) {
                mkdir($logoDir, 0755, true);
            }

            $logoSubDir = public_path('images/logo');
            if (!file_exists($logoSubDir)) {
                mkdir($logoSubDir, 0755, true);
            }

            $pngPath = public_path('images/logo/logo.png');

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

            try {
                $svgContent = file_get_contents($svgPath);
                $svgXml     = simplexml_load_string($svgContent);

                if ($svgXml) {
                    $attributes = $svgXml->attributes();
                    $width  = isset($attributes->width)  ? (int) $attributes->width  : 200;
                    $height = isset($attributes->height) ? (int) $attributes->height : 200;

                    $image = imagecreatetruecolor($width, $height);
                    $white = imagecolorallocate($image, 255, 255, 255);
                    imagefilledrectangle($image, 0, 0, $width, $height, $white);

                    $text = '';
                    foreach ($svgXml->children() as $child) {
                        if ($child->getName() === 'text') {
                            $text = (string) $child;
                            break;
                        }
                    }

                    if (empty($text)) {
                        foreach ($svgXml->children() as $child) {
                            if (isset($child->text)) {
                                $text = (string) $child->text;
                                break;
                            }
                        }
                    }

                    if (empty($text)) {
                        $text = 'AIYO TICKETS';
                    }

                    $black      = imagecolorallocate($image, 0, 0, 0);
                    $fontSize   = 5;
                    $textWidth  = imagefontwidth($fontSize) * strlen($text);
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

            try {
                $image = imagecreatetruecolor(200, 80);
                $white = imagecolorallocate($image, 255, 255, 255);
                $black = imagecolorallocate($image, 0, 0, 0);
                imagefilledrectangle($image, 0, 0, 200, 80, $white);

                $text       = 'AIYO TICKETS';
                $fontSize   = 5;
                $textWidth  = imagefontwidth($fontSize) * strlen($text);
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

    public function getStatus($id)
    {
        try {
            $ticket = Ticket::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id'        => $ticket->id,
                    'name'      => $ticket->name,
                    'price'     => $ticket->price,
                    'quantity'  => $ticket->quantity,
                    'sold'      => $ticket->sold_count,
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