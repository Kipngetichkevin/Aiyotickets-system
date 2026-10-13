<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/preline"></script>
    <style>
        .cart-gradient { background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #ec4899 100%); }
        .cart-gradient-soft { background: linear-gradient(135deg, #faf5ff 0%, #fdf2f8 100%); }
        .glass-card { backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.85); }
        @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
        .pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    @include('includes.header')
    <script>window.APP_AUTHED = {{ auth()->check() ? 'true' : 'false' }};</script>

    @php
        $cartSubtotal = 0;
        foreach ($cart as $item) {
            $cartSubtotal += ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
        }
        $expSubtotal = 0;
        foreach ($experiences as $item) {
            $expSubtotal += ($item['quantity'] ?? 0) * ($item['price'] ?? 0);
        }
        $promoDiscount = (float) ($promo['discount'] ?? 0);
        $grandTotal = max(0, round($cartSubtotal + $expSubtotal - $promoDiscount));
        $hasAnyItems = count($cart) > 0 || count($experiences) > 0;
        $totalQty = 0;
        foreach ($cart as $it) { $totalQty += $it['quantity'] ?? 0; }
        foreach ($experiences as $it) { $totalQty += $it['quantity'] ?? 0; }
    @endphp

    <div class="cart-gradient-soft border-b border-purple-100">
        <div class="container mx-auto px-4 lg:px-8 py-10 lg:py-14">
            <div class="flex items-start gap-4">
                <div class="hidden sm:flex w-14 h-14 rounded-2xl cart-gradient items-center justify-center shadow-lg shadow-purple-300/40 shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl lg:text-4xl font-bold text-gray-900">Your Cart</h1>
                    <p class="text-gray-600 mt-1">
                        @if($hasAnyItems)
                            {{ $totalQty }} {{ \Illuminate\Support\Str::plural('item', $totalQty) }} ready for checkout
                        @else
                            Your cart is waiting for its first adventure
                        @endif
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ route('events.browse') }}"
                           class="inline-flex items-center gap-1.5 rounded-full border border-purple-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-purple-700 backdrop-blur-sm hover:bg-purple-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                            </svg>
                            Browse Events
                        </a>
                        <a href="{{ route('experiences.browse') }}"
                           class="inline-flex items-center gap-1.5 rounded-full border border-pink-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-pink-700 backdrop-blur-sm hover:bg-pink-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                            </svg>
                            Browse Experiences
                        </a>
                        <a href="{{ route('home') }}"
                           class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-gray-600 backdrop-blur-sm hover:bg-gray-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                            </svg>
                            Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 lg:px-8 py-10">
        <div class="grid lg:grid-cols-3 gap-8 items-start">
            <div class="lg:col-span-2 space-y-8">

                @if(count($cart) > 0)
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-purple-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">Event Tickets</h2>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-purple-100 text-purple-700 font-semibold">
                                {{ $cartSubtotal > 0 ? 'KES ' . number_format($cartSubtotal, 0) : '' }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($cart as $ticket_id => $item)
                                <div class="group bg-white rounded-2xl border border-gray-200 p-5 hover:border-purple-300 hover:shadow-lg hover:shadow-purple-100/50 transition-all duration-300">
                                    <div class="flex gap-4">
                                        <img src="{{ asset($item['event_image'] ?? 'images/default-event.jpg') }}"
                                             alt="{{ $item['event_name'] ?? 'Event' }}"
                                             class="w-24 h-24 rounded-xl object-cover border border-gray-100 shrink-0">

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <h3 class="text-base font-bold text-gray-900 truncate">
                                                        {{ $item['event_name'] ?? 'Event' }}
                                                    </h3>
                                                    <p class="text-sm text-gray-500 mt-0.5">
                                                        {{ $item['ticket_name'] ?? 'Ticket' }}
                                                    </p>
                                                </div>

                                                <form action="{{ route('cart.remove', ['ticket_id' => $ticket_id]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            title="Remove"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                            onclick="return confirm('Remove this ticket from your cart?')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>

                                            <div class="mt-4 flex items-end justify-between">
                                                <div class="flex items-center gap-3 text-sm">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-medium">
                                                        Qty {{ $item['quantity'] ?? 0 }}
                                                    </span>
                                                    <span class="text-gray-400">×</span>
                                                    <span class="text-gray-600">KES {{ number_format($item['price'] ?? 0, 0) }}</span>
                                                </div>
                                                <div class="text-lg font-bold text-gray-900">
                                                    KES {{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(count($experiences) > 0)
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-xl bg-pink-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-gray-900">Experiences</h2>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-pink-100 text-pink-700 font-semibold">
                                {{ $expSubtotal > 0 ? 'KES ' . number_format($expSubtotal, 0) : '' }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($experiences as $exp_id => $item)
                                <div class="group bg-white rounded-2xl border border-gray-200 p-5 hover:border-pink-300 hover:shadow-lg hover:shadow-pink-100/50 transition-all duration-300">
                                    <div class="flex gap-4">
                                        @if(!empty($item['image']))
                                            <img src="{{ asset('storage/' . $item['image']) }}"
                                                 alt="{{ $item['experience_name'] ?? 'Experience' }}"
                                                 class="w-24 h-24 rounded-xl object-cover border border-gray-100 shrink-0">
                                        @else
                                            <div class="w-24 h-24 rounded-xl cart-gradient flex items-center justify-center shrink-0">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                     stroke="white" stroke-width="1.5" class="h-9 w-9 opacity-70">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                                </svg>
                                            </div>
                                        @endif

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 mb-1">
                                                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-pink-100 text-pink-700 font-bold uppercase tracking-wide">
                                                            Experience
                                                        </span>
                                                        @if(!empty($item['package_name']))
                                                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-semibold">
                                                                {{ $item['package_name'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <h3 class="text-base font-bold text-gray-900 truncate">
                                                        {{ $item['experience_name'] ?? 'Experience' }}
                                                    </h3>
                                                    @if(!empty($item['duration']))
                                                        <p class="text-sm text-gray-500 mt-0.5">
                                                            {{ $item['duration'] }} min
                                                        </p>
                                                    @endif
                                                </div>

                                                <form action="{{ route('cart.remove', ['ticket_id' => 'exp-' . $exp_id]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            title="Remove"
                                                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                            onclick="return confirm('Remove this experience from your cart?')">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            </div>

                                            <div class="mt-4 flex items-end justify-between">
                                                <div class="flex items-center gap-3 text-sm">
                                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-100 text-gray-700 font-medium">
                                                        {{ $item['quantity'] ?? 0 }} × {{ ($item['quantity'] ?? 0) > 1 ? 'slots' : 'slot' }}
                                                    </span>
                                                    <span class="text-gray-400">×</span>
                                                    <span class="text-gray-600">KES {{ number_format($item['price'] ?? 0, 0) }}</span>
                                                </div>
                                                <div class="text-lg font-bold text-gray-900">
                                                    KES {{ number_format(($item['quantity'] ?? 0) * ($item['price'] ?? 0), 0) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!$hasAnyItems)
                    <div class="bg-white rounded-2xl border border-gray-200 py-16 px-8 text-center">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-3xl cart-gradient-soft flex items-center justify-center">
                            <svg class="w-12 h-12 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Your cart is empty</h3>
                        <p class="text-gray-500 mb-6 max-w-sm mx-auto">
                            Browse events and experiences to add tickets and adventures to your cart.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            <a href="{{ route('events.browse') }}"
                               class="px-6 py-3 rounded-xl cart-gradient text-white font-semibold shadow-lg shadow-purple-300/40 hover:shadow-xl transition-all">
                                Browse Events
                            </a>
                            <a href="{{ route('experiences.browse') }}"
                               class="px-6 py-3 rounded-xl border-2 border-purple-200 text-purple-700 font-semibold hover:bg-purple-50 transition-all">
                                Browse Experiences
                            </a>
                        </div>
                    </div>
                @endif

            </div>

            <div class="lg:sticky lg:top-6">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-lg shadow-gray-200/50 overflow-hidden">

                    <div class="cart-gradient px-6 py-5">
                        <h3 class="text-lg font-bold text-white">Payment Details</h3>
                        <p class="text-xs text-white/80 mt-0.5">Complete your purchase securely</p>
                    </div>

                    {{-- PROMO CODE (own form: forms can't be nested in the checkout form) --}}
                    @if (count($cart) > 0)
                        <div class="px-6 pt-6">
                            @if (!empty($promo['promo']))
                                <div class="flex items-center justify-between rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm">
                                    <span class="text-green-800"><strong>{{ $promo['promo']->code }}</strong> · {{ $promo['promo']->label() }}</span>
                                    <form method="POST" action="{{ route('cart.promo.remove') }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-green-800 underline">Remove</button>
                                    </form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('cart.promo.apply') }}" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="code" placeholder="Promo code" value="{{ old('code') }}"
                                           class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm uppercase focus:border-purple-500 focus:ring-purple-500">
                                    <button type="submit" class="shrink-0 rounded-xl border border-purple-600 px-4 text-sm font-semibold text-purple-700 hover:bg-purple-50">Apply</button>
                                </form>
                            @endif
                            @if (session('promo_error'))
                                <p class="mt-2 text-xs text-red-600">{{ session('promo_error') }}</p>
                            @elseif (session('promo_success'))
                                <p class="mt-2 text-xs text-green-700">{{ session('promo_success') }}</p>
                            @endif
                        </div>
                    @endif

                    <form id="checkout-form" action="{{ route('cart.checkout') }}" method="POST" class="p-6 space-y-5">
                        @csrf
                        <input type="hidden" name="attendee_data" value="{{ json_encode($cart) }}">

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                                Payment Method
                            </label>
                            <div class="grid grid-cols-3 gap-2">
                                <button type="button" id="mobile-money-btn"
                                    class="payment-tab active p-3 text-center rounded-xl border-2 border-purple-600 bg-purple-50 text-purple-700 font-semibold transition-all">
                                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-[11px]">M-Pesa</span>
                                </button>

                                <button type="button" id="card-payment-btn"
                                    class="payment-tab p-3 text-center rounded-xl border-2 border-gray-200 text-gray-500 font-semibold hover:border-gray-300 transition-all">
                                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <span class="text-[11px]">Card</span>
                                </button>

                                <button type="button" id="crypto-payment-btn"
                                    class="payment-tab p-3 text-center rounded-xl border-2 border-gray-200 text-gray-500 font-semibold hover:border-gray-300 transition-all relative">
                                    <span class="absolute -top-2 -right-2 bg-amber-400 text-amber-900 text-[9px] px-1.5 py-0.5 rounded-full font-bold">Soon</span>
                                    <svg class="w-5 h-5 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-[11px]">Crypto</span>
                                </button>
                            </div>
                        </div>

                        <div id="mobile-money-form" class="payment-form">
                            <label for="phone" class="block text-xs font-semibold text-gray-700 mb-1.5">
                                M-Pesa Phone Number
                            </label>
                            <input type="tel" name="phone" id="phone" required
                                class="py-2.5 px-3.5 block w-full border border-gray-300 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition"
                                placeholder="712 345 678">
                            <p class="text-xs text-gray-500 mt-1.5">
                                We'll send an STK push to this number
                            </p>
                        </div>

                        <div id="card-payment-form" class="payment-form hidden">
                            <div class="bg-gray-50 rounded-xl p-6 text-center border border-dashed border-gray-300">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                <p class="text-sm font-semibold text-gray-700">Card payments coming soon</p>
                                <p class="text-xs text-gray-500 mt-1">Use M-Pesa for now</p>
                            </div>
                        </div>

                        <div id="crypto-payment-form" class="payment-form hidden">
                            <div class="bg-gray-50 rounded-xl p-6 text-center border border-dashed border-gray-300">
                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-sm font-semibold text-gray-700">Crypto payments coming soon</p>
                                <p class="text-xs text-gray-500 mt-1">Use M-Pesa for now</p>
                            </div>
                        </div>

                        <div class="space-y-3 pt-1">
                            <div>
                                <label for="name" class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Full Name
                                </label>
                                <input type="text" name="name" id="name" required
                                    @auth value="{{ auth()->user()->name }}" @endauth
                                    class="py-2.5 px-3.5 block w-full border border-gray-300 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition"
                                    placeholder="John Doe">
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    Email Address
                                </label>
                                <input type="email" name="email" id="email" required
                                    @auth value="{{ auth()->user()->email }}" @endauth
                                    class="py-2.5 px-3.5 block w-full border border-gray-300 rounded-xl text-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 outline-none transition"
                                    placeholder="you@example.com">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Gender</label>
                                <div class="flex gap-2">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="gender" value="1" checked class="peer sr-only">
                                        <div class="text-center py-2 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 transition">
                                            Male
                                        </div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="gender" value="2" class="peer sr-only">
                                        <div class="text-center py-2 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 transition">
                                            Female
                                        </div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="gender" value="3" class="peer sr-only">
                                        <div class="text-center py-2 rounded-xl border-2 border-gray-200 text-sm font-medium text-gray-600 peer-checked:border-purple-500 peer-checked:bg-purple-50 peer-checked:text-purple-700 transition">
                                            Other
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="payment_mode" id="payment_mode" value="Mobile Money">
                        <input type="hidden" name="gender" id="gender" value="1">

                        {{-- LIPA MDOGO MDOGO (only when this cart qualifies; otherwise say why) --}}
                        @if (empty($instalment) && !empty($instalmentUnavailable))
                            <div class="flex gap-2 rounded-xl border border-gray-200 bg-gray-50 p-3 text-xs text-gray-600">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                <span><strong class="text-gray-700">Lipa mdogo mdogo:</strong> {{ $instalmentUnavailable }}</span>
                            </div>
                        @endif

                        @if (!empty($instalment))
                            <div x-data="{ plan: 'full' }" class="space-y-2">
                                <p class="text-sm font-semibold text-gray-800">How would you like to pay?</p>
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3" :class="plan === 'full' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                    <input type="radio" name="payment_plan" value="full" x-model="plan" class="mt-1 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm"><span class="font-semibold text-gray-900">Pay in full</span>
                                        <span class="block text-xs text-gray-500">KES {{ number_format($grandTotal) }} now, tickets immediately</span></span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-3" :class="plan === 'instalments' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                    <input type="radio" name="payment_plan" value="instalments" x-model="plan" class="mt-1 text-purple-600 focus:ring-purple-500">
                                    <span class="text-sm"><span class="font-semibold text-gray-900">Lipa mdogo mdogo</span>
                                        <span class="block text-xs text-gray-500">
                                            KES {{ number_format($instalment['deposit']) }} deposit now ({{ $instalment['percent'] }}%),
                                            the rest in any amounts by {{ $instalment['due_date']->format('d M Y') }}.
                                            Your place is held; tickets are sent once fully paid.
                                        </span></span>
                                </label>
                                <p x-show="plan === 'instalments'" x-cloak class="text-[11px] text-gray-500">
                                    If the balance isn't paid by the due date the booking is cancelled: the deposit is kept and later payments are refunded.
                                </p>
                            </div>
                        @endif

                        <div class="border-t border-gray-200 pt-4 mt-2">
                            <div class="space-y-2 text-sm">
                                @if($cartSubtotal > 0)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Tickets</span>
                                        <span class="font-medium">KES {{ number_format($cartSubtotal, 2) }}</span>
                                    </div>
                                @endif
                                @if($expSubtotal > 0)
                                    <div class="flex justify-between text-gray-600">
                                        <span>Experiences</span>
                                        <span class="font-medium">KES {{ number_format($expSubtotal, 2) }}</span>
                                    </div>
                                @endif
                                @if($promoDiscount > 0)
                                    <div class="flex justify-between text-green-700">
                                        <span>Discount ({{ $promo['promo']->code }})</span>
                                        <span class="font-medium">− KES {{ number_format($promoDiscount, 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-gray-500 text-xs pt-1">
                                    <span>Processing fee</span>
                                    <span class="text-green-600 font-medium">Free</span>
                                </div>
                                <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                                    <span class="font-semibold text-gray-900">Total</span>
                                    <span class="text-2xl font-bold text-purple-600">
                                        KES {{ number_format($grandTotal, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        @guest
                            <p class="text-center text-xs text-gray-500">
                                No account needed — your tickets are sent to your email.
                                <a href="{{ route('login') }}?redirect=/cart" class="font-semibold text-purple-700 underline">Log in</a> to keep them with your past orders.
                            </p>
                        @endguest

                        <button type="button" id="checkout-btn"
                            class="w-full py-3.5 px-4 rounded-xl font-bold text-white cart-gradient shadow-lg shadow-purple-300/50 hover:shadow-xl hover:shadow-purple-300/60 transition-all disabled:opacity-50 disabled:cursor-not-allowed disabled:shadow-none"
                            {{ !$hasAnyItems ? 'disabled' : '' }}>
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Pay Now
                            </span>
                        </button>

                        <div class="flex items-center justify-center gap-4 pt-2 text-[11px] text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Secure checkout
                            </span>
                            <span>•</span>
                            <span>Powered by M-Pesa</span>
                        </div>

                        <p class="text-[11px] text-gray-400 text-center leading-relaxed">
                            By completing this purchase you agree to our
                            <a href="#" class="text-purple-600 hover:underline">Terms of Service</a>.
                            Tickets are non-refundable.
                        </p>
                    </form>

                    <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-2 text-center">
                            Want to add more?
                        </p>
                        <div class="flex gap-2">
                            <a href="{{ route('events.browse') }}"
                               class="flex-1 text-center py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:border-purple-300 hover:text-purple-700 transition">
                                + Events
                            </a>
                            <a href="{{ route('experiences.browse') }}"
                               class="flex-1 text-center py-2 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-700 hover:border-pink-300 hover:text-pink-700 transition">
                                + Experiences
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    <div id="payment-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
            <div class="text-center">
                <div class="relative w-20 h-20 mx-auto mb-5">
                    <div class="absolute inset-0 rounded-full cart-gradient opacity-20 pulse-soft"></div>
                    <div class="relative w-20 h-20 rounded-full cart-gradient flex items-center justify-center">
                        <svg class="w-10 h-10 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <h3 class="text-xl font-bold text-gray-900 mb-2">Waiting for Payment</h3>
                <p class="text-sm text-gray-600 mb-5" id="payment-status-text">
                    Check your phone and enter your M-Pesa PIN to complete the payment.
                </p>

                <div class="w-full bg-gray-100 rounded-full h-2 mb-3 overflow-hidden">
                    <div id="payment-progress" class="cart-gradient h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>

                <p class="text-xs text-gray-400 mb-5" id="payment-timer">Waiting for confirmation...</p>

                <button type="button" id="cancel-payment-btn"
                    class="text-sm text-gray-500 hover:text-gray-800 font-medium">
                    Cancel Payment
                </button>
            </div>
        </div>
    </div>

    <div id="success-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
            <div class="text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Payment Successful!</h3>
                <p class="text-sm text-gray-600 mb-5">Your order is being confirmed and you'll be redirected shortly.</p>
                <div class="flex items-center justify-center gap-2 text-purple-600 text-sm font-medium animate-pulse">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Redirecting...
                </div>
            </div>
        </div>
    </div>

    <div id="error-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl">
            <div class="text-center">
                <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Payment Failed</h3>
                <p class="text-sm text-gray-600 mb-6" id="error-message">
                    Please try again or use a different payment method.
                </p>
                <button type="button" id="retry-payment-btn"
                    class="w-full py-3 rounded-xl cart-gradient text-white font-semibold shadow-lg shadow-purple-300/40 hover:shadow-xl transition-all">
                    Try Again
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            let paymentCheckInterval = null;
            let paymentCheckAttempts = 0;
            const MAX_ATTEMPTS = 90;
            let currentCheckoutRequestId = null;
            let currentOrderId = null;
            let isOrderCreated = false;

            const PENDING_KEY = 'cart_resume';

            // ---- Restore stashed form data + auto-fire if back from login ----
            (function restoreAfterLogin() {
                const url = new URL(window.location.href);
                const shouldAuto = url.searchParams.get('auto') === '1';

                try {
                    const stored = sessionStorage.getItem(PENDING_KEY);
                    if (stored) {
                        const d = JSON.parse(stored);
                        const isSamePage = (d.path || '/cart') === window.location.pathname;
                        const isFresh    = (Date.now() - (d.timestamp || 0)) < 600000;

                        if (isSamePage && isFresh) {
                            if (d.name)   $('#name').val(d.name);
                            if (d.email)  $('#email').val(d.email);
                            if (d.phone)  $('#phone').val(d.phone);
                            if (d.gender) {
                                $(`input[name="gender"][value="${d.gender}"]`).prop('checked', true);
                                $('#gender').val(d.gender);
                            }
                        }

                        sessionStorage.removeItem(PENDING_KEY);
                    }
                } catch (e) { /* ignore */ }

                // Auto-fire Pay Now if the user just logged in from this page
                if (shouldAuto && window.APP_AUTHED) {
                    url.searchParams.delete('auto');
                    window.history.replaceState({}, '', url.toString());

                    setTimeout(function() {
                        $('#checkout-btn').trigger('click');
                    }, 500);
                }
            })();

            function showPaymentModal() {
                $('#payment-progress').css('width', '0%');
                $('#payment-status-text').text('Check your phone and enter your M-Pesa PIN to complete the payment.');
                $('#payment-timer').text('Waiting for confirmation...');
                $('#payment-modal').removeClass('hidden').show();
            }

            function updateProgress(attempts, maxAttempts) {
                const progress = Math.min((attempts / maxAttempts) * 100, 95);
                $('#payment-progress').css('width', progress + '%');
                $('#payment-timer').text(`Waiting for confirmation... (${Math.floor(attempts/2)}s)`);
            }

            function hidePaymentModal() { $('#payment-modal').hide(); }

            function showSuccessModal() {
                hidePaymentModal();
                $('#success-modal').removeClass('hidden').show();
                setTimeout(function() {
                    window.location.href = '/cart/thankyou?order=' + currentOrderId;
                }, 2000);
            }

            function showErrorModal(message) {
                hidePaymentModal();
                $('#error-message').text(message || 'Payment failed. Please try again.');
                $('#error-modal').removeClass('hidden').show();
            }

            function createOrderAfterPayment(checkoutRequestId) {
                if (isOrderCreated) return;
                isOrderCreated = true;
                clearInterval(paymentCheckInterval);
                $('#payment-status-text').text('Payment confirmed! Creating your order...');
                $('#payment-progress').css('width', '100%');

                $.ajax({
                    url: "{{ route('cart.create-order') }}",
                    method: "POST",
                    data: {
                        checkout_request_id: checkoutRequestId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
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
                        if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
                        showErrorModal(message);
                    }
                });
            }

            function checkPaymentStatus(checkoutRequestId) {
                if (paymentCheckInterval) clearInterval(paymentCheckInterval);
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
                                if (response.data.status === 'completed') {
                                    clearInterval(paymentCheckInterval);
                                    createOrderAfterPayment(checkoutRequestId);
                                } else if (response.data.status === 'failed') {
                                    clearInterval(paymentCheckInterval);
                                    $('#checkout-btn').prop('disabled', false).text('Pay Now');
                                    showErrorModal('Payment failed. Please try again.');
                                }
                            }

                            if (paymentCheckAttempts >= MAX_ATTEMPTS) {
                                clearInterval(paymentCheckInterval);
                                $('#checkout-btn').prop('disabled', false).text('Pay Now');
                                $.ajax({
                                    url: '/mpesa/query/' + checkoutRequestId,
                                    method: 'GET',
                                    success: function(queryResponse) {
                                        if (queryResponse.success && queryResponse.data && queryResponse.data.is_ready_for_order) {
                                            createOrderAfterPayment(checkoutRequestId);
                                        } else {
                                            showErrorModal('Payment is taking longer than expected. Please check your M-Pesa app.');
                                        }
                                    },
                                    error: function() {
                                        showErrorModal('Payment is taking longer than expected. Please check your M-Pesa app.');
                                    }
                                });
                            }
                        },
                        error: function() { console.log('Status check error, continuing...'); }
                    });
                }, 2000);
            }

            $('.payment-tab').on('click', function() {
                $('.payment-tab').removeClass('active border-purple-600 bg-purple-50 text-purple-700')
                    .addClass('border-gray-200 text-gray-500');
                $(this).addClass('active border-purple-600 bg-purple-50 text-purple-700')
                    .removeClass('border-gray-200 text-gray-500');

                $('.payment-form').addClass('hidden');

                const tabId = $(this).attr('id');
                if (tabId === 'mobile-money-btn') {
                    $('#mobile-money-form').removeClass('hidden');
                    $('#payment_mode').val('Mobile Money');
                    $('#phone').prop('required', true);
                } else if (tabId === 'card-payment-btn') {
                    $('#card-payment-form').removeClass('hidden');
                    $('#payment_mode').val('Card');
                    $('#phone').prop('required', false);
                } else if (tabId === 'crypto-payment-btn') {
                    $('#crypto-payment-form').removeClass('hidden');
                    $('#payment_mode').val('Crypto');
                    $('#phone').prop('required', false);
                }
            });

            $('input[name="gender"]').on('change', function() { $('#gender').val($(this).val()); });

            $('#phone').on('input', function() {
                let value = $(this).val().replace(/\D/g, '');
                if (value.startsWith('0')) value = value.substring(1);
                if (!value.startsWith('254') && value.length > 0) {
                    if (value.length === 9 && (value.startsWith('7') || value.startsWith('1'))) value = '254' + value;
                }
                if (value.length > 12) value = value.substring(0, 12);
                $(this).val(value);
            });

            // === PAY NOW — guest checkout: no login needed (name, phone and email are enough) ===
            $('#checkout-btn').on('click', function() {
                const form = $('#checkout-form')[0];
                const $btn = $(this);

                const phoneInput = $('#phone');
                if (phoneInput.is(':visible') && phoneInput.prop('required')) {
                    let phone = phoneInput.val().replace(/\D/g, '').replace(/^0+/, '');
                    if (phone.length === 9 && (phone.startsWith('7') || phone.startsWith('1'))) phone = '254' + phone;
                    else if (phone.startsWith('254') && phone.length === 12) { /* ok */ }
                    else if (phone.length === 10 && phone.startsWith('0')) phone = '254' + phone.substring(1);
                    else { alert('Please enter a valid phone number (e.g., 712345678 or 254712345678)'); return; }
                    phoneInput.val(phone);
                }

                if (!form.checkValidity()) { form.reportValidity(); return; }

                $btn.prop('disabled', true).html('<span class="flex items-center justify-center gap-2"><svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>Sending STK Push...</span>');

                $.ajax({
                    url: "{{ route('cart.checkout') }}",
                    method: "POST",
                    data: $('#checkout-form').serialize(),
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        if (response.success) {
                            if (response.data && response.data.checkout_request_id) {
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
                    error: function(xhr) {
                        $btn.prop('disabled', false).text('Pay Now');
                        if (xhr.status === 401) {
                            window.location.href = '/login?redirect=' + encodeURIComponent('/cart?auto=1');
                            return;
                        }
                        let message = 'An error occurred';
                        if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
                        else if (xhr.status === 400) message = 'Please check your details and try again';
                        alert(message);
                    }
                });
            });

            $('#cancel-payment-btn').on('click', function() {
                if (confirm('Are you sure you want to cancel this payment?')) {
                    clearInterval(paymentCheckInterval);
                    hidePaymentModal();
                    $('#checkout-btn').prop('disabled', false).text('Pay Now');
                }
            });

            $('#retry-payment-btn').on('click', function() {
                $('#error-modal').hide();
                location.reload();
            });

            $('#phone').on('blur', function() {
                let phone = $(this).val().replace(/\D/g, '');
                if (phone.length > 0) {
                    phone = phone.replace(/^0+/, '');
                    if (phone.length === 9 && (phone.startsWith('7') || phone.startsWith('1'))) phone = '254' + phone;
                    if (phone.length === 10 && phone.startsWith('0')) phone = '254' + phone.substring(1);
                    $(this).val(phone);
                }
            });
        });
    </script>

    @include('includes.footer')
</body>
</html>