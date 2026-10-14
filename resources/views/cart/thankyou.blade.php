<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/preline"></script>
</head>

<body class="bg-white">
    @include('includes.header')

    <div class="container mx-auto px-4 lg:px-8 py-20">
        <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-xl p-8 text-center">

            {{-- Success Icon --}}
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>

            @if ($order->status === 'sold_out')
                <h1 class="text-3xl font-bold mb-2">Payment received, but it sold out</h1>
                <p class="text-gray-600 mb-6">
                    Your payment came through after your places were released, and they sold out in the meantime.
                    We'll refund <strong>KES {{ number_format($order->refund) }}</strong> to your M-Pesa
                    (receipt {{ $order->transaction_code ?: $order->order_no }}).
                </p>
            @elseif ($order->status === 'partial')
                <h1 class="text-3xl font-bold mb-2">Deposit received 🎉</h1>
                <p class="text-gray-600 mb-6">
                    Your place is held. Pay the balance of <strong>KES {{ number_format($order->balance_due) }}</strong>
                    by <strong>{{ $order->due_date?->format('d M Y') }}</strong> — any amount, any time — and your QR tickets will be sent right away.
                    <a href="{{ \App\Http\Controllers\InstalmentController::payLinkFor($order) }}" class="mt-3 block font-semibold text-purple-700 underline">Go to My instalments</a>
                </p>
            @else
            <h1 class="text-3xl font-bold mb-2">Payment Successful! 🎉</h1>
            <p class="text-gray-600 mb-6">
                @if($bookings->count() > 0 && $order->attendees->count() > 0)
                    Your tickets and experiences have been confirmed and are ready.
                @elseif($bookings->count() > 0)
                    Your experience booking has been confirmed and is ready.
                @else
                    Your tickets have been confirmed and are ready.
                @endif
            </p>
            @endif

            {{-- Order Details --}}
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
                        <span class="text-gray-600">Email:</span>
                        <span class="font-medium">{{ $order->email }}</span>
                    </div>
                </div>
            </div>

            {{-- ============================== --}}
            {{-- TICKETS SECTION                --}}
            {{-- ============================== --}}
            @if($order->attendees->count() > 0)
                <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="font-semibold text-lg mb-3">Tickets ({{ $order->attendees->count() }})</h3>
                    <div class="space-y-2">
                        @foreach($order->attendees as $attendee)
                            <div class="flex justify-between text-sm border-b border-gray-200 pb-2">
                                <span>{{ $attendee->name }}</span>
                                <span class="text-purple-600 font-mono">{{ $attendee->qr_code ? '#' . $attendee->qr_code : 'QR issued when fully paid' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ============================== --}}
            {{-- EXPERIENCES SECTION            --}}
            {{-- ============================== --}}
            @if($bookings->count() > 0)
                @foreach($bookings as $booking)
                    <div class="bg-white border-2 border-purple-200 rounded-lg p-6 mb-6 text-left overflow-hidden">

                        {{-- Experience header --}}
                        <div class="flex items-start gap-4 mb-4">
                            @if($booking->experience?->image)
                                <img src="{{ asset('storage/' . $booking->experience->image) }}"
                                     alt="{{ $booking->experience->name }}"
                                     class="w-24 h-24 object-cover rounded-lg flex-shrink-0">
                            @elseif($booking->experience?->poster_image)
                                <img src="{{ asset('storage/' . $booking->experience->poster_image) }}"
                                     alt="{{ $booking->experience->name }}"
                                     class="w-24 h-24 object-cover rounded-lg flex-shrink-0">
                            @endif
                            <div class="flex-1">
                                <p class="text-xs font-semibold text-purple-600 uppercase tracking-wide mb-1">
                                    Experience Booking
                                </p>
                                <h3 class="font-bold text-xl text-gray-900">
                                    {{ $booking->experience->name ?? 'Experience' }}
                                </h3>
                                @if($booking->experience?->location)
                                    <p class="text-sm text-gray-600 mt-1">
                                        📍 {{ $booking->experience->location }}
                                    </p>
                                @endif
                                @if($booking->experience?->start_date)
                                    <p class="text-sm text-gray-600">
                                        📅 {{ $booking->experience->start_date->format('d M Y') }}
                                        @if($booking->experience->end_date)
                                            – {{ $booking->experience->end_date->format('d M Y') }}
                                        @endif
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Package details --}}
                        <div class="bg-purple-50 rounded-lg p-4 mb-4">
                            <h4 class="font-semibold text-sm text-gray-900 mb-2">Package</h4>
                            <div class="space-y-1.5 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Package:</span>
                                    <span class="font-medium">{{ $booking->package?->name ?? 'Standard' }}</span>
                                </div>
                                @if($booking->category)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Race Category:</span>
                                        <span class="font-medium">{{ $booking->category->name }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Package Price:</span>
                                    <span class="font-medium">KES {{ number_format($booking->package_price, 2) }}</span>
                                </div>
                                @if($booking->category_surcharge > 0)
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Category Surcharge:</span>
                                        <span class="font-medium">KES {{ number_format($booking->category_surcharge, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between border-t border-purple-200 pt-1.5 mt-1.5">
                                    <span class="font-semibold text-gray-900">Total Paid:</span>
                                    <span class="font-bold text-purple-700">KES {{ number_format($booking->total_price, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Attendee details --}}
                        <div class="mb-4">
                            <h4 class="font-semibold text-sm text-gray-900 mb-2">Attendee</h4>
                            <div class="text-sm text-gray-700">
                                <p class="font-medium">{{ $booking->name }}</p>
                                @if($booking->email) <p class="text-gray-500">{{ $booking->email }}</p> @endif
                                @if($booking->phone) <p class="text-gray-500">{{ $booking->phone }}</p> @endif
                            </div>
                        </div>

                        {{-- QR code --}}
                        <div class="bg-gray-900 rounded-lg p-4 mb-4">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">
                                QR Code — show at check-in
                            </p>
                            <p class="text-lg font-mono font-bold text-white break-all">
                                {{ $booking->qr_code ?? 'QR issued when fully paid' }}
                            </p>
                        </div>

                        {{-- Download PDF --}}
                        <a href="{{ route('experience.booking.pdf', $booking) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Download QR PDF
                        </a>
                    </div>
                @endforeach
            @endif

            {{-- Action buttons --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-6">
                <a href="{{ route('home') }}"
                   class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    Back to Home
                </a>
                @if($bookings->count() > 0)
                    <a href="{{ route('experiences.browse') }}"
                       class="px-6 py-3 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50 transition">
                        Browse More Experiences
                    </a>
                @else
                    <a href="{{ route('events.browse') }}"
                       class="px-6 py-3 border border-purple-600 text-purple-600 rounded-lg hover:bg-purple-50 transition">
                        Browse More Events
                    </a>
                @endif
            </div>

            <p class="text-xs text-gray-500 mt-6">
                A confirmation email has been sent to your email address.
            </p>
        </div>
    </div>

    @include('includes.footer')
</body>
</html>