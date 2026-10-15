@php
    $appName  = \App\Helpers\SystemHelper::appName();
    $metaDesc = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($event->description ?? ''))), 160)
        ?: $event->name . ' — ' . ($event->location ?? '') . ' · Tickets on ' . $appName;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $event->name }} | {{ $appName }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $appName }}">
    <meta property="og:title" content="{{ $event->name }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($event->image_url)
        <meta property="og:image" content="{{ $event->image_url }}">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
@include('includes.header')

<main>

    {{-- HERO --}}
    <div class="relative w-full overflow-hidden">
        <div class="absolute inset-0">
            @if($event->image_url)
                <img src="{{ $event->image_url }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
            @elseif($event->image)
                <img src="{{ asset('storage/' . ltrim($event->image, 'storage/')) }}" alt="{{ $event->name }}" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/40"></div>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="text-white space-y-5">

                <div class="flex flex-wrap items-center gap-2">
                    @if($event->age_restriction)
                        <span class="inline-flex items-center rounded-full bg-purple-500/90 px-3 py-1 text-xs font-semibold text-white shadow-lg backdrop-blur-sm">
                            Ages {{ $event->age_restriction }}+
                        </span>
                    @endif
                    @if($event->merchant?->name)
                        <span class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white backdrop-blur-sm">
                            by {{ $event->merchant->name }}
                        </span>
                    @endif
                    @if($event->status === 'active')
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-500/90 px-3 py-1 text-xs font-semibold text-white shadow-lg backdrop-blur-sm">
                            ● Live
                        </span>
                    @endif
                </div>

                <h1 class="text-4xl md:text-6xl font-bold drop-shadow-lg leading-tight">
                    {{ $event->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @if($event->date)
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->date)->format('D, d M Y') }}
                            @if($event->end_date && $event->end_date != $event->date)
                                – {{ \Carbon\Carbon::parse($event->end_date)->format('d M Y') }}
                            @endif
                        </div>
                    @endif

                    @if($event->date)
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ \Carbon\Carbon::parse($event->date)->format('g:i A') }}
                            @if($event->end_date)
                                – {{ \Carbon\Carbon::parse($event->end_date)->format('g:i A') }}
                            @endif
                        </div>
                    @endif

                    @if($event->location)
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            {{ $event->location }}
                        </div>
                    @endif
                </div>

                {{-- COUNTDOWN --}}
                @if($event->date && \Carbon\Carbon::parse($event->date)->isFuture())
                    @php
                        $hoursUntil = now()->diffInHours(\Carbon\Carbon::parse($event->date), false);
                    @endphp
                    <div x-data="eventCountdown('{{ \Carbon\Carbon::parse($event->date)->toIso8601String() }}')"
                         x-init="start()">
                        <div class="inline-flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl backdrop-blur-md border border-white/10 px-5 py-3 shadow-2xl {{ $hoursUntil < 24 ? 'bg-red-600/50' : 'bg-black/50' }}">
                            <div class="flex items-center gap-2 text-white/80">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-widest">Starts in</span>
                            </div>
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="days"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Days</div>
                                </div>
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="hours"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Hrs</div>
                                </div>
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="mins"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Mins</div>
                                </div>
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="secs"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Secs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- BODY --}}
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT: Description --}}
            <div class="lg:col-span-2 space-y-8">

                @if($event->description)
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs sm:p-8">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                     class="h-5 w-5">
                                    <path d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-800">About This Event</h2>
                        </div>
                        <div class="prose prose-sm max-w-none text-gray-600">
                            {!! \App\Support\HtmlSanitizer::clean($event->description) !!}
                        </div>
                    </div>
                @endif

            </div>

            {{-- RIGHT: Ticket Selection --}}
            <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-6 lg:self-start">

                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs">
                    <h2 class="text-lg font-bold text-gray-900 mb-1">Select Tickets</h2>
                    <p class="text-xs text-gray-500 mb-5">Pick your tickets and quantities. Guests can add to cart — you'll only need to log in at checkout.</p>

                    @if($closedReason = $event->salesClosedReason())
                        <p class="rounded-xl bg-gray-100 px-4 py-3 text-sm font-medium text-gray-700">{{ $closedReason }}</p>
                    @elseif($event->ticketTypes->count() > 0)
                        <div class="space-y-3">
                            @foreach($event->ticketTypes as $ticketType)
                                @php($sale = $ticketType->saleStatus())
                                @php($canBuy = $ticketType->isPurchasable())
                                <div class="rounded-xl border border-gray-200 p-4 {{ $canBuy ? '' : 'opacity-60' }}">
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="min-w-0">
                                            <h3 class="font-semibold text-gray-900 truncate">{{ $ticketType->name }}</h3>
                                            @if($ticketType->description)
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $ticketType->description }}</p>
                                            @endif
                                            @if($sale === 'upcoming')
                                                <span class="mt-1 inline-flex rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700">Not on sale yet · opens {{ $ticketType->start_date->format('d M, H:i') }}</span>
                                            @elseif($sale === 'ended')
                                                <span class="mt-1 inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600">Sales ended</span>
                                            @elseif($ticketType->end_date)
                                                <span class="mt-1 inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-700">Sales end {{ $ticketType->end_date->format('d M, H:i') }}</span>
                                            @endif
                                        </div>
                                        <p class="text-base font-bold text-gray-900 shrink-0">
                                            {{ $event->currency ?? 'KES' }} {{ number_format((float) $ticketType->price, 0) }}
                                        </p>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between">
                                        <div class="inline-flex items-center rounded-lg border border-gray-200 bg-white">
                                            <button type="button"
                                                    class="flex h-8 w-8 items-center justify-center text-gray-600 hover:bg-gray-50 rounded-l-lg font-bold"
                                                    onclick="updateQuantity({{ $ticketType->id }}, -1, {{ $ticketType->price }})">−</button>
                                            <span id="ticket-quantity-{{ $ticketType->id }}"
                                                  class="px-3 text-sm font-semibold tabular-nums">0</span>
                                            <button type="button"
                                                    class="flex h-8 w-8 items-center justify-center text-gray-600 hover:bg-gray-50 rounded-r-lg font-bold"
                                                    @disabled(!$canBuy)
                                                    onclick="updateQuantity({{ $ticketType->id }}, 1, {{ $ticketType->price }})">+</button>
                                        </div>
                                        <span id="ticket-subtotal-{{ $ticketType->id }}"
                                              class="text-sm font-semibold text-gray-700">
                                            KES 0
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 rounded-xl bg-gradient-to-br from-blue-50 to-purple-50 p-4 border border-blue-100">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-gray-800">Total</span>
                                <span id="grand-total" class="text-xl font-bold text-blue-700">KES 0.00</span>
                            </div>
                        </div>

                        <button type="button"
                                onclick="proceedToCheckout()"
                                class="mt-4 w-full rounded-xl bg-blue-600 text-white px-5 py-3 font-bold hover:bg-blue-700 transition shadow-lg shadow-blue-300/40">
                            Add to Cart
                        </button>

                        <p class="mt-4 text-[11px] text-gray-500 leading-relaxed">
                            By purchasing these ticket(s) you agree to be bound by
                            <a href="{{ route('terms') }}" class="text-blue-600 hover:underline">Aiyo Tickets' Terms &amp; Conditions</a>.
                            {{ \App\Support\RefundPolicy::text($event) }}
                            <a href="{{ route('refund-policy') }}" class="text-blue-600 hover:underline">Refund policy</a>
                        </p>
                    @else
                        <p class="text-sm text-gray-500">No tickets available yet.</p>
                    @endif
                </div>

                {{-- Quick links --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('cart.view') }}"
                           class="flex-1 text-center py-2 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            View Cart
                        </a>
                        <a href="{{ route('events.browse') }}"
                           class="flex-1 text-center py-2 rounded-lg border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                            Browse More
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

@include('includes.footer')

{{-- TOAST --}}
<div id="exp-toast"
     style="display:none;"
     class="fixed bottom-6 right-6 z-[100000] rounded-lg px-5 py-3 text-sm font-medium text-white shadow-xl">
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('eventCountdown', (targetIso) => ({
        days: '00', hours: '00', mins: '00', secs: '00',
        interval: null,
        target: new Date(targetIso).getTime(),

        pad(n) { return String(n).padStart(2, '0'); },

        start() {
            this.tick();
            this.interval = setInterval(() => this.tick(), 1000);
        },

        tick() {
            const diff = this.target - Date.now();
            if (diff <= 0) {
                this.days = this.hours = this.mins = this.secs = '00';
                if (this.interval) clearInterval(this.interval);
                return;
            }
            this.days  = this.pad(Math.floor(diff / 86400000));
            this.hours = this.pad(Math.floor((diff % 86400000) / 3600000));
            this.mins  = this.pad(Math.floor((diff % 3600000) / 60000));
            this.secs  = this.pad(Math.floor((diff % 60000) / 1000));
        },

        destroy() { if (this.interval) clearInterval(this.interval); }
    }));
});

// TICKET CART
let cart = {};

function updateQuantity(ticketTypeId, change, price) {
    if (!cart[ticketTypeId]) {
        cart[ticketTypeId] = { quantity: 0, price: price };
    }
    cart[ticketTypeId].quantity += change;
    if (cart[ticketTypeId].quantity < 0) cart[ticketTypeId].quantity = 0;

    document.getElementById(`ticket-quantity-${ticketTypeId}`).innerText = cart[ticketTypeId].quantity;
    document.getElementById(`ticket-subtotal-${ticketTypeId}`).innerText =
        `KES ${(cart[ticketTypeId].quantity * cart[ticketTypeId].price).toFixed(2)}`;

    updateGrandTotal();
}

function updateGrandTotal() {
    let grandTotal = 0;
    for (let ticketTypeId in cart) {
        grandTotal += cart[ticketTypeId].quantity * cart[ticketTypeId].price;
    }
    document.getElementById('grand-total').innerText = `KES ${grandTotal.toFixed(2)}`;
}

function proceedToCheckout() {
    const url = `{{ route('cart.add') }}`;
    const cartData = Object.keys(cart).map(ticketTypeId => ({
        ticket_id: ticketTypeId,
        quantity: cart[ticketTypeId].quantity,
        event_id: {{ $event->id }},
        name: @json($event->name),
        price: cart[ticketTypeId].price
    })).filter(item => item.quantity > 0);

    if (cartData.length === 0) {
        showToast('Please select at least one ticket.', 'error');
        return;
    }

    fetch(url, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ cart: cartData })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `{{ route('cart.view') }}`;
        } else {
            showToast(data.message || 'Failed to add items to cart.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// TOAST
function showToast(message, type = 'success') {
    const el = document.getElementById('exp-toast');
    el.textContent = message;
    el.className = 'fixed bottom-6 right-6 z-[100000] rounded-lg px-5 py-3 text-sm font-medium text-white shadow-xl ' +
        (type === 'error' ? 'bg-red-600' : 'bg-green-600');
    el.style.display = 'block';
    clearTimeout(el._timer);
    el._timer = setTimeout(() => { el.style.display = 'none'; }, 3500);
}
</script>

</body>
</html>