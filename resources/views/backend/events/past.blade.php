@vite('resources/css/app.css')

@include('backend.includes.header')
@include('backend.includes.Sidebar')

<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <div class="w-full lg:ps-64 max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8">
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white">Past Events</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">Organizer ID: {{ session('id') }}</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <a href="{{ route('backend.events.create') }}" 
                   class="hs-button-full bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-4 rounded-lg transition-all flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Event
                </a>
                
                <a href="{{ route('backend.events.show', ['user_id' => session('user_id')]) }}" 
                   class="hs-button-full bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-4 rounded-lg transition-all flex items-center justify-center gap-2 dark:bg-neutral-700 dark:hover:bg-neutral-600 dark:text-white">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Current Events
                </a>
            </div>
        </div>

        <!-- Events Grid -->
        @if ($events->isEmpty())
            <div class="text-center py-12 bg-white rounded-xl dark:bg-neutral-800">
                <p class="text-gray-500 dark:text-neutral-400">No past events found. Go back!</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($events as $event)
    <div class="group bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all dark:bg-neutral-800 dark:border-neutral-700">
        <div class="relative rounded-t-xl overflow-hidden">
            <img src="{{ asset($event->image) }}" alt="{{ $event->name }}" 
                 class="aspect-video w-full object-cover transform transition-transform duration-300 group-hover:scale-105">
            @if(empty($event->image))
                <div class="absolute inset-0 bg-gray-100 flex items-center justify-center">
                    <span class="text-gray-400">No image available</span>
                </div>
            @endif
        </div>

        <div class="p-4 md:p-5">
            <div class="mb-4">
                <h3 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">{{ $event->name }}</h3>
                <div class="mt-2 space-y-1 text-sm">
                    <p class="flex items-center text-gray-600 dark:text-neutral-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $event->date->format('M j, Y H:i') }}
                    </p>
                    <p class="flex items-center text-gray-600 dark:text-neutral-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $event->location ?? 'Location not specified' }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-4 dark:border-neutral-700">
                <a href="{{ route('event.show', $event->id) }}" class="hs-tooltip inline-flex items-center gap-x-2 text-sm font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </a>

                <a href="{{ route('backend.events.page', ['event_id' => $event->id, 'user_id' => session('user_id')]) }}" class="hs-tooltip inline-flex items-center gap-x-2 text-sm font-medium text-gray-600 hover:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </a>

                @if (auth()->id() === $event->user_id)
                <div class="flex items-center gap-2 ml-auto">
                    <a href="{{ route('backend.events.edit', ['event' => $event->id]) }}" class="hs-tooltip inline-flex items-center gap-x-2 text-sm text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </a>

                    <button type="button" data-hs-overlay="#delete-modal-{{ $event->id }}" class="hs-tooltip inline-flex items-center gap-x-2 text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="delete-modal-{{ $event->id }}" class="hs-overlay hidden size-full fixed top-0 start-0 z-[100] overflow-x-hidden overflow-y-auto">
        <div class="hs-overlay-open:mt-7 hs-overlay-open:opacity-100 hs-overlay-open:duration-500 mt-14 ease-out transition-all sm:max-w-lg sm:w-full m-3 sm:mx-auto">
            <div class="flex flex-col bg-white border shadow-sm rounded-xl dark:bg-neutral-800 dark:border-neutral-700">
                <div class="flex justify-between items-center py-3 px-4 border-b dark:border-neutral-700">
                    <h3 class="font-bold text-gray-800 dark:text-white">Confirm Deletion</h3>
                    <button type="button" class="hs-overlay-toggle size-8 inline-flex justify-center items-center gap-x-2 rounded-full bg-gray-100 hover:bg-gray-200 focus:outline-none dark:bg-neutral-700 dark:hover:bg-neutral-600" data-hs-overlay="#delete-modal-{{ $event->id }}">
                        <span class="sr-only">Close</span>
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18"/>
                            <path d="m6 6 12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <p class="text-gray-800 dark:text-neutral-400">
                        Are you sure you want to delete the event <strong>"{{ $event->name }}"</strong>? This action cannot be undone.
                    </p>
                </div>
                <div class="flex justify-end items-center gap-x-2 py-3 px-4 border-t dark:border-neutral-700">
                    <button type="button" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700" data-hs-overlay="#delete-modal-{{ $event->id }}">
                        Cancel
                    </button>
                    <form action="/backend/events/delete/{{$event->id}}" method="POST">
                        @csrf
                        @method('DELETE')
                       
                        <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-red-600 text-white hover:bg-red-700">
                            Delete Event
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/preline@2.0.0/dist/preline.min.js"></script>

<script>
document.querySelectorAll('.hs-overlay-toggle').forEach((button) => {
    button.addEventListener('click', (e) => {
        // Get the target modal and the overlay
        const modalId = button.getAttribute('data-hs-overlay');
        const modal = document.querySelector(modalId);
        const overlay = modal.querySelector('.hs-overlay'); // Ensure this matches the overlay class

        // Toggle the 'hidden' class on the modal and the overlay
        if (modal) {
            modal.classList.toggle('hidden');
        }

        if (overlay) {
            overlay.classList.toggle('hidden'); // This will hide the dark overlay
        }
    });
});


</script>