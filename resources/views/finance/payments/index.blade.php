{{-- resources/views/finance/payments/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Payments made')

@php($field = 'h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90')

@section('content')
<main>
    <div x-data="{ pageName: 'Payments made' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Payments made</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Every payment out: bills, salaries, advances and other expenses.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('finance.payments.create', ['kind' => 'salary']) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Pay salary</a>
                <a href="{{ route('finance.payments.create', ['kind' => 'other']) }}" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Record expense</a>
            </div>
        </div>

        @include('finance._flash')

        <form method="GET" class="mb-4 flex flex-wrap items-end gap-3">
            <select name="kind" class="{{ $field }}" aria-label="Type">
                <option value="">All types</option>
                @foreach (\App\Models\FinancePayment::KINDS as $key => $text)<option value="{{ $key }}" @selected(($filters['kind'] ?? '') === $key)>{{ $text }}</option>@endforeach
            </select>
            <select name="payee" class="{{ $field }}" aria-label="Paid to">
                <option value="">Everyone</option>
                @foreach ($payees as $p)<option value="{{ $p->id }}" @selected((int) ($filters['payee'] ?? 0) === $p->id)>{{ $p->name }}</option>@endforeach
            </select>
            <select name="category" class="{{ $field }}" aria-label="Category">
                <option value="">All categories</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((int) ($filters['category'] ?? 0) === $c->id)>{{ $c->name }}</option>@endforeach
            </select>
            <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="{{ $field }}" aria-label="From">
            <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="{{ $field }}" aria-label="To">
            <button class="h-10 rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white">Filter</button>
            <button name="export" value="csv" class="h-10 rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Export CSV</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Paid to</th>
                            <th class="px-5 py-3">For</th>
                            <th class="px-5 py-3">How</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($payments as $p)
                            <tr class="align-top text-gray-700 dark:text-gray-300">
                                <td class="whitespace-nowrap px-5 py-3 text-xs">{{ $p->paid_on->format('d M Y') }}</td>
                                <td class="px-5 py-3"><a href="{{ route('finance.payees.show', $p->payee) }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-white">{{ $p->payee->name }}</a></td>
                                <td class="px-5 py-3">
                                    {{ \App\Models\FinancePayment::KINDS[$p->kind] }}@if ($p->period) · {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $p->period)->format('M Y') }}@endif
                                    <span class="block text-xs text-gray-500">{{ $p->category->name }}@if ($p->bill) · <a href="{{ route('finance.bills.show', $p->bill) }}" class="hover:text-brand-600">{{ $p->bill->description }}</a>@endif</span>
                                    @if ($p->deductions)
                                        <span class="block text-xs text-gray-500">Gross {{ number_format((float) $p->gross) }} − {{ collect($p->deductions)->map(fn ($v, $k) => "{$k} " . number_format($v))->implode(', ') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs">{{ \App\Models\Payee::METHODS[$p->method] }}<span class="block font-mono">{{ $p->reference }}</span></td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold">KES {{ number_format((float) $p->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No payments match.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($payments->total())
                        <tfoot class="bg-gray-50 text-sm font-semibold dark:bg-gray-800/50">
                            <tr><td colspan="4" class="px-5 py-3 text-gray-600 dark:text-gray-300">Total ({{ $payments->total() }} payments)</td><td class="px-5 py-3 text-right text-gray-900 dark:text-white">KES {{ number_format($total, 2) }}</td></tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $payments->links() }}</div>
    </div>
</main>
@endsection
