<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Ticket;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AttendeeController extends Controller
{

    public function checkout(Request $request)
    {
        // Assume the cart is stored in the session or retrieved via a service
        $cart = session('cart', []);

        // Validate required fields (you can adjust this as needed)
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
            // Generate QR code (this is just an example, you can customize how QR codes are generated)
            // $qrCode = 'QR-' . strtoupper(uniqid());
            $qrCode = QrCode::generate($item['ticket_id']);

            // Store attendee data
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

        // After saving the attendees, clear the cart or handle it as needed
        session()->forget('cart');

        // Optionally, you can redirect to a confirmation page
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
            'mode_of_payment' => 'required|in:crypto,mobile_money,card',
            'phone' => 'required|string',
            'email' => 'required|email',
            'qty' => 'required|integer|min:1', // New field for quantity
        ]);

        // Retrieve the ticket price
        $ticket = Ticket::findOrFail($request->ticket_id);
        $totalPrice = $ticket->price * $request->qty; // Calculate total price

        // Generate QR code (you can implement your QR code generation logic here)
        $qrCode = $this->generateQRCode($ticket->id, $request->name);

        // Create the attendee
        Attendee::create([
            'ticket_id' => $request->ticket_id,
            'name' => $request->name,
            'gender' => $request->gender,
            'transaction_code' => $request->transaction_code,
            'mode_of_payment' => $request->mode_of_payment,
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
            'mode_of_payment' => 'required|in:crypto,mobile_money,card',
            'phone' => 'required|string',
            'email' => 'required|email',
            'qty' => 'required|integer|min:1', // Ensure qty is validated
        ]);

        $attendee = Attendee::findOrFail($id);
        
        // Update total price based on quantity
        $ticket = Ticket::findOrFail($attendee->ticket_id);
        $totalPrice = $ticket->price * $request->qty;

        $attendee->update([
            'name' => $request->name,
            'gender' => $request->gender,
            'transaction_code' => $request->transaction_code,
            'mode_of_payment' => $request->mode_of_payment,
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

    // Show the QR Scan Page with a specific attendee's data
 
}