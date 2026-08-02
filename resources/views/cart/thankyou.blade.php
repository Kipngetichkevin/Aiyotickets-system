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

    <div class="container mx-auto px-4 lg:px-8 py-20">
        <div class="max-w-2xl mx-auto bg-white shadow-lg rounded-xl p-8 text-center">
            <!-- Success Icon -->
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            <h1 class="text-3xl font-bold mb-2">Payment Successful! 🎉</h1>
            <p class="text-gray-600 mb-6">Your tickets have been confirmed and are ready.</p>

            <!-- Order Details -->
            <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                <h3 class="font-semibold text-lg mb-3">Order Details</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Order Number:</span>
                        <span class="font-medium">{{ $order->order_no }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Transaction Code:</span>
                        <span class="font-medium">{{ $order->transaction_code }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Amount:</span>
                        <span class="font-medium">KES {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tickets:</span>
                        <span class="font-medium">{{ $order->qty }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium">{{ $order->email }}</span>
                    </div>
                </div>
            </div>

            <!-- Attendees List -->
            @if($order->attendees->count() > 0)
                <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="font-semibold text-lg mb-3">Attendees ({{ $order->attendees->count() }})</h3>
                    <div class="space-y-2">
                        @foreach($order->attendees as $attendee)
                            <div class="flex justify-between text-sm border-b border-gray-200 pb-2">
                                <span>{{ $attendee->name }}</span>
                                <span class="text-purple-600">#{{ $attendee->qr_code }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('home') }}" 
                   class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Back to Home
                </a>
                <a href="{{ route('events.index') }}" 
                   class="px-6 py-3 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50 transition">
                    Browse More Events
                </a>
            </div>

            <p class="text-xs text-gray-500 mt-6">
                A confirmation email has been sent to your email address.
            </p>
        </div>
    </div>

    @include('includes.footer')
</body>
</html>