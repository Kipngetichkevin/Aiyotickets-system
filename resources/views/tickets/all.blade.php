@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">All Tickets</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $isAdmin ? 'Every ticket type across all events' : 'Ticket types for your events' }}
            </p>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Ticket Types</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Capacity</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($stats['capacity']) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Sold</p>
            <h3 class="mt-2 text-2xl font-semibold text-green-600">{{ number_format($stats['sold']) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Available</p>
            <h3 class="mt-2 text-2xl font-semibold text-blue-600">{{ number_format($stats['available']) }}</h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('tickets.all') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <select name="event_id" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All events</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" @selected(request('event_id') == $ev->id)>{{ $ev->name }}</option>
                @endforeach
            </select>

            <select name="status" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All statuses</option>
                <option value="available" @selected(request('status') === 'available')>Available</option>
                <option value="soldout" @selected(request('status') === 'soldout')>Sold Out</option>
            </select>

            <div class="flex gap-2 lg:col-span-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('tickets.all') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">Ticket No</th>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3 text-right">Price</th>
                        <th class="px-5 py-3 text-center">Qty</th>
                        <th class="px-5 py-3 text-center">Sold</th>
                        <th class="px-5 py-3 text-center">Available</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($tickets as $ticket)
                        @php
                            $sold = \App\Models\Attendee::where('ticket_id', $ticket->id)->sum('qty');
                            $available = max(0, $ticket->quantity - $sold);
                            $isSoldOut = $available <= 0;
                        @endphp
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-3 font-mono text-xs">{{ $ticket->ticket_no ?? '—' }}</td>
                            <td class="px-5 py-3 font-medium">{{ $ticket->name ?? '—' }}</td>
                            <td class="px-5 py-3">{{ optional($ticket->event)->name ?? '—' }}</td>
                            <td class="px-5 py-3 text-right">{{ $ticket->currency }} {{ number_format($ticket->price, 2) }}</td>
                            <td class="px-5 py-3 text-center">{{ $ticket->quantity }}</td>
                            <td class="px-5 py-3 text-center font-medium text-green-600">{{ $sold }}</td>
                            <td class="px-5 py-3 text-center font-medium {{ $isSoldOut ? 'text-red-600' : 'text-blue-600' }}">{{ $available }}</td>
                            <td class="px-5 py-3">
                                @if($isSoldOut)
                                    <span class="rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700">Sold Out</span>
                                @else
                                    <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Available</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                @if($ticket->event_id)
                                    <a href="{{ route('tickets.edit', ['event_id' => $ticket->event_id, 'id' => $ticket->id]) }}"
                                       class="text-blue-600 hover:underline text-xs">Edit</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                No tickets found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">
            {{ $tickets->links() }}
        </div>
    </div>
</div>
@endsection