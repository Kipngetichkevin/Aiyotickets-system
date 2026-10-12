{{-- resources/views/experiences/bookings-index.blade.php --}}
@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'Bookings' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        {{-- HEADER --}}
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Bookings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                All experience bookings, grouped by experience.
            </p>
        </div>

        {{-- TOTALS --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Experiences</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['experiences']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Being tracked</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total bookings</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totals['bookings']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Across all</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Checked in</p>
                <p class="mt-2 text-2xl font-bold text-green-600">{{ number_format($totals['checked_in']) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $totals['bookings'] > 0 ? round(($totals['checked_in'] / max($totals['bookings'], 1)) * 100) : 0 }}% of total
                </p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Revenue</p>
                <p class="mt-2 text-2xl font-bold text-brand-600">KES {{ number_format($totals['revenue'], 0) }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">From paid bookings</p>
            </div>
        </div>

        {{-- GRID --}}
        @if($experiences->count() > 0)
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach($experiences as $exp)
                    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden hover:shadow-theme-md transition">
                        <div class="aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800 relative">
                            @if($exp->image)
                                <img src="{{ asset('storage/' . $exp->image) }}" alt="{{ $exp->name }}" class="w-full h-full object-cover">
                            @elseif($exp->poster_image)
                                <img src="{{ asset('storage/' . $exp->poster_image) }}" alt="{{ $exp->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300 dark:text-gray-700">
                                    <svg class="h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0022.5 18.75V5.25A2.25 2.25 0 0020.25 3H3.75A2.25 2.25 0 001.5 5.25v13.5A2.25 2.25 0 003.75 21z"/>
                                    </svg>
                                </div>
                            @endif

                            <span class="absolute top-3 right-3 inline-flex items-center gap-1.5 rounded-full bg-black/60 px-2.5 py-1 text-xs font-semibold text-white shadow backdrop-blur-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/>
                                </svg>
                                {{ $exp->bookings_count }}
                            </span>
                        </div>

                        <div class="p-5">
                            <h3 class="text-base font-bold text-gray-900 dark:text-white truncate">{{ $exp->name }}</h3>

                            @if($exp->location)
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">📍 {{ $exp->location }}</p>
                            @endif

                            <div class="mt-4 grid grid-cols-3 gap-3 py-3 border-t border-b border-gray-100 dark:border-gray-800">
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">Total</p>
                                    <p class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white">{{ $exp->bookings_count }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">In</p>
                                    <p class="mt-0.5 text-lg font-bold text-green-600">{{ $exp->checked_in_count }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-semibold text-gray-500 uppercase tracking-wide">KES</p>
                                    <p class="mt-0.5 text-lg font-bold text-brand-600">{{ number_format($exp->revenue, 0) }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2">
                                <a href="{{ route('experiences.bookings', $exp->id) }}"
                                   class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                                    View bookings
                                </a>
                                <a href="{{ route('qrcode.experience-scanner', $exp->id) }}"
                                   title="Scan QR"
                                   class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-2.5 py-2 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4m8-18h4a2 2 0 012 2v4m-4 14h4a2 2 0 002-2v-4M7 12h10"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">No experiences yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create an experience to start receiving bookings.</p>
                <a href="{{ route('experiences.index') }}"
                   class="mt-5 inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                    Go to Experiences
                </a>
            </div>
        @endif
    </div>
</main>
@endsection