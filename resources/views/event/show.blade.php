@vite('resources/css/app.css')

<body class="bg-white">
@include('includes.header')

<!-- Full-width Background Section -->
<div class="relative w-full overflow-hidden">
    <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 to-transparent"></div>
        <img
            src="{{ asset($event->image) }}"
            alt="{{ $event->name }}"
            class="w-full h-full object-cover object-center"
            loading="lazy"
        >
    </div>

    <div class="min-h-[50vh] md:min-h-[75vh] flex items-center justify-center relative">
        <div class="max-w-6xl w-full px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
            <div class="text-center text-white space-y-6">
                <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                    {{ $event->name }}
                </h1>

                <div class="flex flex-col md:flex-row justify-center items-center gap-4 text-lg">
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('M d, Y') }}
                            @if($event->end_date)
                                – {{ \Carbon\Carbon::parse($event->end_date)->format('M d, Y') }}
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>
                            {{ \Carbon\Carbon::parse($event->date)->format('g:i A') }}
                            @if($event->end_date)
                                – {{ \Carbon\Carbon::parse($event->end_date)->format('g:i A') }}
                            @endif
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>{{ $event->location }}</span>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-center items-center gap-4 text-base mt-4">
                    <div class="flex items-center gap-2 bg-white text-black px-3 py-1 rounded-lg">
                        <span>{{ $event->merchant->name }}</span>
                    </div>

                    @if($event->age_restriction)
                    <div class="flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span>Ages {{ $event->age_restriction }}+</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Event Content -->
<div class="max-w-6xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <!-- Left: Event Details -->
        <div class="bg-white rounded-xl shadow-lg p-6 h-fit sticky top-8">
            <h1 class="text-3xl font-bold mb-4">Description</h1>
            <div class="mt-2">
                <strong class="text-gray-800 dark:text-neutral-200">Description:</strong>
                <div class="prose prose-sm max-w-none dark:prose-invert mt-1">
                    {!! $event->description !!}
                </div>
            </div>
        </div>

        <!-- Right: Ticket Selection -->
        <div class="bg-white rounded-xl shadow-lg p-6 h-fit sticky top-8">
            <h2 class="text-2xl font-semibold mb-4">Select Tickets</h2>

            @foreach ($event->ticketTypes as $ticketType)
                @php
                    $available = max(0, (int) $ticketType->quantity - (int) $ticketType->attendees()->sum('qty'));
                    $isSoldOut = $available <= 0;
                @endphp
                <div class="mb-4 border p-4 rounded-lg shadow-sm {{ $isSoldOut ? 'opacity-60 bg-gray-50' : '' }}">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold">
                                {{ $ticketType->name }}
                                @if($isSoldOut)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Sold Out</span>
                                @endif
                            </h3>
                            <p class="text-sm text-gray-600">{{ $ticketType->description }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $available }} of {{ $ticketType->quantity }} available
                            </p>
                        </div>
                        <div>
                            <span class="text-lg font-bold">KES{{ $ticketType->price }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4">
                        <div>
                            <button
                                class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="updateQuantity({{ $ticketType->id }}, -1)"
                                id="minus-{{ $ticketType->id }}"
                                @if($isSoldOut) disabled @endif>-</button>
                            <span id="ticket-quantity-{{ $ticketType->id }}" class="mx-2">0</span>
                            <button
                                class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                onclick="updateQuantity({{ $ticketType->id }}, 1)"
                                id="plus-{{ $ticketType->id }}"
                                @if($isSoldOut) disabled @endif>+</button>
                        </div>
                        <div>
                            <span id="ticket-subtotal-{{ $ticketType->id }}" class="font-bold">KES 0.00</span>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Grand Total -->
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <p class="text-sm text-gray-600 mb-2">
                    By registering and/or purchasing these ticket(s) you are agreeing to be bound by <a
                        href="{{ route('terms') }}" class="text-blue-600 underline hover:text-blue-800">Aiyo
                        Tickets' Terms & Conditions</a>, and by the <a href="#"
                        class="text-blue-600 underline hover:text-blue-800">Privacy Policy</a>.
                    <br />Tickets are subject to change without notice under event organizers advisory.
                    <br />The organizer of this event has a no-refund and ticket change policy.
                </p>
                <button class="mt-4 w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors" onclick="proceedToCheckout()">
                    Proceed to Checkout to Pay: <span id="grand-total">KES 0.00</span>
                </button>
            </div>
        </div>

    </div>
</div>

@include('includes.footer')

<script>
    // Per-ticket-type catalog from the server
    const ticketCatalog = {
        @foreach($event->ticketTypes as $t)
            {{ $t->id }}: {
                price: {{ (float) $t->price }},
                available: {{ max(0, (int) $t->quantity - (int) $t->attendees()->sum('qty')) }}
            },
        @endforeach
    };

    let cart = {};

    function updateQuantity(ticketTypeId, change) {
        const meta = ticketCatalog[ticketTypeId];
        if (!meta) return;

        if (!cart[ticketTypeId]) {
            cart[ticketTypeId] = { quantity: 0, price: meta.price };
        }

        const newQty = cart[ticketTypeId].quantity + change;

        if (newQty < 0) return;

        if (newQty > meta.available) {
            alert(`Only ${meta.available} ticket(s) available for this type.`);
            return;
        }

        cart[ticketTypeId].quantity = newQty;

        document.getElementById(`ticket-quantity-${ticketTypeId}`).innerText = newQty;
        document.getElementById(`ticket-subtotal-${ticketTypeId}`).innerText =
            `KES ${(newQty * meta.price).toFixed(2)}`;

        // Disable + when max reached
        const plusBtn = document.getElementById(`plus-${ticketTypeId}`);
        const minusBtn = document.getElementById(`minus-${ticketTypeId}`);
        if (plusBtn) plusBtn.disabled = newQty >= meta.available;
        if (minusBtn) minusBtn.disabled = newQty <= 0;

        updateGrandTotal();
    }

    function updateGrandTotal() {
        let grandTotal = 0;
        for (let id in cart) {
            grandTotal += cart[id].quantity * cart[id].price;
        }
        document.getElementById('grand-total').innerText = `KES ${grandTotal.toFixed(2)}`;
    }

    function proceedToCheckout() {
        const url = `{{ route('cart.add') }}`;

        const cartData = Object.keys(cart).map(ticketTypeId => ({
            ticket_id: parseInt(ticketTypeId),
            quantity: cart[ticketTypeId].quantity,
            event_id: {{ $event->id }},
            name: ticketCatalog[ticketTypeId] ? '{{ addslashes($event->name) }}' : '',
            price: cart[ticketTypeId].price
        })).filter(item => item.quantity > 0);

        if (cartData.length === 0) {
            alert('Please select at least one ticket.');
            return;
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ cart: cartData })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));

            if (response.ok && data.success) {
                window.location.href = `{{ route('cart.view') }}`;
            } else {
                alert(data.message || 'Failed to add items to cart. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Initialize button states on load
    document.addEventListener('DOMContentLoaded', () => {
        for (const id in ticketCatalog) {
            const plusBtn = document.getElementById(`plus-${id}`);
            const minusBtn = document.getElementById(`minus-${id}`);
            if (plusBtn) plusBtn.disabled = ticketCatalog[id].available <= 0;
            if (minusBtn) minusBtn.disabled = true;
        }
    });
</script>

</body>

</html>