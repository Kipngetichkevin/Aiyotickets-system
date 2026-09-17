@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Attendees</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $isAdmin ? 'Every attendee across all events' : 'Attendees for your events' }}
            </p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Attendees</p>
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
        <form method="GET" action="{{ route('attendees.all') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="Search name, email, phone, QR"
                class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm"
            >

            <select name="event_id" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All events</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" @selected(request('event_id') == $ev->id)>{{ $ev->name }}</option>
                @endforeach
            </select>

            <select name="payment_status" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All payment statuses</option>
                <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                <option value="pending" @selected(request('payment_status') === 'pending')>Pending</option>
                <option value="refunded" @selected(request('payment_status') === 'refunded')>Refunded</option>
                <option value="failed" @selected(request('payment_status') === 'failed')>Failed</option>
            </select>

            <div class="flex gap-2 lg:col-span-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('attendees.all') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Ticket</th>
                        <th class="px-5 py-3 text-center">Qty</th>
                        <th class="px-5 py-3 text-right">Total</th>
                        <th class="px-5 py-3">Payment</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($attendees as $a)
                        @php
                            $event = optional(optional($a->ticket)->event);
                            $payStatus = $a->payment_status ?? 'pending';
                            $statusClasses = match($payStatus) {
                                'paid' => 'bg-green-100 text-green-700',
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'refunded' => 'bg-blue-100 text-blue-700',
                                'failed' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-3">
                                <div class="font-medium">{{ $a->name ?? '—' }}</div>
                                @if($a->qr_code)
                                    <div class="text-xs font-mono text-gray-500 dark:text-gray-400 mt-0.5">{{ \Illuminate\Support\Str::limit($a->qr_code, 24) }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div>{{ $a->email ?? '—' }}</div>
                                <div class="text-xs text-gray-500">{{ $a->phone ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3">{{ $event->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ optional($a->ticket)->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-center">{{ $a->qty ?? 1 }}</td>
                            <td class="px-5 py-3 text-right font-medium">KES {{ number_format($a->total_price ?? 0, 2) }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusClasses }}">
                                    {{ ucfirst($payStatus) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <a href="{{ route('ticket.pdf', $a->id) }}"
                                   target="_blank"
                                   class="text-blue-600 hover:underline text-xs">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                No attendees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">
            {{ $attendees->links() }}
        </div>
    </div>
</div>
@endsection