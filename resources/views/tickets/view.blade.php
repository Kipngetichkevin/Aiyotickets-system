@php($appName = \App\Helpers\SystemHelper::appName())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Your tickets · {{ $order->order_no }} | {{ $appName }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 font-outfit text-gray-800">
    <main class="mx-auto max-w-md px-4 py-8">
        <div class="mb-6 text-center">
            <p class="text-sm font-semibold text-brand-600">{{ $appName }}</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Your tickets</h1>
            <p class="mt-1 text-sm text-gray-500">Order {{ $order->order_no }} · show a QR code at the entrance</p>
        </div>

        <div class="space-y-4">
            @forelse ($items as $item)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-theme-xs">
                    <p class="text-lg font-bold text-gray-900">{{ $item['title'] }}</p>
                    @if ($item['subtitle'])<p class="text-sm font-medium text-brand-600">{{ $item['subtitle'] }}</p>@endif
                    <p class="mt-1 text-xs text-gray-500">{{ collect([$item['when'], $item['where']])->filter()->implode(' · ') }}</p>

                    <img src="{{ $item['qr'] }}" alt="{{ $item['code'] }}" class="mx-auto my-4 h-56 w-56">

                    <p class="font-mono text-sm font-semibold text-gray-800">{{ $item['code'] }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ $item['name'] }}</p>
                    @if ($item['checked'])
                        <span class="mt-3 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Already checked in</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-sm text-gray-500">No tickets found for this order.</p>
            @endforelse
        </div>

        <p class="mt-8 text-center text-xs text-gray-400">Keep this link private: anyone with it can use these tickets.</p>
    </main>
</body>
</html>
