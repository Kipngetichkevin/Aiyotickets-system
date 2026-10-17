{{-- resources/views/user/my-qrs.blade.php --}}
@extends('layouts.app')

@section('content')
<main x-data="myQrCodes()" x-init="load()" x-cloak>
    <div x-data="{ pageName: 'My QR Codes' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">My QR Codes</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Show these at check-in. Download a PDF for offline use.
                </p>
            </div>

            <button @click="load()" :disabled="loading"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                <svg class="w-4 h-4" :class="loading ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                </svg>
                Refresh
            </button>
        </div>

        {{-- FILTER TABS --}}
        <div class="mb-5 flex flex-wrap gap-1.5">
            <template x-for="tab in [{k:'all', l:'All'}, {k:'tickets', l:'Tickets'}, {k:'experiences', l:'Experiences'}]" :key="tab.k">
                <button @click="filter = tab.k; load()"
                        class="inline-flex items-center rounded-lg px-4 py-1.5 text-xs font-semibold transition"
                        :class="filter === tab.k
                            ? 'bg-brand-500 text-white shadow-sm shadow-brand-500/20'
                            : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700'"
                        x-text="tab.l">
                </button>
            </template>
        </div>

        {{-- LOADING --}}
        <div x-show="loading" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <template x-for="i in 3" :key="i">
                <div class="h-72 rounded-2xl bg-gray-100 dark:bg-gray-800 animate-pulse"></div>
            </template>
        </div>

        {{-- EMPTY --}}
        <div x-show="!loading && !hasAny" x-cloak
             class="rounded-2xl border-2 border-dashed border-gray-200 bg-white p-12 text-center dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">No QR codes yet</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                Once you purchase a ticket or an experience, its QR code will appear here.
            </p>
            <div class="mt-5 flex justify-center gap-3">
                <a href="{{ route('events.browse') }}" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Browse Events</a>
                <a href="{{ route('experiences.browse') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Browse Experiences</a>
            </div>
        </div>

        {{-- GRID --}}
        <div x-show="!loading && hasAny" x-cloak class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">

            {{-- TICKETS --}}
            <template x-for="t in tickets" :key="'t-' + t.id">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="cart-gradient px-5 py-3 flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-white/80">Ticket</span>
                        <template x-if="t.checked_in">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold text-white">
                                ✓ Used
                            </span>
                        </template>
                    </div>

                    <div class="p-5">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white truncate" x-text="t.title"></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="t.subtitle"></p>

                        <div class="mt-2 space-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                            <template x-if="t.location"><p>📍 <span x-text="t.location"></span></p></template>
                            <template x-if="t.date"><p>📅 <span x-text="t.date"></span></p></template>
                        </div>

                        {{-- QR --}}
                        <div class="mt-4 rounded-xl bg-white border border-gray-100 p-3 flex justify-center dark:bg-gray-800 dark:border-gray-700">
                            <img x-show="t.qr_url" :src="t.qr_url"
                                 :alt="t.qr_code"
                                 class="w-40 h-40 object-contain rounded-md">
                        </div>
                        <p class="mt-2 text-center font-mono text-[11px] font-semibold text-gray-700 dark:text-gray-300 break-all" x-text="t.qr_code"></p>

                        <div class="mt-4 flex items-center gap-2">
                            <a :href="t.pdf_url" target="_blank"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download PDF
                            </a>
                            <button @click="expand(t)"
                                    class="rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- EXPERIENCES --}}
            <template x-for="e in experiences" :key="'e-' + e.id">
                <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
                    <div class="px-5 py-3 flex items-center justify-between" style="background: linear-gradient(135deg, #ec4899 0%, #a855f7 50%, #7c3aed 100%);">
                        <span class="text-[10px] font-bold uppercase tracking-widest text-white/80">Experience</span>
                        <template x-if="e.checked_in">
                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2 py-0.5 text-[10px] font-bold text-white">
                                ✓ Used
                            </span>
                        </template>
                    </div>

                    <div class="p-5">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white truncate" x-text="e.title"></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="e.subtitle"></p>

                        <div class="mt-2 space-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                            <template x-if="e.location"><p>📍 <span x-text="e.location"></span></p></template>
                            <template x-if="e.date"><p>📅 <span x-text="e.date"></span></p></template>
                        </div>

                        <div class="mt-4 rounded-xl bg-white border border-gray-100 p-3 flex justify-center dark:bg-gray-800 dark:border-gray-700">
                            <img x-show="e.qr_url" :src="e.qr_url"
                                 :alt="e.qr_code"
                                 class="w-40 h-40 object-contain rounded-md">
                        </div>
                        <p class="mt-2 text-center font-mono text-[11px] font-semibold text-gray-700 dark:text-gray-300 break-all" x-text="e.qr_code"></p>

                        <div class="mt-4 flex items-center gap-2">
                            <a :href="e.pdf_url" target="_blank"
                               class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold text-white hover:opacity-90"
                               style="background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Download PDF
                            </a>
                            <button @click="expand(e)"
                                    class="rounded-lg border border-gray-200 p-2 text-gray-500 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- FULLSCREEN QR MODAL --}}
        <div x-show="expanded" x-cloak @keydown.escape.window="expanded = null"
             class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/90 p-4"
             @click.self="expanded = null">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 dark:bg-gray-900">
                <div class="flex items-start justify-between mb-4">
                    <div class="min-w-0">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white truncate" x-text="expanded?.title"></h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="expanded?.subtitle"></p>
                    </div>
                    <button @click="expanded = null" class="ml-3 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex justify-center rounded-2xl bg-white border border-gray-100 p-4">
                    <img x-show="expanded?.qr_url" :src="expanded?.qr_url || ''"
                         alt="QR code"
                         class="w-full max-w-sm object-contain rounded-lg">
                </div>

                <p class="mt-4 text-center font-mono text-sm font-semibold text-gray-700 dark:text-gray-300 break-all" x-text="expanded?.qr_code"></p>
                <p class="mt-1 text-center text-xs text-gray-500 dark:text-gray-400">Show this to the gate staff</p>
            </div>
        </div>

    </div>
</main>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('myQrCodes', () => ({
        loading: true,
        filter: 'all',
        tickets: [],
        experiences: [],
        expanded: null,

        get hasAny() {
            return this.tickets.length > 0 || this.experiences.length > 0;
        },

        async load() {
            this.loading = true;
            try {
                const res = await fetch(`{{ route('my-qrs.json') }}?filter=${this.filter}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                const json = await res.json();
                if (json.success) {
                    this.tickets = json.tickets;
                    this.experiences = json.experiences;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.loading = false;
            }
        },

        expand(item) {
            this.expanded = item;
        },
    }));
});
</script>
@endsection