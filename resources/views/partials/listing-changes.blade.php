{{-- Cancel / postpone an event or experience. Expects $type ('event'|'experience') and $item. --}}
@php
    $start   = $type === 'event' ? $item->date : $item->start_date;
    $field   = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp
<div x-data="{ modal: null }" class="space-y-2">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
    @endif
    @if (session('error') || $errors->hasAny(['reason', 'confirm', 'start', 'end']))
        <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ session('error') ?? $errors->first() }}</div>
    @endif

    @if ($item->cancelled_at)
        <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">
            <strong>Cancelled</strong> {{ $item->cancelled_at->format('d M Y') }}: {{ $item->cancel_reason }}. Buyers were told and their refunds are listed for payment.
        </div>
    @else
        <div class="grid grid-cols-2 gap-2">
            <button type="button" @click="modal = 'postpone'" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Postpone</button>
            <button type="button" @click="modal = 'cancel'" class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/30">Cancel {{ $type }}</button>
        </div>
        @if ($item->postponed_at)
            <p class="text-xs text-gray-500">Postponed {{ $item->postponed_at->format('d M Y') }} — buyers may ask for refunds until {{ $item->postponed_at->copy()->addDays(\App\Support\RefundPolicy::POSTPONED_DAYS)->format('d M') }}.</p>
        @endif
    @endif

    {{-- Postpone --}}
    <div x-show="modal === 'postpone'" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/60 p-4" @keydown.escape.window="modal = null">
        <form method="POST" action="{{ route('listings.postpone', [$type, $item->id]) }}" @click.outside="modal = null"
              class="w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            @csrf
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Postpone {{ $item->name }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Every buyer is emailed the new date. Their tickets stay valid, and they can ask for a full refund for {{ \App\Support\RefundPolicy::POSTPONED_DAYS }} days.</p>
            <label class="block text-sm text-gray-700 dark:text-gray-300">New start
                <input type="datetime-local" name="start" required value="{{ old('start', $start?->format('Y-m-d\TH:i')) }}" class="{{ $field }} mt-1">
            </label>
            <label class="block text-sm text-gray-700 dark:text-gray-300">New end (optional)
                <input type="datetime-local" name="end" value="{{ old('end', $item->end_date?->format('Y-m-d\TH:i')) }}" class="{{ $field }} mt-1">
            </label>
            <label class="block text-sm text-gray-700 dark:text-gray-300">Message to buyers (optional)
                <textarea name="note" rows="2" maxlength="500" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:text-white/90" placeholder="E.g. moved because of the weather; same venue">{{ old('note') }}</textarea>
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" @click="modal = null" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Back</button>
                <button class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-600">Postpone and tell buyers</button>
            </div>
        </form>
    </div>

    {{-- Cancel --}}
    <div x-show="modal === 'cancel'" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center bg-gray-900/60 p-4" @keydown.escape.window="modal = null">
        <form method="POST" action="{{ route('listings.cancel', [$type, $item->id]) }}" @click.outside="modal = null"
              class="w-full max-w-md space-y-4 rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
            @csrf
            <h3 class="text-lg font-bold text-red-600">Cancel {{ $item->name }}?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">This can't be undone. Sales stop, every ticket stops working at the gate, every buyer is refunded in full (lipa mdogo mdogo buyers get back all they paid) and told by email.</p>
            <label class="block text-sm text-gray-700 dark:text-gray-300">Reason (sent to buyers)
                <textarea name="reason" rows="2" required maxlength="500" class="mt-1 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm dark:border-gray-700 dark:text-white/90" placeholder="E.g. the venue is no longer available">{{ old('reason') }}</textarea>
            </label>
            <label class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="confirm" value="1" required class="mt-0.5 h-4 w-4 rounded border-gray-300 text-red-600">
                I understand every buyer will be refunded and this can't be undone.
            </label>
            <div class="flex justify-end gap-2">
                <button type="button" @click="modal = null" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Keep it</button>
                <button class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Cancel and refund everyone</button>
            </div>
        </form>
    </div>
</div>
