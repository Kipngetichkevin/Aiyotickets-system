{{-- resources/views/finance/payees/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Suppliers & staff')

@section('content')
<main>
    <div x-data="{ pageName: 'Suppliers & staff' }">@include('partials.breadcrumb')</div>

    <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Suppliers & staff</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Everyone the platform pays, with their payment details.</p>
            </div>
            <a href="{{ route('finance.payees.create', ['type' => $type ?? 'supplier']) }}" class="rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-600">+ Add {{ strtolower(\App\Models\Payee::TYPES[$type ?? 'supplier']) }}</a>
        </div>

        @include('finance._flash')

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('finance.payees.index', ['q' => $search ?: null]) }}"
                   class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ !$type ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200' }}">All ({{ $counts->sum() }})</a>
                @foreach (\App\Models\Payee::TYPES as $key => $text)
                    <a href="{{ route('finance.payees.index', ['type' => $key, 'q' => $search ?: null]) }}"
                       class="rounded-lg border px-3 py-1.5 text-xs font-semibold {{ $type === $key ? 'border-brand-500 bg-brand-500 text-white' : 'border-gray-200 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-200' }}">{{ $text }} ({{ $counts[$key] ?? 0 }})</a>
                @endforeach
            </div>
            <form method="GET" class="flex gap-2">
                @if ($type)<input type="hidden" name="type" value="{{ $type }}">@endif
                <input name="q" value="{{ $search }}" placeholder="Name, phone or KRA PIN" class="h-10 w-56 rounded-lg border border-gray-300 bg-transparent px-3 text-sm dark:border-gray-700 dark:text-white/90">
                <button class="rounded-lg border border-gray-200 px-4 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Search</button>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800/50 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Type</th>
                            <th class="px-5 py-3">Contact</th>
                            <th class="px-5 py-3">Pay to</th>
                            <th class="px-5 py-3 text-right">We owe</th>
                            <th class="px-5 py-3 text-right">Paid to date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($payees as $p)
                            <tr class="text-gray-700 dark:text-gray-300 {{ $p->is_active ? '' : 'opacity-50' }}">
                                <td class="px-5 py-3">
                                    <a href="{{ route('finance.payees.show', $p) }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-white">{{ $p->name }}</a>
                                    @if ($p->job_title)<span class="block text-xs text-gray-500">{{ $p->job_title }}</span>@endif
                                    @unless ($p->is_active)<span class="block text-xs text-gray-500">Inactive</span>@endunless
                                </td>
                                <td class="px-5 py-3">{{ \App\Models\Payee::TYPES[$p->type] ?? $p->type }}</td>
                                <td class="px-5 py-3 text-xs">{{ $p->phone }}<span class="block text-gray-500">{{ $p->email }}</span></td>
                                <td class="px-5 py-3 text-xs">{{ $p->payTo() }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $p->owed > 0 ? 'text-red-600' : '' }}">{{ $p->owed > 0 ? 'KES ' . number_format($p->owed) : '—' }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format((float) $p->paid_total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No one here yet. Add your suppliers, staff and contractors to start recording bills and payments.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $payees->links() }}</div>
    </div>
</main>
@endsection
