{{-- resources/views/finance/categories.blade.php --}}
@extends('layouts.app')

@section('title', 'Expense categories')

@php($field = 'h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90')

@section('content')
<main>
    <div x-data="{ pageName: 'Expense categories' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-3xl md:p-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Expense categories</h1>
        <p class="mb-6 mt-1 text-sm text-gray-500 dark:text-gray-400">How spending is grouped in the overview and exports. Hide a category instead of deleting it, so past payments keep their label.</p>

        @include('finance._flash')

        <form method="POST" action="{{ route('finance.categories.store') }}" class="mb-6 flex gap-2">@csrf
            <input name="name" required maxlength="100" placeholder="New category, e.g. Security" class="{{ $field }} flex-1">
            <button class="rounded-lg bg-brand-500 px-4 text-sm font-semibold text-white hover:bg-brand-600">Add</button>
        </form>

        <div class="divide-y divide-gray-100 rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
            @foreach ($categories as $c)
                <form method="POST" action="{{ route('finance.categories.update', $c) }}" class="flex flex-wrap items-center gap-3 px-5 py-3 {{ $c->is_active ? '' : 'opacity-60' }}">
                    @csrf @method('PUT')
                    <input name="name" value="{{ $c->name }}" required maxlength="100" class="{{ $field }} min-w-[200px] flex-1">
                    <span class="w-32 text-right text-xs text-gray-500">KES {{ number_format((float) $c->spent) }} · {{ $c->bills_count }} {{ \Illuminate\Support\Str::plural('bill', $c->bills_count) }}</span>
                    <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-300">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked($c->is_active) class="h-4 w-4 rounded border-gray-300 text-brand-500"> In use
                    </label>
                    <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">Save</button>
                </form>
            @endforeach
        </div>
    </div>
</main>
@endsection
