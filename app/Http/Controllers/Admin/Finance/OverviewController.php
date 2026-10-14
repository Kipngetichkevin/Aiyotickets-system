<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\FinancePayment;
use App\Models\MpesaStk;
use App\Models\Order;
use App\Models\Payout;
use App\Services\MpesaPaymentService;
use App\Services\SalesReport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * The finance dashboard. Money collected by M-Pesa is not all ours: organisers'
 * share is owed to them, so the platform's income is the fee on paid sales, and
 * profit is that fee less what we spend.
 */
class OverviewController extends Controller
{
    public const PERIODS = [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year'  => 'This year',
        'all'        => 'All time',
    ];

    public function index(Request $request)
    {
        $period = array_key_exists($request->input('period'), self::PERIODS) ? $request->input('period') : 'this_month';
        [$from, $to] = self::range($period);

        $sales     = (new SalesReport($request->user(), $from, $to))->totals();
        $collected = (float) MpesaStk::where('status', MpesaPaymentService::COMPLETED)
            ->when($from, fn ($q) => $q->whereBetween('created_at', [$from, $to]))->sum('amount');
        $spent     = (float) FinancePayment::when($from, fn ($q) => $q->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()]))->sum('amount');

        // Balances right now (not limited to the period)
        $allSales      = (new SalesReport($request->user()))->totals();
        $payoutsPaid   = (float) Payout::where('status', Payout::PAID)->sum('amount');
        $owedOrganisers = max(0, round($allSales['net'] - $payoutsPaid, 2));

        return view('finance.overview', [
            'period'   => $period,
            'periods'  => self::PERIODS,
            'figures'  => [
                'collected' => $collected,
                'gross'     => $sales['gross'],
                'fee'       => $sales['fee'],
                'spent'     => $spent,
                'profit'    => round($sales['fee'] - $spent, 2),
            ],
            'balances' => [
                'owed_organisers'  => $owedOrganisers,
                'payout_requests'  => (float) Payout::whereIn('status', [Payout::REQUESTED, Payout::APPROVED])->sum('amount'),
                'refunds_due'      => (float) Order::where('refund', '>', 0)->whereNull('refunded_at')->sum('refund'),
                'bills_owed'       => (float) Bill::open()->sum(DB::raw('amount - amount_paid')),
                'bills_overdue'    => Bill::open()->whereDate('due_date', '<', today())->count(),
                'bills_awaiting'   => Bill::where('status', Bill::AWAITING)->count(),
                'instalments_owed' => $allSales['outstanding'],
            ],
            'byCategory' => FinancePayment::query()
                ->join('finance_categories', 'finance_categories.id', '=', 'finance_payments.category_id')
                ->when($from, fn ($q) => $q->whereBetween('paid_on', [$from->toDateString(), $to->toDateString()]))
                ->selectRaw('finance_categories.name, sum(finance_payments.amount) as total')
                ->groupBy('finance_categories.name')
                ->orderByDesc('total')
                ->get(),
            'dueSoon' => Bill::open()->with('payee')->whereDate('due_date', '<=', today()->addDays(14))->orderBy('due_date')->limit(8)->get(),
            'recent'  => FinancePayment::with(['payee', 'category'])->latest('paid_on')->latest('id')->limit(8)->get(),
        ]);
    }

    /** @return array{0: ?Carbon, 1: ?Carbon} */
    public static function range(string $period): array
    {
        return match ($period) {
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year'  => [now()->startOfYear(), now()->endOfDay()],
            'all'        => [null, null],
            default      => [now()->startOfMonth(), now()->endOfDay()],
        };
    }
}
