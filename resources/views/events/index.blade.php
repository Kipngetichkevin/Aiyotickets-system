@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'My Events' }">
        @include('partials.breadcrumb')
    </div>

    {{-- ── Header Row ─────────────────────────────────────── --}}
    <div class="px-4 sm:px-6 mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">My Events</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Manage your events, ticket types, and attendee lists.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('events.past') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03] dark:text-gray-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Past Events
            </a>

            <a href="{{ route('events.create') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
                Create Event
            </a>
        </div>
    </div>

    {{-- ── Metric Cards ──────────────────────────────────── --}}
    @php
        $totalEvents    = $events->total() ?? $events->count();
        $upcomingCount  = $events->where('date', '>=', now())->count();
        $totalTickets   = $events->sum(fn($e) => $e->tickets->sum('quantity'));
        $totalSold      = $events->sum(fn($e) => $e->tickets->sum(fn($t) => $t->sold_count ?? 0));
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-6 px-4 sm:px-6">
        @foreach ([
            ['label' => 'Total Events',   'value' => number_format($totalEvents),   'hint' => 'All time', 'tone' => 'blue'],
            ['label' => 'Upcoming',       'value' => number_format($upcomingCount), 'hint' => 'Scheduled', 'tone' => 'green'],
            ['label' => 'Tickets Issued', 'value' => number_format($totalSold),     'hint' => 'Across events', 'tone' => 'purple'],
            ['label' => 'Capacity',       'value' => number_format($totalTickets),  'hint' => 'Total seats',   'tone' => 'orange'],
        ] as $card)
            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-xl sm:text-2xl font-semibold text-gray-800 dark:text-white/90">
                    {{ $card['value'] }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Events Table ──────────────────────────────────── --}}
    <div class="px-4 sm:px-6">
        @include('partials.table.table-events', [
            'events'      => $events,
            'merchants'   => $merchants   ?? collect(),
            'eventTypes'  => $eventTypes  ?? collect(),
        ])
    </div>

    {{-- ── Pagination ────────────────────────────────────── --}}
    @if(method_exists($events, 'links'))
        <div class="px-4 sm:px-6 mt-6">
            {{ $events->withQueryString()->links() }}
        </div>
    @endif
</main>
@endsection