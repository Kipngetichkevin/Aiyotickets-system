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
// Remove this: use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TicketController extends Controller
{
    // ... other methods ...

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

            // Header Section - Use PNG from the logo directory
            $logoPath = public_path('images/logo/logo.png');
            
            // If PNG doesn't exist, try to use SVG or create a fallback
            if (!file_exists($logoPath)) {
                $svgPath = public_path('images/logo/logo.svg');
                if (file_exists($svgPath)) {
                    // Try to convert SVG to PNG
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
            $qrCodeData = "Ticket name: " . $attendee->ticket->name . "\n" .
                "Ticket number: " . $ticketNumber . "\n" .
                "Ticket value: " . number_format($ticketValue, 2) . "\n" .
                "Payment Reference: " . $paymentReference;

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
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Convert SVG to PNG using ImageMagick or GD
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
     * Get a PDF ticket for download (alternative method)
     */
    public function downloadTicket($attendee_id)
    {
        return $this->generateTicketPDF($attendee_id);
    }
}