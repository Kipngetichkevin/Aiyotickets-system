@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Welcome, {{ $user->name }}</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Here's what's happening with your tickets and upcoming events
        </p>
    </div>

    {{-- Stat cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">My Tickets</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($myTicketsCount) }}</h3>
            <p class="mt-1 text-xs text-gray-500">Tickets you own</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">My Orders</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($myOrdersCount) }}</h3>
            <p class="mt-1 text-xs text-gray-500">Purchase history</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Spent</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($mySpent, 2) }}</h3>
            <p class="mt-1 text-xs text-gray-500">On paid orders</p>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <a href="{{ route('browse') }}"
           class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 hover:border-blue-300 hover:bg-blue-50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:bg-blue-900/10 transition">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div>
                <p class="font-medium text-gray-800 dark:text-white/90">Browse Events</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Find something to attend</p>
            </div>
        </a>

        <a href="{{ route('my-tickets.index') }}"
           class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 hover:border-green-300 hover:bg-green-50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:bg-green-900/10 transition">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                </svg>
            </div>
            <div>
                <p class="font-medium text-gray-800 dark:text-white/90">My Tickets</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">View and download</p>
            </div>
        </a>

        <a href="{{ route('my-orders.index') }}"
           class="flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 hover:border-purple-300 hover:bg-purple-50 dark:border-gray-800 dark:bg-white/[0.03] dark:hover:bg-purple-900/10 transition">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <p class="font-medium text-gray-800 dark:text-white/90">My Orders</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Order history</p>
            </div>
        </a>
    </div>

    {{-- Upcoming events --}}
    <div class="mb-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-800 dark:text-white/90">Upcoming Events</h2>
            <a href="{{ route('browse') }}" class="text-sm text-blue-600 hover:underline">View all</a>
        </div>

        @if($upcomingEvents->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center dark:border-gray-700 dark:bg-gray-900/50">
                <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming events yet.</p>
                <a href="{{ route('browse') }}" class="mt-3 inline-block text-sm font-medium text-blue-600 hover:underline">Browse events →</a>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($upcomingEvents as $event)
                    <a href="{{ route('event.show', $event->id) }}"
                       class="group rounded-xl border border-gray-200 bg-white overflow-hidden hover:border-blue-300 hover:shadow-md transition dark:border-gray-800 dark:bg-white/[0.03]">
                        @if($event->image)
                            <div class="aspect-[16/9] overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <img src="{{ $event->image }}" alt="{{ $event->name }}"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            </div>
                        @endif
                        <div class="p-4">
                            <h3 class="font-medium text-gray-800 dark:text-white/90 truncate">{{ $event->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                {{ \Carbon\Carbon::parse($event->date)->format('D, d M Y') }}
                            </p>
                            @if($event->location)
                                <p class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $event->location }}</p>
                            @endif
                            <p class="mt-3 inline-flex items-center text-sm font-medium text-blue-600">
                                Buy Tickets →
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent tickets --}}
    @if($myRecentTickets->isNotEmpty())
        <div>
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-800 dark:text-white/90">Recent Tickets</h2>
                <a href="{{ route('my-tickets.index') }}" class="text-sm text-blue-600 hover:underline">View all</a>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03] overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3">Ticket</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Purchased</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($myRecentTickets as $t)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3">{{ optional(optional($t->ticket)->event)->name ?? '—' }}</td>
                                <td class="px-5 py-3">{{ optional($t->ticket)->name ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    @if($t->payment_status === 'paid')
                                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Paid</span>
                                    @else
                                        <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">{{ ucfirst($t->payment_status) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    {{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('d M Y') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection