@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">My Orders</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your ticket purchase history</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-5 py-3">Order #</th>
                    <th class="px-5 py-3">Event</th>
                    <th class="px-5 py-3 text-center">Qty</th>
                    <th class="px-5 py-3 text-right">Total</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Date</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($orders as $o)
                    @php
                        $statusCls = match($o->status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'refunded' => 'bg-blue-100 text-blue-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr class="text-gray-700 dark:text-gray-300">
                        <td class="px-5 py-3 font-mono text-xs">{{ $o->order_no }}</td>
                        <td class="px-5 py-3">{{ optional($o->event)->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">{{ $o->qty }}</td>
                        <td class="px-5 py-3 text-right font-medium">KES {{ number_format($o->total_amount, 2) }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusCls }}">{{ ucfirst($o->status) }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            {{ $o->created_at ? \Carbon\Carbon::parse($o->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('orders.show', $o->id) }}"
                               class="text-blue-600 hover:underline text-xs">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                            No orders yet. <a href="{{ route('browse') }}" class="text-blue-600 hover:underline">Browse events →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $orders->links() }}</div>
    </div>
</div>
@endsection