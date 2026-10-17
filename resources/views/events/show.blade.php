@extends('layouts.app')

@section('content')
<main>
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: `{{ $event->name }}` }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    <!-- Statistics Grid -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8 px-4">
        <!-- Tickets Sold Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
            <div class="p-4 md:p-5">
                <div class="flex items-center gap-x-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Tickets Sold</p>
                </div>
                <div class="mt-1 flex items-center gap-x-2">
                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">{{ $ticketsSold ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <!-- Ticket Sales Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
            <div class="p-4 md:p-5">
                <div class="flex items-center gap-x-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Ticket Sales</p>
                </div>
                <div class="mt-1 flex items-center gap-x-2">
                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">KES {{ number_format($ticketSales ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- Net Sales Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
            <div class="p-4 md:p-5">
                <div class="flex items-center gap-x-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Net Sales</p>
                </div>
                <div class="mt-1 flex items-center gap-x-2">
                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">KES {{ number_format($netSales ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>

        <!-- Ticket Types Card -->
        <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
            <div class="p-4 md:p-5">
                <div class="flex items-center gap-x-2">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Ticket Types</p>
                </div>
                <div class="mt-1 flex items-center gap-x-2">
                    <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">{{ $event->tickets->count() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Details Section -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8 mx-4 dark:bg-neutral-800 dark:border-neutral-700">
        <div class="flex flex-col md:flex-row gap-6">
            <div class="md:w-1/3">
                <div class="aspect-w-16 aspect-h-9 rounded-xl overflow-hidden">
                    @if($event->image)
                        <img src="{{ asset($event->image) }}" alt="{{ $event->name }}"
                             class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
                    @else
                        <div class="w-full h-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                            <span class="text-gray-500 dark:text-gray-400">No image</span>
                        </div>
                    @endif
                </div>
                <!-- Edit Event Button -->
                <div class="mt-4">
                    <button @click="$dispatch('open-event-edit-modal', @js([
                        'id' => $event->id,
                        'name' => $event->name,
                        'event_type_id' => $event->event_type_id,
                        'merchant_id' => $event->merchant_id,
                        'currency' => $event->currency,
                        'description' => $event->description,
                        'date' => (string) $event->getRawOriginal('date'),
                        'end_date' => (string) $event->getRawOriginal('end_date'),
                        'location' => $event->location,
                        'status' => $event->status,
                        'image' => $event->image,
                        'banner' => $event->banner,
                    ]))"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Event
                    </button>
                </div>
                <div class="mt-3">
                    @include('partials.listing-changes', ['type' => 'event', 'item' => $event])
                </div>
            </div>

            <div class="md:w-2/3 space-y-4">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Event Details</h2>
                <div class="space-y-2 text-gray-600 dark:text-neutral-400">
                    <p><strong class="text-gray-800 dark:text-neutral-200">Organizer:</strong> {{ $event->merchant?->name ?? auth()->user()->name ?? 'N/A' }}</p>
                    <p><strong class="text-gray-800 dark:text-neutral-200">Date:</strong> {{ \Carbon\Carbon::parse($event->date)->format('l, F j, Y') }}</p>
                    <p><strong class="text-gray-800 dark:text-neutral-200">Location:</strong> {{ $event->location }}</p>
                    <p><strong class="text-gray-800 dark:text-neutral-200">Status:</strong>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($event->status == 'active') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400
                            @elseif($event->status == 'inactive') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400
                            @elseif($event->status == 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400
                            @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400
                            @endif">
                            {{ ucfirst($event->status) }}
                        </span>
                    </p>
                    <div class="mt-2">
                        <strong class="text-gray-800 dark:text-neutral-200">Description:</strong>
                        <div class="prose prose-sm max-w-none dark:prose-invert mt-1">
                            {!! \App\Support\HtmlSanitizer::clean($event->description) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tickets Section -->
    @include('partials.table.table-tickets', [
        'tickets' => $event->tickets,
        'eventId' => $event->id,
        'soldQuantities' => $soldQuantities ?? []
    ])

    @include('partials.table.table-attendees', [
        'attendees' => $attendees ?? collect(),
        'event' => $event
    ])
</main>
    @include('partials.modal.event-create-modal', ['eventTypes' => $eventTypes, 'merchants' => $merchants])
@endsection