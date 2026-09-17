<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Event;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * Event IDs the current user can see. Null = admin (all).
     */
    private function eventScope(): ?array
    {
        $user = Auth::user();
        if ($user->hasRole('admin')) {
            return null;
        }
        return Event::where('user_id', $user->id)->pluck('id')->toArray();
    }

    /**
     * Payment query scoped by role.
     * Admin → all payments. Merchant → payments tied to their events.
     */
    private function paymentsQuery()
    {
        $user = Auth::user();
        $query = Payment::query();

        if (!$user->hasRole('admin')) {
            $eventIds = Event::where('user_id', $user->id)->pluck('id');
            $orderNos = Order::whereIn('event_id', $eventIds)->pluck('order_no');
            $query->whereIn('order_no', $orderNos);
        }

        return $query;
    }

    /**
     * Budget query scoped by role + optional category.
     */
    private function budgetsQuery(?string $category = null)
    {
        $user = Auth::user();
        $query = Budget::with('event');

        if (!$user->hasRole('admin')) {
            $eventIds = Event::where('user_id', $user->id)->pluck('id');
            $query->whereIn('event_id', $eventIds);
        }

        if ($category) {
            $query->where('category', $category);
        }

        return $query;
    }

    /**
     * Stats for a single budget category.
     */
    private function categoryStats(string $category): array
    {
        $query = $this->budgetsQuery($category);
        $billed = (clone $query)->sum('cost');
        $paid   = (clone $query)->sum('paid_amount');

        return [
            'count'       => (clone $query)->count(),
            'billed'      => $billed,
            'paid'        => $paid,
            'outstanding' => max(0, $billed - $paid),
        ];
    }

    /**
     * Per-event payout breakdown.
     * Net = Paid payments − Paid expenses − Paid budgets
     */
    private function payoutsData(bool $isAdmin, ?array $eventIds)
    {
        $events = $isAdmin
            ? Event::orderBy('name')->get()
            : Event::whereIn('id', $eventIds ?? [])->orderBy('name')->get();

        return $events->map(function ($event) {
            $orderNos = Order::where('event_id', $event->id)->pluck('order_no');
            $revenue = Payment::whereIn('order_no', $orderNos)
                ->where('status', 'paid')
                ->sum('amount_paid');

            $expensesPaid = Expense::where('event_id', $event->id)->sum('paid_amount');
            $budgetsPaid  = Budget::where('event_id', $event->id)->sum('paid_amount');

            $totalCosts = $expensesPaid + $budgetsPaid;

            return [
                'id'            => $event->id,
                'name'          => $event->name,
                'revenue'       => $revenue,
                'expenses_paid' => $expensesPaid,
                'budgets_paid'  => $budgetsPaid,
                'total_costs'   => $totalCosts,
                'net'           => $revenue - $totalCosts,
            ];
        })->sortByDesc('net')->values();
    }

    public function index()
    {
        $eventIds = $this->eventScope();
        $isAdmin = $eventIds === null;

        // Orders scoped by event
        $ordersQuery = Order::query();
        if (!$isAdmin) {
            $ordersQuery->whereIn('event_id', $eventIds);
        }

        // Payments — now matches Reports scoping exactly
        $paymentsQuery = $this->paymentsQuery();

        $totalRevenue = (clone $paymentsQuery)->where('status', 'paid')->sum('amount_paid');
        $outstanding  = (clone $paymentsQuery)->where('status', 'pending')->sum('balance');

        // Expenses
        $expensesQuery = Expense::query();
        if (!$isAdmin) {
            $expensesQuery->whereIn('event_id', $eventIds);
        }
        $totalExpenses     = (clone $expensesQuery)->sum('amount');
        $totalExpensesPaid = (clone $expensesQuery)->sum('paid_amount');

        $netProfit = $totalRevenue - $totalExpensesPaid;

        // Invoices
        $invoicesQuery = Invoice::query();
        if (!$isAdmin) {
            $invoicesQuery->whereIn('event_id', $eventIds);
        }
        $totalInvoiced = (clone $invoicesQuery)->sum('total_amount');
        $unpaidInvoices = (clone $invoicesQuery)
            ->whereIn('status', ['draft', 'sent', 'partial', 'overdue'])
            ->sum('total_amount');

        // Monthly revenue
        $monthlyRevenue = (clone $paymentsQuery)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(6))
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('SUM(amount_paid) as total')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $recentPayments = (clone $paymentsQuery)->orderByDesc('created_at')->limit(10)->get();

        // Lists
        $expenses = $expensesQuery->orderByDesc('created_at')->paginate(15, ['*'], 'expenses_page');
        $invoices = $invoicesQuery->with('event')->orderByDesc('created_at')->paginate(15, ['*'], 'invoices_page');

        // Supplier directory
        $suppliers = Supplier::orderBy('name')->get();

        // ---- Category-based budget tabs ----
        $personnel     = $this->budgetsQuery('personnel')->orderByDesc('created_at')->paginate(15, ['*'], 'personnel_page');
        $supplierBills = $this->budgetsQuery('supplier')->orderByDesc('created_at')->paginate(15, ['*'], 'supplier_bills_page');
        $creditors     = $this->budgetsQuery('creditor')->orderByDesc('created_at')->paginate(15, ['*'], 'creditors_page');
        $debtors       = $this->budgetsQuery('debtor')->orderByDesc('created_at')->paginate(15, ['*'], 'debtors_page');

        $personnelStats = $this->categoryStats('personnel');
        $supplierStats  = $this->categoryStats('supplier');
        $creditorStats  = $this->categoryStats('creditor');
        $debtorStats    = $this->categoryStats('debtor');

        // ---- Payouts ----
        $payouts = $this->payoutsData($isAdmin, $eventIds);

        // Events for filters + expense form
        $events = $isAdmin
            ? Event::orderBy('name')->get(['id', 'name'])
            : Event::whereIn('id', $eventIds)->orderBy('name')->get(['id', 'name']);

        return view('finance.index', compact(
            'isAdmin',
            'totalRevenue',
            'outstanding',
            'totalExpenses',
            'totalExpensesPaid',
            'netProfit',
            'totalInvoiced',
            'unpaidInvoices',
            'monthlyRevenue',
            'recentPayments',
            'expenses',
            'invoices',
            'suppliers',
            'personnel',
            'supplierBills',
            'creditors',
            'debtors',
            'personnelStats',
            'supplierStats',
            'creditorStats',
            'debtorStats',
            'payouts',
            'events'
        ));
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'event_id'         => 'required|exists:events,id',
            'category'         => 'required|string|max:50',
            'supplier_name'    => 'required|string|max:255',
            'description'      => 'nullable|string',
            'amount'           => 'required|numeric|min:0',
            'paid_amount'      => 'nullable|numeric|min:0',
            'status'           => 'nullable|in:pending,partial,paid,overdue',
            'due_date'         => 'nullable|date',
            'paid_date'        => 'nullable|date',
            'payment_method'   => 'nullable|string|max:50',
            'supplier_invoice' => 'nullable|string|max:255',
        ]);

        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['status']      = $validated['status'] ?? 'pending';

        Expense::create($validated);

        return redirect()->route('finance.index', ['tab' => 'expenses'])
            ->with('success', 'Expense added successfully.');
    }

    public function updateExpense(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'event_id'         => 'required|exists:events,id',
            'category'         => 'required|string|max:50',
            'supplier_name'    => 'required|string|max:255',
            'description'      => 'nullable|string',
            'amount'           => 'required|numeric|min:0',
            'paid_amount'      => 'nullable|numeric|min:0',
            'status'           => 'nullable|in:pending,partial,paid,overdue',
            'due_date'         => 'nullable|date',
            'paid_date'        => 'nullable|date',
            'payment_method'   => 'nullable|string|max:50',
            'supplier_invoice' => 'nullable|string|max:255',
        ]);

        $expense->update($validated);

        return redirect()->route('finance.index', ['tab' => 'expenses'])
            ->with('success', 'Expense updated successfully.');
    }

    public function destroyExpense($id)
    {
        Expense::findOrFail($id)->delete();

        return redirect()->route('finance.index', ['tab' => 'expenses'])
            ->with('success', 'Expense deleted.');
    }

    public function storeSupplier(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        Supplier::create($validated);

        return redirect()->route('finance.index', ['tab' => 'suppliers'])
            ->with('success', 'Supplier added.');
    }

    public function destroySupplier($id)
    {
        Supplier::findOrFail($id)->delete();

        return redirect()->route('finance.index', ['tab' => 'suppliers'])
            ->with('success', 'Supplier deleted.');
    }
}