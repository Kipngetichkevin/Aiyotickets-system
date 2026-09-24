@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">My Tickets</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">All tickets you have purchased</p>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/[0.02]">
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <th class="px-5 py-3">Event</th>
                    <th class="px-5 py-3">Ticket</th>
                    <th class="px-5 py-3">Order #</th>
                    <th class="px-5 py-3">Qty</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3">Status</th>
                    <th class="px-5 py-3">Purchased</th>
                    <th class="px-5 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($tickets as $t)
                    @php
                        $event = optional(optional($t->ticket)->event);
                        $status = $t->payment_status ?? 'pending';
                        $statusCls = match($status) {
                            'paid' => 'bg-green-100 text-green-700',
                            'pending' => 'bg-yellow-100 text-yellow-700',
                            'refunded' => 'bg-blue-100 text-blue-700',
                            'failed' => 'bg-red-100 text-red-700',
                            default => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <tr class="text-gray-700 dark:text-gray-300">
                        <td class="px-5 py-3 font-medium">{{ $event->name ?? '—' }}</td>
                        <td class="px-5 py-3">{{ optional($t->ticket)->name ?? '—' }}</td>
                        <td class="px-5 py-3 font-mono text-xs">{{ optional($t->order)->order_no ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $t->qty ?? 1 }}</td>
                        <td class="px-5 py-3 text-right">KES {{ number_format($t->total_price ?? 0, 2) }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusCls }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-500">
                            {{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('d M Y') : '—' }}
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('ticket.pdf', $t->id) }}" target="_blank"
                               class="text-blue-600 hover:underline text-xs">Download PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                            No tickets yet. <a href="{{ route('browse') }}" class="text-blue-600 hover:underline">Browse events →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-5 py-3">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection