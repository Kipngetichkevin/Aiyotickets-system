{{-- resources/views/qrcode/scanner.blade.php
     Shared gate scanner for events and experiences.
     Expects: $title, $backUrl, $backLabel, $verifyUrl, $payload (extra JSON fields), $stats (nullable: checked_in, total) --}}
@extends('layouts.app')

@section('content')
<main x-data="gateScanner(@js($verifyUrl), @js($payload), @js($stats))" x-init="init()" x-cloak>
    <div x-data="{ pageName: 'QR Scanner' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-md md:p-6">

        {{-- HEADER --}}
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90 truncate">
                    Scan QR — {{ $title }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Point the camera at the attendee's QR code to check them in.
                </p>
            </div>

            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-200">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                {{ $backLabel }}
            </a>
        </div>

        {{-- CHECK-IN COUNTER --}}
        <template x-if="stats">
            <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-semibold text-gray-800 dark:text-white/90">Checked in</span>
                    <span class="font-bold text-gray-900 dark:text-white" x-text="stats.checked_in + ' / ' + stats.total"></span>
                </div>
                <div class="mt-2 h-2 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                    <div class="h-2 rounded-full bg-brand-500 transition-all"
                         :style="'width:' + (stats.total ? Math.round(stats.checked_in / stats.total * 100) : 0) + '%'"></div>
                </div>
            </div>
        </template>

        {{-- SCANNER VIEWPORT --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div id="qr-reader" class="aspect-square w-full bg-black"></div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-800 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span x-show="cameraOn" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Camera live
                    </span>
                    <span class="text-xs text-gray-500 dark:text-gray-400" x-text="scanCount + ' scans this session'"></span>
                </div>

                <button @click="toggleCamera()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">
                    <span x-text="cameraOn ? 'Pause' : 'Resume'"></span>
                </button>
            </div>

            {{-- MANUAL ENTRY (damaged QR / no camera) --}}
            <form @submit.prevent="manualCode.trim() && handleScan(manualCode.trim(), true)"
                  class="p-4 border-t border-gray-100 dark:border-gray-800 flex gap-2">
                <input type="text" x-model="manualCode" placeholder="Or type the code, e.g. QR-ORD-…"
                       class="h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90">
                <button type="submit"
                        class="shrink-0 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">
                    Check in
                </button>
            </form>
        </div>

        {{-- RESULT / TOAST --}}
        <div x-show="flash" x-transition x-cloak
             class="mt-4 rounded-2xl border p-4"
             :class="{
                 'bg-emerald-50 border-emerald-200 dark:bg-emerald-500/10 dark:border-emerald-500/30': flash?.type === 'success',
                 'bg-red-50 border-red-200 dark:bg-red-500/10 dark:border-red-500/30': flash?.type === 'error',
                 'bg-amber-50 border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/30': flash?.type === 'warn'
             }">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                     :class="{
                         'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400': flash?.type === 'success',
                         'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400': flash?.type === 'error',
                         'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400': flash?.type === 'warn'
                     }">
                    <template x-if="flash?.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <template x-if="flash?.type === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </template>
                    <template x-if="flash?.type === 'warn'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.008v.008H12v-.008z"/>
                        </svg>
                    </template>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-bold text-gray-900 dark:text-white" x-text="flash?.title"></p>
                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5" x-text="flash?.message"></p>
                </div>
                <button @click="flash = null" class="text-gray-400 hover:text-gray-700 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- RECENT SCANS --}}
        <div class="mt-6 rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Recent scans</h3>
                <span class="text-xs text-gray-400" x-text="recent.length + ' shown'"></span>
            </div>

            <div x-show="recent.length === 0" class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                No scans yet this session.
            </div>

            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                <template x-for="(r, i) in recent" :key="i">
                    <li class="px-4 py-3 flex items-center gap-3">
                        <span class="w-2 h-2 rounded-full shrink-0"
                              :class="{
                                  'bg-emerald-500': r.type === 'success',
                                  'bg-red-500': r.type === 'error',
                                  'bg-amber-500': r.type === 'warn'
                              }"></span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate" x-text="r.name || r.code"></p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400" x-text="r.time"></p>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wide"
                              :class="{
                                  'text-emerald-600': r.type === 'success',
                                  'text-red-600': r.type === 'error',
                                  'text-amber-600': r.type === 'warn'
                              }"
                              x-text="r.type"></span>
                    </li>
                </template>
            </ul>
        </div>

    </div>
</main>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('gateScanner', (verifyUrl, payload, stats) => ({
        verifyUrl,
        payload,
        stats,
        scanner: null,
        cameraOn: false,
        scanCount: 0,
        flash: null,
        recent: [],
        manualCode: '',
        lastScanned: '',
        lastScannedAt: 0,
        busy: false,

        init() {
            this.startCamera();
        },

        cameraConfig() {
            return { fps: 10, qrbox: { width: 260, height: 260 } };
        },

        async startCamera() {
            try {
                this.scanner = new Html5Qrcode('qr-reader');
                await this.scanner.start({ facingMode: 'environment' }, this.cameraConfig(),
                    (decodedText) => this.handleScan(decodedText),
                    () => { /* per-frame error — ignore */ });
                this.cameraOn = true;
            } catch (e) {
                this.flash = {
                    type: 'error',
                    title: 'Camera error',
                    message: (e && e.message) || 'Could not start camera. Grant permission, or type the code below.',
                };
            }
        },

        async toggleCamera() {
            if (!this.scanner) return;

            if (this.cameraOn) {
                await this.scanner.stop().catch(() => {});
                this.cameraOn = false;
            } else {
                try {
                    await this.scanner.start({ facingMode: 'environment' }, this.cameraConfig(),
                        (t) => this.handleScan(t), () => {});
                    this.cameraOn = true;
                } catch (e) {
                    console.error(e);
                }
            }
        },

        async handleScan(code, manual = false) {
            const now = Date.now();

            // Debounce: ignore repeat camera scans of the same code within 3 seconds
            if (this.busy) return;
            if (!manual && code === this.lastScanned && now - this.lastScannedAt < 3000) return;

            this.busy = true;
            this.lastScanned = code;
            this.lastScannedAt = now;

            const entry = (type, name) => this.recent.unshift({ type, name, code, time: new Date().toLocaleTimeString() });

            try {
                const res = await fetch(this.verifyUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ ...this.payload, qr: code }),
                });

                const json = await res.json();
                this.scanCount++;
                if (json.stats) this.stats = json.stats;

                if (json.success) {
                    this.flash = {
                        type: 'success',
                        title: 'Checked in — ' + json.attendee,
                        message: [json.detail, json.checked_in_at].filter(Boolean).join(' · '),
                    };
                    entry('success', json.attendee);
                    if (manual) this.manualCode = '';
                } else if (json.already) {
                    this.flash = {
                        type: 'warn',
                        title: 'Already checked in — ' + json.attendee,
                        message: json.checked_in_at ? 'First check-in: ' + json.checked_in_at : '',
                    };
                    entry('warn', json.attendee);
                } else {
                    this.flash = { type: 'error', title: 'Scan rejected', message: json.message || 'Unknown error' };
                    entry('error', json.attendee || code);
                }
            } catch (e) {
                this.flash = { type: 'error', title: 'Network error', message: e.message };
            } finally {
                this.recent = this.recent.slice(0, 20);
                setTimeout(() => { this.busy = false; }, 800);
            }
        },
    }));
});
</script>
@endsection
