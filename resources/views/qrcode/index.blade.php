{{-- resources/views/qrcode/index.blade.php — the gate hub: every event and experience you can check people into --}}
@extends('layouts.app')

@section('title', 'Check-in')

@php
    $card = function ($title, $when, $where, $image, $url, $counts, $kind, $live) {
        $total   = (int) ($counts->total ?? 0);
        $checked = (int) ($counts->checked_in ?? 0);
        return compact('title', 'when', 'where', 'image', 'url', 'total', 'checked', 'kind', 'live') + ['percent' => $total ? round($checked / $total * 100) : 0];
    };
    $gates = collect()
        ->concat($events->map(fn ($e) => $card(
            $e->name, $e->date?->format('D d M · H:i'), $e->location, $e->image_url, route('qrcode.show', $e->id), $eventCounts[$e->id] ?? null, 'Event',
            $e->date && $e->date->lte(now()->addHours(6)),
        )))
        ->concat($experiences->map(fn ($x) => $card(
            $x->name, $x->start_date?->format('D d M') . ($x->end_date ? ' – ' . $x->end_date->format('d M') : ''), $x->location,
            ($x->image ?: $x->poster_image) ? asset('storage/' . ltrim($x->image ?: $x->poster_image, '/')) : null,
            route('qrcode.experience-scanner', $x->id), $expCounts[$x->id] ?? null, 'Experience',
            $x->start_date && $x->start_date->lte(now()->addHours(6)),
        )));
    // Gates opening soonest (or already open) first
    $gates = $gates->sortByDesc('live')->values();
@endphp

@section('content')
<main>
    <div x-data="{ pageName: 'Check-in' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Check-in</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Open a gate to scan tickets. Works on any phone with a camera — open this page on each gate phone.</p>
        </div>

        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ session('error') }}</div>
        @endif

        @if ($gates->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-gray-200 py-16 text-center dark:border-gray-800">
                <p class="text-gray-500 dark:text-gray-400">No upcoming events or experiences to check people into.</p>
                <a href="{{ route('events.index') }}" class="mt-4 inline-flex rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">Go to events</a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($gates as $g)
                    <a href="{{ $g['url'] }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="relative h-36 bg-gradient-to-br from-brand-500 to-purple-600">
                            @if ($g['image'])
                                <img src="{{ $g['image'] }}" alt="" class="h-full w-full object-cover" loading="lazy" onerror="this.remove()">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-950/70 to-transparent"></div>
                            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-gray-800">{{ $g['kind'] }}</span>
                            @if ($g['live'])
                                <span class="absolute right-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-white">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span> Gate open
                                </span>
                            @endif
                            <h3 class="absolute bottom-3 left-4 right-4 truncate text-lg font-semibold text-white">{{ $g['title'] }}</h3>
                        </div>
                        <div class="p-4">
                            <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ collect([$g['when'], $g['where']])->filter()->implode(' · ') }}</p>
                            <div class="mt-4 flex items-center justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-300"><strong class="text-gray-900 dark:text-white">{{ number_format($g['checked']) }}</strong> of {{ number_format($g['total']) }} checked in</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $g['percent'] }}%</span>
                            </div>
                            <div class="mt-2 h-2 rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-2 rounded-full bg-brand-500" style="width: {{ $g['percent'] }}%"></div></div>
                            <span class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white group-hover:bg-brand-600">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/></svg>
                                Open scanner
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</main>
@endsection
