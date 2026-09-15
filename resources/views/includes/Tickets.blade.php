<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite('resources/css/app.css')
</head>
<body>

@include('includes.header')

<div class="px-4 lg:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold">{{ $event->name }}</h1>
    <p class="mt-4 text-gray-700">{{ $event->description }}</p>
    <p class="mt-2 text-gray-500">Date: {{ $event->date->format('d M Y') }}</p>
    <p class="mt-2 text-gray-500">Location: {{ $event->location }}</p>

    <a href="{{ route('checkout', $event->id) }}" class="mt-6 inline-block bg-blue-600 text-white py-2 px-4 rounded-lg">Buy Tickets</a>
</div>

<!-- Card Grid -->
<div class="py-12 grid grid-cols-2 gap-4 sm:gap-6 md:gap-8 lg:gap-12">
  <!-- Card -->
  <a class="group flex flex-col focus:outline-none" href="#">
    <div class="aspect-w-16 aspect-h-12 overflow-hidden bg-gray-100 rounded-2xl">
      <img class="group-hover:scale-105 group-focus:scale-105 transition-transform duration-500 ease-in-out object-cover rounded-2xl" src="https://images.unsplash.com/photo-1575052814086-f385e2e2ad1b?q=80&w=560&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Blog Image">
    </div>

    <div class="pt-4">
      <h3 class="relative inline-block font-medium text-lg text-black before:absolute before:bottom-0.5 before:start-0 before:-z-[1] before:w-full before:h-1 before:bg-lime-400 before:transition before:origin-left before:scale-x-0 group-hover:before:scale-x-100">
        eYoga
      </h3>
      <p class="mt-1 text-gray-600">
        A revamped and dynamic approach to yoga analytics
      </p>

      <div class="mt-3 flex flex-wrap gap-2">
        <span class="py-1.5 px-3 bg-white text-gray-600 border border-gray-200 text-xs sm:text-sm rounded-xl">
          Discovery
        </span>
        <span class="py-1.5 px-3 bg-white text-gray-600 border border-gray-200 text-xs sm:text-sm rounded-xl">
          Brand Guidelines
        </span>
        <span class="py-1.5 px-3 bg-white text-gray-600 border border-gray-200 text-xs sm:text-sm rounded-xl">
          Yoga
        </span>
      </div>
    </div>
  </a>
  <!-- End Card -->

  <!-- Add the rest of the card grid here as needed -->
</div>
<!-- End Card Grid -->

@include('includes.Footer')

</body>
</html>
