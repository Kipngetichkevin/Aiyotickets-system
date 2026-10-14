{{-- resources/views/admin/payments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'M-Pesa payments')

@php
    $badge = [
        'completed' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'pending'   => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        'failed'    => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
    ];
    $field = 'h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90';
@endphp

@section('content')
<main>
    <div x-data="{ pageName: 'M-Pesa payments' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">M-Pesa payments</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Every payment prompt sent to a buyer's phone. Match the receipt numbers against your M-Pesa statement; use “Re-check” to ask Safaricom again about one.</p>
        </div>

        {{-- Totals for the current filters --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Paid', 'completed', 'text-emerald-600'],
                ['Waiting', 'pending', 'text-amber-600'],
                ['Failed / cancelled', 'failed', 'text-red-600'],
            ] as [$text, $key, $colour])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $text }} · {{ number_format($totals[$key]->n ?? 0) }}</p>
                    <p class="mt-1 text-xl font-bold {{ $colour }}">KES {{ number_format((float) ($totals[$key]->total ?? 0)) }}</p>
                </div>
            @endforeach
            <div class="rounded-2xl border p-5 shadow-theme-xs {{ $unfulfilled ? 'border-red-300 bg-red-50 dark:border-red-500/40 dark:bg-red-500/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }}">
                <p class="text-xs text-gray-500 dark:text-gray-400">Paid but no order</p>
                <p class="mt-1 text-xl font-bold {{ $unfulfilled ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">{{ $unfulfilled }}</p>
            </div>
        </div>

        <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <div class="min-w-[220px] flex-1">
                <label class="mb-1 block text-xs text-gray-500" for="q">Receipt, phone, order no. or email</label>
                <input id="q" name="q" value="{{ $search }}" class="{{ $field }} w-full">
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500" for="from">From</label>
                <input id="from" type="date" name="from" value="{{ $from }}" class="{{ $field }}">
            </div>
            <div>
                <label class="mb-1 block text-xs text-gray-500" for="to">To</label>
                <input id="to" type="date" name="to" value="{{ $to }}" class="{{ $field }}">
            </div>
            <button class="h-10 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">Filter</button>
            @if ($search || $from || $to)
                <a href="{{ route('admin.payments.index', ['status' => $status]) }}" class="h-10 rounded-lg border border-gray-200 px-4 text-sm font-semibold leading-10 text-gray-700 dark:border-gray-700 dark:text-gray-200">Clear</a>
            @endif
        </form>

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach (['all' => 'All', 'completed' => 'Paid', 'pending' => 'Waiting', 'failed' => 'Failed', 'unfulfilled' => 'Paid, no order'] as $key => $text)
                <a href="{{ route('admin.payments.index', ['status' => $key] + request()->only('q', 'from', 'to')) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $status === $key ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200' }}">{{ $text }}</a>
            @endforeach
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">When</th>
                            <th class="px-5 py-3">Buyer</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3">Receipt</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($payments as $p)
                            @php($order = $p->order ?? $p->instalmentOrder)
                            <tr class="align-top text-gray-700 dark:text-gray-300">
                                <td class="whitespace-nowrap px-5 py-3 text-xs">
                                    {{ $p->created_at->format('d M Y') }}<br>
                                    <span class="text-gray-500">{{ $p->created_at->format('H:i:s') }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $p->user?->name ?? ($p->pending_data['name'] ?? '—') }}</span>
                                    <span class="block text-xs text-gray-500">{{ $p->phone_number ?: ($p->pending_data['phone'] ?? '') }}</span>
                                    <span class="block text-xs text-gray-500">{{ $p->user?->email }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold">
                                    KES {{ number_format((float) $p->amount) }}
                                    @if (!empty($p->pending_data['instalment_for']))
                                        <span class="block text-xs font-normal text-gray-500">instalment</span>
                                    @elseif (!empty($p->pending_data['plan']))
                                        <span class="block text-xs font-normal text-gray-500">deposit</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 font-mono text-xs">{{ $p->mpesa_receipt_number ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $badge[$p->status] ?? $badge['pending'] }}">{{ $p->status_label }}</span>
                                    @if ($p->result_desc && $p->status !== 'completed')
                                        <span class="mt-1 block max-w-[220px] text-xs text-gray-500">{{ $p->result_desc }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs">
                                    @if ($order)
                                        <span class="font-mono">{{ $order->order_no }}</span>
                                        <span class="block text-gray-500">{{ $order->status }}</span>
                                    @elseif ($p->status === 'completed')
                                        <span class="font-semibold text-red-600">Missing</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if ($p->status !== 'completed' || !$order)
                                        <form method="POST" action="{{ route('admin.payments.recheck', $p) }}">@csrf
                                            <button class="whitespace-nowrap rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Re-check</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No payments match.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">{{ $payments->links() }}</div>
    </div>
</main>
@endsection
