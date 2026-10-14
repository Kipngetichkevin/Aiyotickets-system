{{-- resources/views/qrcode/gates.blade.php — gate links for door staff (organiser) --}}
@extends('layouts.app')

@section('title', 'Gate staff links')

@php
    $field   = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $scanner = $type === 'event' ? route('qrcode.show', $target->id) : route('qrcode.experience-scanner', $target->id);
@endphp

@section('content')
<main>
    <div x-data="{ pageName: 'Gate staff links' }">@include('partials.breadcrumb')</div>

    <div class="mx-auto max-w-4xl p-4 md:p-6">
        <a href="{{ $scanner }}" class="text-sm font-semibold text-brand-600 hover:underline">← Back to the scanner</a>
        <h1 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">Gate staff links — {{ $target->name }}</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Give each door volunteer their own link. It opens only this {{ $type }}'s scanner — no account, no access to sales or payouts —
            and stops working when it expires or you switch it off.
        </p>

        @include('finance._flash')

        {{-- New link --}}
        <form method="POST" action="{{ route('qrcode.gates.store', [$type, $target->id]) }}"
              class="mt-6 grid grid-cols-1 gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:grid-cols-4">
            @csrf
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="label">Who / which gate</label>
                <input id="label" name="label" value="{{ old('label') }}" required maxlength="100" placeholder="Main gate — Brian" class="{{ $field }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="pin">PIN (optional)</label>
                <input id="pin" name="pin" value="{{ old('pin') }}" inputmode="numeric" pattern="[0-9]{4,6}" maxlength="6" placeholder="4–6 digits" class="{{ $field }}">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400" for="expires_at">Works until</label>
                <input id="expires_at" type="datetime-local" name="expires_at" value="{{ old('expires_at', $defaultExpiry->format('Y-m-d\TH:i')) }}" required class="{{ $field }}">
            </div>
            <div class="sm:col-span-4 flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-gray-500 dark:text-gray-400">A PIN stops a forwarded link from being used by someone else. Send the PIN separately.</p>
                <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Create link</button>
            </div>
        </form>

        {{-- Links --}}
        <div class="mt-6 space-y-4">
            @forelse ($passes as $pass)
                @php
                    $active = $pass->isActive();
                    $state  = $pass->revoked_at ? 'Switched off' : ($active ? 'Active' : 'Expired');
                    $share  = "You're on the gate for {$target->name}. Open this link on your phone to scan tickets: " . $pass->url();
                @endphp
                <div x-data="{ copied: false, qr: {{ session('created') === $pass->id ? 'true' : 'false' }} }"
                     class="rounded-2xl border bg-white p-5 shadow-theme-xs dark:bg-gray-900 {{ session('created') === $pass->id ? 'border-brand-300 ring-2 ring-brand-500/20' : 'border-gray-200 dark:border-gray-800' }} {{ $active ? '' : 'opacity-60' }}">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $pass->label }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                <span class="font-semibold {{ $active ? 'text-emerald-600' : 'text-gray-500' }}">{{ $state }}</span>
                                · until {{ $pass->expires_at?->format('D d M, H:i') ?? 'switched off' }}
                                · {{ $pass->pin_hash ? 'PIN required' : 'no PIN' }}
                                · {{ number_format($scans[$pass->id] ?? 0) }} checked in
                                @if ($pass->last_used_at) · last used {{ $pass->last_used_at->diffForHumans() }}@endif
                            </p>
                        </div>
                        @if ($active)
                            <form method="POST" action="{{ route('qrcode.gates.revoke', $pass) }}" onsubmit="return confirm('Switch off this link? It stops working immediately.')">
                                @csrf
                                <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/30">Switch off</button>
                            </form>
                        @endif
                    </div>

                    @if ($active)
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <input readonly value="{{ $pass->url() }}" class="h-10 min-w-0 flex-1 rounded-lg border border-gray-200 bg-gray-50 px-3 font-mono text-xs text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200" @focus="$event.target.select()">
                            <button type="button" @click="navigator.clipboard.writeText(@js($pass->url())); copied = true; setTimeout(() => copied = false, 2000)"
                                    class="h-10 rounded-lg border border-gray-200 px-3 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200" x-text="copied ? 'Copied ✓' : 'Copy'"></button>
                            <a href="https://wa.me/?text={{ rawurlencode($share) }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex h-10 items-center rounded-lg bg-emerald-500 px-3 text-xs font-semibold text-white hover:bg-emerald-600">WhatsApp</a>
                            <button type="button" @click="qr = !qr" class="h-10 rounded-lg border border-gray-200 px-3 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200" x-text="qr ? 'Hide QR' : 'Show QR'"></button>
                        </div>
                        <div x-show="qr" x-cloak class="mt-4 flex items-center gap-4 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/50">
                            <div class="rounded-lg bg-white p-2">{!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(150)->margin(0)->generate($pass->url()) !!}</div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">The volunteer points their phone camera at this code to open the scanner{{ $pass->pin_hash ? ', then enters the PIN' : '' }}.</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border-2 border-dashed border-gray-200 py-12 text-center text-sm text-gray-500 dark:border-gray-800">
                    No gate links yet. Create one for each person working the door.
                </div>
            @endforelse
        </div>
    </div>
</main>
@endsection
