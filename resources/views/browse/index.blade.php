@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Browse Events</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Find events happening near you</p>
    </div>

    @if($events->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-12 text-center dark:border-gray-700 dark:bg-gray-900/50">
            <p class="text-gray-500 dark:text-gray-400">No events available at the moment.</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($events as $event)
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
                            View & Buy →
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection