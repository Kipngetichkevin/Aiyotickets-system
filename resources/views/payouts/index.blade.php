{{-- resources/views/payouts/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Payouts')

@section('content')
<main>
    <div x-data="{ pageName: 'Payouts' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-xl md:p-6">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Payouts</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Your earnings after the {{ $feePercent }}% platform fee.
                @if ($afterEnd) Sales become withdrawable once the event or experience has ended. @endif
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
        @endif

        {{-- BALANCE --}}
        <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Withdrawable', $balance['withdrawable'], 'Available to request now', 'text-brand-600'],
                ['Pending', $balance['pending'], 'Events not ended yet', 'text-gray-900 dark:text-white'],
                ['In progress', $balance['in_progress'], 'Requested, not paid yet', 'text-amber-600'],
                ['Paid out', $balance['paid_out'], 'Total received', 'text-green-600'],
            ] as [$label, $value, $hint, $color])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold {{ $color }}">KES {{ number_format($value, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- REQUEST --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 lg:col-span-1">
                <h3 class="mb-4 text-sm font-bold text-gray-800 dark:text-white/90">Request a payout</h3>

                @if ($balance['withdrawable'] < $minimum)
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        You need at least KES {{ number_format($minimum) }} withdrawable to request a payout.
                    </p>
                @else
                    <form method="POST" action="{{ route('payouts.store') }}" class="space-y-4" x-data="{ method: '{{ old('method', 'mpesa') }}' }">
                        @csrf
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Amount (KES)</label>
                            <input type="number" name="amount" step="0.01" min="{{ $minimum }}" max="{{ $balance['withdrawable'] }}"
                                   value="{{ old('amount', floor($balance['withdrawable'])) }}" required
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90">
                            @error('amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Send to</label>
                            <select name="method" x-model="method"
                                    class="h-11 w-full rounded-lg border border-gray-300 bg-white px-4 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="mpesa">M-Pesa</option>
                                <option value="bank">Bank account</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                                   x-text="method === 'mpesa' ? 'M-Pesa number' : 'Bank, branch and account number'"></label>
                            <input type="text" name="destination" value="{{ old('destination') }}" required
                                   :placeholder="method === 'mpesa' ? '0712345678' : 'Equity Bank, Westlands, 0123456789'"
                                   class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:text-white/90">
                            @error('destination')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-600">
                            Request payout
                        </button>
                    </form>
                @endif
            </div>

            {{-- HISTORY --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Requested</th>
                                <th class="px-5 py-3 text-right">Amount</th>
                                <th class="px-5 py-3">To</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Reference / note</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($payouts as $p)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3">{{ $p->created_at->format('d M Y') }}</td>
                                    <td class="px-5 py-3 text-right font-semibold">KES {{ number_format($p->amount, 2) }}</td>
                                    <td class="px-5 py-3">{{ $p->method === 'mpesa' ? 'M-Pesa' : 'Bank' }} · {{ $p->destination }}</td>
                                    <td class="px-5 py-3">@include('payouts._status', ['status' => $p->status])</td>
                                    <td class="px-5 py-3 text-xs">{{ $p->reference ?? $p->note ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No payouts yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
