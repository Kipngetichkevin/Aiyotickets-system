@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'Scan Tickets for {{ $event->name }}' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- ============ LEFT: SCANNER ============ --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-xl font-medium text-gray-800 dark:text-white/90 mb-4">Scan QR Code</h3>

                <div id="reader" class="w-full max-w-md mx-auto overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800"
                     style="min-height: 320px;"></div>

                <div class="mt-4 flex justify-center gap-3">
                    <button id="start-scanner"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Start Scanning
                    </button>
                    <button id="stop-scanner" disabled
                            class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 6h12v12H6z"/>
                        </svg>
                        Stop Scanning
                    </button>
                </div>

                {{-- Result banner --}}
                <div id="result-banner" class="mt-4 hidden rounded-lg border p-3 text-sm"></div>

                {{-- Manual entry --}}
                <div class="mt-6 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center mb-2">Or enter attendee UUID / ticket ID manually</p>
                    <div class="flex gap-2">
                        <input type="text" id="manual-uuid" placeholder="Paste UUID or ticket ID"
                               class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <button id="manual-checkin"
                                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            Check In
                        </button>
                    </div>
                </div>
            </div>

            {{-- ============ RIGHT: ACTIVITY + TICKET COUNTS ============ --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-xl font-medium text-gray-800 dark:text-white/90 mb-4">Recent Activity</h3>

                <div id="scan-activity" class="space-y-2 max-h-[340px] overflow-y-auto">
                    @if($recent->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 text-center py-4">Scan a ticket to see activity here</p>
                    @else
                        @foreach($recent as $a)
                            <div class="p-3 rounded-lg bg-green-50 dark:bg-green-900/20">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-green-800 dark:text-green-400 truncate">
                                            {{ $a->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $a->ticket->name ?? '—' }} • {{ optional($a->checked_in_at)->format('d M Y, H:i:s') }}
                                        </p>
                                    </div>
                                    <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="mt-6">
                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tickets</h4>
                    <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ticket</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Checked In</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Pending</th>
                                </tr>
                            </thead>
                            <tbody id="ticket-stats" class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($ticketStats as $row)
                                    <tr data-ticket-id="{{ $row['id'] }}">
                                        <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $row['name'] }}</td>
                                        <td class="px-3 py-2 text-right text-green-600 dark:text-green-400 font-medium">
                                            <span class="checked-count">{{ $row['checked'] }}</span> / <span class="total-count">{{ $row['total'] }}</span>
                                        </td>
                                        <td class="px-3 py-2 text-right text-yellow-600 dark:text-yellow-400 font-medium">
                                            <span class="pending-count">{{ $row['pending'] }}</span>
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

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const eventId    = {{ $event->id }};
    const startBtn   = document.getElementById('start-scanner');
    const stopBtn    = document.getElementById('stop-scanner');
    const banner     = document.getElementById('result-banner');
    const manualIn   = document.getElementById('manual-uuid');
    const manualBtn  = document.getElementById('manual-checkin');
    const activity   = document.getElementById('scan-activity');
    const csrf       = '{{ csrf_token() }}';

    let html5QrCode  = null;
    let scanning     = false;
    let lastScanText = null;
    let lastScanTime = 0;

    function showBanner(html, ok) {
        banner.className = 'mt-4 rounded-lg border p-3 text-sm ' +
            (ok ? 'bg-green-50 border-green-200 text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300'
                : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-800 dark:text-red-300');
        banner.innerHTML = html;
        banner.classList.remove('hidden');
    }

    function addActivity(name, ticketName, time, ok = true) {
        if (activity.querySelector('p.text-center')) activity.innerHTML = '';
        const div = document.createElement('div');
        div.className = 'p-3 rounded-lg ' + (ok ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20');
        div.innerHTML = `
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-sm font-medium ${ok ? 'text-green-800 dark:text-green-400' : 'text-red-800 dark:text-red-400'} truncate">${name}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">${ticketName} • ${time}</p>
                </div>
                <svg class="w-5 h-5 ${ok ? 'text-green-500' : 'text-red-500'} shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${ok ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>'
                         : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>'}
                </svg>
            </div>
        `;
        activity.prepend(div);
        while (activity.children.length > 15) activity.removeChild(activity.lastChild);
    }

    async function checkIn(qrData) {
        try {
            const res = await fetch(`/qrcode/check-in/${eventId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ qr_data: qrData }),
            });

            const data = await res.json();

            if (data.success) {
                showBanner(`<strong>✅ ${data.attendee.name}</strong> — ${data.attendee.ticket} checked in.`, true);
                addActivity(data.attendee.name, data.attendee.ticket, new Date().toLocaleTimeString(), true);
            } else {
                showBanner(`<strong>❌ ${data.message}</strong>`, false);
                addActivity(data.message, '', new Date().toLocaleTimeString(), false);
            }

            refreshStats();
        } catch (err) {
            showBanner(`<strong>❌ Network error:</strong> ${err.message}`, false);
        }
    }

    async function refreshStats() {
        try {
            const res  = await fetch(`/qrcode/activity/${eventId}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            document.querySelectorAll('#ticket-stats tr').forEach(row => {
                const id = row.dataset.ticketId;
                const match = data.stats.find(s => String(s.id) === String(id));
                if (match) {
                    row.querySelector('.checked-count').textContent = match.checked;
                    row.querySelector('.total-count').textContent   = match.total;
                    row.querySelector('.pending-count').textContent = match.pending;
                }
            });
        } catch (_) { /* silent */ }
    }

    // Debounce — don't resubmit the same QR within 2 seconds
    async function handleDecode(decodedText) {
        const now = Date.now();
        if (decodedText === lastScanText && (now - lastScanTime) < 2000) return;
        lastScanText = decodedText;
        lastScanTime = now;
        await checkIn(decodedText);
    }

    // ---- Start scanner ----
    startBtn.addEventListener('click', async function () {
        if (scanning) return;

        // Clear the reader div before init — required after a stop
        document.getElementById('reader').innerHTML = '';

        html5QrCode = new Html5Qrcode("reader", {
            verbose: false,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true,   // native OS decoder when available
            },
        });

        const config = {
            fps: 15,
            // Dynamic box — 70% of the shorter viewport dimension
            qrbox: function (vw, vh) {
                const min  = Math.min(vw, vh);
                const size = Math.floor(min * 0.7);
                return { width: size, height: size };
            },
            aspectRatio: 1.0,
            disableFlip: false,
            experimentalFeatures: {
                useBarCodeDetectorIfSupported: true,
            },
        };

        try {
            await html5QrCode.start(
                { facingMode: "environment" },
                config,
                (decodedText) => { handleDecode(decodedText); },
                () => { /* ignore per-frame decode failures */ }
            );
            scanning = true;
            startBtn.disabled = true;
            stopBtn.disabled  = false;
            showBanner('📷 Scanner started. Point the camera at the QR code.', true);
        } catch (err) {
            showBanner(`<strong>❌ Camera error:</strong> ${err}`, false);
        }
    });

    // ---- Stop scanner ----
    stopBtn.addEventListener('click', async function () {
        if (!scanning || !html5QrCode) return;
        try {
            await html5QrCode.stop();
            await html5QrCode.clear();
        } catch (_) {}
        html5QrCode = null;
        scanning = false;
        startBtn.disabled = false;
        stopBtn.disabled  = true;
    });

    // ---- Manual ----
    manualBtn.addEventListener('click', () => {
        const v = manualIn.value.trim();
        if (!v) { showBanner('<strong>❌ Enter a UUID or ticket ID.</strong>', false); return; }
        checkIn(v);
        manualIn.value = '';
    });
    manualIn.addEventListener('keypress', e => {
        if (e.key === 'Enter') manualBtn.click();
    });
});
</script>
@endsection