{{-- resources/views/promo-codes/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Promo codes')

@section('content')
<main>
    <div x-data="{ pageName: 'Promo codes' }">
        @include('partials.breadcrumb')
    </div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">

        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Promo codes</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Discounts on your tickets and experiences. Set a minimum number of tickets/places for group offers, e.g. 15% off 5 or more.
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-400">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- CREATE --}}
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
                <h3 class="mb-4 text-sm font-bold text-gray-800 dark:text-white/90">New promo code</h3>

                @php($input = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm text-gray-800 focus:border-brand-300 focus:outline-none focus:ring focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90')
                @php($label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400')

                <form method="POST" action="{{ route('promo-codes.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="{{ $label }}">Code</label>
                        <input name="code" value="{{ old('code') }}" required placeholder="EARLYBIRD" class="{{ $input }} uppercase">
                        @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Discount</label>
                            <select name="type" class="{{ $input }}">
                                <option value="percent" @selected(old('type') === 'percent')>Percent (%)</option>
                                <option value="fixed" @selected(old('type') === 'fixed')>Fixed (KES)</option>
                            </select>
                        </div>
                        <div>
                            <label class="{{ $label }}">Value</label>
                            <input type="number" step="0.01" name="value" value="{{ old('value') }}" required class="{{ $input }}">
                        </div>
                    </div>
                    @error('value')<p class="-mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    <div>
                        <label class="{{ $label }}">Applies to</label>
                        <select name="target" class="{{ $input }}">
                            <option value="">All my events and experiences</option>
                            @if ($events->isNotEmpty())
                                <optgroup label="Events">
                                    @foreach ($events as $e)
                                        <option value="event:{{ $e->id }}" @selected(old('target') === "event:{$e->id}")>{{ $e->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            @if ($experiences->isNotEmpty())
                                <optgroup label="Experiences">
                                    @foreach ($experiences as $x)
                                        <option value="experience:{{ $x->id }}" @selected(old('target') === "experience:{$x->id}")>{{ $x->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        </select>
                        @error('event_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        @error('experience_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Min. tickets</label>
                            <input type="number" name="min_tickets" min="1" value="{{ old('min_tickets', 1) }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Max. uses</label>
                            <input type="number" name="max_uses" min="1" value="{{ old('max_uses') }}" placeholder="Unlimited" class="{{ $input }}">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $label }}">Starts</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label class="{{ $label }}">Ends</label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="{{ $input }}">
                        </div>
                    </div>
                    @error('ends_at')<p class="-mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    <button type="submit" class="w-full rounded-lg bg-brand-500 px-4 py-3 text-sm font-semibold text-white hover:bg-brand-600">Create code</button>
                </form>
            </div>

            {{-- LIST --}}
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Code</th>
                                <th class="px-5 py-3">Discount</th>
                                <th class="px-5 py-3">Applies to</th>
                                <th class="px-5 py-3 text-right">Used</th>
                                <th class="px-5 py-3">Valid</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($codes as $c)
                                <tr class="text-gray-700 dark:text-gray-300 {{ $c->is_active ? '' : 'opacity-50' }}">
                                    <td class="px-5 py-3 font-mono font-semibold text-gray-900 dark:text-white">{{ $c->code }}</td>
                                    <td class="px-5 py-3">
                                        {{ $c->label() }}
                                        @if ($c->min_tickets > 1)<span class="block text-xs text-gray-500">min. {{ $c->min_tickets }} tickets/places</span>@endif
                                    </td>
                                    <td class="px-5 py-3">{{ $c->targetName() }}</td>
                                    <td class="px-5 py-3 text-right">{{ $c->used_count }}{{ $c->max_uses ? ' / ' . $c->max_uses : '' }}</td>
                                    <td class="px-5 py-3 text-xs">
                                        {{ $c->starts_at?->format('d M') ?? 'Now' }} – {{ $c->ends_at?->format('d M Y') ?? 'no end' }}
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('promo-codes.toggle', $c) }}">
                                            @csrf
                                            <button class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200">
                                                {{ $c->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No promo codes yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
