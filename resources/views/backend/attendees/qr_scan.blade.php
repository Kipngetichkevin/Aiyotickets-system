<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QR Code Check-In</title>
</head>
<body>
    <div id="qr-code-section">
        <h1>Scan QR Code to Check-In</h1>
        
        <!-- Image Upload Section -->
        <div>
            <h3>Upload Ticket Image (PDF Print / Screenshot with QR)</h3>
            <input type="file" id="qr-image-input" accept="image/*">
        </div>

        <!-- QR Code Scanning via Camera -->
        <div id="qr-camera-section">
            <h3>Or, Scan Using Camera</h3>
            <div id="qr-reader" style="width: 100%; height: 300px;"></div>
        </div>
        
        <div id="message" style="margin-top: 20px;"></div>
    </div>

    <script src="https://unpkg.com/html5-qrcode/minified/html5-qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Function to handle image upload for QR code scanning
            const qrImageInput = document.getElementById('qr-image-input');
            qrImageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imgElement = new Image();
                        imgElement.src = e.target.result;
                        imgElement.onload = function() {
                            const qrCode = new Html5Qrcode();
                            qrCode.decodeFromImage(imgElement).then(decodedText => {
                                handleScan(decodedText);
                            }).catch(err => {
                                alert('Failed to scan QR code from image!');
                            });
                        }
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Handle QR Code scan
            function handleScan(decodedText) {
                const url = new URL(decodedText);
                const attendeeId = url.pathname.split('/').pop(); // Extract the attendee ID from the URL

                // Send the scanned ID to the backend to update the check-in status
                fetch(`/attendees/qr_scan/${attendeeId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ attendeeId })
                })
                .then(response => response.json())
                .then(data => {
                    const messageElement = document.getElementById('message');
                    if (data.success) {
                        messageElement.innerText = data.message;
                    } else {
                        messageElement.innerText = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }

            // Start QR code scanning via camera
            function startCameraScanning() {
                const scanner = new Html5QrcodeScanner('qr-reader', {
                    fps: 10,
                    qrbox: 250,
                    aspectRatio: 1.0,
                    disableScanIfSameImage: true,
                });
                scanner.render(onScanSuccess);
            }

            // Handle camera scan success
            function onScanSuccess(decodedText, decodedResult) {
                handleScan(decodedText);
            }

            // Start camera scanning automatically
            startCameraScanning();
        });
    </script>
</body>
</html>
