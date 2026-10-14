{{-- resources/views/finance/bills/form.blade.php --}}
@extends('layouts.app')

@php
    $editing = $bill->exists;
    $input   = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $label   = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $defaults = $payees->pluck('default_category_id', 'id');
@endphp

@section('title', $editing ? 'Edit bill' : 'New bill')

@section('content')
<main>
    <div x-data="{ pageName: '{{ $editing ? 'Edit bill' : 'New bill' }}' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-3xl md:p-6">
        <h1 class="mb-1 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $editing ? 'Edit bill' : 'New bill' }}</h1>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">Enter the supplier's invoice as it is. It's paid only after someone approves it.</p>
        @include('finance._flash')

        @if ($payees->isEmpty())
            <div class="rounded-2xl border-2 border-dashed border-gray-200 p-8 text-center text-gray-500 dark:border-gray-800">
                Add the supplier first. <a href="{{ route('finance.payees.create', ['type' => 'supplier']) }}" class="font-semibold text-brand-600 hover:underline">Add supplier</a>
            </div>
        @else
        <form method="POST" enctype="multipart/form-data" action="{{ $editing ? route('finance.bills.update', $bill) : route('finance.bills.store') }}"
              x-data="{ defaults: @js($defaults), category: '{{ old('category_id', $bill->category_id) }}' }"
              class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="{{ $label }}" for="payee_id">Supplier <span class="text-red-500">*</span></label>
                    <select id="payee_id" name="payee_id" required class="{{ $input }}" @change="if (!category && defaults[$event.target.value]) category = String(defaults[$event.target.value])">
                        <option value="">Choose…</option>
                        @foreach ($payees as $p)
                            <option value="{{ $p->id }}" @selected((int) old('payee_id', $bill->payee_id) === $p->id)>{{ $p->name }} ({{ \App\Models\Payee::TYPES[$p->type] }})</option>
                        @endforeach
                    </select>
                    <a href="{{ route('finance.payees.create', ['type' => 'supplier']) }}" class="mt-1 inline-block text-xs font-semibold text-brand-600 hover:underline">+ New supplier</a>
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}" for="description">What it's for <span class="text-red-500">*</span></label>
                    <input id="description" name="description" value="{{ old('description', $bill->description) }}" required maxlength="255" class="{{ $input }}" placeholder="E.g. Sound system hire, Sauti Nights">
                </div>
                <div>
                    <label class="{{ $label }}" for="category_id">Category <span class="text-red-500">*</span></label>
                    <select id="category_id" name="category_id" required x-model="category" class="{{ $input }}">
                        <option value="">Choose…</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="reference">Their invoice no.</label>
                    <input id="reference" name="reference" value="{{ old('reference', $bill->reference) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="bill_date">Bill date <span class="text-red-500">*</span></label>
                    <input id="bill_date" type="date" name="bill_date" value="{{ old('bill_date', $bill->bill_date?->toDateString()) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="due_date">Due date <span class="text-red-500">*</span></label>
                    <input id="due_date" type="date" name="due_date" value="{{ old('due_date', $bill->due_date?->toDateString()) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="amount">Amount (KES) <span class="text-red-500">*</span></label>
                    <input id="amount" type="number" step="0.01" min="1" name="amount" value="{{ old('amount', $bill->amount) }}" required class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="attachment">Invoice / receipt (PDF or photo)</label>
                    <input id="attachment" type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png" class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-semibold dark:text-gray-300 dark:file:bg-gray-800">
                    @if ($bill->attachment)<p class="mt-1 text-xs text-gray-500">A file is attached; choosing another replaces it.</p>@endif
                </div>
            </div>

            <div class="flex gap-3">
                <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">{{ $editing ? 'Save' : 'Save bill' }}</button>
                <a href="{{ $editing ? route('finance.bills.show', $bill) : route('finance.bills.index') }}" class="rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</a>
            </div>
        </form>
        @endif
    </div>
</main>
@endsection
