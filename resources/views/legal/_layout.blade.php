{{-- Shared shell for the legal pages. Sections: title, updated, body. --}}
@php
    $appName = \App\Helpers\SystemHelper::appName();
    $contact = \App\Helpers\SystemHelper::contactEmail() ?: 'support@aiyotickets.com';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ $appName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-outfit text-gray-700 antialiased">
@include('includes.header')

<main class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
    <p class="text-sm font-semibold text-brand-600">{{ $appName }}</p>
    <h1 class="mt-1 text-3xl font-bold text-gray-900 sm:text-4xl">@yield('title')</h1>
    <p class="mt-2 text-sm text-gray-500">Last updated @yield('updated')</p>

    <nav class="mt-6 flex flex-wrap gap-2 text-sm">
        @foreach (['terms' => 'Terms', 'privacy' => 'Privacy', 'refund-policy' => 'Refunds'] as $route => $label)
            <a href="{{ route($route) }}" class="rounded-full border px-3 py-1 {{ request()->routeIs($route) ? 'border-brand-500 bg-brand-50 font-semibold text-brand-700' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-100' }}">{{ $label }}</a>
        @endforeach
    </nav>

    <article class="legal mt-8 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 leading-relaxed shadow-theme-xs sm:p-8">
        @yield('body')
    </article>
</main>

<style>
    .legal h2 { margin-top: 2rem; font-size: 1.25rem; font-weight: 700; color: #111827; }
    .legal h2:first-child { margin-top: 0; }
    .legal ul { list-style: disc; padding-left: 1.25rem; }
    .legal li + li { margin-top: .375rem; }
    .legal a { font-weight: 600; color: var(--primary-color, #465fff); }
    .legal a:hover { text-decoration: underline; }
</style>

@include('includes.footer')
</body>
</html>
