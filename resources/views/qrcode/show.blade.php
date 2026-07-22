@extends('layouts.app')

@section('content')
<main>
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'Scan Tickets for {{ $event->name }}' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Left Column - Scanner -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-xl font-medium text-gray-800 dark:text-white/90 mb-4">
                    Scan QR Code
                </h3>
                
                <!-- Scanner Container -->
                <div id="scanner-container" class="relative aspect-square max-w-md mx-auto overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                    <video id="preview" class="w-full h-full object-cover"></video>
                    <div id="scanner-overlay" class="absolute inset-0 pointer-events-none">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-48 h-48 border-2 border-blue-500 rounded-lg"></div>
                        </div>
                    </div>
                </div>

                <!-- Controls -->
                <div class="mt-4 flex justify-center gap-4">
                    <button id="start-scanner" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Start Scanning
                    </button>
                    <button id="stop-scanner" class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 18h14M5 6h14"/>
                        </svg>
                        Stop Scanning
                    </button>
                </div>

                <!-- Manual Entry -->
                <div class="mt-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center">Or enter ticket ID manually</p>
                    <div class="flex gap-2 mt-2">
                        <input type="text" id="manual-ticket-id" placeholder="Enter ticket ID" 
                               class="flex-1 rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <button id="manual-checkin" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Check In
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column - Recent Activity -->
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-xl font-medium text-gray-800 dark:text-white/90 mb-4">
                    Recent Activity
                </h3>
                
                <div id="scan-activity" class="space-y-3 max-h-[500px] overflow-y-auto">
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">Scan a ticket to see activity here</p>
                </div>

                <!-- Ticket List -->
                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">All Tickets</h4>
                    <div class="max-h-64 overflow-y-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($tickets as $ticket)
                                <tr>
                                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $ticket->name }}</td>
                                    <td class="px-3 py-2">
                                        @if($ticket->checked_in)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                Checked In
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                                Pending
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Include Instascan for QR scanning -->
<script src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let scanner = null;
    const preview = document.getElementById('preview');
    const startBtn = document.getElementById('start-scanner');
    const stopBtn = document.getElementById('stop-scanner');
    const manualInput = document.getElementById('manual-ticket-id');
    const manualCheckin = document.getElementById('manual-checkin');
    const activityLog = document.getElementById('scan-activity');

    function addActivity(message, isSuccess = true) {
        const div = document.createElement('div');
        div.className = `p-3 rounded-lg ${isSuccess ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'}`;
        div.innerHTML = `
            <div class="flex items-center gap-3">
                <div class="flex-1">
                    <p class="text-sm ${isSuccess ? 'text-green-800 dark:text-green-400' : 'text-red-800 dark:text-red-400'}">${message}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">${new Date().toLocaleTimeString()}</p>
                </div>
                <div class="flex-shrink-0">
                    ${isSuccess ? 
                        '<svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>' :
                        '<svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
                    }
                </div>
            </div>
        `;
        activityLog.prepend(div);
        
        // Keep only last 10 entries
        while (activityLog.children.length > 10) {
            activityLog.removeChild(activityLog.lastChild);
        }
    }

    async function checkInTicket(ticketId) {
        try {
            const response = await fetch(`/qrcode/scan/{{ $event->id }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ticket_id: ticketId })
            });

            const data = await response.json();

            if (data.success) {
                addActivity(`✅ Ticket ${ticketId} checked in successfully!`);
                // Update the ticket status in the table
                const rows = document.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    if (row.textContent.includes(ticketId)) {
                        row.querySelector('td:last-child').innerHTML = `
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                Checked In
                            </span>
                        `;
                    }
                });
            } else {
                addActivity(`❌ ${data.message}`, false);
            }
        } catch (error) {
            addActivity(`❌ Error checking in ticket: ${error.message}`, false);
        }
    }

    // Start Scanner
    startBtn.addEventListener('click', function() {
        if (scanner) {
            scanner.stop();
            scanner = null;
        }

        scanner = new Instascan.Scanner({ video: preview });
        
        scanner.addListener('scan', function(content) {
            // Parse the QR code content (assumes it contains ticket ID)
            try {
                const data = JSON.parse(content);
                if (data.ticket_id) {
                    checkInTicket(data.ticket_id);
                } else {
                    addActivity('❌ Invalid QR code format', false);
                }
            } catch {
                // If not JSON, treat as ticket ID
                checkInTicket(content);
            }
        });

        Instascan.Camera.getCameras().then(function(cameras) {
            if (cameras.length > 0) {
                scanner.start(cameras[0]);
                startBtn.textContent = 'Scanning...';
                startBtn.disabled = true;
                addActivity('📷 Scanner started. Point camera at a QR code.');
            } else {
                addActivity('❌ No cameras found.', false);
            }
        }).catch(function(e) {
            addActivity(`❌ Error accessing camera: ${e}`, false);
        });
    });

    // Stop Scanner
    stopBtn.addEventListener('click', function() {
        if (scanner) {
            scanner.stop();
            scanner = null;
            startBtn.textContent = 'Start Scanning';
            startBtn.disabled = false;
            addActivity('📷 Scanner stopped.');
        }
    });

    // Manual Check In
    manualCheckin.addEventListener('click', function() {
        const ticketId = manualInput.value.trim();
        if (ticketId) {
            checkInTicket(ticketId);
            manualInput.value = '';
        } else {
            addActivity('❌ Please enter a ticket ID.', false);
        }
    });

    manualInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            manualCheckin.click();
        }
    });
});
</script>

<style>
    #scanner-container {
        position: relative;
    }
    #scanner-container video {
        transform: scaleX(-1);
    }
    #scanner-overlay {
        pointer-events: none;
    }
    #scanner-overlay .border-2 {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    #scan-activity {
        scroll-behavior: smooth;
    }
    #scan-activity::-webkit-scrollbar {
        width: 4px;
    }
    #scan-activity::-webkit-scrollbar-track {
        background: transparent;
    }
    #scan-activity::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 2px;
    }
    #scan-activity::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }
</style>
@endsection