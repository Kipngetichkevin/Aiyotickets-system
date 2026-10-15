{{-- resources/views/experiences/bookings.blade.php --}}
@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'Experience Bookings' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <a href="{{ route('bookings.index') }}"
                   class="flex h-10 w-10 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
                    </svg>
                </a>
                <div class="min-w-0">
                    <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90 truncate">
                        {{ $experience->name }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Bookings &amp; check-in</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('qrcode.experience-scanner', $experience->id) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3.5 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4m8-18h4a2 2 0 012 2v4m-4 14h4a2 2 0 002-2v-4M7 12h10"/>
                    </svg>
                    Scan QR
                </a>

                <a href="{{ route('experience.show', $experience->slug) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                    </svg>
                    Preview page
                </a>
            </div>
        </div>

        <div class="mb-6 max-w-md">
            @include('partials.listing-changes', ['type' => 'experience', 'item' => $experience])
        </div>

        {{-- STATS --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total bookings</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['total']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['paid'] }} paid</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Checked in</p>
                <p class="mt-2 text-2xl font-bold text-green-600">{{ number_format($stats['checked_in']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $stats['total'] > 0 ? round(($stats['checked_in'] / max($stats['total'], 1)) * 100) : 0 }}% of total
                </p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Not checked in</p>
                <p class="mt-2 text-2xl font-bold text-amber-600">{{ number_format($stats['not_checked']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Still expected</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Revenue</p>
                <p class="mt-2 text-2xl font-bold text-brand-600">KES {{ number_format($stats['revenue'], 0) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">From paid bookings</p>
            </div>
        </div>

        {{-- LIVE CHECK-IN FEED --}}
        <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50/50 p-4 dark:border-emerald-500/30 dark:bg-emerald-500/5"
             x-data="liveCheckInFeed({{ $experience->id }})" x-init="start()" x-cloak>
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        LIVE
                    </span>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Check-in feed</h3>
                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="'(' + entries.length + ')'"></span>
                </div>
                <button @click="toggle()"
                        class="text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300">
                    <span x-text="active ? 'Pause' : 'Resume'"></span>
                </button>
            </div>

            <div x-show="entries.length === 0" class="text-xs text-gray-500 dark:text-gray-400 italic">
                Waiting for check-ins… (open the scanner on your phone and it will appear here)
            </div>

            <ul class="space-y-1.5 max-h-64 overflow-y-auto">
                <template x-for="(e, i) in entries" :key="i">
                    <li class="flex items-center gap-2 text-xs">
                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="e.attendee"></span>
                        <span class="text-gray-500 dark:text-gray-400">·</span>
                        <span class="text-gray-600 dark:text-gray-300" x-text="e.package || ''"></span>
                        <span class="ml-auto text-gray-400 shrink-0" x-text="e.time"></span>
                    </li>
                </template>
            </ul>
        </div>

        {{-- FILTERS + SEARCH --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex flex-wrap gap-1">
                    @php
                        $tabs = [
                            'all'            => 'All',
                            'paid'           => 'Paid',
                            'checked-in'     => 'Checked in',
                            'not-checked-in' => 'Not checked in',
                        ];
                    @endphp
                    @foreach($tabs as $key => $label)
                        <a href="{{ route('experiences.bookings', ['id' => $experience->id, 'filter' => $key, 'q' => request('q')]) }}"
                           class="inline-flex items-center rounded-lg px-3.5 py-1.5 text-xs font-semibold transition
                                  {{ $filter === $key
                                      ? 'bg-brand-500 text-white shadow-sm shadow-brand-500/20'
                                      : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('experiences.bookings', $experience->id) }}"
                      class="flex items-center gap-2">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" stroke-width="2"/>
                            <line x1="21" y1="21" x2="16.65" y2="16.65" stroke-width="2"/>
                        </svg>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Search name, email, QR, receipt…"
                               class="h-10 w-64 rounded-lg border border-gray-200 bg-white pl-9 pr-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    </div>
                    <button type="submit"
                            class="h-10 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">
                        Search
                    </button>
                    @if(request('q'))
                        <a href="{{ route('experiences.bookings', ['id' => $experience->id, 'filter' => $filter]) }}"
                           class="text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">

            @if($bookings->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Attendee</th>
                                <th class="px-4 py-3">Package</th>
                                <th class="px-4 py-3">Contact</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                                <th class="px-4 py-3">Receipt</th>
                                <th class="px-4 py-3">QR</th>
                                <th class="px-4 py-3 text-center">Check-in</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($bookings as $b)
                                @php
                                    $initials = collect(explode(' ', trim($b->name)))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
                                    data-booking-id="{{ $b->id }}">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-brand-500 to-brand-600 text-xs font-bold text-white">
                                                {{ $initials }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-medium text-gray-900 dark:text-white truncate">{{ $b->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $b->created_at->format('d M Y, H:i') }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-3">
                                        @if($b->package)
                                            <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-bold text-white"
                                                  style="background-color: {{ $b->package->badge_color ?: '#9ca3af' }};">
                                                {{ $b->package->name }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">—</span>
                                        @endif
                                        @if($b->category)
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">🏃 {{ $b->category->name }}</p>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3">
                                        <p class="text-xs text-gray-700 dark:text-gray-300 truncate max-w-[180px]">{{ $b->email }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $b->phone }}</p>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <p class="font-semibold text-gray-900 dark:text-white">KES {{ number_format($b->total_price, 0) }}</p>
                                        <p class="text-[11px] {{ $b->payment_status === 'paid' ? 'text-green-600' : 'text-amber-600' }} font-semibold uppercase">{{ $b->payment_status }}</p>
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-gray-600 dark:text-gray-300">{{ $b->transaction_code ?? '—' }}</span>
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-gray-700 dark:text-gray-300 break-all">{{ $b->qr_code }}</span>
                                    </td>

                                    <td class="px-4 py-3 text-center">
                                        @if($b->checked_in)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 dark:bg-green-500/10 dark:text-green-400">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Checked in
                                            </span>
                                            @if($b->checked_in_at)
                                                <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">{{ $b->checked_in_at->format('d M, H:i') }}</p>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                                Not yet
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button"
                                                    class="checkin-btn inline-flex items-center gap-1 rounded-lg border px-2.5 py-1.5 text-xs font-semibold transition
                                                           {{ $b->checked_in
                                                               ? 'border-gray-200 text-gray-600 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800'
                                                               : 'border-green-200 text-green-700 hover:bg-green-50 dark:border-green-500/30 dark:text-green-400 dark:hover:bg-green-500/10' }}"
                                                    data-booking="{{ $b->uuid }}"
                                                    data-checked="{{ $b->checked_in ? '1' : '0' }}">
                                                @if($b->checked_in)
                                                    Undo
                                                @else
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    Check in
                                                @endif
                                            </button>

                                            <a href="{{ route('experience.booking.pdf', $b) }}"
                                               target="_blank"
                                               title="Download PDF"
                                               class="inline-flex items-center rounded-lg border border-gray-200 p-1.5 text-gray-500 hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-800">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($bookings->hasPages())
                    <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                        {{ $bookings->links() }}
                    </div>
                @endif
            @else
                <div class="py-16 px-6 text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">No bookings yet</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if(request('q') || $filter !== 'all')
                            Try clearing your filters.
                        @else
                            Bookings will appear here once someone registers.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</main>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('liveCheckInFeed', (experienceId) => ({
        experienceId,
        active: true,
        entries: [],
        seenIds: new Set(),
        timer: null,

        start() {
            this.poll();
            this.timer = setInterval(() => {
                if (this.active) this.poll();
            }, 5000);
        },

        toggle() {
            this.active = !this.active;
        },

        async poll() {
            try {
                const res = await fetch(`/experiences/${this.experienceId}/bookings/feed`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                const json = await res.json();
                if (json.success && Array.isArray(json.items)) {
                    json.items.forEach(item => {
                        if (!this.seenIds.has(item.id)) {
                            this.seenIds.add(item.id);
                            this.entries.unshift(item);
                        }
                    });
                    this.entries = this.entries.slice(0, 20);
                }
            } catch (e) { /* silent */ }
        },
    }));
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrf) return;

    document.querySelectorAll('.checkin-btn').forEach(btn => {
        btn.addEventListener('click', async function () {
            const bookingId = this.dataset.booking;
            const url = `/experiences/bookings/${bookingId}/check-in`;

            const originalHTML = this.innerHTML;
            this.disabled = true;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: JSON.stringify({}),
                });

                const json = await res.json();
                if (!json.success) throw new Error(json.message || 'Failed');
                window.location.reload();
            } catch (e) {
                console.error(e);
                alert('Could not update check-in. Please refresh and try again.');
                this.disabled = false;
                this.innerHTML = originalHTML;
            }
        });
    });
});
</script>
@endsection