@vite('resources/css/app.css')
<!-- Sidebar and Header -->
@include('backend.includes.header')
@include('backend.includes.Sidebar')

<div class="p-4 sm:p-6 space-y-4 sm:space-y-6">
    <!-- Content -->
    <div class="w-full lg:ps-64">
        <h1 class="text-2xl font-semibold">Current Events</h1>

        <!-- Display a list of events for the user -->
        <ul class="mt-4 space-y-2">
            @foreach ($events as $event)
                <li class="flex justify-between items-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="flex-1">
                        <p class="text-lg font-medium">{{ $event->name }}</p>
                        <p class="text-sm text-gray-500">{{ $event->date->format('F j, Y') }} -
                            {{ $event->end_date->format('F j, Y') }}</p>
                    </div>

                    <!-- Check-In Button -->
                    <a href="{{ route('backend.qrcode.show', ['event_id' => $event->id]) }}"
                        class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                        Check-In Attendees
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    <!-- End Content -->
</div>


<!-- JS Implementing Plugins -->
<script src="https://cdn.jsdelivr.net/npm/preline/dist/preline.min.js"></script>
