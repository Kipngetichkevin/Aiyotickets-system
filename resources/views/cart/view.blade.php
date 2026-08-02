<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite('resources/css/app.css')
    <script src="https://cdn.jsdelivr.net/npm/preline"></script>
</head>

<body class="bg-white">
    @include('includes.header')

    <div class="container mx-auto px-4 lg:px-8 py-10">
        <h1 class="text-3xl font-bold mb-6 text-center">Your Cart</h1>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Cart Items -->
            <div class="lg:col-span-2 space-y-6">
                @forelse ($cart as $ticket_id => $item)
                    <div class="group bg-white shadow-sm rounded-xl p-6 border border-gray-200 hover:border-purple-500 transition-all">
                        <div class="flex gap-4">
                            <img src="{{ asset($item['event_image'] ?? 'images/default-event.jpg') }}" alt="{{ $item['event_name'] ?? 'Event' }}" 
                                class="w-24 h-24 rounded-lg object-cover border border-gray-200">
                            
                            <div class="flex-1">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $item['event_name'] ?? 'Event' }}</h3>
                                <div class="mt-2 flex flex-wrap items-center gap-4">
                                    <div class="flex items-center gap-2 text-sm text-gray-600">
                                        <span class="font-medium">{{ $item['quantity'] ?? 0 }} x {{ $item['ticket_name'] ?? 'Ticket' }}</span>
                                        <span class="mx-1">·</span>
                                        <span class="text-purple-600">KES {{ number_format($item['price'] ?? 0, 2) }}</span>
                                    </div>
                                    <div class="ml-auto">
                                        <span class="text-lg font-bold text-gray-900">
                                            KES {{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 2) }}
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
                    
                    <!-- Payment Method Selector -->
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <button type="button" id="mobile-money-btn" 
                            class="payment-tab active p-3 text-center rounded-lg border-2 border-purple-600 bg-purple-50 text-purple-600 font-medium transition-all">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="text-xs">Mobile Money</span>
                        </button>
                        
                        <button type="button" id="card-payment-btn" 
                            class="payment-tab p-3 text-center rounded-lg border-2 border-gray-200 hover:border-gray-300 text-gray-600 font-medium transition-all">
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="text-xs">Card</span>
                        </button>
                        
                        <button type="button" id="crypto-payment-btn" 
                            class="payment-tab p-3 text-center rounded-lg border-2 border-gray-200 hover:border-gray-300 text-gray-600 font-medium transition-all relative">
                            <span class="absolute -top-1 -right-1 bg-yellow-400 text-xs px-1.5 py-0.5 rounded-full">Soon</span>
                            <svg class="w-6 h-6 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="text-xs">Crypto</span>
                        </button>
                    </div>

                    <!-- Mobile Money Form -->
                    <div id="mobile-money-form" class="payment-form">
                        <div class="space-y-4">
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                                    Phone Number
                                </label>

                                <input type="tel" name="phone" id="phone" required
                                    class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500"
                                    placeholder="712345678">
                                <p class="text-xs text-gray-500 mt-1">
                                    Enter phone number without 0 (e.g., 712345678)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Card Payment Form (Coming Soon) -->
                    <div id="card-payment-form" class="payment-form hidden">
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <p class="text-gray-600 font-medium">Card Payments Coming Soon</p>
                            <p class="text-sm text-gray-500 mt-1">We're working on integrating card payments</p>
                        </div>
                    </div>

                    <!-- Crypto Payment Form (Coming Soon) -->
                    <div id="crypto-payment-form" class="payment-form hidden">
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-600 font-medium">Crypto Payments Coming Soon</p>
                            <p class="text-sm text-gray-500 mt-1">We're working on integrating cryptocurrency payments</p>
                        </div>
                    </div>

                    <!-- Personal Details -->
                    <div class="relative">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" id="name" required
                            class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500"
                            placeholder="John Doe">
                    </div>
                    
                    <div class="relative">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" name="email" id="email" required
                            class="py-2 px-3 block w-full border-gray-200 rounded-lg text-sm focus:border-purple-500 focus:ring-purple-500"
                            placeholder="you@example.com">
                    </div>

                    <!-- Gender Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gender</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2">
                                <input type="radio" name="gender" value="1" checked>
                                <span>Male</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="gender" value="2">
                                <span>Female</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" name="gender" value="3">
                                <span>Other</span>
                            </label>
                        </div>
                    </div>

                    <input type="hidden" name="payment_mode" id="payment_mode" value="Mobile Money">
                    <input type="hidden" name="gender" id="gender" value="1">

                    <!-- Terms -->
                    <div class="text-xs text-gray-500">
                        <p>By completing this purchase, you agree to our <a href="#" class="text-purple-600 hover:text-purple-700">Terms of Service</a> and acknowledge that tickets are non-refundable.</p>
                    </div>

                    <!-- Total Amount -->
                    <div class="border-t border-gray-200 pt-4 mt-4">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold">Total:</span>
                            <span class="text-2xl font-bold text-purple-600">
                                KES {{ number_format(array_sum(array_map(function($item) { 
                                    return ($item['quantity'] ?? 0) * ($item['price'] ?? 0); 
                                }, $cart)), 2) }}
                            </span>
                        </div>
                    </div>

                    <button type="button" id="checkout-btn"
                        class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-lg border border-transparent font-semibold bg-purple-600 text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all disabled:bg-gray-400 disabled:cursor-not-allowed"
                        {{ empty($cart) ? 'disabled' : '' }}>
                        Pay Now
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- PAYMENT MODALS                                   -->
    <!-- ============================================ -->

    <!-- Payment Waiting Modal -->
    <div id="payment-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <!-- Loading Spinner -->
                <div class="w-16 h-16 border-4 border-purple-600 border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
                
                <h3 class="text-xl font-bold mb-2">Waiting for Payment</h3>
                <p class="text-gray-600 mb-4" id="payment-status-text">
                    Please check your phone and enter your M-PESA PIN to complete payment.
                </p>
                
                <!-- Progress Bar -->
                <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                    <div id="payment-progress" class="bg-purple-600 h-2.5 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                
                <p class="text-xs text-gray-500" id="payment-timer">Waiting for confirmation...</p>
                
                <button type="button" id="cancel-payment-btn" 
                    class="mt-4 text-sm text-gray-500 hover:text-gray-700">
                    Cancel Payment
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Payment Successful!</h3>
                <p class="text-gray-600">Your tickets are being processed...</p>
                <div class="mt-4 animate-pulse">Redirecting...</div>
            </div>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-2">Payment Failed</h3>
                <p class="text-gray-600" id="error-message">Please try again or use a different payment method.</p>
                <button type="button" id="retry-payment-btn" 
                    class="mt-4 px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                    Try Again
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Payment status polling variables
            let paymentCheckInterval = null;
            let paymentCheckAttempts = 0;
            const MAX_ATTEMPTS = 90; // 90 * 2 seconds = 3 minutes max wait
            let currentCheckoutRequestId = null;
            let currentOrderId = null;
            let isOrderCreated = false;

            // Function to format phone number
            function formatPhoneNumber(phone) {
                let cleaned = phone.replace(/\D/g, '');
                cleaned = cleaned.replace(/^0+/, '');
                
                if (cleaned.length === 9 && (cleaned.startsWith('7') || cleaned.startsWith('1'))) {
                    return '254' + cleaned;
                }
                if (cleaned.startsWith('254') && cleaned.length === 12) {
                    return cleaned;
                }
                if (cleaned.startsWith('0')) {
                    return '254' + cleaned.substring(1);
                }
                if (cleaned.length === 10 && cleaned.startsWith('0')) {
                    return '254' + cleaned.substring(1);
                }
                return cleaned;
            }

            // Show payment waiting modal
            function showPaymentModal() {
                $('#payment-progress').css('width', '0%');
                $('#payment-status-text').text('Please check your phone and enter your M-PESA PIN to complete payment.');
                $('#payment-timer').text('Waiting for confirmation...');
                $('#payment-modal').removeClass('hidden').show();
            }

            // Update progress
            function updateProgress(attempts, maxAttempts) {
                const progress = Math.min((attempts / maxAttempts) * 100, 95);
                $('#payment-progress').css('width', progress + '%');
                $('#payment-timer').text(`Waiting for confirmation... (${Math.floor(attempts/2)}s)`);
            }

            // Hide payment modal
            function hidePaymentModal() {
                $('#payment-modal').hide();
            }

            // Show success modal
            function showSuccessModal() {
                hidePaymentModal();
                $('#success-modal').removeClass('hidden').show();
                setTimeout(function() {
                    window.location.href = '/cart/thankyou?order=' + currentOrderId;
                }, 2000);
            }

            // Show error modal
            function showErrorModal(message) {
                hidePaymentModal();
                $('#error-message').text(message || 'Payment failed. Please try again.');
                $('#error-modal').removeClass('hidden').show();
            }

            // Create order after payment confirmation
            function createOrderAfterPayment(checkoutRequestId) {
                if (isOrderCreated) {
                    return; // Prevent duplicate
                }
                
                isOrderCreated = true;
                clearInterval(paymentCheckInterval);
                
                $('#payment-status-text').text('✅ Payment confirmed! Creating your order...');
                $('#payment-progress').css('width', '100%');
                
                $.ajax({
                    url: "{{ route('cart.create-order') }}",
                    method: "POST",
                    data: {
                        checkout_request_id: checkoutRequestId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            currentOrderId = response.data.order_id;
                            $('#checkout-btn').prop('disabled', false).text('Pay Now');
                            showSuccessModal();
                        } else {
                            showErrorModal(response.message || 'Failed to create order. Please contact support.');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Failed to create order. Please contact support.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        showErrorModal(message);
                    }
                });
            }

            // Check payment status
            function checkPaymentStatus(checkoutRequestId) {
                if (paymentCheckInterval) {
                    clearInterval(paymentCheckInterval);
                }
                
                currentCheckoutRequestId = checkoutRequestId;
                paymentCheckAttempts = 0;
                isOrderCreated = false;
                
                showPaymentModal();
                
                paymentCheckInterval = setInterval(function() {
                    paymentCheckAttempts++;
                    updateProgress(paymentCheckAttempts, MAX_ATTEMPTS);
                    
                    $.ajax({
                        url: '/mpesa/status/' + checkoutRequestId,
                        method: 'GET',
                        success: function(response) {
                            if (response.success && response.data) {
                                console.log('Payment status:', response.data.status);
                                
                                if (response.data.status === 'completed') {
                                    clearInterval(paymentCheckInterval);
                                    // Payment confirmed - create order now
                                    createOrderAfterPayment(checkoutRequestId);
                                    
                                } else if (response.data.status === 'failed') {
                                    clearInterval(paymentCheckInterval);
                                    $('#checkout-btn').prop('disabled', false).text('Pay Now');
                                    showErrorModal('Payment failed. Please try again.');
                                }
                            }
                            
                            // If too many attempts, stop polling
                            if (paymentCheckAttempts >= MAX_ATTEMPTS) {
                                clearInterval(paymentCheckInterval);
                                $('#checkout-btn').prop('disabled', false).text('Pay Now');
                                
                                // Try one last query to M-Pesa directly
                                $.ajax({
                                    url: '/mpesa/query/' + checkoutRequestId,
                                    method: 'GET',
                                    success: function(queryResponse) {
                                        if (queryResponse.success && queryResponse.data && queryResponse.data.is_ready_for_order) {
                                            createOrderAfterPayment(checkoutRequestId);
                                        } else {
                                            showErrorModal('Payment is taking longer than expected. Please check your M-PESA app.');
                                        }
                                    },
                                    error: function() {
                                        showErrorModal('Payment is taking longer than expected. Please check your M-PESA app.');
                                    }
                                });
                            }
                        },
                        error: function() {
                            // Silent error - continue polling
                            console.log('Status check error, continuing...');
                        }
                    });
                }, 2000); // Check every 2 seconds
            }

            // Payment tab switching
            $('.payment-tab').on('click', function() {
                $('.payment-tab').removeClass('active border-purple-600 bg-purple-50 text-purple-600')
                    .addClass('border-gray-200 text-gray-600');
                
                $(this).addClass('active border-purple-600 bg-purple-50 text-purple-600')
                    .removeClass('border-gray-200 text-gray-600');
                
                $('.payment-form').addClass('hidden');
                
                const tabId = $(this).attr('id');
                if (tabId === 'mobile-money-btn') {
                    $('#mobile-money-form').removeClass('hidden');
                    $('#payment_mode').val('Mobile Money');
                    $('#phone').prop('required', true);
                    $('#phone').closest('.relative').show();
                } else if (tabId === 'card-payment-btn') {
                    $('#card-payment-form').removeClass('hidden');
                    $('#payment_mode').val('Card');
                    $('#phone').prop('required', false);
                    $('#phone').closest('.relative').hide();
                } else if (tabId === 'crypto-payment-btn') {
                    $('#crypto-payment-form').removeClass('hidden');
                    $('#payment_mode').val('Crypto');
                    $('#phone').prop('required', false);
                    $('#phone').closest('.relative').hide();
                }
            });

            // Capture gender selection
            $('input[name="gender"]').on('change', function() {
                $('#gender').val($(this).val());
            });

            // Format phone number on input
            $('#phone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                
                if (value.startsWith('0')) {
                    value = value.substring(1);
                }
                
                if (!value.startsWith('254') && value.length > 0) {
                    if (value.length === 9 && (value.startsWith('7') || value.startsWith('1'))) {
                        value = '254' + value;
                    }
                }
                
                if (value.length > 12) {
                    value = value.substring(0, 12);
                }
                
                $(this).val(value);
            });

            // Handle checkout button click
            $('#checkout-btn').on('click', function() {
                const form = $('#checkout-form')[0];
                const $btn = $(this);
                
                // Format phone number before submission
                const phoneInput = $('#phone');
                if (phoneInput.is(':visible') && phoneInput.prop('required')) {
                    let phone = phoneInput.val().replace(/\D/g, '');
                    phone = phone.replace(/^0+/, '');
                    
                    if (phone.length === 9 && (phone.startsWith('7') || phone.startsWith('1'))) {
                        phone = '254' + phone;
                    } else if (phone.startsWith('254') && phone.length === 12) {
                        // Valid
                    } else if (phone.length === 10 && phone.startsWith('0')) {
                        phone = '254' + phone.substring(1);
                    } else {
                        alert('Please enter a valid phone number (e.g., 712345678 or 254712345678)');
                        return;
                    }
                    phoneInput.val(phone);
                }
                
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                $btn.prop('disabled', true).text('Sending STK Push...');

                const formData = $('#checkout-form').serialize();
                
                $.ajax({
                    url: "{{ route('cart.checkout') }}",
                    method: "POST",
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            if (response.data && response.data.checkout_request_id) {
                                // Start checking payment status
                                $btn.text('Waiting for payment...');
                                checkPaymentStatus(response.data.checkout_request_id);
                            } else {
                                $btn.prop('disabled', false).text('Pay Now');
                                alert(response.message || 'STK Push sent successfully!');
                            }
                        } else {
                            $btn.prop('disabled', false).text('Pay Now');
                            alert(response.message || 'Something went wrong');
                        }
                    },
                    error: function(xhr, status, error) {
                        $btn.prop('disabled', false).text('Pay Now');
                        
                        let message = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        } else if (xhr.status === 400) {
                            message = 'Please check your details and try again';
                        }
                        alert(message);
                        console.error('Error:', xhr.responseJSON || error);
                    }
                });
            });

            // Cancel payment
            $('#cancel-payment-btn').on('click', function() {
                if (confirm('Are you sure you want to cancel this payment?')) {
                    clearInterval(paymentCheckInterval);
                    hidePaymentModal();
                    $('#checkout-btn').prop('disabled', false).text('Pay Now');
                }
            });

            // Retry payment
            $('#retry-payment-btn').on('click', function() {
                $('#error-modal').hide();
                location.reload();
            });

            // Auto-format phone number on blur
            $('#phone').on('blur', function() {
                let phone = $(this).val().replace(/\D/g, '');
                
                if (phone.length > 0) {
                    phone = phone.replace(/^0+/, '');
                    
                    if (phone.length === 9 && (phone.startsWith('7') || phone.startsWith('1'))) {
                        phone = '254' + phone;
                    }
                    
                    if (phone.length === 10 && phone.startsWith('0')) {
                        phone = '254' + phone.substring(1);
                    }
                    
                    $(this).val(phone);
                }
            });
        });
    </script>

    @include('includes.footer')
</body>
</html>