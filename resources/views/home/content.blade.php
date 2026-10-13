{{-- resources/views/home/content.blade.php — welcome page body (shared by guest and logged-in layouts) --}}
@php
    $activeTab    = request('tab', 'upcoming');
    $activeEvents = $activeTab === 'past' ? $past : $upcoming;
@endphp

{{-- ============================== HERO SLIDER ============================== --}}
@if ($slides->isNotEmpty())
    <section x-data="heroSlider({{ $slides->count() }})" x-init="play()"
             @mouseenter="pause()" @mouseleave="play()"
             @touchstart.passive="touchStart($event)" @touchend.passive="touchEnd($event)"
             class="relative h-[460px] overflow-hidden bg-gray-900 sm:h-[540px]" aria-roledescription="carousel">

        @foreach ($slides as $i => $slide)
            <div x-show="current === {{ $i }}"
                 x-transition:enter="transition-opacity duration-700" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-700" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="absolute inset-0" @if ($i > 0) x-cloak @endif
                 aria-roledescription="slide" aria-label="{{ $i + 1 }} of {{ $slides->count() }}">
                <div class="absolute inset-0 bg-gradient-to-br from-brand-600 via-purple-700 to-gray-900"></div>
                @if ($slide['image'])
                    <img src="{{ $slide['image'] }}" alt="" class="absolute inset-0 h-full w-full object-cover"
                         @if ($i > 0) loading="lazy" @endif onerror="this.remove()">
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/45 to-black/10"></div>

                <div class="relative mx-auto flex h-full max-w-7xl flex-col justify-end px-4 pb-16 sm:px-6 sm:pb-20 lg:px-8">
                    <span class="mb-4 inline-flex w-fit items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-white backdrop-blur">
                        <span class="h-1.5 w-1.5 rounded-full {{ $slide['kind'] === 'Event' ? 'bg-brand-300' : 'bg-amber-300' }}"></span>
                        {{ $slide['kind'] }}
                    </span>
                    <h2 class="max-w-3xl text-3xl font-bold leading-tight text-white sm:text-5xl">{{ $slide['title'] }}</h2>
                    @if ($slide['meta'])
                        <p class="mt-3 text-sm text-white/80 sm:text-base">{{ $slide['meta'] }}</p>
                    @endif
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <a href="{{ $slide['url'] }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-900 shadow-lg transition hover:bg-gray-100">
                            {{ $slide['cta'] }}
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                        @if ($slide['price'] !== null)
                            <span class="text-sm text-white/80">From <strong class="text-lg text-white">KES {{ number_format((float) $slide['price']) }}</strong></span>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        @if ($slides->count() > 1)
            <button type="button" @click="prev()" aria-label="Previous slide"
                    class="absolute left-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/30 sm:flex">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" @click="next()" aria-label="Next slide"
                    class="absolute right-3 top-1/2 hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/15 text-white backdrop-blur transition hover:bg-white/30 sm:flex">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>

            <div class="absolute bottom-6 left-1/2 flex -translate-x-1/2 gap-2">
                @foreach ($slides as $i => $slide)
                    <button type="button" @click="go({{ $i }})" aria-label="Go to slide {{ $i + 1 }}"
                            class="h-2 rounded-full transition-all" :class="current === {{ $i }} ? 'w-8 bg-white' : 'w-2 bg-white/50 hover:bg-white/80'"></button>
                @endforeach
            </div>
        @endif
    </section>
@else
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-600 via-purple-700 to-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
            <h1 class="max-w-2xl text-4xl font-bold leading-tight text-white sm:text-5xl">Find your next unforgettable moment</h1>
            <p class="mt-4 max-w-xl text-lg text-white/80">Concerts, comedy, festivals and multi-day adventures across Kenya — pay with M-Pesa, get your QR ticket instantly.</p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="#events" class="rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-900 hover:bg-gray-100">Browse events</a>
                <a href="{{ route('experiences.browse') }}" class="rounded-xl border border-white/40 px-6 py-3 text-sm font-bold text-white hover:bg-white/10">Explore experiences</a>
            </div>
        </div>
    </section>
@endif

{{-- ============================== EVENTS ============================== --}}
<section id="events" class="mx-auto max-w-7xl px-4 pt-14 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Events</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Concerts, conferences, sports and more.</p>
        </div>
        <div class="inline-flex rounded-xl bg-gray-100 p-1 dark:bg-gray-800">
            @foreach (['upcoming' => ['Upcoming', $upcoming->count()], 'past' => ['Past', $past->count()]] as $tab => [$label, $count])
                <a href="{{ route('events.browse', ['tab' => $tab]) }}#events"
                   class="inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition {{ $activeTab === $tab ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white' }}">
                    {{ $label }}
                    <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs dark:bg-gray-700">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 {{ $activeTab === 'past' ? 'opacity-90' : '' }}">
        @forelse ($activeEvents as $event)
            <a href="{{ route('event.show', $event->id) }}"
               class="group block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
                <div class="relative aspect-[4/3] overflow-hidden bg-gradient-to-br from-brand-500 to-purple-600">
                    @if ($event->image)
                        <img src="{{ $event->image }}" alt="{{ $event->name }}" loading="lazy" onerror="this.remove()"
                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @endif
                    @if ($event->date)
                        <div class="absolute left-3 top-3 rounded-xl bg-white/95 px-2.5 py-1.5 text-center shadow dark:bg-gray-900/95">
                            <div class="text-lg font-bold leading-none text-gray-900 dark:text-white">{{ $event->date->format('d') }}</div>
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-brand-600">{{ $event->date->format('M') }}</div>
                        </div>
                    @endif
                    @if ($event->end_date && $event->end_date->isPast())
                        <span class="absolute right-3 top-3 rounded-full bg-gray-900/80 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-white">Ended</span>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="truncate font-semibold text-gray-900 transition group-hover:text-brand-600 dark:text-white">{{ $event->name }}</h3>
                    <p class="mt-1 truncate text-xs text-gray-500 dark:text-gray-400">{{ $event->location }}</p>
                    <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-800">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $event->merchant?->name ?? 'Organiser' }}</span>
                        @if ($event->tickets_min_price !== null)
                            <span class="text-sm font-bold text-gray-900 dark:text-white">From KES {{ number_format((float) $event->tickets_min_price) }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full rounded-2xl border-2 border-dashed border-gray-200 py-14 text-center text-gray-500 dark:border-gray-800 dark:text-gray-400">
                {{ $activeTab === 'past' ? 'No past events yet.' : 'No upcoming events right now — check back soon.' }}
            </div>
        @endforelse
    </div>
</section>

{{-- ============================== EXPERIENCES ============================== --}}
<section class="mx-auto max-w-7xl px-4 pt-16 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white sm:text-3xl">Experiences</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Multi-day adventures with packages and full itineraries.</p>
        </div>
        <a href="{{ route('experiences.browse') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
            View all
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
        </a>
    </div>

    @if ($experiences->isEmpty())
        <div class="rounded-2xl border-2 border-dashed border-gray-200 py-14 text-center text-gray-500 dark:border-gray-800 dark:text-gray-400">
            New adventures are added regularly — check back soon.
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($experiences as $exp)
                <a href="{{ route('experience.show', $exp->slug) }}"
                   class="group block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs transition hover:-translate-y-0.5 hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
                    <div class="relative aspect-video overflow-hidden bg-gradient-to-br from-amber-400 to-brand-600">
                        @if ($exp->image || $exp->poster_image)
                            <img src="{{ asset('storage/' . ltrim($exp->image ?: $exp->poster_image, '/')) }}" alt="{{ $exp->name }}" loading="lazy" onerror="this.remove()"
                                 class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        @endif
                        @if ($exp->featured)
                            <span class="absolute left-3 top-3 rounded-full bg-amber-400 px-2.5 py-1 text-xs font-bold text-gray-900 shadow">★ Featured</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <h3 class="truncate font-bold text-gray-900 transition group-hover:text-brand-600 dark:text-white">{{ $exp->name }}</h3>
                        @if ($exp->short_description)
                            <p class="mt-1 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">{{ $exp->short_description }}</p>
                        @endif
                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                            {{ collect([$exp->location, $exp->start_date ? $exp->start_date->format('d M') . ($exp->end_date ? ' – ' . $exp->end_date->format('d M Y') : '') : null])->filter()->implode(' · ') }}
                        </p>
                        @if ($exp->packages->count() > 0)
                            <div class="mt-4 flex items-end justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                                <div>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400">From</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">KES {{ number_format((float) $exp->packages->min('price')) }}</p>
                                </div>
                                @if ($exp->allow_instalments)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Lipa mdogo mdogo</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- ============================== WHY US ============================== --}}
<section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach ([
            ['Pay with M-Pesa', 'Confirm on your phone in seconds. No card needed.', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'],
            ['Instant QR tickets', 'Sent to your email and kept under My QR Codes — just scan at the gate.', 'M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z'],
            ['Lipa mdogo mdogo', 'Hold your place with a deposit and pay the rest bit by bit.', 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ] as [$title, $text, $icon])
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 dark:bg-brand-500/10">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $text }}</p>
            </div>
        @endforeach
    </div>
</section>

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('heroSlider', (count) => ({
        current: 0,
        timer: null,
        startX: 0,
        next() { this.current = (this.current + 1) % count; },
        prev() { this.current = (this.current - 1 + count) % count; },
        go(i) { this.current = i; this.play(); },
        play() {
            this.pause();
            if (count > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.timer = setInterval(() => this.next(), 6000);
            }
        },
        pause() { clearInterval(this.timer); },
        touchStart(e) { this.startX = e.changedTouches[0].clientX; },
        touchEnd(e) {
            const dx = e.changedTouches[0].clientX - this.startX;
            if (Math.abs(dx) > 50) { dx < 0 ? this.next() : this.prev(); this.play(); }
        },
    }));
});
</script>
@endonce
