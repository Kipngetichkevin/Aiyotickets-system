@vite('resources/css/app.css')

<body class="bg-white">
@include('includes.header')

<!-- Full-width Background Section -->
<div class="relative w-full overflow-hidden">
    <!-- Background Image Container -->
    <div class="absolute inset-0 -z-10">
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 to-transparent"></div>
        <img 
            src="{{ asset($event->image) }}" 
            alt="{{ $event->name }}" 
            class="w-full h-full object-cover object-center"
            loading="lazy"
        >
    </div>

    <!-- Content Container -->
    <div class="min-h-[50vh] md:min-h-[75vh] flex items-center justify-center relative">
        <div class="max-w-6xl w-full px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
            <!-- Centered Event Details -->
            <div class="text-center text-white space-y-6">
                <h1 class="text-4xl md:text-6xl font-bold mb-4 drop-shadow-lg">
                    {{ $event->name }}
                </h1>

                <!-- Event Details in One Line -->
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

                <!-- Merchant Name with Background -->
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

    <!-- Two-column layout -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

        <!-- Left: Event Details -->
        <div class="bg-white rounded-xl shadow-lg p-6 h-fit sticky top-8">
            <h1 class="text-3xl font-bold mb-4"> Description</h1>
            <!-- <p class="text-gray-700 mt-2"><strong>Date:</strong> {{ \Carbon\Carbon::parse($event->date)->format('D d M') }}</p> -->
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
                <div class="mb-4 border p-4 rounded-lg shadow-sm">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-xl font-semibold">{{ $ticketType->name }}</h3>
                            <p class="text-sm text-gray-600">{{ $ticketType->description }}</p>
                        </div>
                        <div>
                            <span class="text-lg font-bold">KES{{ $ticketType->price }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-4">
                        <div>
                            <!-- Quantity Counter -->
                            <button class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300"
                                onclick="updateQuantity({{ $ticketType->id }}, -1, {{ $ticketType->price }})">-</button>
                            <span id="ticket-quantity-{{ $ticketType->id }}" class="mx-2">0</span>
                            <button class="px-3 py-1 bg-gray-200 rounded-lg hover:bg-gray-300"
                                onclick="updateQuantity({{ $ticketType->id }}, 1, {{ $ticketType->price }})">+</button>
                        </div>
                        <div>
                            <!-- Subtotal for ticket -->
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
    // Initialize quantities and totals
    let cart = {};

    function updateQuantity(ticketTypeId, change, price) {
        if (!cart[ticketTypeId]) {
            cart[ticketTypeId] = {
                quantity: 0,
                price: price
            };
        }

        cart[ticketTypeId].quantity += change;
        if (cart[ticketTypeId].quantity < 0) cart[ticketTypeId].quantity = 0;

        // Update quantity and subtotal in the UI
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
            name: '{{ $event->name }}',
            price: cart[ticketTypeId].price
        })).filter(item => item.quantity > 0);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                cart: cartData
            })
        }).then(response => {
            if (response.ok) {
                window.location.href = `{{ route('cart.view') }}`;
            } else {
                alert('Failed to add items to cart. Please try again.');
            }
        }).catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
</script>

</body>

</html>