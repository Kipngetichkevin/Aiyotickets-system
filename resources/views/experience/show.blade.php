{{-- resources/views/experience/show.blade.php --}}
@extends('layouts.app')

@section('title', $experience->name)

@push('meta')
    @php($expDesc = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags($experience->description ?? ''))), 160))
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $experience->name }}">
    <meta property="og:description" content="{{ $expDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($experience->image)
        <meta property="og:image" content="{{ str_starts_with($experience->image, 'http') ? $experience->image : asset('storage/' . $experience->image) }}">
    @endif
@endpush

@section('content')
<script>window.APP_AUTHED = {{ auth()->check() ? 'true' : 'false' }};</script>

<main x-data="experienceRegistration({
    slug: '{{ $experience->slug }}',
    packages: {{ $experience->packages->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price])->values()->toJson() }},
    categories: {{ $experience->categories->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'surcharge' => (float) $c->price_surcharge])->values()->toJson() }}
})" x-cloak>

    {{-- ============================================================
         HERO
    ============================================================ --}}
    <div class="relative w-full overflow-hidden">
        <div class="absolute inset-0">
            @if($experience->banner)
                <img src="{{ asset('storage/' . $experience->banner) }}" alt="" class="w-full h-full object-cover">
            @elseif($experience->image)
                <img src="{{ asset('storage/' . $experience->image) }}" alt="" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/60 to-black/40"></div>
        </div>

        <div class="relative mx-auto max-w-6xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="text-white space-y-5">

                <div class="flex flex-wrap items-center gap-2">
                    @if($experience->featured)
                        <span class="inline-flex items-center gap-1 rounded-full bg-purple-500/90 px-3 py-1 text-xs font-semibold text-white shadow-lg backdrop-blur-sm">
                            ★ Featured
                        </span>
                    @endif
                    @if($experience->location)
                        <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-3 py-1 text-xs font-medium text-white backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" class="h-3.5 w-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            {{ $experience->location }}
                        </span>
                    @endif
                </div>

                <h1 class="text-4xl md:text-6xl font-bold drop-shadow-lg leading-tight">
                    {{ $experience->name }}
                </h1>

                @if($experience->short_description)
                    <p class="max-w-3xl text-lg md:text-xl text-white/90">
                        {{ $experience->short_description }}
                    </p>
                @endif

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    @if($experience->start_date)
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
                            </svg>
                            {{ $experience->start_date->format('d M') }}
                            @if($experience->end_date)
                                – {{ $experience->end_date->format('d M Y') }}
                            @else
                                {{ $experience->start_date->format('Y') }}
                            @endif
                        </div>
                    @endif

                    @if($experience->total_capacity)
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                            </svg>
                            {{ $experience->total_capacity }} spots
                        </div>
                    @endif

                    @if($experience->days->count() > 0)
                        <div class="flex items-center gap-2 rounded-lg bg-white/10 px-4 py-2 text-sm backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $experience->days->count() }} {{ \Illuminate\Support\Str::plural('day', $experience->days->count()) }}
                        </div>
                    @endif
                </div>

                {{-- ============================================================
                     COUNTDOWN TIMER
                ============================================================ --}}
                @if($experience->start_date && $experience->start_date->isFuture())
                    <div x-data="experienceCountdown('{{ $experience->start_date->toIso8601String() }}')"
                         x-init="start()">
                        <div class="inline-flex flex-wrap items-center gap-x-5 gap-y-2 rounded-2xl backdrop-blur-md border border-white/10 px-5 py-3 shadow-2xl {{ $experience->start_date->diffInHours(now()) < 24 ? 'bg-red-600/50' : 'bg-black/50' }}">
                            <div class="flex items-center gap-2 text-white/80">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-widest">Starts in</span>
                            </div>
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="days"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Days</div>
                                </div>
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="hours"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Hrs</div>
                                </div>
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="mins"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Mins</div>
                                </div>
                                <div class="text-center min-w-[42px]">
                                    <div class="text-2xl font-black text-white tabular-nums leading-none" x-text="secs"></div>
                                    <div class="mt-1 text-[10px] font-semibold text-white/60 uppercase tracking-wider">Secs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    {{-- ============================================================
         BODY
    ============================================================ --}}
    <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- LEFT: Description + Itinerary --}}
            <div class="lg:col-span-2 space-y-8">

                @if($experience->description)
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                     class="h-5 w-5">
                                    <path d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">About This Experience</h2>
                        </div>
                        <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-300 dark:prose-invert">
                            {!! nl2br(e($experience->description)) !!}
                        </div>
                    </div>
                @endif

                {{-- Photo gallery: click a photo to open it full size --}}
                @php($gallery = collect($experience->gallery ?? [])->filter()->values())
                @if ($gallery->isNotEmpty())
                    <div x-data="{ open: null }" class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
                        <h2 class="mb-4 text-xl font-semibold text-gray-800 dark:text-white/90">Photos</h2>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach ($gallery as $i => $photo)
                                <button type="button" @click="open = {{ $i }}"
                                        class="group relative overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800 {{ $i === 0 ? 'col-span-2 row-span-2 aspect-square sm:aspect-auto' : 'aspect-square' }}">
                                    <img src="{{ asset('storage/' . ltrim($photo, '/')) }}" alt="{{ $experience->name }} photo {{ $i + 1 }}" loading="lazy"
                                         class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                </button>
                            @endforeach
                        </div>

                        {{-- Full-size viewer --}}
                        <div x-show="open !== null" x-cloak x-transition.opacity @keydown.escape.window="open = null"
                             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/90 p-4" @click.self="open = null">
                            @foreach ($gallery as $i => $photo)
                                <img x-show="open === {{ $i }}" src="{{ asset('storage/' . ltrim($photo, '/')) }}" alt=""
                                     class="max-h-[90vh] max-w-full rounded-xl object-contain">
                            @endforeach
                            <button type="button" @click="open = null" aria-label="Close"
                                    class="absolute right-4 top-4 flex h-10 w-10 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                            <button type="button" @click="open = (open - 1 + {{ $gallery->count() }}) % {{ $gallery->count() }}" aria-label="Previous photo"
                                    class="absolute left-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="open = (open + 1) % {{ $gallery->count() }}" aria-label="Next photo"
                                    class="absolute right-4 top-1/2 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white hover:bg-white/30">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if($experience->days->count() > 0)
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:p-8">
                        <div class="mb-8 flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10 dark:text-brand-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                     class="h-5 w-5">
                                    <path d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-white/90">Itinerary</h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Day-by-day schedule · every registered member joins all activities
                                </p>
                            </div>
                        </div>

                        <div class="space-y-12">
                            @foreach($experience->days as $day)
                                <div class="relative">
                                    <div class="mb-5 flex flex-wrap items-center gap-3">
                                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-600 text-xl font-bold text-white shadow-lg shadow-brand-500/30">
                                            {{ $day->day_number }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                                                Day {{ $day->day_number }}
                                            </p>
                                            <h3 class="mt-0.5 text-lg font-bold text-gray-900 dark:text-white">
                                                {{ $day->title }}
                                            </h3>
                                            @if($day->date)
                                                <p class="mt-0.5 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                    {{ \Carbon\Carbon::parse($day->date)->format('l, d M Y') }}
                                                </p>
                                            @endif
                                        </div>
                                        @if($day->activities->count() > 0)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                     stroke="currentColor" stroke-width="2.5" class="h-3 w-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $day->activities->count() }}
                                            </span>
                                        @endif
                                    </div>

                                    @if($day->description)
                                        <p class="mb-5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                            {{ $day->description }}
                                        </p>
                                    @endif

                                    @if($day->activities->count() > 0)
                                        <div class="relative space-y-3 border-l-2 border-dashed border-brand-200 pl-6 dark:border-brand-800/60">
                                            @foreach($day->activities as $index => $activity)
                                                <div class="group relative">
                                                    <div class="absolute -left-[31px] top-6 flex h-4 w-4 items-center justify-center rounded-full bg-white ring-2 ring-brand-400 transition-all group-hover:scale-125 group-hover:ring-brand-600 dark:bg-gray-900">
                                                        <div class="h-1.5 w-1.5 rounded-full bg-brand-500"></div>
                                                    </div>

                                                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-300 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900 dark:hover:border-brand-700">
                                                        <div class="flex flex-col sm:flex-row">

                                                            @if($activity->image)
                                                                <div class="sm:w-44 sm:shrink-0">
                                                                    <div class="aspect-video sm:aspect-auto sm:h-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                                                                        <img src="{{ asset('storage/' . $activity->image) }}"
                                                                             alt="{{ $activity->title }}"
                                                                             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            <div class="flex-1 p-5">
                                                                <div class="mb-2 flex items-center justify-between gap-3">
                                                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-gray-100 text-xs font-bold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                                        {{ $index + 1 }}
                                                                    </span>
                                                                    @if($activity->time_label)
                                                                        <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700 dark:bg-brand-500/10 dark:text-brand-400">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                                                 stroke="currentColor" stroke-width="2.5" class="h-3 w-3">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                            </svg>
                                                                            {{ $activity->time_label }}
                                                                        </span>
                                                                    @endif
                                                                </div>

                                                                <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                                                    {{ $activity->title }}
                                                                </h4>

                                                                @if($activity->description)
                                                                    <p class="mt-1.5 text-sm leading-relaxed text-gray-600 dark:text-gray-400">
                                                                        {{ $activity->description }}
                                                                    </p>
                                                                @endif

                                                                @if($activity->tags && count(array_filter($activity->tags)) > 0)
                                                                    <div class="mt-3 flex flex-wrap gap-1.5">
                                                                        @foreach($activity->tags as $tag)
                                                                            @if(trim($tag) !== '')
                                                                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">
                                                                                    {{ $tag }}
                                                                                </span>
                                                                            @endif
                                                                        @endforeach
                                                                    </div>
                                                                @endif

                                                                @if($activity->pro_tip)
                                                                    <div class="mt-3 flex items-start gap-2 rounded-lg border-l-4 border-amber-400 bg-amber-50 px-3 py-2 dark:border-amber-500 dark:bg-amber-500/10">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                                             stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                                                             class="mt-0.5 h-4 w-4 shrink-0 text-amber-600 dark:text-amber-400">
                                                                            <path d="M12 18v-5.25m0 0a6.01 6.01 0 001.5-.189m-1.5.189a6.01 6.01 0 01-1.5-.189m3.75 7.478a12.06 12.06 0 01-4.5 0m3.75 2.383a14.406 14.406 0 01-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 10-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                                                                        </svg>
                                                                        <p class="text-xs leading-relaxed text-amber-800 dark:text-amber-300">
                                                                            <strong>Pro Tip:</strong> {{ $activity->pro_tip }}
                                                                        </p>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            {{-- RIGHT: Categories + Packages + Register --}}
            <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-6 lg:self-start">

                {{-- Race Categories (now selectable) --}}
                @if($experience->categories->count() > 0)
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        <h3 class="mb-1 text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Pick Your Race
                        </h3>
                        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                            Optional. Pick a category or leave blank.
                        </p>
                        <div class="space-y-2">
                            <label class="flex items-center gap-3 rounded-lg border-2 border-gray-200 px-3 py-2.5 cursor-pointer transition hover:border-brand-400 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 dark:border-gray-700 dark:has-[:checked]:border-brand-500 dark:has-[:checked]:bg-brand-500/5">
                                <input type="radio" name="category_id" value=""
                                       x-model="categoryId"
                                       class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">No race — spectator</span>
                            </label>
                            @foreach($experience->categories as $cat)
                                <label class="flex items-center justify-between gap-3 rounded-lg border-2 border-gray-200 px-3 py-2.5 cursor-pointer transition hover:border-brand-400 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 dark:border-gray-700 dark:has-[:checked]:border-brand-500 dark:has-[:checked]:bg-brand-500/5">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="radio" name="category_id" value="{{ $cat->id }}"
                                               x-model.number="categoryId"
                                               class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $cat->name }}</span>
                                    </div>
                                    @if($cat->price_surcharge > 0)
                                        <span class="text-xs font-semibold text-brand-600 dark:text-brand-400 shrink-0">
                                            +{{ number_format((float) $cat->price_surcharge, 0) }}
                                        </span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Packages + Register --}}
                <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="mb-1 text-lg font-bold text-gray-800 dark:text-white/90">
                        Choose Your Package
                    </h3>
                    <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
                        Different tiers change transport, accommodation and extras.
                    </p>

                    @php($instalmentDue = $experience->allow_instalments && $experience->start_date
                        ? $experience->start_date->copy()->subDays($experience->instalment_due_days) : null)
                    @if ($instalmentDue && $instalmentDue->gt(now()->addDay()))
                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                            <p class="font-semibold text-emerald-800 dark:text-emerald-400">Lipa mdogo mdogo available</p>
                            <p class="mt-1 text-emerald-700 dark:text-emerald-300/90">
                                Pay a {{ $experience->instalment_deposit_percent }}% deposit to hold your place, then the rest in any amounts
                                by {{ $instalmentDue->format('d M Y') }}. Choose <strong>Add to Cart</strong>, then <strong>Lipa mdogo mdogo</strong> at checkout.
                            </p>
                        </div>
                    @endif

                    <p class="mb-4 flex gap-2 rounded-xl bg-gray-50 p-3 text-xs text-gray-600 dark:bg-gray-800/50 dark:text-gray-300">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        <span>{{ \App\Support\RefundPolicy::text($experience) }} <a href="{{ route('refund-policy') }}" class="font-semibold underline">Refund policy</a></span>
                    </p>

                    @if($experience->packages->count() > 0)
                        <div class="space-y-3">
                            @foreach($experience->packages as $package)
                                <label class="group block cursor-pointer rounded-xl border-2 border-gray-200 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-brand-400 hover:shadow-md has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50/50 has-[:checked]:shadow-md dark:border-gray-700 dark:hover:border-brand-600 dark:has-[:checked]:border-brand-500 dark:has-[:checked]:bg-brand-500/5">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="min-w-0 flex items-center gap-3">
                                            <input type="radio" name="package_id" value="{{ $package->id }}"
                                                   x-model.number="packageId"
                                                   class="h-4 w-4 shrink-0 border-gray-300 text-brand-500 focus:ring-brand-500">
                                            <div class="min-w-0">
                                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold text-white"
                                                      style="background-color: {{ $package->badge_color ?: '#9ca3af' }};">
                                                    {{ $package->name }}
                                                </span>
                                                @if($package->tagline)
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $package->tagline }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-lg font-bold text-gray-900 dark:text-white">
                                                {{ $package->currency }} {{ number_format((float) $package->price, 0) }}
                                            </p>
                                        </div>
                                    </div>

                                    @if($package->transport_type || $package->accommodation_type)
                                        <div class="mt-3 flex flex-wrap gap-x-3 gap-y-1 border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800 dark:text-gray-400">
                                            @if($package->transport_type)
                                                <span>🚐 {{ $package->transport_type }}</span>
                                            @endif
                                            @if($package->accommodation_type)
                                                <span>🏨 {{ $package->accommodation_type }}</span>
                                            @endif
                                            @if($package->accommodation_sharing)
                                                <span>🛏 {{ $package->accommodation_sharing }}</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($package->includes && count(array_filter($package->includes)) > 0)
                                        <ul class="mt-3 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                                            @foreach($package->includes as $inc)
                                                @if(trim($inc) !== '')
                                                    <li class="flex items-start gap-1.5">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                             stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                                                             class="mt-0.5 h-3 w-3 shrink-0 text-green-500">
                                                            <path d="M4.5 12.75l6 6 9-13.5"/>
                                                        </svg>
                                                        <span>{{ $inc }}</span>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>
                                    @endif
                                </label>
                            @endforeach
                        </div>

                        {{-- REGISTER FORM --}}
                        <div class="mt-6 space-y-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-white/90">
                                Your Details
                            </h4>

                            <input type="text" x-model="form.name" placeholder="Full name" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                            <input type="email" x-model="form.email" placeholder="Email" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                            <input type="tel" x-model="form.phone" placeholder="Phone (2547…)" required
                                   class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                            <select x-model="form.gender"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                <option value="1">Male</option>
                                <option value="2">Female</option>
                                <option value="3">Other</option>
                            </select>
                        </div>

                        {{-- TOTAL + REGISTER --}}
                        <div class="mt-5 rounded-xl bg-gradient-to-br from-brand-50 to-brand-100/60 p-4 dark:from-brand-500/10 dark:to-brand-500/5">
                            <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-300">
                                <span>Package</span>
                                <span x-text="'KES ' + packagePrice.toLocaleString()"></span>
                            </div>
                            <div class="mt-1 flex items-center justify-between text-sm text-gray-600 dark:text-gray-300" x-show="categorySurcharge > 0">
                                <span>Category surcharge</span>
                                <span x-text="'+ KES ' + categorySurcharge.toLocaleString()"></span>
                            </div>
                            <div class="mt-3 flex items-center justify-between border-t border-brand-200/60 pt-3 dark:border-brand-800/40">
                                <span class="text-sm font-semibold text-gray-800 dark:text-white/90">Total</span>
                                <span class="text-xl font-bold text-brand-600 dark:text-brand-400"
                                      x-text="'KES ' + total.toLocaleString()"></span>
                            </div>
                        </div>

                        <div x-show="errorMessage" x-cloak
                             class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400"
                             x-text="errorMessage"></div>

                        <button type="button"
                                @click="register()"
                                :disabled="submitting || !packageId || !form.name || !form.email || !form.phone"
                                class="mt-5 w-full rounded-xl bg-brand-500 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-brand-500/20 transition hover:bg-brand-600 hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-text="submitting ? 'Sending STK push…' : 'Register & Pay'"></span>
                        </button>

                        <button type="button"
                                @click="addToCart()"
                                :disabled="addingToCart || !packageId"
                                class="mt-3 w-full rounded-xl border-2 border-brand-500 bg-white px-5 py-3.5 text-sm font-bold text-brand-600 transition hover:bg-brand-50 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="inline-flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="2" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/>
                                </svg>
                                <span x-text="addingToCart ? 'Adding…' : 'Add to Cart (bundle)'"></span>
                            </span>
                        </button>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            No packages available yet.
                        </p>
                    @endif
                </div>

            </div>

        </div>
    </div>

    {{-- WAITING MODAL --}}
    <div x-show="waiting" x-cloak
         class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/60 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-8 dark:bg-gray-900">
            <div class="text-center">
                <div class="mx-auto mb-4 h-16 w-16 animate-spin rounded-full border-4 border-brand-500 border-t-transparent"></div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">Waiting for Payment</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Please check your phone and enter your M-PESA PIN.
                </p>
                <p class="mt-3 text-xs text-gray-400 dark:text-gray-500" x-text="statusMessage"></p>
            </div>
        </div>
    </div>

</main>

<script>
document.addEventListener('alpine:init', () => {

    /* ============================================================
       Countdown timer component
    ============================================================ */
    Alpine.data('experienceCountdown', (targetIso) => ({
        days: '00',
        hours: '00',
        mins: '00',
        secs: '00',
        interval: null,
        target: new Date(targetIso).getTime(),

        pad(n) {
            return String(n).padStart(2, '0');
        },

        start() {
            this.tick();
            this.interval = setInterval(() => this.tick(), 1000);
        },

        tick() {
            const diff = this.target - Date.now();

            if (diff <= 0) {
                this.days = this.hours = this.mins = this.secs = '00';
                if (this.interval) clearInterval(this.interval);
                return;
            }

            this.days  = this.pad(Math.floor(diff / 86400000));
            this.hours = this.pad(Math.floor((diff % 86400000) / 3600000));
            this.mins  = this.pad(Math.floor((diff % 3600000) / 60000));
            this.secs  = this.pad(Math.floor((diff % 60000) / 1000));
        },

        destroy() {
            if (this.interval) clearInterval(this.interval);
        }
    }));

    /* ============================================================
       Experience registration component
    ============================================================ */
    Alpine.data('experienceRegistration', (config) => ({
        slug: config.slug,
        packages: config.packages || [],
        categories: config.categories || [],

        packageId: null,
        categoryId: null,

        form: {
            name: '',
            email: '',
            phone: '',
            gender: '1',
        },

        submitting: false,
        addingToCart: false,
        waiting: false,
        errorMessage: '',
        statusMessage: '',
        pollTimer: null,
        attempts: 0,

        async register() {
            this.errorMessage = '';
            this.submitting = true;

            try {
                const res = await fetch(`/experiences/${this.slug}/register`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        package_id: this.packageId,
                        category_id: this.categoryId,
                        name: this.form.name,
                        email: this.form.email,
                        phone: this.form.phone,
                        gender: this.form.gender,
                    }),
                });

                if (res.status === 401) {
                    window.location.href = '/login?redirect=' + encodeURIComponent(window.location.pathname);
                    return;
                }

                const json = await res.json();

                if (!json.success) {
                    this.errorMessage = json.message || 'Registration failed.';
                    this.submitting = false;
                    return;
                }

                this.waiting = true;
                this.attempts = 0;
                this.statusMessage = 'Waiting for confirmation…';
                this.pollPayment(json.data.checkout_request_id);
            } catch (err) {
                console.error(err);
                this.errorMessage = 'Network error: ' + err.message;
                this.submitting = false;
            }
        },

        async addToCart() {
            this.errorMessage = '';

            if (!this.packageId) {
                this.errorMessage = 'Please select a package first.';
                return;
            }

            this.addingToCart = true;

            try {
                const res = await fetch(`/experiences/${this.slug}/add-to-cart`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        package_id: this.packageId,
                        category_id: this.categoryId,
                        quantity: 1,
                    }),
                });

                const json = await res.json();

                if (!json.success) {
                    this.errorMessage = json.message || 'Failed to add to cart.';
                    this.addingToCart = false;
                    return;
                }

                window.location.href = json.cart_url || '/cart';
            } catch (err) {
                this.errorMessage = 'Network error: ' + err.message;
                this.addingToCart = false;
            }
        },

        pollPayment(checkoutRequestId) {
            if (this.pollTimer) clearInterval(this.pollTimer);

            const MAX_ATTEMPTS = 90;

            this.pollTimer = setInterval(async () => {
                this.attempts++;
                this.statusMessage = `Checking… (${Math.floor(this.attempts * 2)}s)`;

                try {
                    const res = await fetch(`/mpesa/status/${checkoutRequestId}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const json = await res.json();

                    if (json.success && json.data) {
                        if (json.data.status === 'completed') {
                            clearInterval(this.pollTimer);
                            this.statusMessage = 'Payment confirmed! Redirecting…';

                            if (json.data.order_id) {
                                window.location.href = `/cart/thankyou?order=${json.data.order_id}`;
                            } else {
                                setTimeout(() => this.pollPayment(checkoutRequestId), 2000);
                            }
                            return;
                        }

                        if (json.data.status === 'failed') {
                            clearInterval(this.pollTimer);
                            this.waiting = false;
                            this.submitting = false;
                            this.errorMessage = json.data.result_desc || 'Payment failed.';
                            return;
                        }
                    }

                    if (this.attempts >= MAX_ATTEMPTS) {
                        clearInterval(this.pollTimer);
                        this.waiting = false;
                        this.submitting = false;
                        this.errorMessage = 'Payment is taking longer than expected. Check your M-PESA app.';
                    }
                } catch (err) {
                }
            }, 2000);
        },
    }));
});
</script>
@endsection