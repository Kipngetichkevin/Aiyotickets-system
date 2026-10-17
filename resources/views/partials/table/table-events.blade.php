{{-- resources/views/partials/table/table-events.blade.php --}}
@props(['events'])

@php
    $canManage = auth()->check() && (auth()->user()->hasRole('merchant') || auth()->user()->hasRole('admin'));
@endphp

    <!-- Events Table Section -->
    <div class="grid grid-cols-12 gap-4 md:gap-6 mt-6">
        <div class="col-span-12">
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">


                <!-- Header Section -->
                <div class="flex items-center justify-between px-6 py-5">
                    <div>
                        <h3 class="text-xl font-medium text-gray-800 dark:text-white/90">
                            Your Events
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Manage and view all your events
                        </p>
                    </div>
                    @if($canManage)
                        <!-- Create New Event Button - Opens Modal -->
                        <button @click="$dispatch('open-event-create-modal')"
                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-brand-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Create New Event
                        </button>
                    @endif
                </div>

                <!-- Events Grid -->
                <div class="border-t border-gray-100 p-4 dark:border-gray-800 sm:p-6">
                    @if($events->count() > 0)
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($events as $event)
                        <!-- Event Card -->
                        <div class="group" x-data="{ editModalOpen: false }">
                            <!-- Card Container -->
                            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] transition-all duration-300 ease-in-out group-hover:shadow-lg group-hover:scale-[1.02] group-hover:border-blue-200 dark:group-hover:border-blue-800">
                                <!-- Event Image - 1080x800 portrait ratio -->
                                <div class="mb-5 overflow-hidden rounded-lg transition-transform duration-300 group-hover:scale-[1.01] aspect-[1080/800]">
                                    <img 
                                        src="{{ $event->image }}" 
                                        class="w-full h-full object-cover rounded-lg" 
                                        alt="{{ $event->name }}"
                                        onerror="this.onerror=null;this.src='{{ asset('images/placeholder.jpg') }}'"
                                    />
                                </div>
                                <!-- Event Content -->
                                <div>
                                    <!-- Title and Category -->
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="mb-1 text-lg font-medium text-gray-800 dark:text-white/90 transition-colors duration-200 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                            <a href="{{ route('events.show', $event->id) }}" class="hover:underline">{{ $event->name }}</a>
                                            
                                        </h4>
                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-400 transition-colors duration-200 group-hover:bg-blue-200 dark:group-hover:bg-blue-800/50">
                                            {{ $event->eventType->name ?? 'No category' }}
                                        </span>
                                    </div>

                                    <!-- Event Date and Venue -->
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                            {{ $event->date ? \Carbon\Carbon::parse($event->date)->format('D, d M Y') : 'Update date' }}
                                        </p>
                                        @if($event->location)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors duration-200 group-hover:text-gray-700 dark:group-hover:text-gray-300">
                                            {{ $event->location }}
                                        </p>
                                        @endif
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex justify-between items-center mt-4">
                                        <a href="{{ route('events.show', $event->id) }}" class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">
                                            View Details
                                        </a>
                                        @if($canManage)
                                            <div class="flex space-x-2">
                                                <!-- Edit Button - Now triggers the modal -->
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
                                                        class="p-2 text-gray-500 hover:text-blue-600 dark:text-gray-400 dark:hover:text-blue-400 transition-colors duration-200 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-full" 
                                                        title="Edit">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button @click="$dispatch('open-delete-event-modal', { eventId: {{ $event->id }}, eventName: '{{ addslashes($event->name) }}' })" 
                                                        class="p-2 text-gray-500 hover:text-red-600 dark:text-gray-400 dark:hover:text-red-400 transition-colors duration-200 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-full" 
                                                        title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 dark:text-gray-400">No events found. Create your first event!</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div> 

<!-- Include Modals (only for merchants/admins) -->
@if($canManage)
    @include('partials.modal.delete-modal')
    @include('partials.modal.event-create-modal', ['eventTypes' => $eventTypes ?? [], 'merchants' => $merchants ?? []])
    {{-- /events?new=1 (where "Create event" leads) opens the form straight away --}}
    @if (request()->boolean('new'))
        <div x-data x-init="setTimeout(() => $dispatch('open-event-create-modal'), 100)"></div>
    @endif
@endif