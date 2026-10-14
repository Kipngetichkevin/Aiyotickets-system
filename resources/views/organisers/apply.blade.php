{{-- resources/views/organisers/apply.blade.php — "Sell with us": pitch, application form, application status --}}
@extends('layouts.app')

@section('title', 'Sell tickets')

@php
    $input  = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $label  = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $status    = $application?->status;
    $isAdmin   = $user?->hasRole('admin') ?? false;
    // The form shows for a new applicant, for one fixing a rejected application, and
    // read-only for admins so they can see exactly what applicants are asked
    $showForm  = $isAdmin || ($user && $user->hasVerifiedEmail() && !$isOrganiser && in_array($status, [null, 'rejected'], true));
    $v         = fn ($field) => $isAdmin ? null : old($field, $application?->{$field});
    $fee       = rtrim(rtrim(number_format((float) config('aiyo.platform_fee_percent'), 2), '0'), '.');
    $minPayout = number_format((int) config('aiyo.payout_minimum'));
    $appName   = \App\Helpers\SystemHelper::appName();
    $cta       = $user ? '#apply' : route('register', ['redirect' => '/become-organiser']);
@endphp

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">

    @if ($isAdmin)
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
            <span><strong>Admin view.</strong> This is the page organisers see. Applications arrive in Organisers for you to approve.</span>
            <a href="{{ route('admin.organisers.index') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                Review applications ({{ \App\Models\Merchant::where('status', 'pending')->count() }} pending)
            </a>
        </div>
    @endif

    {{-- Hero --}}
    <section class="mb-14 text-center">
        <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-brand-600 dark:bg-brand-500/10">For organisers</span>
        <h1 class="mx-auto mt-4 max-w-3xl text-3xl font-bold text-gray-900 dark:text-white sm:text-5xl">Host your event or experience on {{ $appName }}</h1>
        <p class="mx-auto mt-4 max-w-2xl text-gray-500 dark:text-gray-400">
            Concerts, comedy nights, conferences, festivals, road trips and getaways. Buyers pay with M-Pesa — in full or lipa mdogo mdogo —
            and get QR tickets instantly. You scan them at the gate, follow sales live and get paid to your M-Pesa or bank.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ $cta }}" class="rounded-xl bg-brand-500 px-6 py-3 text-sm font-bold text-white shadow-theme-xs hover:bg-brand-600">Apply to sell</a>
            <a href="#how-it-works" class="rounded-xl border border-gray-200 px-6 py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">How it works</a>
        </div>
        <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Free to join · {{ $fee }}% per ticket sold · no monthly fees</p>
    </section>

    {{-- The whole journey --}}
    <section id="how-it-works" class="mb-14 scroll-mt-24">
        <h2 class="mb-2 text-center text-2xl font-bold text-gray-900 dark:text-white">How hosting works, start to finish</h2>
        <p class="mb-8 text-center text-sm text-gray-500 dark:text-gray-400">From your first sign-up to money in your account.</p>

        <ol class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Create your account', 'Sign up with your email and confirm it from the link we send. It takes a minute and it\'s free.'],
                ['Apply as an organiser', 'Fill in the form below: your name or business, ID or registration number, KRA PIN, contacts, what you plan to host, and where payouts should go.'],
                ['We review and approve', 'Our team checks your details, usually within one working day. You get an email either way; if something is missing, fix it and send again.'],
                ['Set up your event or experience', 'From your dashboard add the poster, date, venue and description. Events get ticket types (Regular, VIP…) with prices, quantities and sale dates. Experiences get packages, a day-by-day itinerary and a photo gallery.'],
                ['Choose how buyers pay', 'Full payment by M-Pesa, or allow lipa mdogo mdogo: buyers hold their place with a deposit and clear the balance by your due date. Create promo codes for early birds, groups or partners.'],
                ['Go live and share', 'Your page goes up on the site. Share the link on social media and WhatsApp. Buyers check out in seconds — no account needed.'],
                ['Buyers get QR tickets', 'As soon as M-Pesa confirms, each buyer gets their QR tickets by email (and SMS/WhatsApp where enabled). Instalment buyers get theirs once fully paid.'],
                ['Check people in', 'On the day, open the scanner on any phone and scan tickets at the gate. Each QR code works once, so copies are turned away.'],
                ['Get paid', "Follow sales and attendees live in Reports. Once the event ends, request a payout to your M-Pesa or bank; we keep {$fee}% and send you the rest."],
            ] as $i => [$title, $text])
                <li class="relative rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <span class="mb-3 flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white">{{ $i + 1 }}</span>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Pricing and requirements --}}
    <section class="mb-14 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pricing</h2>
            <p class="mt-3 text-4xl font-bold text-brand-600">{{ $fee }}%<span class="text-base font-medium text-gray-500"> per ticket or booking sold</span></p>
            <ul class="mt-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                @foreach ([
                    'Free to sign up and to create your listings',
                    'No setup or monthly fees — you only pay when you sell',
                    'Payouts to M-Pesa or bank once your event has ended',
                    "Request a payout any time from KES {$minPayout}",
                    'Deposits kept from unpaid lipa mdogo mdogo bookings are yours',
                ] as $point)
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">What you'll need to apply</h2>
            <ul class="mt-5 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                @foreach ([
                    'A confirmed email address (your account login)',
                    'National ID or passport number — or your company registration number',
                    'Your KRA PIN (personal or company)',
                    'A phone number buyers and our team can reach',
                    'An M-Pesa number or bank account for payouts, in your or your company\'s name',
                    'A short description of what you plan to host; a website or social page helps',
                ] as $point)
                    <li class="flex gap-2"><span class="text-brand-500">•</span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- FAQ --}}
    <section class="mb-14">
        <h2 class="mb-6 text-center text-2xl font-bold text-gray-900 dark:text-white">Questions organisers ask</h2>
        <div class="mx-auto max-w-3xl divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @foreach ([
                ['Do my buyers need an account?', 'No. They pay with name, email and M-Pesa number. Tickets arrive by email with a private link; they can create an account later to see all their tickets in one place.'],
                ['When do I get my money?', "Sales collect in your balance as they come in and become withdrawable once the event or experience has ended. You request a payout from your dashboard; we send it to your M-Pesa or bank, less our {$fee}% fee."],
                ['Why after the event?', 'It protects buyers if an event is cancelled or changed, which keeps their trust in buying from you.'],
                ['Can buyers pay in instalments?', 'Yes, if you switch on lipa mdogo mdogo. You choose the deposit and the deadline. Places are held while they pay; anyone who misses the deadline loses the place, you keep the deposit, and we refund later payments.'],
                ['How do I stop fake or shared tickets?', 'Every ticket has its own QR code that can only be scanned in once, with our scanner on your phone.'],
                ['Can I host more than one event?', 'Yes — as many events and experiences as you like from one organiser account, each with its own reports.'],
                ['What if my application is not approved?', "We email you the reason. Update your details on this page and send it again."],
            ] as [$q, $a])
                <details class="group px-5 py-4">
                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold text-gray-900 dark:text-white">
                        {{ $q }}
                        <span class="text-gray-400 transition group-open:rotate-45">+</span>
                    </summary>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $a }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <h2 id="apply" class="mb-4 scroll-mt-24 text-center text-2xl font-bold text-gray-900 dark:text-white">{{ $isAdmin ? 'The application form' : 'Apply to sell' }}</h2>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
        @if (!$user)
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Start with an account</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Create a free account (or log in), confirm your email, then fill in the application.</p>
                <div class="mt-6 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('register', ['redirect' => '/become-organiser']) }}" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Create account</a>
                    <a href="{{ route('login', ['redirect' => '/become-organiser']) }}" class="rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Log in</a>
                </div>
            </div>

        @elseif ($isOrganiser && !$isAdmin)
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">You're already an organiser</h2>
                <a href="{{ route('dashboard') }}" class="mt-5 inline-flex rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Go to dashboard</a>
            </div>

        @elseif (!$user->hasVerifiedEmail())
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Confirm your email first</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">We sent a link to <strong>{{ $user->email }}</strong>. Open it, then come back here to apply.</p>
                <form method="POST" action="{{ route('verification.send') }}" class="mt-5">@csrf
                    <button class="rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Resend the link</button>
                </form>
            </div>

        @elseif ($status === 'pending')
            <div class="text-center">
                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Under review</span>
                <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">We're reviewing {{ $application->name }}</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Sent {{ $application->updated_at->format('d M Y, H:i') }}. We'll email {{ $user->email }} as soon as it's decided.</p>
            </div>

        @elseif ($status === 'suspended')
            <div class="text-center">
                <span class="inline-flex rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">Suspended</span>
                <h2 class="mt-3 text-xl font-semibold text-gray-900 dark:text-white">Your organiser account is suspended</h2>
                @if ($application->review_note)
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $application->review_note }}</p>
                @endif
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Please contact us to discuss it.</p>
            </div>
        @endif

        @if ($showForm)
            @if ($status === 'rejected')
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
                    <strong>Your last application wasn't approved:</strong> {{ $application->review_note }}<br>
                    Update your details below and send it again.
                </div>
            @endif

            <h2 class="mb-1 text-xl font-semibold text-gray-900 dark:text-white">Organiser application</h2>
            <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">We use these details to verify you and to send your payouts.</p>

            @if ($isAdmin)
                <p class="mb-6 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:bg-gray-800/50 dark:text-gray-300">Read-only preview of what applicants fill in.</p>
            @endif
            <form method="POST" action="{{ route('organisers.store') }}" x-data="{ method: '{{ $v('payout_method') ?: 'mpesa' }}' }" class="space-y-8">
                @csrf
                <fieldset @disabled($isAdmin) class="space-y-8 disabled:opacity-70">

                <fieldset class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <legend class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">About you</legend>

                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="name">Organiser or business name <span class="text-red-500">*</span></label>
                        <input id="name" name="name" value="{{ $v('name') }}" required maxlength="120" class="{{ $input }}" placeholder="Shown to buyers on your events">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>

                    <div>
                        <label class="{{ $label }}" for="business_type">You are <span class="text-red-500">*</span></label>
                        <select id="business_type" name="business_type" required class="{{ $input }}">
                            <option value="individual" @selected($v('business_type') === 'individual')>An individual</option>
                            <option value="company" @selected($v('business_type') === 'company')>A registered company</option>
                        </select>
                    </div>

                    <div>
                        <label class="{{ $label }}" for="id_number">ID / passport or company reg. no. <span class="text-red-500">*</span></label>
                        <input id="id_number" name="id_number" value="{{ $v('id_number') }}" required maxlength="50" class="{{ $input }}">
                        <x-input-error :messages="$errors->get('id_number')" class="mt-1" />
                    </div>

                    <div>
                        <label class="{{ $label }}" for="kra_pin">KRA PIN <span class="text-red-500">*</span></label>
                        <input id="kra_pin" name="kra_pin" value="{{ $v('kra_pin') }}" required maxlength="11" class="{{ $input }} uppercase" placeholder="A012345678Z">
                        <x-input-error :messages="$errors->get('kra_pin')" class="mt-1" />
                    </div>

                    <div>
                        <label class="{{ $label }}" for="location">Town / city <span class="text-red-500">*</span></label>
                        <input id="location" name="location" value="{{ $v('location') }}" required maxlength="120" class="{{ $input }}" placeholder="Nairobi">
                        <x-input-error :messages="$errors->get('location')" class="mt-1" />
                    </div>

                    <div>
                        <label class="{{ $label }}" for="phone">Phone <span class="text-red-500">*</span></label>
                        <input id="phone" name="phone" type="tel" value="{{ $v('phone') ?? $user->phone }}" required class="{{ $input }}" placeholder="0712345678">
                        <x-input-error :messages="$errors->get('phone')" class="mt-1" />
                    </div>

                    <div>
                        <label class="{{ $label }}" for="email">Business email <span class="text-red-500">*</span></label>
                        <input id="email" name="email" type="email" value="{{ $v('email') ?? $user->email }}" required class="{{ $input }}">
                        <x-input-error :messages="$errors->get('email')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="website">Website or social page</label>
                        <input id="website" name="website" type="url" value="{{ $v('website') }}" class="{{ $input }}" placeholder="https://instagram.com/yourpage">
                        <x-input-error :messages="$errors->get('website')" class="mt-1" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="description">What events or experiences will you sell? <span class="text-red-500">*</span></label>
                        <textarea id="description" name="description" rows="4" required minlength="30" maxlength="2000"
                                  class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"
                                  placeholder="E.g. monthly comedy nights in Westlands, around 300 guests each">{{ $v('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                </fieldset>

                <fieldset class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <legend class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Payouts</legend>

                    <div class="sm:col-span-2 flex flex-wrap gap-3">
                        @foreach (['mpesa' => 'M-Pesa', 'bank' => 'Bank account'] as $key => $text)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium"
                                   :class="method === '{{ $key }}' ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400' : 'border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-300'">
                                <input type="radio" name="payout_method" value="{{ $key }}" x-model="method" class="sr-only">
                                {{ $text }}
                            </label>
                        @endforeach
                    </div>

                    <div x-show="method === 'mpesa'" class="sm:col-span-2">
                        <label class="{{ $label }}" for="payout_phone">M-Pesa number <span class="text-red-500">*</span></label>
                        <input id="payout_phone" name="payout_phone" type="tel" value="{{ $v('payout_phone') }}" :required="method === 'mpesa'" class="{{ $input }}" placeholder="0712345678">
                        <x-input-error :messages="$errors->get('payout_phone')" class="mt-1" />
                    </div>

                    <template x-if="method === 'bank'">
                        <div class="contents">
                            <div>
                                <label class="{{ $label }}" for="bank_name">Bank <span class="text-red-500">*</span></label>
                                <input id="bank_name" name="bank_name" value="{{ $v('bank_name') }}" required class="{{ $input }}" placeholder="Equity Bank">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="bank_account_number">Account number <span class="text-red-500">*</span></label>
                                <input id="bank_account_number" name="bank_account_number" value="{{ $v('bank_account_number') }}" required class="{{ $input }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="bank_account_name">Account name <span class="text-red-500">*</span></label>
                                <input id="bank_account_name" name="bank_account_name" value="{{ $v('bank_account_name') }}" required class="{{ $input }}">
                            </div>
                        </div>
                    </template>
                    @foreach (['bank_name', 'bank_account_number', 'bank_account_name'] as $field)
                        <x-input-error :messages="$errors->get($field)" class="sm:col-span-2" />
                    @endforeach
                </fieldset>

                <div>
                    <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="terms" value="1" required class="mt-0.5 h-4 w-4 rounded border-gray-300 text-brand-500">
                        <span>I confirm these details are correct and I agree to the <a href="{{ route('terms') }}" target="_blank" class="font-semibold text-brand-600 hover:underline">terms</a>, including that buyers' payments are collected by {{ \App\Helpers\SystemHelper::appName() }} and paid out to me after the event.</span>
                    </label>
                    <x-input-error :messages="$errors->get('terms')" class="mt-1" />
                </div>

                @unless ($isAdmin)
                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-5 py-3 text-sm font-semibold text-white shadow-theme-xs hover:bg-brand-600 sm:w-auto">
                        {{ $status === 'rejected' ? 'Send again' : 'Send application' }}
                    </button>
                @endunless
                </fieldset>
            </form>
        @endif
    </div>
</div>
@endsection
