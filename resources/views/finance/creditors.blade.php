{{-- resources/views/finance/creditors.blade.php — aged payables --}}
@extends('layouts.app')

@section('title', 'Creditors')

@section('content')
<main>
    <div x-data="{ pageName: 'Creditors' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Creditors</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Who we owe on approved bills, and how long past the due date. Pay the right-hand columns first.</p>
        </div>

        <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            @foreach ($buckets + ['total' => 'Total owed'] as $key => $text)
                <div class="rounded-2xl border p-4 shadow-theme-xs {{ in_array($key, ['61_90', '90_plus']) && $totals[$key] > 0 ? 'border-red-200 bg-red-50 dark:border-red-500/30 dark:bg-red-500/10' : 'border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900' }}">
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $text }}</p>
                    <p class="mt-1 text-lg font-bold {{ $key === 'total' ? 'text-brand-600' : 'text-gray-900 dark:text-white' }}">KES {{ number_format($totals[$key]) }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3 text-left">Creditor</th>
                            @foreach ($buckets as $text)<th class="px-5 py-3 text-right">{{ $text }}</th>@endforeach
                            <th class="px-5 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($rows as $row)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3">
                                    <a href="{{ route('finance.bills.index', ['status' => 'open', 'payee' => $row['payee']->id]) }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-white">{{ $row['payee']->name }}</a>
                                    <span class="block text-xs text-gray-500">{{ $row['bills'] }} {{ \Illuminate\Support\Str::plural('bill', $row['bills']) }} · {{ $row['payee']->payTo() }}</span>
                                </td>
                                @foreach (array_keys($buckets) as $key)
                                    <td class="px-5 py-3 text-right {{ $row[$key] > 0 && in_array($key, ['61_90', '90_plus']) ? 'font-semibold text-red-600' : '' }}">{{ $row[$key] > 0 ? number_format($row[$key]) : '—' }}</td>
                                @endforeach
                                <td class="px-5 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ number_format($row['total']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-gray-500">We don't owe anyone right now. 🎉</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection
