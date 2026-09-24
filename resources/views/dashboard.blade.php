@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">
    <!-- Metrics Grid -->
    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <!-- Current Events -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Current Events
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                {{ $currentEventsCount ?? 0 }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Tickets Sold -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Tickets Sold
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                {{ $ticketsSold ?? 0 }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Total Sales -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Total Sales
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                KES {{ number_format($totalSales ?? 0, 2) }}
                            </h3>
                        </div>
                    </div>
                </div>

                <!-- Pending Sales -->
                <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                    <div class="p-4 md:p-5">
                        <div class="flex items-center gap-x-2">
                            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">
                                Pending Sales
                            </p>
                        </div>
                        <div class="mt-1 flex items-center gap-x-2">
                            <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                                KES {{ number_format($pendingSales ?? 0, 2) }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-12 gap-4 md:gap-6 mt-6">
        <div class="col-span-12 lg:col-span-6">
            @include('partials.chart.income-chart')
        </div>
        <div class="col-span-12 lg:col-span-6">
            @include('partials.chart.tickets-chart')
        </div>
    </div>

    <!-- Include the events table partial -->
    @include('partials.table.table-events', ['events' => $events ?? []])

    <!-- Recent Attendees Section -->
    <div class="grid grid-cols-12 gap-4 md:gap-6 mt-6">
        <div class="col-span-12">
            @include('partials.card.attendees-card', ['attendees' => $attendees ?? collect()])
        </div>
    </div>
</div>
@endsection