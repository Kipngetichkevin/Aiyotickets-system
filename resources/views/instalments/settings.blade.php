{{-- resources/views/instalments/settings.blade.php --}}
@extends('layouts.app')

@section('title', 'Lipa mdogo mdogo & refunds')

@section('content')
<main>
    <div x-data="{ pageName: 'Lipa mdogo mdogo & refunds' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Lipa mdogo mdogo &amp; refunds</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Let buyers pay a deposit now and the rest in instalments before a due date. Their place is held;
                the QR ticket is issued once fully paid. If the balance isn't paid in time, the booking is cancelled,
                the deposit is kept and any later instalments are refunded.
            </p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                <strong>Refund policy:</strong> shown to buyers on your page and their tickets. Within it, buyers cancel themselves from their ticket page.
                Whatever you choose, buyers are refunded if you cancel, and may ask for a refund for {{ \App\Support\RefundPolicy::POSTPONED_DAYS }} days if you postpone.
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-400">{{ $errors->first() }}</div>
        @endif

        {{-- SETTINGS --}}
        <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Your upcoming events and experiences</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Starts</th>
                            <th class="px-5 py-3">Instalments</th>
                            <th class="px-5 py-3">Deposit %</th>
                            <th class="px-5 py-3">Balance due (days before)</th>
                            <th class="px-5 py-3">Refund policy</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @php($rows = $events->map(fn ($e) => ['event', $e, $e->date])->concat($experiences->map(fn ($x) => ['experience', $x, $x->start_date])))
                        @forelse ($rows as [$type, $item, $start])
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ $item->name }}</span>
                                    <span class="block text-xs text-gray-500 capitalize">{{ $type }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $start ? $start->format('d M Y') : '—' }}</td>
                                <td class="px-5 py-3" colspan="5">
                                    <form method="POST" action="{{ route('instalments.update', [$type, $item->id]) }}" class="flex flex-wrap items-center gap-3">
                                        @csrf
                                        <label class="inline-flex items-center gap-2 text-sm">
                                            <input type="checkbox" name="allow_instalments" value="1" @checked($item->allow_instalments)
                                                   class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                            On
                                        </label>
                                        <input type="number" name="instalment_deposit_percent" min="10" max="90" value="{{ $item->instalment_deposit_percent }}"
                                               class="h-9 w-20 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90" title="Deposit %">
                                        <input type="number" name="instalment_due_days" min="1" max="90" value="{{ $item->instalment_due_days }}"
                                               class="h-9 w-20 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:text-white/90" title="Days before start">
                                        <select name="refund_policy" class="h-9 rounded-lg border border-gray-300 bg-transparent px-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" title="Refund policy">
                                            @foreach (\App\Support\RefundPolicy::OPTIONS as $key => $label)
                                                <option value="{{ $key }}" @selected(($item->refund_policy ?? 'none') === $key)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button class="rounded-lg bg-brand-500 px-3 py-2 text-xs font-semibold text-white hover:bg-brand-600">Save</button>
                                        @if ($item->allow_instalments && $start)
                                            <span class="text-xs text-gray-500">
                                                Balance due {{ $start->copy()->subDays($item->instalment_due_days)->format('d M Y') }}
                                            </span>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No upcoming events or experiences.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ORDERS --}}
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-800 dark:text-white/90">Instalment orders</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3">Buyer</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Paid</th>
                            <th class="px-5 py-3 text-right">Balance</th>
                            <th class="px-5 py-3">Due</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($orders as $o)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 font-mono text-xs">{{ $o->order_no }}</td>
                                <td class="px-5 py-3">{{ $o->initiator }}<span class="block text-xs text-gray-500">{{ $o->email }}</span></td>
                                <td class="px-5 py-3 text-right">{{ number_format($o->total_amount) }}</td>
                                <td class="px-5 py-3 text-right">{{ number_format($o->amount_paid) }}</td>
                                <td class="px-5 py-3 text-right font-semibold">{{ number_format($o->balance_due) }}</td>
                                <td class="px-5 py-3">{{ $o->due_date?->format('d M Y') }}</td>
                                <td class="px-5 py-3">@include('instalments._status', ['status' => $o->status])</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No instalment orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
