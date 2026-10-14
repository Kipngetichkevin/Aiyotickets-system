{{-- resources/views/finance/payees/form.blade.php — add or edit a supplier / staff member --}}
@extends('layouts.app')

@php
    $editing = $payee->exists;
    $title   = $editing ? 'Edit ' . $payee->name : 'Add supplier or staff';
    $input   = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
    $label   = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400';
    $v       = fn ($f) => old($f, $payee->{$f});
@endphp

@section('title', $title)

@section('content')
<main>
    <div x-data="{ pageName: '{{ $editing ? 'Edit payee' : 'Add payee' }}' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-3xl md:p-6">
        <h1 class="mb-6 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $title }}</h1>
        @include('finance._flash')

        <form method="POST" action="{{ $editing ? route('finance.payees.update', $payee) : route('finance.payees.store') }}"
              x-data="{ type: '{{ $v('type') }}', method: '{{ $v('payment_method') ?: 'mpesa' }}' }"
              class="space-y-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            @csrf
            @if ($editing) @method('PUT') @endif

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}" for="type">Type <span class="text-red-500">*</span></label>
                    <select id="type" name="type" x-model="type" class="{{ $input }}">
                        @foreach (\App\Models\Payee::TYPES as $key => $text)
                            <option value="{{ $key }}" @selected($v('type') === $key)>{{ $text }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="{{ $label }}" for="name">Name <span class="text-red-500">*</span></label>
                    <input id="name" name="name" value="{{ $v('name') }}" required maxlength="150" class="{{ $input }}" placeholder="Person or business name">
                </div>

                <div x-show="type === 'staff'" x-cloak>
                    <label class="{{ $label }}" for="job_title">Job title</label>
                    <input id="job_title" name="job_title" value="{{ $v('job_title') }}" class="{{ $input }}" placeholder="E.g. Operations lead">
                </div>
                <div x-show="type === 'staff'" x-cloak>
                    <label class="{{ $label }}" for="monthly_pay">Usual monthly gross pay (KES)</label>
                    <input id="monthly_pay" name="monthly_pay" type="number" step="0.01" min="0" value="{{ $v('monthly_pay') }}" class="{{ $input }}">
                </div>

                <div>
                    <label class="{{ $label }}" for="phone">Phone</label>
                    <input id="phone" name="phone" value="{{ $v('phone') }}" class="{{ $input }}" placeholder="0712345678">
                </div>
                <div>
                    <label class="{{ $label }}" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ $v('email') }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}" for="kra_pin">KRA PIN</label>
                    <input id="kra_pin" name="kra_pin" value="{{ $v('kra_pin') }}" maxlength="11" class="{{ $input }} uppercase" placeholder="A012345678Z">
                </div>
                <div>
                    <label class="{{ $label }}" for="id_number">ID / registration no.</label>
                    <input id="id_number" name="id_number" value="{{ $v('id_number') }}" class="{{ $input }}">
                </div>
                <div class="sm:col-span-2">
                    <label class="{{ $label }}" for="default_category_id">Usual expense category</label>
                    <select id="default_category_id" name="default_category_id" class="{{ $input }}">
                        <option value="">—</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected((int) $v('default_category_id') === $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <fieldset class="space-y-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                <legend class="text-sm font-bold text-gray-900 dark:text-white">How we pay them</legend>
                <div class="flex flex-wrap gap-3">
                    @foreach (\App\Models\Payee::METHODS as $key => $text)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium"
                               :class="method === '{{ $key }}' ? 'border-brand-500 bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-400' : 'border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-300'">
                            <input type="radio" name="payment_method" value="{{ $key }}" x-model="method" class="sr-only"> {{ $text }}
                        </label>
                    @endforeach
                </div>
                <div x-show="method === 'mpesa'">
                    <label class="{{ $label }}" for="mpesa_phone">M-Pesa number</label>
                    <input id="mpesa_phone" name="mpesa_phone" value="{{ $v('mpesa_phone') }}" class="{{ $input }}" placeholder="0712345678">
                </div>
                <div x-show="method === 'bank'" x-cloak class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <input name="bank_name" value="{{ $v('bank_name') }}" placeholder="Bank" class="{{ $input }}">
                    <input name="bank_account_name" value="{{ $v('bank_account_name') }}" placeholder="Account name" class="{{ $input }}">
                    <input name="bank_account_number" value="{{ $v('bank_account_number') }}" placeholder="Account number" class="{{ $input }}">
                </div>
            </fieldset>

            <div>
                <label class="{{ $label }}" for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm dark:border-gray-700 dark:text-white/90">{{ $v('notes') }}</textarea>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $payee->is_active ?? true)) class="h-4 w-4 rounded border-gray-300 text-brand-500">
                Active (shown when entering bills and payments)
            </label>

            <div class="flex gap-3">
                <button class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">{{ $editing ? 'Save' : 'Add' }}</button>
                <a href="{{ $editing ? route('finance.payees.show', $payee) : route('finance.payees.index') }}" class="rounded-lg border border-gray-200 px-5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Cancel</a>
            </div>
        </form>
    </div>
</main>
@endsection
