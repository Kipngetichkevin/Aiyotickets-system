@vite('resources/css/app.css')
<!-- Sidebar and Header -->
@include('backend.includes.header')
@include('backend.includes.Sidebar')

<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <!-- Content -->
    <div class="w-full lg:ps-64">
        <h1 class="text-2xl font-semibold">Current Event</h1>

        <div class="container">
            <!-- QR Scan Page -->
            <div id="qrscan-container">
                <!-- This will hold the QR scanner or input -->
                <div id="scanner">
                    <button id="start-scan" class="btn btn-primary" onclick="startScan()">Start Scan</button>
                    <button id="stop-scan" class="btn btn-secondary" onclick="stopScan()" style="display: none;">Stop
                        Scan</button>
                    <button id="manual-input" class="btn btn-info" onclick="toggleInput()">Manual Input</button>
                </div>

                <!-- QR Code Reader Area -->
                <div id="qr-reader" style="width: 300px; height: 300px; margin-top: 20px;"></div>

                <!-- Input field to enter ticket ID manually -->
                <input type="text" id="ticket-input" class="form-control" placeholder="Enter ticket ID"
                    style="display: none; margin-top: 20px;" />
                <button id="submit-ticket" class="btn btn-success" onclick="submitTicket()"
                    style="display: none; margin-top: 10px;">Submit Ticket</button>

                <!-- Message/Alert area -->
                <div id="message" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>

<!-- JS Implementing Plugins -->
<script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
<script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>

<script>
    let html5QrCode;
    let scanning = false;

    function startScan() {
        html5QrCode = new Html5Qrcode("qr-reader");
        scanning = true;

        html5QrCode.start({
                    facingMode: "environment"
                }, {
                    fps: 10,
                    qrbox: 250
                },
                (decodedText, decodedResult) => {
                    // Handle the scanned QR code
                    document.getElementById('message').innerText = `Scanned: ${decodedText}`;
                    updateAttendees(decodedText);
                    stopScan(); // Stop scanning after a successful scan
                },
                (errorMessage) => {
                    // Handle scan error
                    console.warn(`QR Code scan error: ${errorMessage}`);
                })
            .catch(err => {
                console.error(`Unable to start scanning: ${err}`);
            });

        document.getElementById('start-scan').style.display = 'none';
        document.getElementById('stop-scan').style.display = 'inline-block';
        document.getElementById('ticket-input').style.display = 'none';
        document.getElementById('submit-ticket').style.display = 'none';
    }

    function stopScan() {
        if (html5QrCode) {
            html5QrCode.stop().then(ignore => {
                scanning = false;
                document.getElementById('start-scan').style.display = 'inline-block';
                document.getElementById('stop-scan').style.display = 'none';
            }).catch(err => {
                console.error(`Error stopping the scan: ${err}`);
            });
        }
    }

    function toggleInput() {
        const ticketInput = document.getElementById('ticket-input');
        const submitButton = document.getElementById('submit-ticket');
        if (ticketInput.style.display === 'none') {
            ticketInput.style.display = 'block';
            submitButton.style.display = 'inline-block';
            stopScan(); // Stop scanning if switching to manual input
        } else {
            ticketInput.style.display = 'none';
            submitButton.style.display = 'none';
        }
    }

    function submitTicket() {
        const ticketId = document.getElementById('ticket-input').value;
        if (ticketId) {
            document.getElementById('message').innerText = `Entered Ticket ID: ${ticketId}`;
            updateAttendees(ticketId);
        } else {
            document.getElementById('message').innerText = 'Please enter a valid ticket ID.';
        }
    }

    function updateAttendees(ticketId) {
        // Implement your logic to update the attendees table
        console.log(`Updating attendees with ticket ID: ${ticketId}`);
        // Example AJAX call (using fetch)
        /*
        fetch('/update-attendees', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ticketId: ticketId }),
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('message').innerText = data.message;
        })
        .catch((error) => {
            console.error('Error:', error);
        });
        */
    }
</script>
