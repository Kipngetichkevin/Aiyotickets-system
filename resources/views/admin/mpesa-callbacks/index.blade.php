@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl">

    {{-- ============ PAGE HEADER ============ --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">
                M-Pesa Callbacks
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Monitor incoming STK push callbacks and their processing status.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.mpesa-callbacks.export-pdf', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-200 transition hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-800 dark:hover:bg-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                     class="h-4 w-4">
                    <path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Export PDF
            </a>

            <a href="{{ route('admin.mpesa-callbacks.index') }}"
               class="inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-theme-xs ring-1 ring-gray-200 transition hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-300 dark:ring-gray-800 dark:hover:bg-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                     class="h-4 w-4">
                    <path d="M16.023 9.348h4.992V4.356M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M21.015 4.356l-3.181-3.183a8.25 8.25 0 00-13.803 3.7"/>
                </svg>
                Refresh
            </a>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <form method="GET"
          class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="flex flex-wrap items-end gap-3">

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</label>
                <select name="status"
                        class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
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
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Phone</label>
                <input type="text" name="phone" value="{{ request('phone') }}"
                       placeholder="254712345678"
                       class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>

            <div class="flex flex-col gap-1 grow min-w-[220px]">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Receipt / Checkout ID</label>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="e.g. UIO337K302 or ws_CO_25092026..."
                       class="h-10 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-700 placeholder:text-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>

            <button type="submit"
                    class="h-10 rounded-lg px-5 text-sm font-medium text-white transition hover:opacity-90"
                    style="background-color: var(--primary-color, #465FFF);">
                Filter
            </button>

            @if(request()->anyFilled(['status','phone','q']))
                <a href="{{ route('admin.mpesa-callbacks.index') }}"
                   class="h-10 inline-flex items-center px-2 text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                    Clear
                </a>
            @endif
        </div>
    </form>

    {{-- ============ SUMMARY CARDS ============ --}}
    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @php
            $statCards = [
                [
                    'label' => 'All Callbacks',
                    'value' => $counts['all'],
                    'color' => 'text-gray-800 dark:text-white/90',
                    'accent'=> 'bg-gray-300 dark:bg-gray-600',
                    'iconBg'=> 'bg-gray-100 dark:bg-white/10',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75"/>',
                ],
                [
                    'label' => 'Received',
                    'value' => $counts['received'],
                    'color' => 'text-yellow-700 dark:text-yellow-400',
                    'accent'=> 'bg-yellow-400 dark:bg-yellow-500',
                    'iconBg'=> 'bg-yellow-50 dark:bg-yellow-500/15',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
                [
                    'label' => 'Processed',
                    'value' => $counts['processed'],
                    'color' => 'text-green-700 dark:text-green-400',
                    'accent'=> 'bg-green-500 dark:bg-green-500',
                    'iconBg'=> 'bg-green-50 dark:bg-green-500/15',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
                [
                    'label' => 'Failed',
                    'value' => $counts['failed'],
                    'color' => 'text-red-700 dark:text-red-400',
                    'accent'=> 'bg-red-500 dark:bg-red-500',
                    'iconBg'=> 'bg-red-50 dark:bg-red-500/15',
                    'icon'  => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
            ];
        @endphp

        @foreach($statCards as $card)
            <div class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs transition duration-200 hover:-translate-y-0.5 hover:shadow-theme-md dark:border-gray-800 dark:bg-gray-900">
                {{-- Left accent bar --}}
                <span class="absolute inset-y-0 left-0 w-1 {{ $card['accent'] }}"></span>

                <div class="flex items-center justify-between gap-3 pl-2">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            {{ $card['label'] }}
                        </p>
                        <p class="mt-1.5 text-2xl font-bold leading-none {{ $card['color'] }}">
                            {{ $card['value'] }}
                        </p>
                    </div>

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $card['iconBg'] }} {{ $card['color'] }}">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                             stroke="currentColor" stroke-width="1.75" class="h-5 w-5">
                            {!! $card['icon'] !!}
                        </svg>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ============ TABLE ============ --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-white/5 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">#</th>
                        <th class="px-4 py-3 font-medium">Received</th>
                        <th class="px-4 py-3 font-medium">Phone</th>
                        <th class="px-4 py-3 font-medium">Amount</th>
                        <th class="px-4 py-3 font-medium">Result</th>
                        <th class="px-4 py-3 font-medium">Receipt</th>
                        <th class="px-4 py-3 font-medium">Order</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($callbacks as $cb)
                        <tr class="transition hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $cb->id }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                {{ $cb->created_at->format('d M Y H:i:s') }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $cb->phone_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                @if($cb->amount !== null)
                                    <span class="font-medium">KES {{ number_format($cb->amount, 2) }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs
                                    @if($cb->result_code === '0') text-green-600 dark:text-green-400
                                    @elseif($cb->result_code) text-red-600 dark:text-red-400
                                    @else text-gray-400 @endif">
                                    {{ $cb->result_code ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300">
                                {{ $cb->mpesa_receipt_number ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                @if($cb->order)
                                    <a href="#" class="font-medium hover:underline"
                                       style="color: var(--primary-color, #465FFF);">
                                        {{ $cb->order->order_no }}
                                    </a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $statusClasses = match($cb->processing_status) {
                                        'processed' => 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
                                        'failed'    => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                        default     => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                    {{ ucfirst($cb->processing_status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.mpesa-callbacks.show', $cb) }}"
                                   class="text-xs font-medium hover:underline"
                                   style="color: var(--primary-color, #465FFF);">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                     stroke="currentColor" stroke-width="1.5" class="mx-auto mb-3 h-10 w-10 text-gray-300 dark:text-gray-600">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75"/>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400">No callbacks found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($callbacks->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">
                {{ $callbacks->links() }}
            </div>
        @endif
    </div>

</div>
@endsection