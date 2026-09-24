<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>M-Pesa Callbacks</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">M-Pesa Callbacks</h1>
            <a href="{{ route('admin.mpesa-callbacks.index') }}"
               class="text-sm text-purple-600 hover:underline">Refresh</a>
        </div>

        {{-- Filters --}}
        <form method="GET" class="bg-white rounded-lg shadow-sm border p-4 mb-6 flex flex-wrap gap-3">
            <select name="status" class="border rounded-lg px-3 py-2 text-sm">
                <option value="">All ({{ $counts['all'] }})</option>
                <option value="received"  @selected(request('status') === 'received')>
                    Received ({{ $counts['received'] }})
                </option>
                <option value="processed" @selected(request('status') === 'processed')>
                    Processed ({{ $counts['processed'] }})
                </option>
                <option value="failed"    @selected(request('status') === 'failed')>
                    Failed ({{ $counts['failed'] }})
                </option>
            </select>

            <input type="text" name="phone" value="{{ request('phone') }}"
                   placeholder="Phone (254...)"
                   class="border rounded-lg px-3 py-2 text-sm">

            <input type="text" name="q" value="{{ request('q') }}"
                   placeholder="Receipt / Checkout ID"
                   class="border rounded-lg px-3 py-2 text-sm">

            <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg text-sm">Filter</button>

            @if(request()->anyFilled(['status','phone','q']))
                <a href="{{ route('admin.mpesa-callbacks.index') }}"
                   class="text-sm text-gray-600 hover:text-gray-900 self-center">Clear</a>
            @endif
        </form>

        {{-- Table --}}
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Received</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Result</th>
                        <th class="px-4 py-3">Receipt</th>
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($callbacks as $cb)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs">#{{ $cb->id }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $cb->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $cb->phone_number ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($cb->amount !== null)
                                    KES {{ number_format($cb->amount, 2) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs
                                    @if($cb->result_code === '0') text-green-600
                                    @elseif($cb->result_code) text-red-600
                                    @else text-gray-400 @endif">
                                    {{ $cb->result_code ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">
                                {{ $cb->mpesa_receipt_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($cb->order)
                                    <a href="#" class="text-purple-600 hover:underline">
                                        {{ $cb->order->order_no }}
                                    </a>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full
                                    @if($cb->processing_status === 'processed') bg-green-100 text-green-700
                                    @elseif($cb->processing_status === 'failed') bg-red-100 text-red-700
                                    @else bg-yellow-100 text-yellow-700 @endif">
                                    {{ $cb->processing_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.mpesa-callbacks.show', $cb) }}"
                                   class="text-purple-600 hover:underline text-xs">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                No callbacks yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $callbacks->links() }}</div>
    </div>
</body>
</html>