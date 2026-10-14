{{-- resources/views/finance/payments/form.blade.php — salary, advance or other expense (bills are paid from the bill) --}}
@extends('layouts.app')

@php
    $input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $payeeData = $payees->mapWithKeys(fn ($p) => [$p->id => [
        'type' => $p->type, 'pay' => (float) $p->monthly_pay, 'method' => $p->payment_method, 'category' => $p->default_category_id,
    ]]);
    $oldDeductions = old('deductions', []);
@endphp

@section('title', 'Record payment')

@section('content')
<main>
    <div x-data="{ pageName: 'Record payment' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-3xl md:p-6">
        <h1 class="mb-1 text-2xl font-semibold text-gray-800 dark:text-white/90">Record a payment</h1>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Send the money first, then record it here with its reference. Supplier invoices are paid from their bill instead.</p>
        @include('finance._flash')

        @if ($payees->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-gray-200 p-8 text-center text-gray-500 dark:border-gray-800">
                Add the person first. <a href="{{ route('finance.payees.create', ['type' => 'staff']) }}" class="font-semibold text-brand-600 hover:underline">Add staff or supplier</a>
            </div>
        @else
        <form method="POST" action="{{ route('finance.payments.store') }}"
              x-data="{
                  kind: '{{ old('kind', $kind) }}',
                  payee: '{{ old('payee_id', $payee?->id) }}',
                  people: @js($payeeData),
                  salaryCategory: '{{ $salaryCategoryId }}',
                  category: '{{ old('category_id') }}',
                  method: '{{ old('method', $payee?->payment_method ?? 'mpesa') }}',
                  gross: {{ (float) old('gross', $kind === 'salary' ? (float) $payee?->monthly_pay : 0) ?: 0 }},
                  deductions: @js(collect(\App\Models\FinancePayment::DEDUCTIONS)->mapWithKeys(fn ($d) => [$d => (float) ($oldDeductions[$d] ?? 0)])),
                  get net() { return Math.max(0, (Number(this.gross) || 0) - Object.values(this.deductions).reduce((a, b) => a + (Number(b) || 0), 0)) },
                  pick() {
                      const p = this.people[this.payee]; if (!p) return;
                      this.method = p.method;
                      if (this.kind === 'salary') { if (p.pay) this.gross = p.pay; this.category = this.salaryCategory; }
                      else if (p.category) this.category = String(p.category);
                  },
                  init() { if (!this.category) this.category = this.kind === 'salary' ? this.salaryCategory : (this.people[this.payee]?.category ? String(this.people[this.payee].category) : '') },
              }"
              class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            @csrf

            <div class="flex flex-wrap gap-2">
                @foreach (['salary' => 'Salary', 'advance' => 'Advance', 'other' => 'Other expense'] as $key => $text)
                    <label class="flex cursor-pointer items-center rounded-lg border px-4 py-2 text-sm font-medium"
                           :class="kind === '{{ $key }}' ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400' : 'border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-300'">
                        <input type="radio" name="kind" value="{{ $key }}" x-model="kind" @change="init(); pick()" class="sr-only"> {{ $text }}
                    </label>
                @endforeach
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}" for="payee_id">Paid to <span class="text-red-500">*</span></label>
                    <select id="payee_id" name="payee_id" x-model="payee" @change="pick()" required class="{{ $input }}">
                        <option value="">Choose…</option>
                        @foreach ($payees as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ \App\Models\Payee::TYPES[$p->type] }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="category_id">Category <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" x-model="category" required class="{{ $input }}">
                        <option value="">Choose…</option>
                        @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                    </select>
                </div>
            </div>

            {{-- Salary: gross, deductions withheld, net --}}
            <template x-if="kind === 'salary'">
                <div class="space-y-4 rounded-xl bg-gray-50 p-4 dark:bg-gray-800/40">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="{{ $label }}" for="period">Month <span class="text-red-500">*</span></label>
                            <input id="period" type="month" name="period" value="{{ old('period', now()->format('Y-m')) }}" required class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}" for="gross">Gross pay (KES) <span class="text-red-500">*</span></label>
                            <input id="gross" type="number" step="0.01" min="0" name="gross" x-model="gross" required class="{{ $input }}">
                        </div>
                    </div>
                    <div>
                        <p class="{{ $label }}">Deductions withheld (KES)</p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
                            @foreach (\App\Models\FinancePayment::DEDUCTIONS as $d)
                                <label class="text-xs text-gray-500">{{ $d }}
                                    <input type="number" step="0.01" min="0" name="deductions[{{ $d }}]" x-model="deductions['{{ $d }}']" class="mt-1 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90">
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Enter the figures from the payslip. What you withhold is owed to KRA, NSSF and SHA: remit it by their monthly deadlines (around the 9th of the next month).</p>
                    </div>
                    <p class="flex items-center justify-between rounded-lg bg-white px-4 py-3 text-sm dark:bg-gray-900">
                        <span class="text-gray-600 dark:text-gray-300">Net pay (what you send)</span>
                        <strong class="text-lg text-gray-900 dark:text-white" x-text="'KES ' + net.toLocaleString('en-KE', { minimumFractionDigits: 2 })"></strong>
                    </p>
                </div>
            </template>

            <template x-if="kind !== 'salary'">
                <div>
                    <label class="{{ $label }}" for="amount">Amount (KES) <span class="text-red-500">*</span></label>
                    <input id="amount" type="number" step="0.01" min="1" name="amount" value="{{ old('amount') }}" required class="{{ $input }}">
                </div>
            </template>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label class="{{ $label }}" for="method">Paid by <span class="text-red-500">*</span></label>
                    <select id="method" name="method" x-model="method" class="{{ $input }}">
                        @foreach (\App\Models\Payee::METHODS as $key => $text)<option value="{{ $key }}">{{ $text }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="reference">Reference</label>
                    <input id="reference" name="reference" value="{{ old('reference') }}" :required="method !== 'cash'" placeholder="M-Pesa / bank ref" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="paid_on">Date <span class="text-red-500">*</span></label>
                    <input id="paid_on" type="date" name="paid_on" value="{{ old('paid_on', today()->toDateString()) }}" max="{{ today()->toDateString() }}" required class="{{ $input }}">
                </div>
            </div>

            <div>
                <label class="{{ $label }}" for="notes">Note</label>
                <input id="notes" name="notes" value="{{ old('notes') }}" maxlength="1000" class="{{ $input }}">
            </div>

            <div class="flex gap-3">
                <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">Record payment</button>
                <a href="{{ route('finance.payments.index') }}" class="rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</a>
            </div>
        </form>
        @endif
    </div>
</main>
@endsection
