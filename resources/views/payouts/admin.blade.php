{{-- resources/views/payouts/admin.blade.php --}}
@extends('layouts.app')

@section('title', 'Payout requests')

@section('content')
<main>
    <div x-data="{ pageName: 'Payout requests' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Payout requests</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Send the money, then mark the request paid with the M-Pesa or bank reference.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach (['requested' => 'Requested', 'approved' => 'Approved', 'paid' => 'Paid', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label)
                    <a href="{{ route('admin.payouts.index', ['status' => $key]) }}"
                       class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $status === $key ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200' }}">
                        {{ $label }}@if ($key !== 'all') ({{ $counts[$key] ?? 0 }})@endif
                    </a>
                @endforeach
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
        @endif
        @if (session('error') || $errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ session('error') ?? $errors->first() }}</div>
        @endif

        @if ($refunds->isNotEmpty())
            <div class="mb-6 overflow-hidden rounded-2xl border border-amber-200 bg-white shadow-theme-xs dark:border-amber-500/30 dark:bg-gray-900">
                <div class="border-b border-amber-100 bg-amber-50 px-5 py-3 dark:border-amber-500/20 dark:bg-amber-500/10">
                    <h3 class="text-sm font-bold text-amber-800 dark:text-amber-400">Refunds due to buyers</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($refunds as $o)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3 font-mono text-xs">{{ $o->order_no }}</td>
                                    <td class="px-5 py-3">{{ $o->initiator }}<span class="block text-xs text-gray-500">{{ $o->email }}</span></td>
                                    <td class="px-5 py-3 text-right font-semibold">KES {{ number_format($o->refund, 2) }}</td>
                                    <td class="px-5 py-3 text-xs">{{ $o->refund_reason ?: match ($o->status) { 'lapsed' => 'Lapsed ' . $o->lapsed_at?->format('d M Y'), 'sold_out' => 'Sold out before payment', default => 'Overpaid' } }}</td>
                                    <td class="px-5 py-3">
                                        <form method="POST" action="{{ route('admin.payouts.refunded', $o) }}" class="flex gap-2">@csrf
                                            <input name="reference" required placeholder="M-Pesa ref"
                                                   class="h-8 w-40 rounded-lg border border-gray-300 bg-transparent px-2 text-xs dark:border-gray-700 dark:text-white/90">
                                            <button class="shrink-0 rounded-lg bg-brand-500 px-3 text-xs font-semibold text-white hover:bg-brand-600">Mark refunded</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">#</th>
                            <th class="px-5 py-3">Merchant</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3">Send to</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($payouts as $p)
                            <tr class="align-top text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 text-xs text-gray-500">#{{ $p->id }}<br>{{ $p->created_at->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $p->user?->name }}</span>
                                    <span class="block text-xs text-gray-500">{{ $p->user?->email }}</span>
                                </td>
                                <td class="px-5 py-3 text-right font-semibold">KES {{ number_format($p->amount, 2) }}</td>
                                <td class="px-5 py-3">{{ $p->method === 'mpesa' ? 'M-Pesa' : 'Bank' }}<span class="block text-xs">{{ $p->destination }}</span></td>
                                <td class="px-5 py-3">
                                    @include('payouts._status', ['status' => $p->status])
                                    @if ($p->processed_at)
                                        <span class="mt-1 block text-xs text-gray-500">{{ $p->processor?->name }}, {{ $p->processed_at->format('d M H:i') }}</span>
                                    @endif
                                    @if ($p->reference || $p->note)
                                        <span class="mt-1 block text-xs">{{ $p->reference ?? $p->note }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if (in_array($p->status, ['requested', 'approved']))
                                        <div class="flex flex-col gap-2 min-w-[220px]">
                                            @if ($p->status === 'requested')
                                                <form method="POST" action="{{ route('admin.payouts.approve', $p) }}">@csrf
                                                    <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Approve</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('admin.payouts.paid', $p) }}" class="flex gap-2">@csrf
                                                <input name="reference" required placeholder="M-Pesa / bank ref"
                                                       class="h-8 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-xs dark:border-gray-700 dark:text-white/90">
                                                <button class="shrink-0 rounded-lg bg-brand-500 px-3 text-xs font-semibold text-white hover:bg-brand-600">Mark paid</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.payouts.reject', $p) }}" class="flex gap-2">@csrf
                                                <input name="note" required placeholder="Reason"
                                                       class="h-8 w-full rounded-lg border border-gray-300 bg-transparent px-2 text-xs dark:border-gray-700 dark:text-white/90">
                                                <button class="shrink-0 rounded-lg border border-red-200 px-3 text-xs font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/30">Reject</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No payout requests here.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
