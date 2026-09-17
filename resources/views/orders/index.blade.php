@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Orders</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $isAdmin ? 'All orders across every event' : 'Orders for your events' }}
            </p>
        </div>
    </div>

    {{-- Stats cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Orders</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Paid</p>
            <h3 class="mt-2 text-2xl font-semibold text-green-600">{{ number_format($stats['paid']) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Pending</p>
            <h3 class="mt-2 text-2xl font-semibold text-yellow-600">{{ number_format($stats['pending']) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Revenue (Paid)</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($stats['revenue'], 2) }}</h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('orders.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search order #, email, or name"
                class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm"
            >

            <select name="event_id" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All events</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" @selected(request('event_id') == $ev->id)>{{ $ev->name }}</option>
                @endforeach
            </select>

            <select name="status" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All statuses</option>
                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
                <option value="refunded" @selected(request('status') === 'refunded')>Refunded</option>
            </select>

            <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
            <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">

            <div class="flex gap-2 sm:col-span-2 lg:col-span-5">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('orders.index') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">Order #</th>
                        <th class="px-5 py-3">Buyer</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3 text-center">Qty</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Date</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($orders as $order)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-3 font-mono text-xs">{{ $order->order_no }}</td>
                            <td class="px-5 py-3">
                                <div class="font-medium">{{ $order->initiator ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $order->email ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3">{{ optional($order->event)->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">{{ $order->qty }}</td>
                            <td class="px-5 py-3 text-right font-medium">KES {{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-5 py-3">
                                @php
                                    $statusClasses = match($order->status) {
                                        'paid' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'refunded' => 'bg-blue-100 text-blue-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusClasses }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-gray-500">
                                {{ $order->created_at?->format('d M Y H:i') ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('orders.show', $order->id) }}" class="text-blue-600 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                No orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection