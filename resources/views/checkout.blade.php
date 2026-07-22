<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body>

@include('includes.header')

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-4">Checkout</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Left: Payment Method -->
        <div>
            <h2 class="text-xl font-semibold mb-4">Choose Payment Method</h2>
            <select id="payment-method" class="w-full p-2 border border-gray-300 rounded-md shadow-sm">
                <option value="mobile">Mobile Money (KES)</option>
                <option value="crypto">Cryptocurrency (SOL, USDT)</option>
                <option value="card">Card Payment (USD/KES)</option>
            </select>
        </div>

        <!-- Right: Payment Summary -->
        <div>
            <h2 class="text-xl font-semibold mb-4">Payment Summary</h2>
            <div id="payment-summary" class="p-4 border border-gray-300 rounded-md shadow-sm">
                <!-- Payment details like conversion rates and fees will be displayed dynamically -->
            </div>
        </div>
    </div>

    <div class="mt-8">
        <button class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Complete Payment</button>
    </div>
</div>

@include('includes.footer')

<script>
    // Update payment summary based on payment method
    document.getElementById('payment-method').addEventListener('change', function () {
        let paymentMethod = this.value;
        let paymentSummary = document.getElementById('payment-summary');
        let totalAmount = {{ $totalAmount }}; // Assuming totalAmount is passed from the controller

        switch (paymentMethod) {
            case 'mobile':
                paymentSummary.innerHTML = `<p>Amount in KES: ${totalAmount} KES</p>`;
                break;
            case 'crypto':
                let exchangeRate = 0.15; // Example exchange rate, replace with actual rate
                let amountInCrypto = totalAmount * exchangeRate;
                paymentSummary.innerHTML = `<p>Amount in SOL/USDT: ${amountInCrypto.toFixed(2)} (based on current exchange rate)</p>`;
                break;
            case 'card':
                let exchangeRateUSD = 0.009; // Example exchange rate, replace with actual rate
                let amountInUSD = totalAmount * exchangeRateUSD;
                paymentSummary.innerHTML = `<p>Amount in USD: ${amountInUSD.toFixed(2)} USD</p>`;
                break;
        }
    });

    // Trigger change event on page load to display initial summary
    document.getElementById('payment-method').dispatchEvent(new Event('change'));
</script>

</body>
</html>
