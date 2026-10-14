{{-- resources/views/organisers/apply.blade.php — "Sell with us": pitch, application form, application status --}}
@extends('layouts.app')

@section('title', 'Become an organiser')

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
    $appName   = \App\Helpers\SystemHelper::appName();
@endphp

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">

    @if ($isAdmin)
        <div class="mb-8 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
            <span><strong>Admin view.</strong> This is the page organisers see. Applications arrive in Organisers for you to approve.</span>
            <a href="{{ route('admin.organisers.index') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                Review applications ({{ \App\Models\Merchant::where('status', 'pending')->count() }} pending)
            </a>
        </div>
    @endif

    {{-- The pitch lives on the landing page; this page is the application itself --}}
    <section class="mb-10">
        <a href="{{ route('organisers.landing') }}" class="text-sm font-semibold text-brand-600 hover:underline">← How selling on {{ $appName }} works</a>
        <h1 class="mt-3 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">Become an organiser</h1>
        <p class="mt-2 max-w-2xl text-gray-500 dark:text-gray-400">
            Tell us who you are and where your payouts should go. We review applications within one working day and email you the result.
            {{ $fee }}% per ticket sold, no other fees.
        </p>

        <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-sm font-bold text-gray-900 dark:text-white">Have these ready</h2>
            <ul class="mt-3 grid grid-cols-1 gap-2 text-sm text-gray-600 dark:text-gray-300 sm:grid-cols-2">
                @foreach ([
                    'National ID / passport, or company registration number',
                    'Your KRA PIN (personal or company)',
                    'A phone number buyers and our team can reach',
                    'An M-Pesa number or bank account in your or your company’s name',
                ] as $point)
                    <li class="flex gap-2"><span class="text-emerald-500">✓</span>{{ $point }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    <span id="apply" class="block scroll-mt-24"></span>

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
