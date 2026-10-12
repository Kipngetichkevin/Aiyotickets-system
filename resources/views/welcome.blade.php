<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \App\Helpers\SystemHelper::appName() }} | Home</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')
    @vite('resources/js/bootstrap.js')
</head>
<body class="font-outfit antialiased bg-gray-50"
      x-data="{
          sidebarToggle: false,
          darkMode: JSON.parse(localStorage.getItem('darkMode')) || false,
      }"
      x-init="$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
      :class="{'dark bg-gray-900': darkMode}">

    <div class="flex h-screen overflow-hidden">

        {{-- SIDEBAR --}}
        @include('partials.sidebar')

        {{-- MAIN COLUMN --}}
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">

            {{-- Mobile hamburger (only visible on small screens) --}}
            <button type="button"
                    @click="sidebarToggle = !sidebarToggle"
                    class="lg:hidden fixed top-3 left-3 z-50 inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white border border-gray-200 shadow-sm dark:bg-gray-900 dark:border-gray-800">
                <svg class="w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Top header --}}
            @auth
                @include('partials.header')
            @else
                @include('includes.header', ['events' => $upcoming ?? collect()])
            @endauth

            {{-- PAGE CONTENT --}}
            <main class="flex-1">

                @php
                    $activeTab    = request('tab', 'upcoming');
                    $activeEvents = $activeTab === 'past' ? $past : $upcoming;
                @endphp

                {{-- ============================================================
                     EVENTS SECTION
                ============================================================ --}}
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
                    <div class="mb-4">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Events</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Concerts, conferences, sports and more.</p>
                    </div>

                    {{-- Tabs --}}
                    <div class="flex flex-wrap items-center gap-2 border-b border-gray-200 dark:border-gray-800 pb-3 mb-6">
                        <a href="{{ route('events.browse', ['tab' => 'upcoming']) }}"
                           class="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition
                                  {{ $activeTab === 'upcoming'
                                      ? 'bg-blue-600 text-white shadow-md shadow-blue-300/40'
                                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Upcoming Events
                            <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold {{ $activeTab === 'upcoming' ? 'bg-white/20' : 'bg-black/10' }}">
                                {{ $upcoming->count() }}
                            </span>
                        </a>

                        <a href="{{ route('events.browse', ['tab' => 'past']) }}"
                           class="inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition
                                  {{ $activeTab === 'past'
                                      ? 'bg-gray-800 text-white shadow-md'
                                      : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Past Events
                            <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-bold {{ $activeTab === 'past' ? 'bg-white/20' : 'bg-black/10' }}">
                                {{ $past->count() }}
                            </span>
                        </a>
                    </div>
                </div>

                @if($activeTab === 'past')
                    <div class="opacity-90">
                        @include('includes.cards', ['events' => $activeEvents, 'emptyMessage' => 'No past events yet.'])
                    </div>
                @else
                    @include('includes.cards', ['events' => $activeEvents, 'emptyMessage' => 'No upcoming events found.'])
                @endif

                {{-- ============================================================
                     EXPERIENCES SECTION
                ============================================================ --}}
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-12">

                    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Experiences</h2>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Multi-day adventures with packages and full itineraries.</p>
                        </div>

                        <a href="{{ route('experiences.browse') }}"
                           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            View All Experiences
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                            </svg>
                        </a>
                    </div>

                    @if($experiences->isEmpty())
                        <div class="rounded-2xl border-2 border-dashed border-gray-200 bg-white py-16 text-center dark:border-gray-800 dark:bg-gray-900">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">No experiences yet</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Check back later — new adventures are added regularly.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($experiences as $exp)
                                <a href="{{ route('experience.show', $exp->slug) }}"
                                   class="group block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-lg transition overflow-hidden dark:border-gray-800 dark:bg-gray-900">

                                    {{-- Image --}}
                                    <div class="relative aspect-video overflow-hidden bg-gray-100 dark:bg-gray-800">
                                        @if($exp->image)
                                            <img src="{{ asset('storage/' . ltrim($exp->image, 'storage/')) }}"
                                                 alt="{{ $exp->name }}"
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                        @elseif($exp->poster_image)
                                            <img src="{{ asset('storage/' . ltrim($exp->poster_image, 'storage/')) }}"
                                                 alt="{{ $exp->name }}"
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                     stroke="white" stroke-width="1.5" class="h-14 w-14 opacity-70">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                                </svg>
                                            </div>
                                        @endif

                                        @if($exp->featured)
                                            <span class="absolute top-3 left-3 inline-flex items-center gap-1 rounded-full bg-purple-500/90 px-2.5 py-1 text-xs font-semibold text-white shadow backdrop-blur-sm">
                                                ★ Featured
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Body --}}
                                    <div class="p-5">
                                        <h3 class="font-bold text-gray-900 dark:text-white truncate group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                            {{ $exp->name }}
                                        </h3>

                                        @if($exp->short_description)
                                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $exp->short_description }}</p>
                                        @endif

                                        <div class="mt-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                                            @if($exp->location)
                                                <p>📍 {{ $exp->location }}</p>
                                            @endif
                                            @if($exp->start_date)
                                                <p>
                                                    📅 {{ \Carbon\Carbon::parse($exp->start_date)->format('d M') }}
                                                    @if($exp->end_date)
                                                        – {{ \Carbon\Carbon::parse($exp->end_date)->format('d M Y') }}
                                                    @endif
                                                </p>
                                            @endif
                                        </div>

                                        @if($exp->packages->count() > 0)
                                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex items-end justify-between">
                                                <div>
                                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">From</p>
                                                    <p class="text-lg font-bold text-gray-900 dark:text-white">
                                                        KES {{ number_format((float) $exp->packages->min('price'), 0) }}
                                                    </p>
                                                </div>
                                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 dark:text-blue-400 group-hover:underline">
                                                    View
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </main>

            @include('includes.footer')
        </div>
    </div>

</body>
</html>