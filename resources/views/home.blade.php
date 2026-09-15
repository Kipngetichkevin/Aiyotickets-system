@extends('layouts.app')
@vite('resources/css/app.css')

@section('content')
    <div class="px-4 lg:px-6 lg:px-8 py-10">
        <!-- Slider -->
        @include('partials.slider')

        <!-- Hero Image -->
        @if (!empty($event->image))
            <div class="mt-10">
                <img src="{{ $event->image }}" alt="Event Image" class="w-full h-auto object-cover rounded-lg shadow-lg">
            </div>
        @endif

        <!-- Events Section -->
        <div class="mt-10">
            <h2 class="text-2xl font-bold">Upcoming Events</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                @foreach ($events as $event)
                    <div class="bg-white shadow-lg rounded-lg p-5">
                        <h3 class="text-xl font-semibold">{{ $event->name }}</h3>
                        <p class="mt-3 text-gray-500">{{ $event->description }}</p>
                        @if (!empty($event->image))
                            <img src="{{ $event->image }}" alt="Event Image"
                                class="w-full h-auto object-cover rounded-lg shadow-lg mt-4">
                        @endif
                        <a href="{{ route('event.show', $event->id) }}" class="mt-4 inline-block text-blue-600">View
                            Event</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
