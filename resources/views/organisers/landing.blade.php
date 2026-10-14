{{-- resources/views/organisers/landing.blade.php — the organiser landing page ("Create event" for visitors) --}}
@extends('layouts.app')

@section('title', 'Create and sell events')

@php
    $appName   = \App\Helpers\SystemHelper::appName();
    $fee       = (float) config('aiyo.platform_fee_percent');
    $feeLabel  = rtrim(rtrim(number_format($fee, 2), '0'), '.');
    $minPayout = number_format((int) config('aiyo.payout_minimum'));
    // Guests sign up first and land on the application; anyone logged in goes to their next step
    $start     = auth()->check() ? route('create-event') : route('register', ['redirect' => '/become-organiser']);
@endphp

@push('meta')
    <meta name="description" content="Sell tickets for your events and experiences on {{ $appName }}. M-Pesa checkout, lipa mdogo mdogo, instant QR tickets and payouts. {{ $feeLabel }}% per ticket, no setup fees.">
@endpush

@section('content')
<div class="bg-white dark:bg-gray-950">

{{-- ============================== HERO ============================== --}}
<section class="relative overflow-hidden bg-gray-950">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -left-40 -top-40 h-[480px] w-[480px] rounded-full bg-brand-600/40 blur-3xl"></div>
        <div class="absolute -bottom-48 right-0 h-[520px] w-[520px] rounded-full bg-purple-600/30 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.07)_1px,transparent_1px)] [background-size:22px_22px]"></div>
    </div>

    <div class="relative mx-auto grid max-w-7xl grid-cols-1 items-center gap-14 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-28">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand-200 backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> For organisers
            </span>
            <h1 class="mt-6 text-4xl font-bold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-6xl">
                Sell out your next event.
                <span class="bg-gradient-to-r from-brand-300 via-purple-300 to-amber-200 bg-clip-text text-transparent">Paid by M-Pesa.</span>
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-white/70">
                Create your event or experience in minutes. Buyers pay with M-Pesa — in full or lipa mdogo mdogo — and get their QR ticket instantly.
                You scan them in at the gate and get paid to your M-Pesa or bank.
            </p>
            <div class="mt-9 flex flex-wrap items-center gap-4">
                <a href="{{ $start }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 text-sm font-bold text-gray-900 shadow-lg shadow-brand-900/30 transition hover:-translate-y-0.5 hover:bg-gray-100">
                    Get started — it's free
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
                <a href="#how-it-works" class="rounded-xl border border-white/20 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">See how it works</a>
            </div>
            <p class="mt-6 text-sm text-white/50">No setup or monthly fees · {{ $feeLabel }}% only when you sell</p>
        </div>

        {{-- Product mock-up: a ticket, a payment and a payout --}}
        <div class="relative mx-auto w-full max-w-md lg:max-w-none" aria-hidden="true">
            <div class="relative mx-auto w-72 rotate-[-4deg] rounded-[2rem] border border-white/10 bg-gradient-to-b from-gray-800 to-gray-900 p-3 shadow-2xl sm:w-80">
                <div class="overflow-hidden rounded-[1.5rem] bg-white">
                    <div class="bg-gradient-to-br from-brand-500 via-purple-600 to-fuchsia-600 px-5 pb-6 pt-5 text-white">
                        <p class="text-[10px] font-semibold uppercase tracking-widest text-white/70">Your ticket</p>
                        <p class="mt-1 text-xl font-bold leading-tight">Sauti Nights Live</p>
                        <p class="mt-1 text-xs text-white/80">Sat 14 Dec · 7:00 PM · Nairobi</p>
                    </div>
                    <div class="relative -mt-3 rounded-t-2xl bg-white px-5 pb-5 pt-5">
                        <div class="mx-auto grid h-36 w-36 grid-cols-9 gap-[3px] rounded-xl border border-gray-100 p-2.5">
                            @foreach (str_split('111101111100101001101100101101101001111111001100101011011100111111101101010110111101111010011101') as $bit)
                                <span class="rounded-[1px] {{ $bit === '1' ? 'bg-gray-900' : 'bg-transparent' }}"></span>
                            @endforeach
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-dashed border-gray-200 pt-3 text-xs">
                            <span class="text-gray-500">VIP · Admit 1</span>
                            <span class="font-mono font-semibold text-gray-900">QR-ORD-7K2X</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute -left-2 top-10 w-56 rotate-[3deg] rounded-2xl border border-white/10 bg-white/95 p-4 shadow-xl backdrop-blur sm:-left-6 lg:-left-10">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                    </span>
                    <div>
                        <p class="text-xs font-bold text-gray-900">M-Pesa payment received</p>
                        <p class="text-[11px] text-gray-500">KES 2,500 · 2 tickets</p>
                    </div>
                </div>
            </div>

            <div class="absolute -right-2 bottom-8 w-56 rotate-[-2deg] rounded-2xl border border-white/10 bg-white/95 p-4 shadow-xl backdrop-blur sm:-right-4 lg:-right-6">
                <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500">Tickets sold</p>
                <p class="mt-1 text-2xl font-bold text-gray-900">412 <span class="text-xs font-semibold text-emerald-600">+38 today</span></p>
                <div class="mt-3 flex h-10 items-end gap-1">
                    @foreach ([30, 45, 38, 60, 52, 75, 68, 90, 82, 100] as $h)
                        <span class="flex-1 rounded-sm bg-gradient-to-t from-brand-500 to-purple-400" style="height: {{ $h }}%"></span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- What you get, at a glance --}}
    <div class="relative border-t border-white/10 bg-white/[0.03]">
        <div class="mx-auto grid max-w-7xl grid-cols-2 gap-6 px-4 py-8 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ([
                ['KES 0', 'to sign up and list'],
                [$feeLabel . '%', 'per ticket, only when you sell'],
                ['Instant', 'QR tickets by email'],
                ['1 day', 'typical approval time'],
            ] as [$big, $small])
                <div>
                    <p class="text-2xl font-bold text-white sm:text-3xl">{{ $big }}</p>
                    <p class="mt-1 text-sm text-white/60">{{ $small }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================== FEATURES ============================== --}}
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
    <div class="mx-auto max-w-2xl text-center">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-600">Everything in one place</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Built for how Kenya buys tickets</h2>
        <p class="mt-4 text-gray-500 dark:text-gray-400">From the first sale to the last person through the gate.</p>
    </div>

    <div class="mt-14 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ([
            ['M-Pesa checkout', 'Buyers confirm on their phone in seconds. No card, no account, no app to install.', 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3', 'from-emerald-500 to-teal-500'],
            ['Lipa mdogo mdogo', 'Let buyers hold a place with a deposit and pay the rest bit by bit before your deadline. More buyers, earlier.', 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'from-amber-500 to-orange-500'],
            ['QR check-in', 'Scan tickets with any phone at the gate. Every code works once, so screenshots and copies are turned away.', 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z', 'from-brand-500 to-indigo-500'],
            ['Ticket types and promo codes', 'Early bird, Regular, VIP, groups — each with its own price, quantity and sale dates. Discount codes for partners and fans.', 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3zM6 6h.008v.008H6V6z', 'from-fuchsia-500 to-pink-500'],
            ['Live sales and attendees', 'See every sale as it happens, who has checked in, and export your attendee list whenever you need it.', 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', 'from-sky-500 to-cyan-500'],
            ['Experiences, not just events', 'Road trips, retreats and getaways with packages, a day-by-day itinerary and a photo gallery.', 'M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z', 'from-violet-500 to-purple-500'],
        ] as [$title, $text, $icon, $gradient])
            <div class="group rounded-2xl border border-gray-200 bg-white p-7 shadow-theme-xs transition hover:-translate-y-1 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }} text-white shadow-lg">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </span>
                <h3 class="mt-5 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>

{{-- ============================== HOW IT WORKS ============================== --}}
<section id="how-it-works" class="scroll-mt-20 bg-gray-50 py-20 dark:bg-gray-900/40 lg:py-28">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <p class="text-sm font-bold uppercase tracking-wider text-brand-600">How it works</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">From idea to a full house in four steps</h2>
        </div>

        <ol class="relative mt-16 grid grid-cols-1 gap-10 md:grid-cols-4 md:gap-6">
            <div class="absolute left-0 right-0 top-6 hidden h-px bg-gradient-to-r from-transparent via-brand-300 to-transparent md:block dark:via-brand-700"></div>
            @foreach ([
                ['Sign up and apply', 'Create a free account, then tell us who you are and where your payouts go. We approve most organisers within a working day.'],
                ['Create your listing', 'Add your poster, date, venue and description, then your ticket types — or packages and an itinerary for an experience.'],
                ['Share and sell', 'Your page goes live. Share the link on social media and WhatsApp; buyers pay with M-Pesa and get QR tickets at once.'],
                ['Scan in and get paid', 'Scan tickets at the gate with your phone. After the event, request your payout to M-Pesa or bank.'],
            ] as $i => [$title, $text])
                <li class="relative text-center md:text-left">
                    <span class="relative z-10 mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-brand-500 text-lg font-bold text-white shadow-lg shadow-brand-500/30 ring-8 ring-gray-50 dark:ring-gray-950 md:mx-0">{{ $i + 1 }}</span>
                    <h3 class="mt-5 text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500 dark:text-gray-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ============================== WHO IT'S FOR ============================== --}}
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="rounded-3xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-gray-900 sm:p-12">
        <div class="grid grid-cols-1 items-center gap-10 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-3xl">For every kind of host</h2>
                <p class="mt-3 text-gray-500 dark:text-gray-400">Small and big, one-off or every weekend — if people need a ticket or a booking, you can sell it here.</p>
            </div>
            <div class="flex flex-wrap gap-3 lg:col-span-3">
                @foreach (['🎤 Concerts', '😂 Comedy nights', '🎉 Festivals & parties', '💼 Conferences & workshops', '⚽ Sports & tournaments', '🎭 Theatre & arts', '⛺ Road trips & camping', '🏝️ Getaways & retreats', '🍽️ Food & drink events', '⛪ Church & community events'] as $tag)
                    <span class="rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $tag }}</span>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================== PRICING + CALCULATOR ============================== --}}
<section id="pricing" class="scroll-mt-20 mx-auto max-w-7xl px-4 pb-20 sm:px-6 lg:px-8 lg:pb-28">
    <div class="mx-auto max-w-2xl text-center">
        <p class="text-sm font-bold uppercase tracking-wider text-brand-600">Pricing</p>
        <h2 class="mt-3 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Simple. You only pay when you sell.</h2>
    </div>

    <div class="mt-14 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <div class="rounded-3xl border border-gray-200 bg-white p-8 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-10">
            <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Per ticket or booking sold</p>
            <p class="mt-2 text-6xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $feeLabel }}<span class="text-3xl">%</span></p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Deducted from your sales. Nothing else.</p>
            <ul class="mt-8 space-y-3 text-sm text-gray-700 dark:text-gray-300">
                @foreach ([
                    'No setup, listing or monthly fees',
                    'M-Pesa checkout and lipa mdogo mdogo included',
                    'QR tickets by email, check-in scanner, reports',
                    'Unlimited events, experiences and ticket types',
                    "Payouts to M-Pesa or bank after your event, from KES {$minPayout}",
                    'Deposits kept from unpaid lipa mdogo mdogo bookings are yours',
                ] as $point)
                    <li class="flex gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>
            <a href="{{ $start }}" class="mt-10 inline-flex w-full justify-center rounded-xl bg-brand-500 px-6 py-3.5 text-sm font-bold text-white transition hover:bg-brand-600">Get started — it's free</a>
        </div>

        <div x-data="{ price: 1500, qty: 200, fee: {{ $fee }},
                       get gross() { return Math.max(0, this.price) * Math.max(0, this.qty) },
                       get cut() { return Math.round(this.gross * this.fee / 100) },
                       fmt(n) { return 'KES ' + Math.round(n).toLocaleString('en-KE') } }"
             class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 via-purple-700 to-gray-900 p-8 text-white shadow-xl sm:p-10">
            <div class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>
            <h3 class="relative text-xl font-bold">What you'd take home</h3>
            <p class="relative mt-1 text-sm text-white/70">Move the sliders to match your event.</p>

            <div class="relative mt-8 space-y-7">
                <div>
                    <div class="flex items-center justify-between text-sm">
                        <label for="calc-price" class="text-white/80">Ticket price</label>
                        <span class="font-bold" x-text="fmt(price)"></span>
                    </div>
                    <input id="calc-price" type="range" min="100" max="20000" step="100" x-model.number="price" class="mt-3 w-full accent-white">
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm">
                        <label for="calc-qty" class="text-white/80">Tickets sold</label>
                        <span class="font-bold" x-text="qty.toLocaleString('en-KE')"></span>
                    </div>
                    <input id="calc-qty" type="range" min="10" max="5000" step="10" x-model.number="qty" class="mt-3 w-full accent-white">
                </div>
            </div>

            <dl class="relative mt-9 space-y-3 rounded-2xl bg-white/10 p-5 text-sm backdrop-blur">
                <div class="flex justify-between"><dt class="text-white/70">Ticket sales</dt><dd class="font-semibold" x-text="fmt(gross)"></dd></div>
                <div class="flex justify-between"><dt class="text-white/70">Our fee ({{ $feeLabel }}%)</dt><dd class="font-semibold" x-text="'− ' + fmt(cut)"></dd></div>
                <div class="flex items-end justify-between border-t border-white/20 pt-3">
                    <dt class="font-semibold">Your payout</dt>
                    <dd class="text-3xl font-bold" x-text="fmt(gross - cut)"></dd>
                </div>
            </dl>
        </div>
    </div>
</section>

{{-- ============================== FAQ ============================== --}}
<section class="bg-gray-50 py-20 dark:bg-gray-900/40 lg:py-28">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
        <h2 class="text-center text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Questions organisers ask</h2>
        <div class="mt-12 space-y-3" x-data="{ open: 0 }">
            @foreach ([
                ['Who can sell on ' . $appName . '?', 'Anyone hosting a real event or experience — individuals and registered businesses. You need a confirmed email, your ID (or company registration) number, a KRA PIN and an M-Pesa number or bank account for payouts.'],
                ['How long does approval take?', 'Usually within one working day. We email you as soon as it\'s done. If something is missing we tell you what, and you can fix it and send it again.'],
                ['Do my buyers need an account?', 'No. They pay with their name, email and M-Pesa number. Tickets arrive by email with a private link, and they can create an account later to keep all their tickets in one place.'],
                ['When do I get paid?', "Sales build up in your balance as they come in and become withdrawable once your event or experience has ended. Request a payout from your dashboard and we send it to your M-Pesa or bank, less our {$feeLabel}% fee."],
                ['Why are payouts after the event?', 'It protects your buyers if an event is cancelled or changed — which is exactly what makes people comfortable buying from organisers they haven\'t bought from before.'],
                ['How does lipa mdogo mdogo work?', 'You choose the deposit (10–90%) and how long buyers have to pay the rest. Their place is held while they pay any amount, any time. If they miss the deadline, the place is released, you keep the deposit and later payments are refunded.'],
                ['How do I stop fake or shared tickets?', 'Each ticket has its own QR code that can be scanned in only once with our scanner. A copied screenshot is rejected at the gate.'],
            ] as $i => [$q, $a])
                <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                    <button type="button" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}"
                            class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left font-semibold text-gray-900 dark:text-white">
                        {{ $q }}
                        <svg class="h-5 w-5 shrink-0 text-gray-400 transition" :class="open === {{ $i }} && 'rotate-180 text-brand-500'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open === {{ $i }}" x-transition.opacity.duration.200ms @if ($i !== 0) x-cloak @endif>
                        <p class="px-6 pb-5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================== FINAL CTA ============================== --}}
<section class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="relative overflow-hidden rounded-3xl bg-gray-950 px-6 py-16 text-center sm:px-16">
        <div class="pointer-events-none absolute -left-24 -top-24 h-72 w-72 rounded-full bg-brand-600/40 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-24 -right-24 h-72 w-72 rounded-full bg-purple-600/40 blur-3xl"></div>
        <h2 class="relative mx-auto max-w-2xl text-3xl font-bold tracking-tight text-white sm:text-4xl">Your next event deserves a full house</h2>
        <p class="relative mx-auto mt-4 max-w-xl text-white/70">Sign up free, get approved, and start selling. It takes less time than designing the poster.</p>
        <div class="relative mt-9 flex flex-wrap justify-center gap-4">
            <a href="{{ $start }}" class="rounded-xl bg-white px-7 py-3.5 text-sm font-bold text-gray-900 transition hover:-translate-y-0.5 hover:bg-gray-100">Get started — it's free</a>
            @guest
                <a href="{{ route('login', ['redirect' => '/create-event']) }}" class="rounded-xl border border-white/20 px-7 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Organiser log in</a>
            @endguest
        </div>
    </div>
</section>

</div>
@endsection
