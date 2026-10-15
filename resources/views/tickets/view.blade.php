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

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
        @endif

        <div class="space-y-4">
            @forelse ($items as $item)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 text-center shadow-theme-xs">
                    <p class="text-lg font-bold text-gray-900">{{ $item['title'] }}</p>
                    @if ($item['subtitle'])<p class="text-sm font-medium text-brand-600">{{ $item['subtitle'] }}</p>@endif
                    <p class="mt-1 text-xs text-gray-500">{{ collect([$item['when'], $item['where']])->filter()->implode(' · ') }}</p>

                    @if ($item['cancelled'])
                        <p class="my-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">Cancelled — refund on its way to your M-Pesa</p>
                    @else
                        <img src="{{ $item['qr'] }}" alt="{{ $item['code'] }}" class="mx-auto my-4 h-56 w-56">
                        <p class="font-mono text-sm font-semibold text-gray-800">{{ $item['code'] }}</p>
                    @endif
                    <p class="mt-1 text-xs text-gray-500">{{ $item['name'] }}</p>
                    @if ($item['checked'])
                        <span class="mt-3 inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600">Already checked in</span>
                    @endif
                </div>
            @empty
                <p class="text-center text-sm text-gray-500">No tickets found for this order.</p>
            @endforelse
        </div>

        @if ($items->pluck('policy')->filter()->unique()->count() === 1)
            <p class="mt-6 text-center text-xs text-gray-500">{{ $items->pluck('policy')->filter()->first() }} <a href="{{ route('refund-policy') }}" class="underline">Refund policy</a></p>
        @endif

        @if ($refundUrl)
            <form method="POST" action="{{ $refundUrl }}" class="mt-4 text-center"
                  onsubmit="return confirm('Cancel {{ $refundAmount > 0 ? 'and get back KES ' . number_format($refundAmount) : 'these tickets' }}? The tickets will stop working.')">
                @csrf
                <button class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Request a refund (KES {{ number_format($refundAmount) }})</button>
            </form>
        @endif

        <p class="mt-8 text-center text-xs text-gray-400">Keep this link private: anyone with it can use these tickets.</p>
    </main>
</body>
</html>
