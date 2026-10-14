{{-- resources/views/finance/bills/show.blade.php — a bill: approve, pay (in parts), void --}}
@extends('layouts.app')

@php($small = 'h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90')

@section('title', 'Bill — ' . $bill->payee->name)

@section('content')
<main>
    <div x-data="{ pageName: 'Bills' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-xl md:p-6">
        <a href="{{ route('finance.bills.index') }}" class="text-sm font-semibold text-brand-600 hover:underline">← Bills</a>
        <div class="mb-6 mt-2 flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $bill->description }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <a href="{{ route('finance.payees.show', $bill->payee) }}" class="font-semibold hover:text-brand-600">{{ $bill->payee->name }}</a>
                    · {{ $bill->category->name }}@if ($bill->reference) · Invoice {{ $bill->reference }}@endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                @include('finance._bill-status')
                @if ($bill->status === 'awaiting_approval')
                    <a href="{{ route('finance.bills.edit', $bill) }}" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Edit</a>
                @endif
            </div>
        </div>

        @include('finance._flash')

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <div class="grid grid-cols-2 gap-4 rounded-2xl border border-gray-200 bg-white p-5 text-sm shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 sm:grid-cols-4">
                    @foreach ([
                        'Amount'      => 'KES ' . number_format((float) $bill->amount, 2),
                        'Paid'        => 'KES ' . number_format((float) $bill->amount_paid, 2),
                        'Left to pay' => $bill->status === 'void' ? '—' : 'KES ' . number_format($bill->balance(), 2),
                        'Due'         => $bill->due_date->format('d M Y'),
                    ] as $term => $value)
                        <div>
                            <p class="text-xs text-gray-500">{{ $term }}</p>
                            <p class="mt-1 font-semibold {{ $term === 'Due' && $bill->isOverdue() ? 'text-red-600' : 'text-gray-900 dark:text-white' }}">{{ $value }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="border-b border-gray-100 px-5 py-3 font-semibold text-gray-900 dark:border-gray-800 dark:text-white">Payments</h2>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($bill->payments as $p)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3 text-xs">{{ $p->paid_on->format('d M Y') }}</td>
                                    <td class="px-5 py-3">{{ \App\Models\Payee::METHODS[$p->method] }} {{ $p->reference }}<span class="block text-xs text-gray-500">by {{ $p->creator?->name ?? '—' }}{{ $p->notes ? ' · ' . $p->notes : '' }}</span></td>
                                    <td class="px-5 py-3 text-right font-semibold">KES {{ number_format((float) $p->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td class="px-5 py-6 text-center text-gray-500">No payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Bill date {{ $bill->bill_date->format('d M Y') }} · entered by {{ $bill->creator?->name ?? '—' }} {{ $bill->created_at->format('d M Y H:i') }}
                    @if ($bill->approved_at) · approved by {{ $bill->approver?->name ?? '—' }} {{ $bill->approved_at->format('d M Y H:i') }}@endif
                    @if ($bill->void_reason) · voided: {{ $bill->void_reason }}@endif
                    @if ($bill->attachment) · <a href="{{ route('finance.bills.attachment', $bill) }}" target="_blank" class="font-semibold text-brand-600 hover:underline">View invoice</a>@endif
                </div>
            </div>

            {{-- Actions --}}
            <div class="space-y-4">
                @if ($bill->status === 'awaiting_approval')
                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-500/30 dark:bg-amber-500/10">
                        <h3 class="font-semibold text-amber-900 dark:text-amber-300">Check and approve</h3>
                        <p class="mt-1 text-sm text-amber-800 dark:text-amber-400">Confirm the goods or service were received and the amount is right.</p>
                        <form method="POST" action="{{ route('finance.bills.approve', $bill) }}" class="mt-4">@csrf
                            <button class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Approve bill</button>
                        </form>
                    </div>
                @endif

                @if ($bill->status === 'approved' && $bill->balance() > 0)
                    <form method="POST" action="{{ route('finance.bills.pay', $bill) }}" x-data="{ method: '{{ old('method', $bill->payee->payment_method) }}' }"
                          class="space-y-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        @csrf
                        <h3 class="font-semibold text-gray-900 dark:text-white">Record a payment</h3>
                        <p class="text-xs text-gray-500">Send the money first ({{ $bill->payee->payTo() }}), then record it here.</p>
                        <input type="number" name="amount" step="0.01" min="1" max="{{ $bill->balance() }}" value="{{ old('amount', $bill->balance()) }}" required class="{{ $small }}" aria-label="Amount">
                        <select name="method" x-model="method" class="{{ $small }}" aria-label="Method">
                            @foreach (\App\Models\Payee::METHODS as $key => $text)<option value="{{ $key }}">{{ $text }}</option>@endforeach
                        </select>
                        <input name="reference" value="{{ old('reference') }}" :required="method !== 'cash'" placeholder="M-Pesa / bank reference" class="{{ $small }}">
                        <input type="date" name="paid_on" value="{{ old('paid_on', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $small }}" aria-label="Paid on">
                        <input name="notes" value="{{ old('notes') }}" placeholder="Note (optional)" class="{{ $small }}">
                        <button class="w-full rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Record payment</button>
                    </form>
                @endif

                @if ($bill->status !== 'void' && (float) $bill->amount_paid == 0)
                    <form method="POST" action="{{ route('finance.bills.void', $bill) }}" onsubmit="return confirm('Void this bill?')"
                          class="space-y-2 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                        @csrf
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Void bill</h3>
                        <input name="reason" required maxlength="500" placeholder="Why (e.g. duplicate, cancelled)" class="{{ $small }}">
                        <button class="w-full rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-500/30">Void</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection
