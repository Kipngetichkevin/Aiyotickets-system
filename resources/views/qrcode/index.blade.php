@extends('layouts.app')

@section('content')
<main>
    <!-- Breadcrumb Start -->
    <div x-data="{ pageName: 'QR Code Management' }">
        @include('partials.breadcrumb')
    </div>
    <!-- Breadcrumb End -->

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <!-- Header Section -->
            <div class="flex items-center justify-between px-6 py-5">
                <div>
                    <h3 class="text-xl font-medium text-gray-800 dark:text-white/90">
                        Your Events
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        Select an event to manage QR code scanning
                    </p>
                </div>
            </div>

            <!-- Events Grid -->
            <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                @if($events->count() > 0)
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($events as $event)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all duration-300 ease-in-out hover:shadow-lg hover:scale-[1.02] hover:border-blue-200 dark:hover:border-blue-800">
                        <!-- Event Image -->
                        @if($event->image)
                        <div class="mb-5 overflow-hidden rounded-lg aspect-[1080/800]">
                            <img
                                src="{{ $event->image }}"
                                class="w-full h-full object-cover rounded-lg"
                                alt="{{ $event->name }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/placeholder.jpg') }}'"
                            />
                        </div>
                        @endif

                        <!-- Event Content -->
                        <div>
                            <h4 class="mb-1 text-lg font-medium text-gray-800 dark:text-white/90">
                                {{ $event->name }}
                            </h4>
                            <div class="space-y-1">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $event->date ? \Carbon\Carbon::parse($event->date)->format('D, d M Y') : 'Update date' }}
                                </p>
                                @if($event->location)
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $event->location }}
                                </p>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-between items-center mt-4">
                                <a href="{{ route('qrcode.show', $event->id) }}"
                                   class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                    Scan Tickets
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">No upcoming events found. Create an event first!</p>
                    <a href="{{ route('events.create') }}" class="inline-flex items-center gap-2 mt-4 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Create Event
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection