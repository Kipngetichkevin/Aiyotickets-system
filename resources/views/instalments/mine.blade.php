{{-- resources/views/instalments/mine.blade.php --}}
@extends('layouts.app')

@section('title', 'Lipa mdogo mdogo')

@section('content')
<main>
    <div x-data="{ pageName: 'Lipa mdogo mdogo' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-lg md:p-6">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Lipa mdogo mdogo</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Pay any amount, any time, until the balance is cleared. Your QR tickets are sent as soon as it's fully paid.
            </p>
        </div>

        <div class="space-y-5">
            @forelse ($orders as $order)
                @php($percent = $order->total_amount > 0 ? min(100, round($order->amount_paid / $order->total_amount * 100)) : 0)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900"
                     x-data="instalmentPay({{ $order->id }}, {{ (int) ceil($order->balance_due) }}, '{{ route('instalments.pay', $order) }}')">

                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $order->item_name }}</p>
                            <p class="text-xs text-gray-500">{{ $order->order_no }} · {{ $order->item_count }} {{ \Illuminate\Support\Str::plural('place', $order->item_count) }}</p>
                        </div>
                        @include('instalments._status', ['status' => $order->status])
                    </div>

                    <div class="mt-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">KES {{ number_format($order->amount_paid) }} of {{ number_format($order->total_amount) }} paid</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $percent }}%</span>
                        </div>
                        <div class="mt-2 h-2.5 w-full rounded-full bg-gray-100 dark:bg-gray-800">
                            <div class="h-2.5 rounded-full {{ $order->status === 'lapsed' ? 'bg-red-400' : 'bg-brand-500' }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>

                    @if ($order->status === 'partial')
                        <div class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                            <div><p class="text-xs text-gray-500">Balance</p><p class="font-bold text-gray-900 dark:text-white">KES {{ number_format($order->balance_due) }}</p></div>
                            <div><p class="text-xs text-gray-500">Pay by</p><p class="font-semibold text-gray-900 dark:text-white">{{ $order->due_date?->format('D, d M Y') }}</p></div>
                            <div><p class="text-xs text-gray-500">Days left</p><p class="font-semibold {{ $order->due_date && $order->due_date->diffInDays(now()) < 4 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">{{ $order->due_date ? max(0, (int) now()->diffInDays($order->due_date, false)) : '—' }}</p></div>
                        </div>

                        <form @submit.prevent="pay" class="mt-5 flex flex-wrap items-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Amount (KES)</label>
                                <input type="number" x-model.number="amount" min="{{ min($minimum, (int) ceil($order->balance_due)) }}" max="{{ (int) ceil($order->balance_due) }}" required
                                       class="h-11 w-36 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">M-Pesa number</label>
                                <input type="tel" x-model="phone" placeholder="0712345678" required
                                       class="h-11 w-44 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:text-white/90">
                            </div>
                            <button type="submit" :disabled="busy"
                                    class="h-11 rounded-lg bg-brand-500 px-5 text-sm font-semibold text-white hover:bg-brand-600 disabled:opacity-50">
                                <span x-text="busy ? 'Check your phone…' : 'Pay'"></span>
                            </button>
                            <p class="w-full text-xs" :class="error ? 'text-red-600' : 'text-gray-500'" x-text="message"></p>
                        </form>
                    @elseif ($order->status === 'paid')
                        <p class="mt-4 text-sm text-emerald-700 dark:text-emerald-400">
                            Fully paid — your tickets are in <a href="{{ route('my-qrs') }}" class="font-semibold underline">My QR Codes</a> and your email.
                        </p>
                    @elseif ($order->status === 'lapsed')
                        <p class="mt-4 text-sm text-red-600">
                            The balance wasn't paid by {{ $order->due_date?->format('d M Y') }}, so this booking was cancelled.
                            @if ($order->refund > 0)
                                KES {{ number_format($order->refund) }} {{ $order->refunded_at ? 'was refunded on ' . $order->refunded_at->format('d M Y') : 'will be refunded to you' }}.
                            @endif
                        </p>
                    @endif
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm text-gray-500 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    No instalment orders. Choose <strong>Lipa mdogo mdogo</strong> at checkout on events and experiences that offer it.
                </div>
            @endforelse
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
function instalmentPay(orderId, balance, url) {
    return {
        amount: balance, phone: @js(auth()->user()->phone ?? ''), busy: false, error: false, message: '',
        async pay() {
            this.busy = true; this.error = false; this.message = 'Sending M-Pesa prompt…';
            try {
                const res = await fetch(url, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                               'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ amount: this.amount, phone: this.phone }),
                });
                const json = await res.json();
                if (!res.ok || !json.success) throw new Error(json.message || Object.values(json.errors || {})[0] || 'Payment failed');
                this.message = 'Enter your M-Pesa PIN on your phone…';
                this.poll(json.checkout_request_id, 0);
            } catch (e) {
                this.busy = false; this.error = true; this.message = e.message;
            }
        },
        async poll(id, attempt) {
            if (attempt > 40) { this.busy = false; this.message = 'Still waiting — refresh this page in a minute.'; return; }
            const res = await fetch(`/mpesa/status/${id}`, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const status = json?.data?.status;
            if (status === 'completed') { this.message = 'Payment received!'; setTimeout(() => location.reload(), 1200); return; }
            if (status === 'failed') { this.busy = false; this.error = true; this.message = json.data.result_desc || 'Payment failed.'; return; }
            setTimeout(() => this.poll(id, attempt + 1), 3000);
        },
    };
}
</script>
@endpush
