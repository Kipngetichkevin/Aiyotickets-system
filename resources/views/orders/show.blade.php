@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    {{-- Back + Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400">&larr; Back to Orders</a>
            <h1 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90 font-mono">{{ $order->order_no }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Placed {{ $order->created_at?->format('d M Y \a\t H:i') }}
            </p>
        </div>

        <div class="flex items-center gap-3">
            @php
                $statusClasses = match($order->status) {
                    'paid' => 'bg-green-100 text-green-700',
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'refunded' => 'bg-blue-100 text-blue-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    default => 'bg-gray-100 text-gray-700',
                };
            @endphp
            <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusClasses }}">
                {{ ucfirst($order->status) }}
            </span>

            {{-- Generate Invoice / View Invoice button --}}
            @if($existingInvoice)
                <a href="{{ route('finance.index', ['tab' => 'invoices']) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-green-300 bg-green-50 px-4 py-2 text-sm font-medium text-green-700 hover:bg-green-100 dark:border-green-700 dark:bg-green-900/20 dark:text-green-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Invoice {{ $existingInvoice->invoice_number }}
                </a>
            @elseif($order->attendees->count() > 0)
                <form method="POST" action="{{ route('orders.generate-invoice', $order->id) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Generate Invoice
                    </button>
                </form>
            @else
                <span class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800"
                      title="Order has no attendees yet">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Invoice N/A
                </span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- LEFT: Summary + Items + Attendees --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Summary --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">Order Summary</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Buyer</p>
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $order->initiator ?? '—' }}</p>
                        <p class="text-sm text-gray-500">{{ $order->email ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Event</p>
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ optional($order->event)->name ?? '—' }}</p>
                        <p class="text-sm text-gray-500">
                            {{ optional($order->event)->date ? \Carbon\Carbon::parse($order->event->date)->format('d M Y') : '' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Transaction Code</p>
                        <p class="font-mono text-sm text-gray-800 dark:text-white/90">{{ $order->transaction_code ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Quantity</p>
                        <p class="font-medium text-gray-800 dark:text-white/90">{{ $order->qty }} ticket(s)</p>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span>KES {{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <span>Discount</span>
                            <span>- KES {{ number_format($order->discount, 2) }}</span>
                        </div>
                    @endif
                    @if($order->tax > 0)
                        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mt-1">
                            <span>Tax</span>
                            <span>+ KES {{ number_format($order->tax, 2) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-semibold text-gray-800 dark:text-white/90 mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <span>Total</span>
                        <span>KES {{ number_format($order->total_amount + $order->tax - $order->discount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Order Items --}}
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Order Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/[0.02]">
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3">Ticket Type</th>
                                <th class="px-5 py-3 text-center">Qty</th>
                                <th class="px-5 py-3 text-right">Price</th>
                                <th class="px-5 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($order->orderItems as $item)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3">{{ optional($item->ticket)->name ?? $item->event ?? '—' }}</td>
                                    <td class="px-5 py-3 text-center">{{ $item->qty }}</td>
                                    <td class="px-5 py-3 text-right">KES {{ number_format($item->price, 2) }}</td>
                                    <td class="px-5 py-3 text-right font-medium">KES {{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-5 py-6 text-center text-gray-500">No items.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Attendees --}}
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Attendees ({{ $order->attendees->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/[0.02]">
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3">Name</th>
                                <th class="px-5 py-3">Ticket</th>
                                <th class="px-5 py-3">Phone</th>
                                <th class="px-5 py-3">QR Code</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($order->attendees as $a)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3">{{ $a->name }}</td>
                                    <td class="px-5 py-3">{{ optional($a->ticket)->name ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $a->phone ?? '—' }}</td>
                                    <td class="px-5 py-3 font-mono text-xs">{{ $a->qr_code ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-700">{{ ucfirst($a->payment_status) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-6 text-center text-gray-500">No attendees.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT: Payment --}}
        <div class="space-y-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 mb-4">Payments</h3>
                @forelse($order->payments as $p)
                    <div class="border border-gray-100 dark:border-gray-800 rounded-lg p-3 mb-3 last:mb-0">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800 dark:text-white/90">{{ $p->payment_mode ?? 'Payment' }}</span>
                            <span class="text-sm font-medium">{{ $p->currency ?? 'KES' }} {{ number_format($p->amount_paid, 2) }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 font-mono">{{ $p->transaction_id ?? '—' }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d M Y H:i') : '' }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No payment records linked to this order.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection