@extends('layouts.app')

@section('content')
<div class="p-4 mx-auto max-w-screen-2xl md:p-6" x-data="{ tab: '{{ request('tab', 'dashboard') }}' }">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800 dark:text-white/90">Finance</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ $isAdmin ? 'All events overview' : 'Your events overview' }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    @php
        $tabs = [
            'dashboard' => 'Dashboard',
            'expenses'  => 'Expenses',
            'personnel' => 'Personnel',
            'suppliers' => 'Suppliers',
            'creditor'  => 'Creditors',
            'debtor'    => 'Debtors',
            'invoices'  => 'Invoices',
            'payouts'   => 'Payouts',
        ];
    @endphp
    <div class="mb-6 flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-800">
        @foreach($tabs as $key => $label)
            <button
                @click="tab = '{{ $key }}'"
                :class="tab === '{{ $key }}' ? 'border-blue-600 text-blue-600 dark:text-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700'"
                class="px-4 py-2 -mb-px text-sm font-medium border-b-2 transition-colors"
            >{{ $label }}</button>
        @endforeach
    </div>

    {{-- ========================================================= --}}
    {{-- DASHBOARD                                                  --}}
    {{-- ========================================================= --}}
    <div x-show="tab === 'dashboard'" x-cloak>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Revenue</p>
                <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($totalRevenue, 2) }}</h3>
                <p class="mt-1 text-xs text-green-600">Paid payments</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Expenses (paid)</p>
                <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($totalExpensesPaid, 2) }}</h3>
                <p class="mt-1 text-xs text-gray-500">Total billed: KES {{ number_format($totalExpenses, 2) }}</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Net Profit</p>
                <h3 class="mt-2 text-2xl font-semibold {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    KES {{ number_format($netProfit, 2) }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">Revenue − Paid expenses</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding</p>
                <h3 class="mt-2 text-2xl font-semibold text-yellow-600">KES {{ number_format($outstanding + $unpaidInvoices, 2) }}</h3>
                <p class="mt-1 text-xs text-gray-500">Pending payments + invoices</p>
            </div>
        </div>

        {{-- Chart --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Monthly Revenue (Last 6 Months)</h3>
                @if($monthlyRevenue->isNotEmpty())
                    <span class="text-xs text-gray-500 dark:text-gray-400">Max: KES {{ number_format($monthlyRevenue->max('total'), 2) }}</span>
                @endif
            </div>
            @if($monthlyRevenue->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-10">No monthly data to display.</p>
            @else
                @php $maxTotal = $monthlyRevenue->max('total') ?: 1; @endphp
                <div class="overflow-x-auto pb-2">
                    <div class="flex items-end gap-3 min-h-[300px] pt-6 pb-2">
                        @foreach($monthlyRevenue as $m)
                            @php $heightPx = max(8, round(($m->total / $maxTotal) * 220)); @endphp
                            <div class="flex-1 min-w-[44px] flex flex-col items-center justify-end">
                                <div class="text-xs text-gray-600 dark:text-gray-300 mb-1 whitespace-nowrap font-medium">
                                    {{ number_format($m->total / 1000, 1) }}k
                                </div>
                                <div class="w-full bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-t-md transition-all cursor-pointer"
                                     style="height: {{ $heightPx }}px;"
                                     title="{{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('M Y') }}: KES {{ number_format($m->total, 2) }}">
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 whitespace-nowrap">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $m->month)->format('M Y') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Recent payments --}}
        <div class="mt-6 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Recent Payments</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Order #</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Mode</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($recentPayments as $p)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 font-mono text-xs">{{ $p->order_no }}</td>
                                <td class="px-5 py-3">{{ $p->name ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $p->payment_mode ?? '—' }}</td>
                                <td class="px-5 py-3 text-right font-medium">{{ $p->currency ?? 'KES' }} {{ number_format($p->amount_paid, 2) }}</td>
                                <td class="px-5 py-3">
                                    @if($p->status === 'paid')
                                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700">Paid</span>
                                    @else
                                        <span class="rounded-full bg-yellow-100 px-2 py-1 text-xs font-medium text-yellow-700">{{ ucfirst($p->status) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('d M Y') : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No payments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- EXPENSES                                                   --}}
    {{-- ========================================================= --}}
    <div x-show="tab === 'expenses'" x-cloak>
        <div class="mb-4 flex justify-end">
            <button @click="$dispatch('open-expense-modal')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">+ Add Expense</button>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3">Supplier</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3 text-right">Paid</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Due</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($expenses as $e)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3">{{ optional($e->event)->name ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $e->category }}</td>
                                <td class="px-5 py-3">{{ $e->supplier_name }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($e->amount, 2) }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($e->paid_amount, 2) }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs dark:bg-gray-800">{{ $e->status }}</span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $e->due_date?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('finance.expenses.destroy', $e->id) }}" class="inline" onsubmit="return confirm('Delete this expense?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline text-xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No expenses yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3">{{ $expenses->appends(['tab' => 'expenses'])->links() }}</div>
        </div>

        {{-- Add Expense modal --}}
        <div x-data="{ open: false }" @open-expense-modal.window="open = true" x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div @click.outside="open = false" class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Add Expense</h3>
                <form method="POST" action="{{ route('finance.expenses.store') }}">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <label class="block text-sm mb-1">Event</label>
                            <select name="event_id" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800">
                                @foreach($events as $ev)
                                    <option value="{{ $ev->id }}">{{ $ev->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Category</label>
                            <input name="category" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800" placeholder="e.g. Marketing">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Supplier</label>
                            <input name="supplier_name" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm mb-1">Description</label>
                            <textarea name="description" rows="2" class="w-full rounded-lg border-gray-300 dark:bg-gray-800"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Amount (KES)</label>
                            <input type="number" step="0.01" name="amount" required class="w-full rounded-lg border-gray-300 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Paid (KES)</label>
                            <input type="number" step="0.01" name="paid_amount" value="0" class="w-full rounded-lg border-gray-300 dark:bg-gray-800">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-gray-300 dark:bg-gray-800">
                                <option value="pending">Pending</option>
                                <option value="partial">Partial</option>
                                <option value="paid">Paid</option>
                                <option value="overdue">Overdue</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Due Date</label>
                            <input type="date" name="due_date" class="w-full rounded-lg border-gray-300 dark:bg-gray-800">
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="open = false" class="rounded-lg border px-4 py-2 text-sm">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- PERSONNEL / CREDITORS / DEBTORS                            --}}
    {{-- ========================================================= --}}
    @php
        $budgetCategories = [
            'personnel' => ['paginator' => $personnel, 'stats' => $personnelStats],
            'creditor'  => ['paginator' => $creditors, 'stats' => $creditorStats],
            'debtor'    => ['paginator' => $debtors,   'stats' => $debtorStats],
        ];
    @endphp

    @foreach($budgetCategories as $cat => $data)
        <div x-show="tab === '{{ $cat }}'" x-cloak>

            {{-- Header with add button --}}
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90 capitalize">{{ $cat }} Entries</h3>
                <button type="button"
                    onclick='window.dispatchEvent(new CustomEvent("open-budget-create-modal", { detail: { category: "{{ $cat }}" } }))'
                    class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    + Add {{ ucfirst($cat) }}
                </button>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Billed</p>
                    <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($data['stats']['billed'], 2) }}</h3>
                    <p class="mt-1 text-xs text-gray-500">{{ $data['stats']['count'] }} item(s)</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Paid</p>
                    <h3 class="mt-2 text-2xl font-semibold text-green-600">KES {{ number_format($data['stats']['paid'], 2) }}</h3>
                    <p class="mt-1 text-xs text-gray-500">Settled</p>
                </div>
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding</p>
                    <h3 class="mt-2 text-2xl font-semibold text-yellow-600">KES {{ number_format($data['stats']['outstanding'], 2) }}</h3>
                    <p class="mt-1 text-xs text-gray-500">Not yet paid</p>
                </div>
            </div>

            {{-- Table --}}
            <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/[0.02]">
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3">Name</th>
                                <th class="px-5 py-3">Event</th>
                                <th class="px-5 py-3">Contact</th>
                                <th class="px-5 py-3 text-right">Cost</th>
                                <th class="px-5 py-3 text-right">Paid</th>
                                <th class="px-5 py-3 text-right">Balance</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($data['paginator'] as $b)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3 font-medium">{{ $b->name }}</td>
                                    <td class="px-5 py-3">{{ optional($b->event)->name ?? '—' }}</td>
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
                                            $cls = match($b->status) {
                                                'paid' => 'bg-green-100 text-green-700',
                                                'pending' => 'bg-yellow-100 text-yellow-700',
                                                'partial' => 'bg-blue-100 text-blue-700',
                                                'overdue' => 'bg-red-100 text-red-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="rounded-full px-2 py-1 text-xs font-medium {{ $cls }}">{{ ucfirst($b->status) }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-right space-x-2">
                                        <button type="button"
                                                onclick='window.dispatchEvent(new CustomEvent("open-budget-view-modal", { detail: @json($b) }))'
                                                class="text-blue-600 hover:underline text-xs">View</button>
                                        <button type="button"
                                                onclick='window.dispatchEvent(new CustomEvent("open-budget-edit-modal", { detail: @json($b) }))'
                                                class="text-blue-600 hover:underline text-xs">Edit</button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                    No {{ $cat }} entries yet. Click <strong>+ Add {{ ucfirst($cat) }}</strong> to create one.
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-3">{{ $data['paginator']->appends(['tab' => $cat])->links() }}</div>
            </div>
        </div>
    @endforeach

    {{-- ========================================================= --}}
    {{-- SUPPLIERS                                                  --}}
    {{-- ========================================================= --}}
    <div x-show="tab === 'suppliers'" x-cloak>

        {{-- Supplier bill stats --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Total Billed</p>
                <h3 class="mt-2 text-2xl font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($supplierStats['billed'], 2) }}</h3>
                <p class="mt-1 text-xs text-gray-500">{{ $supplierStats['count'] }} item(s)</p>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Paid</p>
                <h3 class="mt-2 text-2xl font-semibold text-green-600">KES {{ number_format($supplierStats['paid'], 2) }}</h3>
            </div>
            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Outstanding</p>
                <h3 class="mt-2 text-2xl font-semibold text-yellow-600">KES {{ number_format($supplierStats['outstanding'], 2) }}</h3>
            </div>
        </div>

        {{-- Header with add button --}}
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Supplier Bills</h3>
            <button type="button"
                onclick='window.dispatchEvent(new CustomEvent("open-budget-create-modal", { detail: { category: "supplier" } }))'
                class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Add Supplier Bill
            </button>
        </div>

        {{-- Supplier bills (per-event) --}}
        <div class="mb-6 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">All Supplier Bills</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3">Contact</th>
                            <th class="px-5 py-3 text-right">Cost</th>
                            <th class="px-5 py-3 text-right">Paid</th>
                            <th class="px-5 py-3 text-right">Balance</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($supplierBills as $b)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 font-medium">{{ $b->name }}</td>
                                <td class="px-5 py-3">{{ optional($b->event)->name ?? '—' }}</td>
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
                                        $cls = match($b->status) {
                                            'paid' => 'bg-green-100 text-green-700',
                                            'pending' => 'bg-yellow-100 text-yellow-700',
                                            'partial' => 'bg-blue-100 text-blue-700',
                                            'overdue' => 'bg-red-100 text-red-700',
                                            default => 'bg-gray-100 text-gray-700',
                                        };
                                    @endphp
                                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $cls }}">{{ ucfirst($b->status) }}</span>
                                </td>
                                <td class="px-5 py-3 text-right space-x-2">
                                    <button type="button"
                                            onclick='window.dispatchEvent(new CustomEvent("open-budget-view-modal", { detail: @json($b) }))'
                                            class="text-blue-600 hover:underline text-xs">View</button>
                                    <button type="button"
                                            onclick='window.dispatchEvent(new CustomEvent("open-budget-edit-modal", { detail: @json($b) }))'
                                            class="text-blue-600 hover:underline text-xs">Edit</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">
                                No supplier bills yet. Click <strong>+ Add Supplier Bill</strong> to create one.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3">{{ $supplierBills->appends(['tab' => 'suppliers'])->links() }}</div>
        </div>

        {{-- Supplier directory --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-medium text-gray-800 dark:text-white/90">Supplier Directory</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/[0.02]">
                            <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <th class="px-5 py-3">Name</th>
                                <th class="px-5 py-3">Contact</th>
                                <th class="px-5 py-3">Email</th>
                                <th class="px-5 py-3">Phone</th>
                                <th class="px-5 py-3 text-right"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($suppliers as $s)
                                <tr class="text-gray-700 dark:text-gray-300">
                                    <td class="px-5 py-3 font-medium">{{ $s->name }}</td>
                                    <td class="px-5 py-3">{{ $s->contact_person ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $s->email ?? '—' }}</td>
                                    <td class="px-5 py-3">{{ $s->phone ?? '—' }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <form method="POST" action="{{ route('finance.suppliers.destroy', $s->id) }}" class="inline" onsubmit="return confirm('Delete supplier?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 text-xs hover:underline">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No suppliers in directory.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h3 class="mb-4 text-lg font-medium text-gray-800 dark:text-white/90">Add to Directory</h3>
                <form method="POST" action="{{ route('finance.suppliers.store') }}" class="space-y-3">
                    @csrf
                    <input name="name" required placeholder="Name *" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                    <input name="contact_person" placeholder="Contact person" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                    <input name="email" type="email" placeholder="Email" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                    <input name="phone" placeholder="Phone" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm">
                    <textarea name="address" rows="2" placeholder="Address" class="w-full rounded-lg border-gray-300 dark:bg-gray-800 text-sm"></textarea>
                    <button class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Add</button>
                </form>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- INVOICES                                                   --}}
    {{-- ========================================================= --}}
    <div x-show="tab === 'invoices'" x-cloak>
        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Invoice #</th>
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Issued</th>
                            <th class="px-5 py-3">Due</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($invoices as $inv)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 font-mono text-xs">{{ $inv->invoice_number }}</td>
                                <td class="px-5 py-3">{{ optional($inv->event)->name ?? '—' }}</td>
                                <td class="px-5 py-3 text-right">{{ $inv->currency }} {{ number_format($inv->total_amount, 2) }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-gray-100 px-2 py-1 text-xs dark:bg-gray-800">{{ $inv->status }}</span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $inv->issue_date?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-xs text-gray-500">{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500 dark:text-gray-400">No invoices yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3">{{ $invoices->appends(['tab' => 'invoices'])->links() }}</div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- PAYOUTS                                                    --}}
    {{-- ========================================================= --}}
    <div x-show="tab === 'payouts'" x-cloak>
        <div class="mb-6 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/10">
            <p class="text-sm text-blue-800 dark:text-blue-300">
                <strong>Net payout</strong> = paid payments − paid expenses − paid budget items, per event.
            </p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/[0.02]">
                        <tr class="text-left text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                            <th class="px-5 py-3">Event</th>
                            <th class="px-5 py-3 text-right">Revenue</th>
                            <th class="px-5 py-3 text-right">Expenses Paid</th>
                            <th class="px-5 py-3 text-right">Budgets Paid</th>
                            <th class="px-5 py-3 text-right">Total Costs</th>
                            <th class="px-5 py-3 text-right">Net Payout</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($payouts as $p)
                            <tr class="text-gray-700 dark:text-gray-300">
                                <td class="px-5 py-3 font-medium">
                                    <a href="{{ route('events.show', $p['id']) }}" class="text-blue-600 hover:underline">
                                        {{ $p['name'] }}
                                    </a>
                                </td>
                                <td class="px-5 py-3 text-right text-green-600">KES {{ number_format($p['revenue'], 2) }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($p['expenses_paid'], 2) }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($p['budgets_paid'], 2) }}</td>
                                <td class="px-5 py-3 text-right font-medium">KES {{ number_format($p['total_costs'], 2) }}</td>
                                <td class="px-5 py-3 text-right font-semibold {{ $p['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    KES {{ number_format($p['net'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-gray-500 dark:text-gray-400">No events found.</td></tr>
                        @endforelse
                    </tbody>
                    @if($payouts->isNotEmpty())
                        <tfoot class="bg-gray-50 dark:bg-white/[0.02] font-medium">
                            <tr class="text-gray-800 dark:text-white/90">
                                <td class="px-5 py-3">TOTAL</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($payouts->sum('revenue'), 2) }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($payouts->sum('expenses_paid'), 2) }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($payouts->sum('budgets_paid'), 2) }}</td>
                                <td class="px-5 py-3 text-right">KES {{ number_format($payouts->sum('total_costs'), 2) }}</td>
                                <td class="px-5 py-3 text-right {{ $payouts->sum('net') >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    KES {{ number_format($payouts->sum('net'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Budget view + edit modals (used by category tabs) --}}
@php
    $u = auth()->user();
    $a = $u->hasRole('admin');
    $evts = $a
        ? \App\Models\Event::orderBy('name')->get(['id', 'name'])
        : \App\Models\Event::where('user_id', $u->id)->orderBy('name')->get(['id', 'name']);
@endphp
@include('partials.modal.budget-view-modal')
@include('partials.modal.budget-modal', ['events' => $evts])

<style>[x-cloak] { display: none !important; }</style>
@endsection