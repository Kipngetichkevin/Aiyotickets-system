{{-- resources/views/experience/browse.blade.php --}}
@extends('layouts.app')

@section('content')
<main>
    <div x-data="{ pageName: 'Browse Experiences' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        {{-- HEADER --}}
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Experiences</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Multi-day adventures with race categories, packages and full itineraries.
            </p>
        </div>

        {{-- SEARCH --}}
        <form method="GET" class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex flex-col gap-1 grow min-w-[240px]">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Search</label>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Search by name, location or description…"
                           class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                </div>
                <button type="submit" class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-medium text-white hover:bg-brand-600">
                    Search
                </button>
                @if(request()->filled('q'))
                    <a href="{{ route('experiences.browse') }}"
                       class="h-10 inline-flex items-center px-2 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        {{-- GRID --}}
        @if($experiences->count() > 0)
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($experiences as $exp)
                    <a href="{{ route('experience.show', $exp->slug) }}"
                       class="group flex flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition hover:-translate-y-1 hover:border-brand-300 hover:shadow-theme-lg dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-700">

                        {{-- Image --}}
                        <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800">
                            @if($exp->image)
                                <img src="{{ asset('storage/' . $exp->image) }}" alt="{{ $exp->name }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @elseif($exp->poster_image)
                                <img src="{{ asset('storage/' . $exp->poster_image) }}" alt="{{ $exp->name }}"
                                     class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-blue-500 to-purple-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="white" stroke-width="1.5" class="h-14 w-14 opacity-70">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                    </svg>
                                </div>
                            @endif

                            @if($exp->featured)
                                <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-purple-500/90 px-2.5 py-1 text-xs font-semibold text-white shadow backdrop-blur-sm">
                                    ★ Featured
                                </span>
                            @endif
                        </div>

                        {{-- Body --}}
                        <div class="flex flex-1 flex-col p-5">
                            <h3 class="text-lg font-semibold text-gray-800 transition group-hover:text-brand-600 dark:text-white/90 dark:group-hover:text-brand-400">
                                {{ $exp->name }}
                            </h3>

                            @if($exp->short_description)
                                <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $exp->short_description }}
                                </p>
                            @endif

                            <div class="mt-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                @if($exp->location)
                                    <p>📍 {{ $exp->location }}</p>
                                @endif
                                @if($exp->start_date)
                                    <p>
                                        📅 {{ $exp->start_date->format('d M') }}
                                        @if($exp->end_date) – {{ $exp->end_date->format('d M Y') }} @endif
                                    </p>
                                @endif
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-1">
                                    @if($exp->packages->count() > 0)
                                        <span class="font-medium text-brand-600 dark:text-brand-400">
                                            📦 {{ $exp->packages->count() }} {{ \Illuminate\Support\Str::plural('package', $exp->packages->count()) }}
                                        </span>
                                    @endif
                                    @if($exp->categories->count() > 0)
                                        <span>🏃 {{ $exp->categories->count() }} {{ \Illuminate\Support\Str::plural('category', $exp->categories->count()) }}</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Bottom --}}
                            <div class="mt-auto flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                                <div>
                                    @if($exp->packages->count() > 0)
                                        <p class="text-xs text-gray-500 dark:text-gray-400">From</p>
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            KES {{ number_format((float) $exp->packages->min('price'), 0) }}
                                        </p>
                                    @endif
                                </div>
                                <span class="inline-flex items-center gap-1 text-sm font-medium text-brand-600 group-hover:underline dark:text-brand-400">
                                    View
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                         stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            @if($experiences->hasPages())
                <div class="mt-6">{{ $experiences->links() }}</div>
            @endif
        @else
            <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" class="mx-auto mb-4 h-16 w-16 text-gray-300 dark:text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">No experiences available</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Check back later — new adventures are added regularly.
                </p>
            </div>
        @endif

    </div>
</main>
@endsection