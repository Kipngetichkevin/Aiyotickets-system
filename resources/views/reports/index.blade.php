@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Reports</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $isAdmin ? 'All events' : 'Your events' }} · {{ $from->format('d M Y') }} → {{ $to->format('d M Y') }}
            </p>
        </div>
    </div>

    {{-- Filter bar --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('reports') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">From</label>
                <input type="date" name="from" value="{{ $from->format('Y-m-d') }}"
                       class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">To</label>
                <input type="date" name="to" value="{{ $to->format('Y-m-d') }}"
                       class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Event</label>
                <select name="event_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                    <option value="">All events</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev->id }}" @selected($selectedEventId == $ev->id)>{{ $ev->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2 lg:col-span-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Apply</button>
                <a href="{{ route('reports') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Reset</a>
            </div>
        </form>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Revenue</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($totalRevenue, 2) }}</h3>
            <p class="mt-1 text-xs text-green-600">Paid payments</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Orders</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($totalOrders) }}</h3>
            <p class="mt-1 text-xs text-gray-500">In selected range</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Tickets Sold</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($ticketsSold) }}</h3>
            <p class="mt-1 text-xs text-gray-500">{{ $totalAttendees }} attendees</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Avg Order Value</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($avgOrderValue, 2) }}</h3>
            <p class="mt-1 text-xs text-gray-500">Revenue ÷ orders</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding</p>
            <h3 class="mt-2 text-2xl font-semibold text-yellow-600">KES {{ number_format($pendingRevenue, 2) }}</h3>
            <p class="mt-1 text-xs text-gray-500">Pending payments</p>
        </div>
    </div>

    {{-- Monthly revenue chart — pure Tailwind, no JS library needed --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 mb-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Monthly Revenue (Last 12 Months)</h3>
            @if($monthlyRevenue->isNotEmpty())
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    Max: KES {{ number_format($monthlyRevenue->max('total'), 2) }}
                </span>
            @endif
        </div>

        @if($monthlyRevenue->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-10">
                No monthly data to display.
            </p>
        @else
            @php
                $maxTotal = $monthlyRevenue->max('total') ?: 1;
            @endphp
            <div class="overflow-x-auto pb-2">
                <div class="flex items-end gap-2 sm:gap-3 min-h-[300px] pt-6 pb-2">
                    @foreach($monthlyRevenue as $m)
                        @php
                            $heightPx = max(8, round(($m->total / $maxTotal) * 220));
                        @endphp
                        <div class="flex-1 min-w-[44px] flex flex-col items-center justify-end">
                            <div class="text-[10px] sm:text-xs text-gray-600 dark:text-gray-300 mb-1 whitespace-nowrap font-medium">
                                {{ number_format($m->total / 1000, 1) }}k
                            </div>
                            <div class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-t-md transition-all cursor-pointer"
                                 style="height: {{ $heightPx }}px;"
                                 title="{{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('M Y') }}: KES {{ number_format($m->total, 2) }}">
                            </div>
                            <div class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mt-2 whitespace-nowrap">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('M') }}
                                <span class="hidden sm:inline">{{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('Y') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Two-column: top events + top tickets --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">

        {{-- Top Events --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Top Events by Revenue</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3 text-center">Orders</th>
                            <th class="px-5 py-3 text-center">Sold</th>
                            <th class="px-5 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($topEvents as $ev)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3">
                                    <a href="{{ route('events.show', $ev->id) }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $ev->name }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-center">{{ $ev->orders_count }}</td>
                                <td class="px-5 py-3 text-center">{{ $ev->tickets_sold }}</td>
                                <td class="px-5 py-3 text-right font-medium">KES {{ number_format($ev->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top Tickets --}}
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Top Ticket Types</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Ticket</th>
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3 text-center">Sold</th>
                            <th class="px-5 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($topTickets as $t)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 font-medium">{{ $t->name }}</td>
                                <td class="px-5 py-3">{{ optional($t->event)->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-center">{{ $t->sold }}</td>
                                <td class="px-5 py-3 text-right font-medium">KES {{ number_format($t->revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Payment Mode Breakdown --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Payment Methods Breakdown</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">Payment Method</th>
                        <th class="px-5 py-3 text-center">Transactions</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($paymentBreakdown as $p)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-3 font-medium">{{ $p->payment_mode ?? 'Unknown' }}</td>
                            <td class="px-5 py-3 text-center">{{ $p->count }}</td>
                            <td class="px-5 py-3 text-right font-medium">KES {{ number_format($p->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-8 text-center text-gray-500">No paid payments in this range.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection