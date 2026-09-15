@extends('layouts.app')

@section('content')
    <div class="container">
        <!-- QR Scan Page -->
        <div id="qrscan-container">
            <!-- This will hold the QR scanner or input -->
            <div id="scanner">
                <button id="start-scan" class="btn btn-primary" onclick="startScan()">Start Scan</button>
            </div>

            <!-- Hidden input field to enter ticket ID manually after scanning -->
            <input type="text" id="ticket-input" class="form-control" placeholder="Enter ticket ID" style="display: none;" />

            <!-- Message/Alert area -->
            <div id="message" class="mt-4"></div>
        </div>

        <!-- If QR scan functionality is used, you may integrate a library -->
        <!-- e.g., using a script here for actual QR scanning -->

        <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script> <!-- If using a QR library -->

        <script>
            // Function to start QR scanning or show the input field
            function startScan() {
                // Optionally, if you have QR scanning functionality, start the scanning process here
                document.getElementById('ticket-input').style.display = 'block'; // Show the input field for manual entry
                document.getElementById('start-scan').style.display = 'none';  // Hide the button

                // If using QR Code library, you can invoke that here
                // For example, HTML5 QR Code Library
                const html5QrCode = new Html5Qrcode("qrscan-container");
                
                html5QrCode.start(
                    { facingMode: "environment" }, // Default camera facing mode
                    {
                        fps: 10,    // Frame rate
                        qrbox: 250  // Size of the QR box
                    },
                    (decodedText, decodedResult) => {
                        // When a QR code is successfully scanned
                        document.getElementById('ticket-input').value = decodedText;  // Set the QR scan result into the input field
                        validateTicket(decodedText);  // Automatically trigger ticket validation
                        html5QrCode.stop();  // Stop scanning after successful scan
                    },
                    (errorMessage) => {
                        console.log(errorMessage); // Optionally log any errors from the scan
                    }
                ).catch((err) => {
                    console.log(err);
                });
            }

            // Validate the ticket either through manual input or QR scan
            document.getElementById('ticket-input').addEventListener('change', function() {
                const ticketId = this.value;
                validateTicket(ticketId);
            });

            // Function to validate the ticket
            function validateTicket(ticketId) {
                if (!ticketId) {
                    document.getElementById('message').innerHTML = "Ticket ID cannot be empty.";
                    return;
                }

                // AJAX request to validate the ticket
                fetch(`/scan-ticket/{{ $eventId }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ticket_id: ticketId })
                })
                .then(response => response.json())
                .then(data => {
                    // Handle the response and show appropriate message
                    if (data.success) {
                        document.getElementById('message').innerHTML = 'Ticket successfully checked in!';
                        alert('Checked In');
                    } else {
                        document.getElementById('message').innerHTML = data.message;
                        alert(data.message);  // Show message returned from the backend
                    }
                })
                .catch(error => {
                    document.getElementById('message').innerHTML = 'Error scanning ticket.';
                    alert('Error scanning ticket');
                });
            }
        </script>
    </div>
@endsection
