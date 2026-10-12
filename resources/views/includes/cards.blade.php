<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @forelse ($events as $event)
            <a href="{{ route('event.show', $event->id) }}"
               class="group block bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">

                <!-- Image + Date Badge -->
                <div class="relative h-64 w-full overflow-hidden">
                    <img
                        src="{{ asset($event->image) }}"
                        alt="{{ $event->name }} poster"
                        class="w-full h-full object-cover transform transition-transform duration-300 group-hover:scale-105"
                    >

                    @if($event->date)
                        <div class="absolute top-3 left-3 bg-black/80 text-white p-2 rounded-lg text-center shadow-md">
                            <div class="font-bold text-lg leading-none">
                                {{ \Carbon\Carbon::parse($event->date)->format('d') }}
                            </div>
                            <div class="text-xs uppercase tracking-wide">
                                {{ \Carbon\Carbon::parse($event->date)->format('M') }}
                            </div>
                        </div>
                    @endif

                    {{-- Past / ended overlay --}}
                    @if($event->end_date && \Carbon\Carbon::parse($event->end_date)->isPast())
                        <div class="absolute top-3 right-3 bg-gray-800/90 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full">
                            Ended
                        </div>
                    @endif
                </div>

                <!-- Details -->
                <div class="p-3 space-y-1">
                    <h3 class="text-md font-semibold text-gray-900 truncate">
                        {{ $event->name }}
                    </h3>

                    <!-- Organizer -->
                    <div class="flex items-center text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                        </svg>
                        <span class="text-indigo-600 font-medium">{{ $event->merchant?->name ?? 'Organizer' }}</span>
                    </div>

                    <!-- Location -->
                    <div class="flex items-center text-xs text-gray-600">
                        <svg class="w-4 h-4 mr-1.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/>
                        </svg>
                        <span class="truncate">{{ $event->venue->name ?? $event->location }}</span>
                    </div>
                </div>
            </a>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">{{ $emptyMessage ?? 'No events found.' }}</p>
            </div>
        @endforelse
    </div>
</div>