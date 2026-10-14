<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use App\Models\FinancePayment;
use App\Models\Payee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Every shilling paid out by the platform (bill payments are recorded from the bill).
 * Salaries are recorded here with gross pay and the deductions withheld.
 */
class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filtered($request);

        if ($request->input('export') === 'csv') {
            return $this->csv($query->clone()->with(['payee', 'category'])->oldest('paid_on')->get());
        }

        return view('finance.payments.index', [
            'payments'   => $query->clone()->with(['payee', 'category', 'bill'])->latest('paid_on')->latest('id')->paginate(30)->withQueryString(),
            'total'      => (float) $query->clone()->sum('amount'),
            'categories' => FinanceCategory::orderBy('name')->get(),
            'payees'     => Payee::orderBy('name')->get(['id', 'name']),
            'filters'    => $request->only('kind', 'payee', 'category', 'from', 'to'),
        ]);
    }

    public function create(Request $request)
    {
        $kind  = array_key_exists($request->input('kind'), FinancePayment::KINDS) && $request->input('kind') !== 'bill' ? $request->input('kind') : 'salary';
        $payee = $request->filled('payee') ? Payee::find($request->integer('payee')) : null;

        return view('finance.payments.form', [
            'kind'       => $kind,
            'payee'      => $payee,
            'payees'     => Payee::where('is_active', true)->orderBy('name')->get(),
            'categories' => FinanceCategory::where('is_active', true)->orderBy('name')->get(),
            'salaryCategoryId' => FinanceCategory::where('name', 'Salaries & wages')->value('id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kind'          => ['required', Rule::in(['salary', 'advance', 'other'])],
            'payee_id'      => 'required|exists:payees,id',
            'category_id'   => 'required|exists:finance_categories,id',
            'period'        => 'required_if:kind,salary|nullable|date_format:Y-m',
            'gross'         => 'required_if:kind,salary|nullable|numeric|min:0',
            'deductions'    => 'nullable|array',
            'deductions.*'  => 'nullable|numeric|min:0',
            'amount'        => 'required_unless:kind,salary|nullable|numeric|min:1',
            'method'        => ['required', Rule::in(array_keys(Payee::METHODS))],
            'reference'     => 'required_unless:method,cash|nullable|string|max:100',
            'paid_on'       => 'required|date|before_or_equal:today',
            'notes'         => 'nullable|string|max:1000',
        ], ['reference.required_unless' => 'Enter the M-Pesa or bank reference.']);

        $deductions = collect($data['deductions'] ?? [])
            ->only(FinancePayment::DEDUCTIONS)
            ->map(fn ($v) => round((float) $v, 2))
            ->filter(fn ($v) => $v > 0)
            ->all();

        if ($data['kind'] === 'salary') {
            // Net pay is gross less what was withheld; it's what actually left our account
            $data['amount'] = round((float) $data['gross'] - array_sum($deductions), 2);
            if ($data['amount'] <= 0) {
                return back()->withInput()->withErrors(['gross' => 'Deductions can\'t be more than the gross pay.']);
            }
            $data['deductions'] = $deductions ?: null;
        } else {
            $data['gross'] = $data['period'] = $data['deductions'] = null;
        }

        $payment = new FinancePayment($data);
        $payment->forceFill(['created_by' => $request->user()->id])->save();

        return redirect()->route('finance.payments.index')
            ->with('success', FinancePayment::KINDS[$payment->kind] . ' of KES ' . number_format($payment->amount, 2) . ' to ' . $payment->payee->name . ' recorded.');
    }

    private function filtered(Request $request)
    {
        return FinancePayment::query()
            ->when($request->filled('kind'), fn ($q) => $q->where('kind', $request->input('kind')))
            ->when($request->filled('payee'), fn ($q) => $q->where('payee_id', $request->integer('payee')))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->date('from'), fn ($q, $d) => $q->whereDate('paid_on', '>=', $d))
            ->when($request->date('to'), fn ($q, $d) => $q->whereDate('paid_on', '<=', $d));
    }

    private function csv($payments): StreamedResponse
    {
        // Leading = + - @ would run as a formula in Excel
        $safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v;

        return response()->streamDownload(function () use ($payments, $safe) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Payee', 'Type', 'Category', 'Period', 'Gross', 'Deductions', 'Amount paid', 'Method', 'Reference', 'Notes']);
            foreach ($payments as $p) {
                fputcsv($out, array_map($safe, [
                    $p->paid_on->format('Y-m-d'), $p->payee?->name, FinancePayment::KINDS[$p->kind] ?? $p->kind, $p->category?->name,
                    $p->period, $p->gross, collect($p->deductions ?? [])->map(fn ($v, $k) => "{$k} {$v}")->implode('; '),
                    $p->amount, Payee::METHODS[$p->method] ?? $p->method, $p->reference, $p->notes,
                ]));
            }
            fclose($out);
        }, 'payments-' . now()->format('Y-m-d') . '.csv', ['Content-Type' => 'text/csv']);
    }
}
