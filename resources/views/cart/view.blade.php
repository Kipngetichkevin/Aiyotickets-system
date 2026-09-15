<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/preline"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const mobileMoneyBtn = document.getElementById('mobile-money-btn');
            const cardPaymentBtn = document.getElementById('card-payment-btn');
            const mobileMoneyForm = document.getElementById('mobile-money-form');
            const cardPaymentForm = document.getElementById('card-payment-form');
            const genderInput = document.getElementById('gender'); // Hidden input for gender

            mobileMoneyBtn.addEventListener('click', () => {
                mobileMoneyForm.classList.remove('hidden');
                cardPaymentForm.classList.add('hidden');
                mobileMoneyBtn.classList.add('bg-blue-600', 'text-white');
                cardPaymentBtn.classList.remove('bg-blue-600', 'text-white');
            });

            cardPaymentBtn.addEventListener('click', () => {
                cardPaymentForm.classList.remove('hidden');
                mobileMoneyForm.classList.add('hidden');
                cardPaymentBtn.classList.add('bg-blue-600', 'text-white');
                mobileMoneyBtn.classList.remove('bg-blue-600', 'text-white');
            });

            // Capture gender selection
            const genderRadios = document.querySelectorAll('input[name="gender"]');
            genderRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    genderInput.value = this
                        .value; // Set the hidden input value to the selected gender
                });
            });
        });
    </script>
</head>

<body class="bg-white">
    @include('includes.Header')

    <div class="container mx-auto px-4 lg:px-8 py-10">
        <h1 class="text-3xl font-bold mb-6 text-center">Your Cart</h1>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-6">
                @forelse ($cart as $ticket_id => $item)
                    <div class="group bg-white shadow-sm rounded-xl p-6 border border-gray-200 hover:border-purple-500 transition-all">
                        <div class="flex gap-4">
                            <img src="{{ asset($item['event_image']) }}" alt="{{ $item['event_name'] }}" 
                                class="w-24 h-24 rounded-lg object-cover border border-gray-200">
                            
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $item['event_name'] }}</h3>
                                <div class="mt-2 flex flex-wrap items-center gap-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <span class="font-medium">{{ $item['quantity'] }} x {{ $item['ticket_name'] }}</span>
                                        <span class="mx-1">·</span>
                                        <span class="text-purple-600">KES {{ number_format($item['price'], 2) }}</span>
                                    </div>
                                    <div class="ml-auto">
                                        <span class="text-lg font-bold text-gray-900">
                                            KES {{ number_format($item['quantity'] * $item['price'], 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <form action="{{ route('cart.remove', ['ticket_id' => $ticket_id]) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="text-red-500 hover:text-red-600 transition-colors"
                                        onclick="return confirm('Are you sure you want to remove this ticket?')">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-xl p-8 text-center border-2 border-dashed border-gray-200">
                        <p class="text-gray-600">Your cart is empty</p>
                    </div>
                @endforelse
            </div>

            <!-- Payment Details Section -->
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 h-fit sticky top-8">
                <h3 class="text-xl font-semibold mb-4 text-center">Payment Details</h3>

                <form id="checkout-form" action="{{ route('cart.checkout') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="attendee_data" value="{{ json_encode($cart) }}">
                    <!-- Ensure this is included -->
                    <!-- Payment Method Selector -->
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <button type="button" id="mobile-money-btn"
                                class="w-full p-4 text-left rounded-lg border border-gray-200 hover:border-purple-500 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                                <div class="flex items-center gap-3">
                                    <!-- <div class="shrink-0">
                                        <img src="{{ asset('images/mpesa-logo.png') }}" class="w-8 h-8" alt="M-Pesa">
                                    </div> -->
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Mobile Money</span>
                                        <span class="block text-sm text-gray-500">M-PESA</span>
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>


                    <!-- Mobile Money Form -->
                    <div id="mobile-money-form">
                        <div class="space-y-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" name="phone" id="phone" required
                                    class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500"
                                    placeholder="254712345678">
                            </div>
                        </div>
                        <input type="hidden" name="payment_mode" value="Mobile Money">
                    </div>

                    <div class="relative">
                        <label for="name" class="bblock text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" id="name"
                            class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500"
                        placeholder="Your Name" required>
                    </div>
                    <div class="relative">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" id="email"
                            class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500"
                        placeholder="you@example.com" required>
                    </div>
                    <input type="hidden" name="gender" id="gender" value="3">
                    <!-- Hidden field for gender -->
                    <input type="hidden" name="qr_code" id="gender" value="0">
                    <!-- Hidden field for qr_code -->

                    <!-- Terms -->
                    <div class="text-xs text-gray-500">
                        <p>By completing this purchase, you agree to our <a href="#" class="text-purple-600 hover:text-purple-700">Terms of Service</a> and acknowledge that tickets are non-refundable.</p>
                    </div>

                    <button @disabled(empty($cart)) id="open-modal" type="button"
                        class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-lg border border-transparent font-semibold bg-purple-600 text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all disabled:bg-gray-400 disabled:cursor-not-allowed">
                        Checkout and Confirm Payment
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {

            const modal = $("#modal");

            // Validate form and open modal
            $("#open-modal").on("click", function() {
                const form = $("#checkout-form")[0];

                // Check if the form is valid
                if (form.checkValidity()) {
                    $('#open-modal').attr('disabled', true).text('Please wait ...')
                    var formData = $("#checkout-form").serialize();
                    // Send data via AJAX
                    $.ajax({
                        url: "{{ route('cart.checkout') }}", // Laravel route for checkout
                        method: "POST", // HTTP method
                        data: formData,
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                "content"), // CSRF token
                        },
                        success: function(response) {
                            // Handle success response

                            console.log("Success:", response);
                            $('#open-modal').attr('disabled', false).text(
                                'Checkout and Confirm Payment.')
                            if (response.success) {
                                window.location.reload();
                            } else {
                                alert(response.message)
                            }
                            // alert("Payment successfully processed!");
                        },
                        error: function(xhr, status, error) {
                            // Handle error response
                            console.error("Error:", error);
                            alert("An error occurred: " + xhr.responseJSON.message);
                        },
                    });
                } else {
                    // Highlight invalid fields
                    form.reportValidity();
                }
            });

            // Close modal buttons
            $("#close-modal, #close-modal-footer").on("click", function() {
                modal.addClass("hidden");
            });

            // Optional: Close modal when clicking outside modal content
            modal.on("click", function(e) {
                if ($(e.target).is("#modal")) {
                    modal.addClass("hidden");
                }
            });



        });
    </script>

    @include('includes.Footer')

</body>

</html>
