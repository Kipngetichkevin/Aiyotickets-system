{{-- resources/views/finance/bills/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Bills')

@section('content')
<main>
    <div x-data="{ pageName: 'Bills' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Bills</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">What suppliers and contractors have invoiced us. Enter → approve → pay. Total to pay: <strong class="text-gray-900 dark:text-white">KES {{ number_format($owed) }}</strong></p>
            </div>
            <a href="{{ route('finance.bills.create') }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">+ New bill</a>
        </div>

        @include('finance._flash')

        <div class="mb-4 flex flex-wrap gap-2">
            @foreach (\App\Http\Controllers\Admin\Finance\BillController::FILTERS as $key => $text)
                <a href="{{ route('finance.bills.index', ['status' => $key]) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $filter === $key ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200' }}">
                    {{ $text }}@isset($counts[$key]) ({{ $counts[$key] }})@endisset
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Supplier</th>
                            <th class="px-5 py-3">For</th>
                            <th class="px-5 py-3">Bill date</th>
                            <th class="px-5 py-3">Due</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3 text-right">Left to pay</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($bills as $bill)
                            <tr class="cursor-pointer text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.02]" onclick="location.href='{{ route('finance.bills.show', $bill) }}'">
                                <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $bill->payee->name }}</td>
                                <td class="px-5 py-3">{{ $bill->description }}<span class="block text-xs text-gray-500">{{ $bill->category->name }}{{ $bill->reference ? ' · ' . $bill->reference : '' }}</span></td>
                                <td class="whitespace-nowrap px-5 py-3 text-xs">{{ $bill->bill_date->format('d M Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-xs {{ $bill->isOverdue() ? 'font-semibold text-red-600' : '' }}">{{ $bill->due_date->format('d M Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right">KES {{ number_format((float) $bill->amount) }}</td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold">{{ $bill->status === 'void' ? '—' : 'KES ' . number_format($bill->balance()) }}</td>
                                <td class="px-5 py-3">@include('finance._bill-status')</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">No bills here.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $bills->links() }}</div>
    </div>
</main>
@endsection
