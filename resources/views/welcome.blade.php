@php($appName = \App\Helpers\SystemHelper::appName())
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appName }} | Events and experiences in Kenya</title>
    <meta name="description" content="{{ \App\Helpers\SystemHelper::metaDescription() ?: 'Tickets for concerts, comedy, festivals and multi-day experiences. Pay with M-Pesa and get your QR ticket instantly.' }}">
    <meta property="og:title" content="{{ $appName }}">
    <meta property="og:description" content="Events and experiences in Kenya — pay with M-Pesa, get your QR ticket instantly.">
    <meta property="og:url" content="{{ url('/') }}">
    @if (!empty($slides[0]['image']))
        <meta property="og:image" content="{{ $slides[0]['image'] }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-outfit antialiased bg-gray-50"
      x-data="{
          sidebarToggle: false,
          darkMode: JSON.parse(localStorage.getItem('darkMode')) || false,
      }"
      x-init="$watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
      :class="{'dark bg-gray-950': darkMode}">

@auth
    {{-- Logged in: the usual sidebar and dashboard header around the same content --}}
    <div class="flex h-screen overflow-hidden">
        @include('partials.sidebar')

        <div class="relative flex flex-1 flex-col overflow-y-auto overflow-x-hidden scroll-smooth">
            @include('partials.header')
            <main class="flex-1">
                @include('home.content')
            </main>
            @include('includes.footer')
        </div>
    </div>
@else
    {{-- Guests: a clean public page, no sidebar --}}
    @include('includes.header')
    <main>
        @include('home.content')
    </main>
    @include('includes.footer')
@endauth

</body>
</html>
