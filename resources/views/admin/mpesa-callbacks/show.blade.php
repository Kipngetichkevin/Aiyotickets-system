<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Callback #{{ $callback->id }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">

        <a href="{{ route('admin.mpesa-callbacks.index') }}"
           class="text-sm text-purple-600 hover:underline">← Back to list</a>

        <h1 class="text-2xl font-bold my-4">Callback #{{ $callback->id }}</h1>

        {{-- Summary --}}
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Received</dt>
                    <dd class="font-medium">{{ $callback->created_at->format('d M Y H:i:s') }}</dd></div>
                <div><dt class="text-gray-500">Status</dt>
                    <dd class="font-medium">{{ $callback->processing_status }}</dd></div>
                <div><dt class="text-gray-500">Result Code</dt>
                    <dd class="font-medium">{{ $callback->result_code ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Result Desc</dt>
                    <dd class="font-medium">{{ $callback->result_desc ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Receipt</dt>
                    <dd class="font-mono">{{ $callback->mpesa_receipt_number ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Amount</dt>
                    <dd class="font-medium">{{ $callback->amount !== null ? 'KES ' . number_format($callback->amount, 2) : '—' }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt>
                    <dd class="font-mono">{{ $callback->phone_number ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">IP</dt>
                    <dd class="font-mono">{{ $callback->ip_address ?? '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-gray-500">CheckoutRequestID</dt>
                    <dd class="font-mono text-xs">{{ $callback->checkout_request_id ?? '—' }}</dd></div>
                <div class="col-span-2"><dt class="text-gray-500">MerchantRequestID</dt>
                    <dd class="font-mono text-xs">{{ $callback->merchant_request_id ?? '—' }}</dd></div>
                @if($callback->mpesa_stk_id)
                    <div><dt class="text-gray-500">MpesaStk</dt>
                        <dd class="font-mono">#{{ $callback->mpesa_stk_id }}</dd></div>
                @endif
                @if($callback->order_id)
                    <div><dt class="text-gray-500">Order</dt>
                        <dd class="font-mono">#{{ $callback->order_id }}</dd></div>
                @endif
            </dl>

            @if($callback->processing_error)
                <div class="mt-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">
                    <strong>Error:</strong> {{ $callback->processing_error }}
                </div>
            @endif
        </div>

        {{-- Raw payload --}}
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <h2 class="font-semibold mb-3">Raw Payload</h2>
            <pre class="bg-gray-900 text-green-300 p-4 rounded-lg text-xs overflow-auto max-h-96">{{ json_encode($callback->raw_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>

        {{-- Headers --}}
        @if($callback->headers)
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h2 class="font-semibold mb-3">Headers</h2>
                <pre class="bg-gray-900 text-gray-300 p-4 rounded-lg text-xs overflow-auto max-h-96">{{ json_encode($callback->headers, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>
        @endif
    </div>
</body>
</html>