{{-- resources/views/finance/payees/show.blade.php — one supplier / staff member: details, bills, payments --}}
@extends('layouts.app')

@section('title', $payee->name)

@section('content')
<main>
    <div x-data="{ pageName: 'Suppliers & staff' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-xl md:p-6">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div>
                <a href="{{ route('finance.payees.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">← Suppliers & staff</a>
                <h1 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $payee->name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ \App\Models\Payee::TYPES[$payee->type] }}@if ($payee->job_title) · {{ $payee->job_title }}@endif
                    @unless ($payee->is_active) · <strong>Inactive</strong>@endunless
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($payee->type === 'staff')
                    <a href="{{ route('finance.payments.create', ['kind' => 'salary', 'payee' => $payee->id]) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Pay salary</a>
                @else
                    <a href="{{ route('finance.bills.create', ['payee' => $payee->id]) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">+ New bill</a>
                @endif
                <a href="{{ route('finance.payments.create', ['kind' => 'other', 'payee' => $payee->id]) }}" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Record payment</a>
                <a href="{{ route('finance.payees.edit', $payee) }}" class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Edit</a>
            </div>
        </div>

        @include('finance._flash')

        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">We owe</p>
                <p class="mt-1 text-2xl font-bold {{ $owed > 0 ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">KES {{ number_format($owed) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500">Paid to date</p>
                <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">KES {{ number_format((float) $payments->sum('amount')) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 text-sm shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <p class="text-gray-500">Pay to</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">{{ $payee->payTo() }}</p>
                @if ($payee->bank_account_name)<p class="text-xs text-gray-500">{{ $payee->bank_account_name }}</p>@endif
                <p class="mt-2 text-xs text-gray-500">{{ collect([$payee->phone, $payee->email, $payee->kra_pin ? 'KRA ' . $payee->kra_pin : null])->filter()->implode(' · ') }}</p>
            </div>
        </div>

        @if ($payee->type !== 'staff' || $bills->isNotEmpty())
            <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">Bills</h2>
            <div class="mb-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($bills as $bill)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3"><a href="{{ route('finance.bills.show', $bill) }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-white">{{ $bill->description }}</a>
                                    <span class="block text-xs text-gray-500">{{ $bill->reference ? $bill->reference . ' · ' : '' }}{{ $bill->bill_date->format('d M Y') }}</span></td>
                                <td class="px-5 py-3 text-xs">Due {{ $bill->due_date->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-right font-semibold">KES {{ number_format((float) $bill->amount) }}</td>
                                <td class="px-5 py-3 text-right">@include('finance._bill-status')</td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-6 text-center text-gray-500">No bills yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">Payments</h2>
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($payments as $p)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-3 text-xs">{{ $p->paid_on->format('d M Y') }}</td>
                            <td class="px-5 py-3">{{ \App\Models\FinancePayment::KINDS[$p->kind] }}@if ($p->period) · {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $p->period)->format('F Y') }}@endif
                                <span class="block text-xs text-gray-500">{{ $p->category->name }}{{ $p->bill ? ' · ' . $p->bill->description : '' }}</span></td>
                            <td class="px-5 py-3 text-xs">{{ \App\Models\Payee::METHODS[$p->method] }} {{ $p->reference }}</td>
                            <td class="px-5 py-3 text-right font-semibold">KES {{ number_format((float) $p->amount, 2) }}
                                @if ($p->gross)<span class="block text-xs font-normal text-gray-500">gross {{ number_format((float) $p->gross) }}</span>@endif</td>
                        </tr>
                    @empty
                        <tr><td class="px-5 py-6 text-center text-gray-500">No payments yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($payee->notes)
            <p class="mt-6 whitespace-pre-line rounded-xl bg-gray-50 p-4 text-sm text-gray-600 dark:bg-gray-800/50 dark:text-gray-300">{{ $payee->notes }}</p>
        @endif
    </div>
</main>
@endsection
