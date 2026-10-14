{{-- resources/views/qrcode/scanner.blade.php
     Shared gate scanner for events and experiences.
     Expects: $title, $subtitle, $backUrl, $backLabel, $verifyUrl, $searchUrl, $undoUrl (with __ID__), $payload, $stats --}}
@extends('layouts.app')

@section('title', 'Scan — ' . $title)

@section('content')
<main x-data="gateScanner(@js(['verify' => $verifyUrl, 'search' => $searchUrl, 'undo' => $undoUrl]), @js($payload), @js($stats))" x-cloak>
    <div x-data="{ pageName: 'Check-in' }">@include('partials.breadcrumb')</div>

    <div class="mx-auto max-w-screen-md p-4 md:p-6">

        {{-- HEADER --}}
        <div class="mb-5 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <h1 class="truncate text-xl font-semibold text-gray-800 dark:text-white/90 sm:text-2xl">{{ $title }}</h1>
                @if (!empty($subtitle))<p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>@endif
            </div>
            <a href="{{ $backUrl }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                {{ $backLabel }}
            </a>
        </div>

        {{-- CHECK-IN COUNTER --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between text-left">
                <span>
                    <span class="text-3xl font-bold text-gray-900 dark:text-white" x-text="stats.checked_in"></span>
                    <span class="text-sm text-gray-500 dark:text-gray-400" x-text="'of ' + stats.total + ' checked in'"></span>
                </span>
                <span class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                    <span x-text="percent(stats) + '%'"></span>
                    <svg class="h-4 w-4 text-gray-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </button>
            <div class="mt-3 h-2.5 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                <div class="h-2.5 rounded-full bg-brand-500 transition-all duration-500" :style="'width:' + percent(stats) + '%'"></div>
            </div>
            <div x-show="open" x-transition class="mt-4 space-y-2.5">
                <template x-for="t in stats.by_type || []" :key="t.name">
                    <div>
                        <div class="flex justify-between text-xs"><span class="text-gray-600 dark:text-gray-300" x-text="t.name"></span><span class="font-semibold text-gray-900 dark:text-white" x-text="t.checked_in + ' / ' + t.total"></span></div>
                        <div class="mt-1 h-1.5 rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-1.5 rounded-full bg-emerald-500" :style="'width:' + percent(t) + '%'"></div></div>
                    </div>
                </template>
            </div>
        </div>

        {{-- TABS --}}
        <div class="mb-4 grid grid-cols-2 rounded-xl bg-gray-100 p-1 dark:bg-gray-800" role="tablist">
            <button type="button" role="tab" @click="setTab('scan')" :aria-selected="tab === 'scan'"
                    class="rounded-lg py-2.5 text-sm font-semibold transition" :class="tab === 'scan' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">Scan QR</button>
            <button type="button" role="tab" @click="setTab('search')" :aria-selected="tab === 'search'"
                    class="rounded-lg py-2.5 text-sm font-semibold transition" :class="tab === 'search' ? 'bg-white text-gray-900 shadow-theme-xs dark:bg-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-400'">Find by name / phone</button>
        </div>

        {{-- SCAN --}}
        <div x-show="tab === 'scan'" class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="relative">
                <div id="qr-reader" class="aspect-square w-full bg-black"></div>
                <div x-show="!cameraOn" class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-gray-950 p-6 text-center text-sm text-white/80">
                    <svg class="h-10 w-10 text-white/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                    <span x-text="cameraError || 'Camera paused'"></span>
                    <button type="button" @click="startCamera()" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-gray-900">Start camera</button>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 p-3 dark:border-gray-800">
                <span class="text-xs text-gray-500 dark:text-gray-400" x-text="scanCount + ' scans this session'"></span>
                <div class="flex flex-wrap gap-2">
                    <button type="button" x-show="torchSupported" @click="toggleTorch()" class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                            :class="torchOn ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-200'">🔦 Torch</button>
                    <button type="button" x-show="cameras.length > 1" @click="switchCamera()" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">⟲ Camera</button>
                    <button type="button" @click="toggleSound()" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200" x-text="sound ? '🔊 Sound' : '🔇 Muted'"></button>
                    <button type="button" x-show="cameraOn" @click="stopCamera()" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Pause</button>
                </div>
            </div>

            <form @submit.prevent="manualCode.trim() && handleScan(manualCode.trim(), true)" class="flex gap-2 border-t border-gray-100 p-3 dark:border-gray-800">
                <input type="text" x-model="manualCode" placeholder="Type the code, e.g. QR-ORD-…" autocapitalize="characters"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm uppercase text-gray-800 placeholder:normal-case placeholder:text-gray-400 dark:border-gray-700 dark:text-white/90">
                <button type="submit" class="shrink-0 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">Check in</button>
            </form>
        </div>

        {{-- SEARCH --}}
        <div x-show="tab === 'search'" x-cloak class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 p-3 dark:border-gray-800">
                <input type="search" x-ref="search" x-model="query" @input.debounce.300ms="search()" placeholder="Name, phone, email or order number"
                       class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 placeholder:text-gray-400 dark:border-gray-700 dark:text-white/90">
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">For a lost or dead phone: ask for ID and check the name matches before checking them in.</p>
            </div>
            <p x-show="searching" class="p-4 text-center text-sm text-gray-500">Searching…</p>
            <p x-show="!searching && query.trim().length >= 2 && results.length === 0" class="p-6 text-center text-sm text-gray-500">No paid ticket matches “<span x-text="query"></span>”.</p>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="r in results" :key="r.id">
                    <li class="flex items-center gap-3 px-4 py-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white" x-text="r.name"></p>
                            <p class="truncate text-xs text-gray-500" x-text="[r.detail, r.phone, r.order_no].filter(Boolean).join(' · ')"></p>
                        </div>
                        <template x-if="!r.checked_in">
                            <button type="button" @click="checkInFromSearch(r)" class="shrink-0 rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600">Check in</button>
                        </template>
                        <template x-if="r.checked_in">
                            <div class="shrink-0 text-right">
                                <p class="text-xs font-semibold text-emerald-600" x-text="'In at ' + r.checked_in_at"></p>
                                <button type="button" @click="undo(r.id, r.name)" class="text-[11px] font-semibold text-gray-500 underline hover:text-red-600">Undo</button>
                            </div>
                        </template>
                    </li>
                </template>
            </ul>
        </div>

        {{-- RECENT SCANS --}}
        <div class="mt-5 rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Recent scans</h3>
                <span class="text-xs text-gray-400" x-text="recent.length + ' this session'"></span>
            </div>
            <p x-show="recent.length === 0" class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">No scans yet.</p>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="(r, i) in recent" :key="i">
                    <li class="flex items-center gap-3 px-4 py-3">
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full" :class="{ 'bg-emerald-500': r.type === 'success', 'bg-red-500': r.type === 'error', 'bg-amber-500': r.type === 'warn', 'bg-gray-400': r.type === 'undone' }"></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-900 dark:text-white" :class="r.type === 'undone' && 'line-through opacity-60'" x-text="r.name || r.code"></p>
                            <p class="truncate text-[11px] text-gray-500 dark:text-gray-400" x-text="[r.time, r.detail].filter(Boolean).join(' · ')"></p>
                        </div>
                        <button type="button" x-show="r.type === 'success' && r.id" @click="undo(r.id, r.name, r)" class="shrink-0 text-xs font-semibold text-gray-500 underline hover:text-red-600">Undo</button>
                        <span x-show="r.type !== 'success'" class="shrink-0 text-[10px] font-bold uppercase tracking-wide"
                              :class="{ 'text-red-600': r.type === 'error', 'text-amber-600': r.type === 'warn', 'text-gray-500': r.type === 'undone' }"
                              x-text="{ error: 'Rejected', warn: 'Already in', undone: 'Undone' }[r.type]"></span>
                    </li>
                </template>
            </ul>
        </div>
    </div>

    {{-- FULL-SCREEN RESULT: readable at arm's length in a busy gate --}}
    <div x-show="flash" x-transition.opacity.duration.150ms @click="dismiss()" x-cloak
         class="fixed inset-0 z-[100000] flex cursor-pointer flex-col items-center justify-center p-8 text-center text-white"
         :class="{ 'bg-emerald-600': flash?.type === 'success', 'bg-amber-500': flash?.type === 'warn', 'bg-red-600': flash?.type === 'error' }"
         role="alert" aria-live="assertive">
        <div class="flex h-28 w-28 items-center justify-center rounded-full bg-white/20">
            <svg x-show="flash?.type === 'success'" class="h-16 w-16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
            <svg x-show="flash?.type === 'warn'" class="h-16 w-16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <svg x-show="flash?.type === 'error'" class="h-16 w-16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </div>
        <p class="mt-6 text-sm font-bold uppercase tracking-[0.2em] text-white/80" x-text="flash?.label"></p>
        <p class="mt-2 max-w-xl text-3xl font-bold leading-tight sm:text-5xl" x-text="flash?.title"></p>
        <p x-show="flash?.detail" class="mt-3 rounded-full bg-white/20 px-4 py-1.5 text-lg font-semibold" x-text="flash?.detail"></p>
        <p x-show="flash?.message" class="mt-4 max-w-md text-base text-white/90" x-text="flash?.message"></p>
        <p class="mt-10 text-xs text-white/70">Tap anywhere to continue</p>
    </div>
</main>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('gateScanner', (urls, payload, stats) => ({
        urls, payload,
        stats: Object.assign({ total: 0, checked_in: 0, by_type: [] }, stats || {}),
        tab: 'scan',
        scanner: null, cameraOn: false, cameraError: '', cameras: [], cameraIndex: 0,
        torchSupported: false, torchOn: false,
        sound: true, audio: null,
        scanCount: 0, recent: [], manualCode: '',
        flash: null, flashTimer: null,
        lastScanned: '', lastScannedAt: 0, busy: false,
        query: '', results: [], searching: false,

        init() {
            try { this.sound = localStorage.getItem('gateSound') !== 'off'; } catch (e) {}
            this.startCamera();
        },

        percent(s) { return s && s.total ? Math.round(s.checked_in / s.total * 100) : 0; },

        setTab(t) {
            this.tab = t;
            if (t === 'search') { this.stopCamera(); this.$nextTick(() => this.$refs.search.focus()); }
            else if (!this.cameraOn) { this.startCamera(); }
        },

        /* ---------- camera ---------- */
        async startCamera() {
            this.cameraError = '';
            try {
                this.scanner ??= new Html5Qrcode('qr-reader');
                if (!this.cameras.length) {
                    this.cameras = await Html5Qrcode.getCameras().catch(() => []);
                    const back = this.cameras.findIndex(c => /back|rear|environment/i.test(c.label));
                    this.cameraIndex = back >= 0 ? back : Math.max(0, this.cameras.length - 1);
                }
                const source = this.cameras[this.cameraIndex]?.id ?? { facingMode: 'environment' };
                await this.scanner.start(source, { fps: 12, qrbox: (w, h) => { const s = Math.floor(Math.min(w, h) * 0.7); return { width: s, height: s }; } },
                    (text) => this.handleScan(text), () => {});
                this.cameraOn = true;
                this.torchOn = false;
                try { this.torchSupported = !!this.scanner.getRunningTrackCapabilities?.().torch; } catch (e) { this.torchSupported = false; }
            } catch (e) {
                this.cameraOn = false;
                this.cameraError = 'Camera unavailable. Allow camera access, or type the code / search by name.';
            }
        },

        async stopCamera() {
            if (this.scanner && this.cameraOn) await this.scanner.stop().catch(() => {});
            this.cameraOn = false;
        },

        async switchCamera() {
            await this.stopCamera();
            this.cameraIndex = (this.cameraIndex + 1) % this.cameras.length;
            await this.startCamera();
        },

        async toggleTorch() {
            try {
                await this.scanner.applyVideoConstraints({ advanced: [{ torch: !this.torchOn }] });
                this.torchOn = !this.torchOn;
            } catch (e) { this.torchSupported = false; }
        },

        /* ---------- feedback ---------- */
        toggleSound() {
            this.sound = !this.sound;
            try { localStorage.setItem('gateSound', this.sound ? 'on' : 'off'); } catch (e) {}
            if (this.sound) this.beep('success');
        },

        beep(type) {
            if (navigator.vibrate) navigator.vibrate({ success: 80, warn: [80, 80, 80], error: [300, 100, 300] }[type]);
            if (!this.sound) return;
            try {
                this.audio ??= new (window.AudioContext || window.webkitAudioContext)();
                const tones = { success: [[880, 0, .12], [1320, .12, .14]], warn: [[660, 0, .12], [660, .2, .12]], error: [[220, 0, .45]] }[type];
                tones.forEach(([freq, at, len]) => {
                    const osc = this.audio.createOscillator(), gain = this.audio.createGain();
                    osc.type = type === 'error' ? 'square' : 'sine';
                    osc.frequency.value = freq;
                    gain.gain.setValueAtTime(.25, this.audio.currentTime + at);
                    gain.gain.exponentialRampToValueAtTime(.001, this.audio.currentTime + at + len);
                    osc.connect(gain).connect(this.audio.destination);
                    osc.start(this.audio.currentTime + at);
                    osc.stop(this.audio.currentTime + at + len);
                });
            } catch (e) {}
        },

        show(flash) {
            this.flash = flash;
            this.beep(flash.type);
            clearTimeout(this.flashTimer);
            this.flashTimer = setTimeout(() => this.dismiss(), flash.type === 'success' ? 1600 : 3500);
        },

        dismiss() { this.flash = null; clearTimeout(this.flashTimer); },

        log(type, name, extra = {}) {
            this.recent.unshift({ type, name, time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }), ...extra });
            this.recent = this.recent.slice(0, 30);
        },

        async post(url, body = {}) {
            const res = await fetch(url, {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify(body),
            });
            return res.json();
        },

        /* ---------- check-in ---------- */
        async handleScan(code, manual = false) {
            const now = Date.now();
            if (this.busy || (this.flash && !manual)) return;
            // The camera keeps seeing the same code: ignore repeats for a few seconds
            if (!manual && code === this.lastScanned && now - this.lastScannedAt < 4000) return;

            this.busy = true;
            this.lastScanned = code;
            this.lastScannedAt = now;

            try {
                const json = await this.post(this.urls.verify, { ...this.payload, qr: code });
                this.scanCount++;
                if (json.stats) this.stats = json.stats;

                if (json.success) {
                    this.show({ type: 'success', label: 'Checked in', title: json.attendee, detail: json.detail });
                    this.log('success', json.attendee, { id: json.id, detail: json.detail });
                    if (manual) this.manualCode = '';
                } else if (json.already) {
                    this.show({ type: 'warn', label: 'Already checked in', title: json.attendee, detail: json.detail, message: 'First scanned ' + (json.checked_in_at || 'earlier') + '. Don\'t let a second person in on this ticket.' });
                    this.log('warn', json.attendee, { detail: json.detail });
                } else {
                    this.show({ type: 'error', label: 'Not valid', title: json.attendee || 'Do not admit', message: json.message || 'This code was rejected.' });
                    this.log('error', json.attendee || code, { detail: json.message });
                }
            } catch (e) {
                this.show({ type: 'error', label: 'No connection', title: 'Try again', message: 'The scan didn\'t reach the server. Check the phone\'s internet.' });
            } finally {
                setTimeout(() => { this.busy = false; }, 600);
            }
        },

        async checkInFromSearch(r) {
            await this.handleScan(r.code, true);
            this.search();
        },

        async undo(id, name, entry = null) {
            if (!confirm('Undo the check-in for ' + name + '?')) return;
            try {
                const json = await this.post(this.urls.undo.replace('__ID__', id));
                if (json.stats) this.stats = json.stats;
                if (entry) entry.type = 'undone'; else this.log('undone', name);
                if (this.tab === 'search') this.search();
            } catch (e) {
                this.show({ type: 'error', label: 'No connection', title: 'Undo failed', message: 'Try again.' });
            }
        },

        async search() {
            const q = this.query.trim();
            if (q.length < 2) { this.results = []; return; }
            this.searching = true;
            try {
                const res = await fetch(this.urls.search + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
                if (q === this.query.trim()) this.results = (await res.json()).results || [];
            } catch (e) {
                this.results = [];
            } finally {
                this.searching = false;
            }
        },
    }));
});
</script>
@endsection
