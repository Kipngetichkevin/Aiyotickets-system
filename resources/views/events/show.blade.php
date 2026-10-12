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
                    <button @click="$dispatch('open-event-edit-modal', { 
                        id: {{ $event->id }},
                        name: '{{ addslashes($event->name) }}',
                        event_type_id: {{ $event->event_type_id ?? 'null' }},
                        currency: '{{ $event->currency }}',
                        description: `{{ addslashes($event->description) }}`,
                        date: '{{ $event->date }}',
                        end_date: '{{ $event->end_date }}',
                        location: '{{ addslashes($event->location) }}',
                        status: '{{ $event->status }}',
                        image: '{{ $event->image }}',
                        banner: '{{ $event->banner }}'
                    })" 
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Event
                    </button>
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
                            {!! $event->description !!}
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

    {{-- ============ EXPERIENCES SECTION ============ --}}
    <div class="bg-white rounded-xl shadow-sm p-6 mb-8 mx-4 dark:bg-neutral-800 dark:border-neutral-700">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                    Experiences
                </h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                    Premium add-ons attached to this event.
                </p>
            </div>

            <a href="{{ route('experiences.index') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="h-4 w-4">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Manage Experiences
            </a>
        </div>

        @php
            $eventExperiences = $event->experiences ?? collect();
        @endphp

        @if($eventExperiences->isEmpty())
            <div class="text-center py-10 border border-dashed border-gray-200 rounded-lg dark:border-neutral-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5"
                     class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-neutral-400">
                    No experiences attached to this event yet.
                </p>
                <a href="{{ route('experiences.index') }}"
                   class="inline-flex items-center gap-1 mt-3 text-sm font-medium text-blue-600 hover:underline">
                    Go to Experiences →
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($eventExperiences as $exp)
                    @php
                        $pivot = $exp->pivot;
                        $isActive = $pivot->is_active ?? true;
                        $price = $pivot->price_override ?? $exp->price;
                        $capacity = $pivot->capacity_override ?? $exp->capacity;
                    @endphp

                    <div class="rounded-xl border border-gray-200 bg-white overflow-hidden transition hover:shadow-md dark:border-neutral-700 dark:bg-neutral-800 {{ $isActive ? '' : 'opacity-60' }}">
                        @if($exp->image)
                            <div class="aspect-video overflow-hidden">
                                <img src="{{ asset('storage/' . $exp->image) }}"
                                     alt="{{ $exp->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                        @endif

                        <div class="p-4">
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-semibold text-gray-800 dark:text-neutral-200 truncate">
                                    {{ $exp->name }}
                                </h3>
                                @if($isActive)
                                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-500/10 dark:text-green-400">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-white/5 dark:text-gray-400">
                                        Inactive
                                    </span>
                                @endif
                            </div>

                            @if($exp->short_description)
                                <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400 line-clamp-2">
                                    {{ $exp->short_description }}
                                </p>
                            @endif

                            <div class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600 dark:text-neutral-400">
                                <span class="font-semibold text-gray-800 dark:text-neutral-200">
                                    {{ $exp->currency }} {{ number_format((float) $price, 2) }}
                                </span>
                                @if($exp->duration_minutes)
                                    <span>{{ $exp->duration_minutes }} min</span>
                                @endif
                                @if($capacity)
                                    <span>Cap: {{ $capacity }}</span>
                                @endif
                            </div>

                            @if($pivot->price_override)
                                <p class="mt-2 text-xs text-amber-600 dark:text-amber-400">
                                    Override price for this event
                                </p>
                            @endif

                            <div class="mt-4 flex items-center gap-2">
                                <a href="{{ route('experiences.index') }}"
                                   class="flex-1 text-center rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-neutral-600 dark:text-neutral-300 dark:hover:bg-neutral-700">
                                    Edit
                                </a>
                                <button type="button"
                                        onclick="detachExperience({{ $exp->id }}, '{{ addslashes($exp->name) }}')"
                                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
                                    Detach
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    @include('partials.table.table-attendees', [
        'attendees' => $attendees ?? collect(),
        'event' => $event
    ])
</main>

<script>
    function detachExperience(experienceId, name) {
        if (!confirm(`Detach "${name}" from this event?`)) return;

        fetch(`{{ url('experiences') }}/${experienceId}`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(json => {
            if (!json.success) {
                alert('Could not load experience');
                return;
            }

            // Get current event IDs, remove this one, then PUT the update
            const ids = (json.data.event_ids || []).filter(id => id !== {{ $event->id }});

            const formData = new FormData();
            formData.append('_method', 'PUT');
            formData.append('name', json.data.name);
            formData.append('short_description', json.data.short_description || '');
            formData.append('description', json.data.description || '');
            formData.append('price', json.data.price);
            formData.append('currency', json.data.currency);
            formData.append('status', json.data.status);
            formData.append('featured', json.data.featured ? 1 : 0);
            ids.forEach(id => formData.append('event_ids[]', id));

            fetch(`{{ url('experiences') }}/${experienceId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    window.location.reload();
                } else {
                    alert(res.message || 'Failed to detach');
                }
            });
        });
    }
</script>
@endsection