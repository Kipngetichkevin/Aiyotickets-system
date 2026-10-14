<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\FinanceCategory;
use App\Models\Payee;
use App\Services\Finance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Bills from suppliers and contractors: enter, approve, pay (in parts if needed). */
class BillController extends Controller
{
    public const FILTERS = [
        'open'     => 'To pay',
        'awaiting' => 'Awaiting approval',
        'overdue'  => 'Overdue',
        'paid'     => 'Paid',
        'void'     => 'Void',
        'all'      => 'All',
    ];

    public function index(Request $request)
    {
        $filter = array_key_exists($request->input('status'), self::FILTERS) ? $request->input('status') : 'open';

        $bills = Bill::with(['payee', 'category'])
            ->when($request->filled('payee'), fn ($q) => $q->where('payee_id', $request->integer('payee')))
            ->when($filter === 'open', fn ($q) => $q->open())
            ->when($filter === 'awaiting', fn ($q) => $q->where('status', Bill::AWAITING))
            ->when($filter === 'overdue', fn ($q) => $q->open()->whereDate('due_date', '<', today()))
            ->when($filter === 'paid', fn ($q) => $q->where('status', Bill::APPROVED)->whereColumn('amount_paid', '>=', 'amount'))
            ->when($filter === 'void', fn ($q) => $q->where('status', Bill::VOID))
            ->orderByRaw($filter === 'paid' || $filter === 'all' ? 'bill_date desc' : 'due_date asc')
            ->paginate(25)->withQueryString();

        return view('finance.bills.index', [
            'bills'  => $bills,
            'filter' => $filter,
            'counts' => [
                'open'     => Bill::open()->count(),
                'awaiting' => Bill::where('status', Bill::AWAITING)->count(),
                'overdue'  => Bill::open()->whereDate('due_date', '<', today())->count(),
            ],
            'owed'   => (float) Bill::open()->sum(DB::raw('amount - amount_paid')),
        ]);
    }

    public function create(Request $request)
    {
        return view('finance.bills.form', [
            'bill'       => new Bill([
                'payee_id'  => $request->integer('payee') ?: null,
                'bill_date' => today(),
                'due_date'  => today()->addDays(30),
            ]),
            'payees'     => Payee::where('is_active', true)->whereIn('type', ['supplier', 'contractor', 'other'])->orderBy('name')->get(),
            'categories' => FinanceCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $bill = new Bill($data);
        $bill->forceFill(['created_by' => $request->user()->id, 'status' => Bill::AWAITING])->save();

        return redirect()->route('finance.bills.show', $bill)->with('success', 'Bill saved. Approve it when it has been checked, then it can be paid.');
    }

    public function show(Bill $bill)
    {
        return view('finance.bills.show', [
            'bill'     => $bill->load(['payee', 'category', 'creator', 'approver', 'payments.creator']),
        ]);
    }

    public function edit(Bill $bill)
    {
        abort_unless($bill->status === Bill::AWAITING, 403, 'Only bills awaiting approval can be edited.');

        return view('finance.bills.form', [
            'bill'       => $bill,
            'payees'     => Payee::where('is_active', true)->orWhere('id', $bill->payee_id)->orderBy('name')->get(),
            'categories' => FinanceCategory::where('is_active', true)->orWhere('id', $bill->category_id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Bill $bill)
    {
        abort_unless($bill->status === Bill::AWAITING, 403, 'Only bills awaiting approval can be edited.');

        $bill->update($this->validated($request, $bill));

        return redirect()->route('finance.bills.show', $bill)->with('success', 'Bill updated.');
    }

    public function approve(Request $request, Bill $bill, Finance $finance)
    {
        $finance->approve($bill, $request->user());

        return back()->with('success', 'Bill approved. It can now be paid.');
    }

    public function pay(Request $request, Bill $bill, Finance $finance)
    {
        $data = $request->validate([
            'amount'    => 'required|numeric|min:1',
            'method'    => ['required', Rule::in(['mpesa', 'bank', 'cash'])],
            'reference' => 'required_unless:method,cash|nullable|string|max:100',
            'paid_on'   => 'required|date|before_or_equal:today',
            'notes'     => 'nullable|string|max:1000',
        ], ['reference.required_unless' => 'Enter the M-Pesa or bank reference.']);

        $finance->payBill($bill, $data, $request->user());

        return back()->with('success', 'Payment of KES ' . number_format($data['amount'], 2) . ' recorded.');
    }

    public function void(Request $request, Bill $bill, Finance $finance)
    {
        $finance->void($bill, $request->validate(['reason' => 'required|string|max:500'])['reason']);

        return back()->with('success', 'Bill voided.');
    }

    private function validated(Request $request, ?Bill $bill = null): array
    {
        $data = $request->validate([
            'payee_id'    => 'required|exists:payees,id',
            'category_id' => 'required|exists:finance_categories,id',
            'reference'   => 'nullable|string|max:100',
            'description' => 'required|string|max:255',
            'bill_date'   => 'required|date',
            'due_date'    => 'required|date|after_or_equal:bill_date',
            'amount'      => 'required|numeric|min:1|max:1000000000',
            'attachment'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('bills');   // private disk, served by attachment()
        } else {
            unset($data['attachment']);
        }

        return $data;
    }

    /** The bill's scanned invoice/receipt, only for admins. */
    public function attachment(Bill $bill)
    {
        abort_unless($bill->attachment && Storage::exists($bill->attachment), 404);

        return Storage::response($bill->attachment);
    }
}
