{{-- resources/views/finance/overview.blade.php --}}
@extends('layouts.app')

@section('title', 'Finance')

@php($kes = fn ($n) => 'KES ' . number_format((float) $n))

@section('content')
<main>
    <div x-data="{ pageName: 'Finance' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Finance overview</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">What came in, what we earned, what we owe and what we spent.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($periods as $key => $text)
                    <a href="{{ route('finance.overview', ['period' => $key]) }}"
                       class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $period === $key ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200' }}">{{ $text }}</a>
                @endforeach
            </div>
        </div>

        {{-- Income statement for the period --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['Collected by M-Pesa', $figures['collected'], 'All ticket money received, incl. deposits and instalments', 'text-gray-900 dark:text-white'],
                ['Platform fees earned', $figures['fee'], 'Our ' . rtrim(rtrim(number_format(config('aiyo.platform_fee_percent'), 2), '0'), '.') . '% of KES ' . number_format($figures['gross']) . ' paid sales', 'text-brand-600'],
                ['Expenses paid', $figures['spent'], 'Bills, salaries and other payments', 'text-red-600'],
                ['Profit', $figures['profit'], 'Fees earned less expenses', $figures['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600'],
            ] as [$title, $value, $hint, $colour])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</p>
                    <p class="mt-2 text-2xl font-bold {{ $colour }}">{{ $kes($value) }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>
                </div>
            @endforeach
        </div>

        {{-- What we owe right now --}}
        <h2 class="mb-3 text-sm font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Owed right now</h2>
        <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['To organisers', $balances['owed_organisers'], 'Their sales after our fee, not yet paid out', route('admin.payouts.index'), $balances['payout_requests'] > 0 ? $kes($balances['payout_requests']) . ' requested' : null],
                ['Refunds to buyers', $balances['refunds_due'], 'Lapsed instalments, overpayments, sold out', route('admin.payouts.index'), null],
                ['Suppliers & creditors', $balances['bills_owed'], 'Approved bills not yet paid', route('finance.creditors'), $balances['bills_overdue'] ? $balances['bills_overdue'] . ' overdue' : null],
                ['Bills to approve', null, 'Check and approve before paying', route('finance.bills.index', ['status' => 'awaiting']), null, $balances['bills_awaiting']],
            ] as $card)
                @php([$title, $value, $hint, $url, $flag] = $card)
                <a href="{{ $url }}" class="group rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs transition hover:border-brand-300 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $title }}</p>
                        @if ($flag)<span class="rounded-full bg-red-50 px-2 py-0.5 text-[11px] font-semibold text-red-600 dark:bg-red-500/10">{{ $flag }}</span>@endif
                    </div>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $value !== null ? $kes($value) : ($card[5] ?? 0) }}</p>
                    <p class="mt-1 text-xs text-gray-400 group-hover:text-brand-600">{{ $hint }} →</p>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- Spending by category --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 font-semibold text-gray-900 dark:text-white">Spending by category</h3>
                @php($max = max(1, (float) $byCategory->max('total')))
                <div class="space-y-3">
                    @forelse ($byCategory as $row)
                        <div>
                            <div class="flex justify-between text-sm"><span class="text-gray-600 dark:text-gray-300">{{ $row->name }}</span><span class="font-semibold text-gray-900 dark:text-white">{{ $kes($row->total) }}</span></div>
                            <div class="mt-1 h-2 rounded-full bg-gray-100 dark:bg-gray-800"><div class="h-2 rounded-full bg-brand-500" style="width: {{ round($row->total / $max * 100) }}%"></div></div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No spending recorded in this period.</p>
                    @endforelse
                </div>
            </div>

            {{-- Bills due soon --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Bills due in 14 days</h3>
                    <a href="{{ route('finance.bills.create') }}" class="text-xs font-semibold text-brand-600 hover:underline">+ New bill</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($dueSoon as $bill)
                        <a href="{{ route('finance.bills.show', $bill) }}" class="flex items-center justify-between gap-3 py-2.5 text-sm hover:text-brand-600">
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-gray-900 dark:text-white">{{ $bill->payee->name }}</span>
                                <span class="block text-xs {{ $bill->isOverdue() ? 'text-red-600' : 'text-gray-500' }}">{{ $bill->isOverdue() ? 'Overdue since' : 'Due' }} {{ $bill->due_date->format('d M') }}</span>
                            </span>
                            <span class="shrink-0 font-semibold text-gray-900 dark:text-white">{{ $kes($bill->balance()) }}</span>
                        </a>
                    @empty
                        <p class="py-2 text-sm text-gray-500">Nothing due soon.</p>
                    @endforelse
                </div>
            </div>

            {{-- Recent payments --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Recent payments</h3>
                    <a href="{{ route('finance.payments.create') }}" class="text-xs font-semibold text-brand-600 hover:underline">+ Record payment</a>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($recent as $p)
                        <div class="flex items-center justify-between gap-3 py-2.5 text-sm">
                            <span class="min-w-0">
                                <span class="block truncate font-medium text-gray-900 dark:text-white">{{ $p->payee->name }}</span>
                                <span class="block text-xs text-gray-500">{{ $p->paid_on->format('d M') }} · {{ $p->category->name }}</span>
                            </span>
                            <span class="shrink-0 font-semibold text-gray-900 dark:text-white">{{ $kes($p->amount) }}</span>
                        </div>
                    @empty
                        <p class="py-2 text-sm text-gray-500">No payments recorded yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        @if ($balances['instalments_owed'] > 0)
            <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">
                Buyers still owe KES {{ number_format($balances['instalments_owed']) }} on lipa mdogo mdogo plans. It isn't counted above until it's paid.
            </p>
        @endif
    </div>
</main>
@endsection
