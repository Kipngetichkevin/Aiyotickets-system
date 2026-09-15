<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body>
@include('includes.Header')

<div class="container mx-auto py-10">
    <h1 class="text-3xl font-bold mb-4">Payment</h1>

    <!-- Payment Method Selection -->
    <h2 class="text-2xl mb-4">Select Payment Method</h2>

    <div class="mb-6">
        <label class="inline-flex items-center">
            <input type="radio" name="payment-method" value="crypto" checked>
            <span class="ml-2">Crypto (Solana Wallet)</span>
        </label>
        <label class="inline-flex items-center ml-4">
            <input type="radio" name="payment-method" value="card">
            <span class="ml-2">Card Payment</span>
        </label>
        <label class="inline-flex items-center ml-4">
            <input type="radio" name="payment-method" value="mobile">
            <span class="ml-2">Mobile Money (Mpesa)</span>
        </label>
    </div>

    <!-- Crypto Payment -->
    <div id="crypto-payment" class="payment-form">
        <h3 class="text-xl font-bold">Crypto Payment</h3>
        <label class="block mt-2">
            Solana Wallet Address:
            <input type="text" id="sol-wallet" class="border rounded w-full px-4 py-2" placeholder="Enter your Solana wallet address">
        </label>
    </div>

    <!-- Card Payment -->
    <div id="card-payment" class="payment-form hidden">
        <h3 class="text-xl font-bold">Card Payment</h3>
        <label class="block mt-2">
            Card Number:
            <input type="text" id="card-number" class="border rounded w-full px-4 py-2" placeholder="Enter your card number">
        </label>
        <label class="block mt-2">
            Expiry Date:
            <input type="text" id="expiry-date" class="border rounded w-full px-4 py-2" placeholder="MM/YY">
        </label>
        <label class="block mt-2">
            CVC:
            <input type="text" id="cvc" class="border rounded w-full px-4 py-2" placeholder="Enter CVC">
        </label>
    </div>

    <!-- Mobile Money Payment -->
    <div id="mobile-payment" class="payment-form hidden">
        <h3 class="text-xl font-bold">Mobile Money Payment</h3>
        <label class="block mt-2">
            Mpesa Phone Number:
            <input type="text" id="mpesa-phone" class="border rounded w-full px-4 py-2" placeholder="Enter your phone number">
        </label>
    </div>

    <!-- Submit Payment -->
    <button onclick="processPayment()" class="bg-blue-600 text-white px-4 py-2 rounded mt-4">Submit Payment</button>
</div>

@include('includes.Footer')

<script>
    document.querySelectorAll('input[name="payment-method"]').forEach(input => {
        input.addEventListener('change', function() {
            document.querySelectorAll('.payment-form').forEach(form => form.classList.add('hidden'));
            document.getElementById(`${this.value}-payment`).classList.remove('hidden');
        });
    });

    function processPayment() {
        let paymentMethod = document.querySelector('input[name="payment-method"]:checked').value;
        let paymentData = {};

        if (paymentMethod === 'crypto') {
            paymentData = {
                mode: 'crypto',
                service: 'solana',
                wallet: document.getElementById('sol-wallet').value
            };
        } else if (paymentMethod === 'card') {
            paymentData = {
                mode: 'card',
                cardNumber: document.getElementById('card-number').value,
                expiry: document.getElementById('expiry-date').value,
                cvc: document.getElementById('cvc').value
            };
        } else if (paymentMethod === 'mobile') {
            paymentData = {
                mode: 'mobile',
                phone: document.getElementById('mpesa-phone').value
            };
        }

        // Save payment data to server and redirect to thank you page
        fetch("{{ route('payments.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(paymentData)
        }).then(() => {
            window.location.href = "{{ route('thank-you') }}";
        });
    }
</script>

</body>
</html>
