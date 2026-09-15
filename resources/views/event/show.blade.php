<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->name }} — Aiyo Tickets</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="margin: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #f9fafb;">

{{-- ============ NAVIGATION ============ --}}
<nav style="position: sticky; top: 0; z-index: 100; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid #e5e7eb;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 14px 24px; display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ route('home') }}" style="display: flex; align-items: center; text-decoration: none;">
            <img src="{{ asset('images/logo.svg') }}" alt="Aiyo Tickets" style="height: 40px; width: auto; max-width: 160px; display: block;">
        </a>
        <div style="display: flex; align-items: center; gap: 8px;">
            <a href="{{ route('home') }}" style="padding: 9px 16px; color: #374151; text-decoration: none; font-size: 14px; font-weight: 500;">
                ← All Events
            </a>
            @auth
                <a href="{{ route('dashboard') }}" style="padding: 9px 18px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" style="padding: 9px 18px; background: #0ea5e9; color: white; text-decoration: none; border-radius: 8px; font-size: 14px; font-weight: 600;">
                    Log in
                </a>
            @endauth
        </div>
    </div>
</nav>

{{-- ============ BANNER ============ --}}
@php
    $bannerExists = $event->banner && file_exists(storage_path('app/public/' . $event->banner));
    $imageExists  = $event->image  && file_exists(storage_path('app/public/' . $event->image));
@endphp

@if($bannerExists)
    <div style="width: 100%; height: 400px; overflow: hidden; background: linear-gradient(135deg, #0ea5e9, #6366f1);">
        <img src="{{ asset('storage/' . $event->banner) }}" alt="{{ $event->name }}"
             style="width: 100%; height: 100%; object-fit: cover; display: block;">
    </div>
@else
    <div style="width: 100%; height: 240px; background: linear-gradient(135deg, #0ea5e9, #6366f1); display: flex; align-items: center; justify-content: center;">
        <h1 style="color: white; font-size: 34px; font-weight: 800; text-align: center; padding: 0 24px; margin: 0; letter-spacing: -0.02em;">
            {{ $event->name }}
        </h1>
    </div>
@endif

{{-- ============ MAIN ============ --}}
<main style="max-width: 1200px; margin: 0 auto; padding: 32px 24px;">

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 32px; align-items: flex-start;">

        {{-- ============ LEFT COLUMN ============ --}}
        <div>

            {{-- Event Header --}}
            <div style="display: flex; gap: 24px; margin-bottom: 32px; flex-wrap: wrap;">

                @if($imageExists)
                    <div style="width: 180px; height: 180px; border-radius: 16px; overflow: hidden; background: #e5e7eb; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->name }}"
                             style="width: 100%; height: 100%; object-fit: cover; display: block;">
                    </div>
                @else
                    <div style="width: 180px; height: 180px; border-radius: 16px; background: linear-gradient(135deg, #0ea5e9, #6366f1); flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: white; font-size: 64px; font-weight: 800;">
                        {{ strtoupper(substr($event->name, 0, 1)) }}
                    </div>
                @endif

                <div style="flex: 1; min-width: 260px;">
                    @if($event->eventType)
                        <span style="display: inline-block; padding: 5px 14px; background: #e0f2fe; color: #075985; border-radius: 999px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">
                            {{ $event->eventType->name }}
                        </span>
                    @endif

                    <h1 style="font-size: 36px; font-weight: 800; color: #111827; margin: 0 0 20px 0; line-height: 1.15; letter-spacing: -0.02em;">
                        {{ $event->name }}
                    </h1>

                    <div style="display: flex; flex-direction: column; gap: 12px; font-size: 15px;">
                        <div style="display: flex; align-items: center; gap: 10px; color: #374151;">
                            <svg style="width: 18px; height: 18px; color: #0ea5e9; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2"/>
                                <path d="M16 2v4M8 2v4M3 10h18"/>
                            </svg>
                            <span style="font-weight: 500;">
                                {{ \Carbon\Carbon::parse($event->date)->format('l, F j, Y') }}
                                @if($event->end_date)
                                    &nbsp;·&nbsp;
                                    {{ \Carbon\Carbon::parse($event->date)->format('g:i A') }}
                                    –
                                    {{ \Carbon\Carbon::parse($event->end_date)->format('g:i A') }}
                                @endif
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px; color: #374151;">
                            <svg style="width: 18px; height: 18px; color: #0ea5e9; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/>
                                <circle cx="12" cy="9" r="2.5"/>
                            </svg>
                            <span style="font-weight: 500;">{{ $event->location }}</span>
                        </div>

                        @if($event->merchant)
                            <div style="display: flex; align-items: center; gap: 10px; color: #374151;">
                                <svg style="width: 18px; height: 18px; color: #0ea5e9; flex-shrink: 0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                </svg>
                                <span style="font-weight: 500;">Organized by {{ $event->merchant->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- About --}}
            @if($event->description)
                <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 28px; margin-bottom: 24px;">
                    <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 16px 0;">About This Event</h2>
                    <div style="font-size: 15px; color: #374151; line-height: 1.7;">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                </div>
            @endif

            {{-- Ticket Types --}}
            <div id="ticketSection" style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 28px;">
                <h2 style="font-size: 20px; font-weight: 700; color: #111827; margin: 0 0 20px 0;">Choose Your Tickets</h2>

                @if($event->tickets && $event->tickets->count())
                    @php
                        // Find the cheapest on-sale ticket to mark as "Best Price"
                        $onSaleTickets = $event->tickets->filter(fn($t) => $t->sale_status === 'on_sale');
                        $bestPriceId = $onSaleTickets->count() ? $onSaleTickets->sortBy('price')->first()->id : null;
                    @endphp

                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        @foreach($event->tickets as $ticket)
                            @php
                                $status = $ticket->sale_status;
                                $statusLabel = $ticket->sale_status_label;
                                $statusColor = $ticket->sale_status_color;
                                $canBuy = $status === 'on_sale';
                                $remaining = $ticket->available_quantity;
                                $isBestPrice = ($ticket->id === $bestPriceId);
                            @endphp

                            <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; transition: border-color 0.15s; {{ $canBuy ? '' : 'opacity: 0.75;' }}"
                                 onmouseover="if({{ $canBuy ? 'true' : 'false' }}) this.style.borderColor='#0ea5e9';"
                                 onmouseout="this.style.borderColor='#e5e7eb';">

                                <div style="flex: 1; min-width: 180px;">

                                    {{-- Ticket name + status badge + best price --}}
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap;">
                                        <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">
                                            {{ $ticket->name }}
                                        </h3>
                                        <span style="display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: {{ $statusColor['bg'] }}; color: {{ $statusColor['fg'] }};">
                                            {{ $statusLabel }}
                                        </span>
                                        @if($isBestPrice)
                                            <span style="display: inline-block; padding: 3px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; background: #fef3c7; color: #92400e;">
                                                ⭐ Best Price
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Meta info --}}
                                    <div style="font-size: 13px; color: #6b7280;">
                                        @if($status === 'on_sale')
                                            {{ number_format($remaining) }} {{ \Illuminate\Support\Str::plural('ticket', $remaining) }} available
                                            @if($ticket->end_date)
                                                · Sales close {{ $ticket->end_date->format('M j, g:i A') }}
                                            @endif
                                        @elseif($status === 'coming_soon')
                                            Sales open {{ $ticket->start_date->format('M j, Y · g:i A') }}
                                        @elseif($status === 'ended')
                                            Sales closed {{ $ticket->end_date->format('M j, Y') }}
                                        @elseif($status === 'sold_out')
                                            No tickets remaining
                                        @else
                                            Currently unavailable
                                        @endif
                                    </div>
                                </div>

                                <div style="text-align: right; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                                    <div style="font-size: 22px; font-weight: 800; color: {{ $canBuy ? '#0c4a6e' : '#9ca3af' }};">
                                        {{ $ticket->currency }} {{ number_format($ticket->price, 2) }}
                                    </div>

                                    @if($canBuy)
                                        <button type="button"
                                                onclick="buyTicket({{ $ticket->id }}, '{{ addslashes($ticket->name) }}', {{ $ticket->price }}, '{{ $ticket->currency }}')"
                                                style="padding: 10px 24px; background: #0ea5e9; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background 0.15s;"
                                                onmouseover="this.style.background='#0284c7';"
                                                onmouseout="this.style.background='#0ea5e9';">
                                            Buy Ticket
                                        </button>
                                    @elseif($status === 'coming_soon')
                                        <button type="button" disabled
                                                style="padding: 10px 24px; background: #dbeafe; color: #1e40af; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: not-allowed;">
                                            Coming Soon
                                        </button>
                                    @elseif($status === 'sold_out')
                                        <button type="button" disabled
                                                style="padding: 10px 24px; background: #fee2e2; color: #991b1b; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: not-allowed;">
                                            Sold Out
                                        </button>
                                    @else
                                        <button type="button" disabled
                                                style="padding: 10px 24px; background: #f3f4f6; color: #6b7280; border: none; border-radius: 8px; font-size: 14px; font-weight: 700; cursor: not-allowed;">
                                            Sales Ended
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Hint: If an Early Bird is ended and Regular is on sale --}}
                    @php
                        $endedEarlyBird = $event->tickets->first(fn($t) => $t->sale_status === 'ended' && stripos($t->name, 'early') !== false);
                        $activeRegular  = $event->tickets->first(fn($t) => $t->sale_status === 'on_sale' && stripos($t->name, 'early') === false);
                    @endphp

                    @if($endedEarlyBird && $activeRegular)
                        <div style="margin-top: 20px; padding: 14px 18px; background: #f0f9ff; border-left: 4px solid #0ea5e9; border-radius: 10px; font-size: 14px; color: #0c4a6e;">
                            <strong>{{ $endedEarlyBird->name }}</strong> has ended.
                            <strong>{{ $activeRegular->name }}</strong> is now on sale at
                            <strong>{{ $activeRegular->currency }} {{ number_format($activeRegular->price, 0) }}</strong>.
                        </div>
                    @endif

                @else
                    <div style="padding: 40px 20px; text-align: center; color: #6b7280;">
                        <p style="margin: 0 0 8px 0; font-size: 15px;">Tickets coming soon</p>
                        <p style="margin: 0; font-size: 13px;">Check back later for ticket availability.</p>
                    </div>
                @endif
            </div>

        </div>

        {{-- ============ RIGHT COLUMN — STICKY BUY CARD ============ --}}
        <div style="position: sticky; top: 96px;">

            <div style="background: white; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.06);">
                <div style="font-size: 13px; color: #6b7280; margin-bottom: 6px;">Tickets from</div>
                @php
                    $onSaleForMin = $event->tickets ? $event->tickets->filter(fn($t) => $t->sale_status === 'on_sale') : collect();
                    $minPrice = $onSaleForMin->count() ? $onSaleForMin->min('price') : null;
                @endphp

                @if($minPrice)
                    <div style="font-size: 32px; font-weight: 800; color: #0c4a6e; line-height: 1; margin-bottom: 20px;">
                        {{ $event->currency }} {{ number_format($minPrice, 0) }}
                    </div>
                @else
                    <div style="font-size: 18px; font-weight: 700; color: #6b7280; margin-bottom: 20px;">
                        No tickets on sale
                    </div>
                @endif

                <button type="button"
                        onclick="document.getElementById('ticketSection').scrollIntoView({behavior:'smooth'})"
                        style="width: 100%; padding: 14px; background: {{ $minPrice ? '#0ea5e9' : '#e5e7eb' }}; color: {{ $minPrice ? 'white' : '#9ca3af' }}; border: none; border-radius: 10px; font-size: 15px; font-weight: 700; cursor: {{ $minPrice ? 'pointer' : 'not-allowed' }}; margin-bottom: 16px;">
                    {{ $minPrice ? 'Get Tickets →' : 'View Tickets' }}
                </button>

                <div style="border-top: 1px solid #f3f4f6; padding-top: 16px; display: flex; flex-direction: column; gap: 10px; font-size: 13px; color: #6b7280;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #10b981;">✓</span> Secure M-Pesa payment
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #10b981;">✓</span> Instant QR ticket delivery
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #10b981;">✓</span> Zero hidden fees
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

{{-- ============ FOOTER ============ --}}
<footer style="padding: 40px 24px 32px; background: #111827; color: #9ca3af; margin-top: 64px;">
    <div style="max-width: 1200px; margin: 0 auto; text-align: center; font-size: 13px;">
        © {{ date('Y') }} Aiyo Tickets. All rights reserved.
    </div>
</footer>

{{-- ============ BUY TICKET HANDLER ============ --}}
<script>
async function buyTicket(ticketId, ticketName, price, currency) {
    @auth
        if (!confirm(`Buy "${ticketName}" for ${currency} ${price}?`)) return;

        try {
            const res = await fetch('{{ route("cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    ticket_id: ticketId,
                    quantity: 1,
                }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                window.location.href = '{{ route("cart.view") }}';
            } else {
                alert(data.message || 'Sorry, could not add this ticket to your cart.');
            }
        } catch (err) {
            alert('Network error. Please try again.');
        }
    @else
        window.location.href = '{{ route('login') }}';
    @endauth
}
</script>

</body>
</html>