{{-- Bare page for door staff on a gate link: no menus, no account --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>@yield('title', 'Gate') | {{ \App\Helpers\SystemHelper::appName() }}</title>
    <link rel="icon" href="{{ \App\Helpers\SystemHelper::faviconUrl() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-outfit antialiased dark:bg-gray-950"
      x-data="{ darkMode: false }">
    <header class="flex items-center justify-between border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
        <img src="{{ asset('images/logo/logo.svg') }}" alt="{{ \App\Helpers\SystemHelper::appName() }}" class="h-7 w-auto">
        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">Gate scanner</span>
    </header>
    @yield('content')
</body>
</html>
