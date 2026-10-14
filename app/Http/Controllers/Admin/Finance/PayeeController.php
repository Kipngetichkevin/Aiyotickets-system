<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\FinanceCategory;
use App\Models\Payee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Suppliers, staff and contractors: who the platform pays, and how. */
class PayeeController extends Controller
{
    public function index(Request $request)
    {
        $type   = array_key_exists($request->input('type'), Payee::TYPES) ? $request->input('type') : null;
        $search = trim((string) $request->input('q'));

        $payees = Payee::query()
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")->orWhere('kra_pin', 'like', "%{$search}%")))
            ->withSum(['bills as owed' => fn ($q) => $q->open()], DB::raw('amount - amount_paid'))
            ->withSum('payments as paid_total', 'amount')
            ->orderByDesc('is_active')->orderBy('name')
            ->paginate(25)->withQueryString();

        return view('finance.payees.index', [
            'payees' => $payees,
            'type'   => $type,
            'search' => $search,
            'counts' => Payee::selectRaw('type, count(*) n')->groupBy('type')->pluck('n', 'type'),
        ]);
    }

    public function create(Request $request)
    {
        return view('finance.payees.form', [
            'payee'      => new Payee(['type' => $request->input('type', 'supplier'), 'payment_method' => 'mpesa', 'is_active' => true]),
            'categories' => FinanceCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $payee = Payee::create($this->validated($request));

        return redirect()->route('finance.payees.show', $payee)->with('success', "{$payee->name} added.");
    }

    public function show(Payee $payee)
    {
        return view('finance.payees.show', [
            'payee'    => $payee,
            'bills'    => $payee->bills()->with('category')->latest('bill_date')->get(),
            'payments' => $payee->payments()->with(['category', 'bill'])->latest('paid_on')->latest('id')->get(),
            'owed'     => $payee->owed(),
        ]);
    }

    public function edit(Payee $payee)
    {
        return view('finance.payees.form', [
            'payee'      => $payee,
            'categories' => FinanceCategory::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Payee $payee)
    {
        $payee->update($this->validated($request));

        return redirect()->route('finance.payees.show', $payee)->with('success', 'Details saved.');
    }

    private function validated(Request $request): array
    {
        $phone = ['nullable', 'regex:/^(?:\+?254|0)[17]\d{8}$/'];

        $data = $request->validate([
            'type'                => ['required', Rule::in(array_keys(Payee::TYPES))],
            'name'                => 'required|string|max:150',
            'phone'               => $phone,
            'email'               => 'nullable|email|max:255',
            'kra_pin'             => ['nullable', 'regex:/^[AP]\d{9}[A-Z]$/i'],
            'id_number'           => 'nullable|string|max:50',
            'job_title'           => 'nullable|string|max:120',
            'monthly_pay'         => 'nullable|numeric|min:0|max:100000000',
            'payment_method'      => ['required', Rule::in(array_keys(Payee::METHODS))],
            'mpesa_phone'         => array_merge($phone, ['required_if:payment_method,mpesa']),
            'bank_name'           => 'required_if:payment_method,bank|nullable|string|max:100',
            'bank_account_name'   => 'required_if:payment_method,bank|nullable|string|max:150',
            'bank_account_number' => 'required_if:payment_method,bank|nullable|string|max:50',
            'default_category_id' => 'nullable|exists:finance_categories,id',
            'notes'               => 'nullable|string|max:2000',
            'is_active'           => 'nullable|boolean',
        ], [
            'kra_pin.regex'     => 'Enter a valid KRA PIN, e.g. A012345678Z.',
            'phone.regex'       => 'Enter a Kenyan mobile number, e.g. 0712345678.',
            'mpesa_phone.regex' => 'Enter a Kenyan M-Pesa number, e.g. 0712345678.',
        ]);

        $data['kra_pin']   = isset($data['kra_pin']) ? strtoupper($data['kra_pin']) : null;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
