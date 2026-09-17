@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6">

    {{-- Header --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Budgets</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $isAdmin ? 'All budget items across events' : 'Budget items for your events' }}
            </p>
        </div>
        <button type="button"
                onclick="window.dispatchEvent(new CustomEvent('open-budget-create-modal'))"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
            + Add Budget Item
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Items</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($stats['total']) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Billed</p>
            <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($stats['billed'], 2) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Paid</p>
            <h3 class="mt-2 text-2xl font-semibold text-green-600">KES {{ number_format($stats['paid'], 2) }}</h3>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding</p>
            <h3 class="mt-2 text-2xl font-semibold text-yellow-600">KES {{ number_format($stats['outstanding'], 2) }}</h3>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="GET" action="{{ route('budgets.index') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <select name="event_id" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All events</option>
                @foreach($events as $ev)
                    <option value="{{ $ev->id }}" @selected(request('event_id') == $ev->id)>{{ $ev->name }}</option>
                @endforeach
            </select>

            <select name="category" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All categories</option>
                <option value="personnel" @selected(request('category') === 'personnel')>Personnel</option>
                <option value="supplier" @selected(request('category') === 'supplier')>Supplier</option>
                <option value="debtor" @selected(request('category') === 'debtor')>Debtor</option>
                <option value="creditor" @selected(request('category') === 'creditor')>Creditor</option>
            </select>

            <select name="status" class="rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                <option value="">All statuses</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="partial" @selected(request('status') === 'partial')>Partial</option>
                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                <option value="overdue" @selected(request('status') === 'overdue')>Overdue</option>
            </select>

            <div class="flex gap-2">
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Filter</button>
                <a href="{{ route('budgets.index') }}" class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/[0.02]">
                    <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Event</th>
                        <th class="px-5 py-3">Category</th>
                        <th class="px-5 py-3">Contact</th>
                        <th class="px-5 py-3 text-right">Cost</th>
                        <th class="px-5 py-3 text-right">Paid</th>
                        <th class="px-5 py-3 text-right">Balance</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($budgets as $b)
                        <tr class="text-gray-700 dark:text-gray-300">
                            <td class="px-5 py-3 font-medium">{{ $b->name }}</td>
                            <td class="px-5 py-3">{{ optional($b->event)->name ?? '—' }}</td>
                            <td class="px-5 py-3">
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs dark:bg-gray-800">{{ ucfirst($b->category) }}</span>
                            </td>
                            <td class="px-5 py-3 text-xs">
                                <div>{{ $b->point_of_contact ?? '—' }}</div>
                                <div class="text-gray-500">{{ $b->phone ?? '' }}</div>
                            </td>
                            <td class="px-5 py-3 text-right">KES {{ number_format($b->cost, 2) }}</td>
                            <td class="px-5 py-3 text-right">KES {{ number_format($b->paid_amount, 2) }}</td>
                            <td class="px-5 py-3 text-right font-medium {{ $b->balance > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                                KES {{ number_format($b->balance, 2) }}
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $statusClasses = match($b->status) {
                                        'paid' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'partial' => 'bg-blue-100 text-blue-700',
                                        'overdue' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="rounded-full px-2 py-1 text-xs font-medium {{ $statusClasses }}">
                                    {{ ucfirst($b->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-right space-x-2">
                                <a href="{{ route('budgets.show', $b->id) }}" class="text-blue-600 hover:underline text-xs">View</a>
                                {{-- Edit uses plain JS dispatch — no Alpine scope dependency --}}
                                <button type="button"
                                        onclick='window.dispatchEvent(new CustomEvent("open-budget-edit-modal", { detail: @json($b) }))'
                                        class="text-blue-600 hover:underline text-xs">Edit</button>
                                <form method="POST" action="{{ route('budgets.destroy', $b->id) }}" class="inline" onsubmit="return confirm('Delete this budget item?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No budget items yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3">{{ $budgets->links() }}</div>
    </div>
</div>

{{-- Budget slide-over modal --}}
@include('partials.modal.budget-modal', ['events' => $events])

@endsection