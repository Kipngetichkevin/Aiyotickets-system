<?php

namespace App\Http\Controllers\Admin\Finance;

use App\Http\Controllers\Controller;
use App\Models\Bill;

/** Creditors: who we owe, how much, and how overdue (aged payables). */
class CreditorController extends Controller
{
    public const BUCKETS = ['current' => 'Not due yet', '1_30' => '1–30 days', '31_60' => '31–60 days', '61_90' => '61–90 days', '90_plus' => 'Over 90 days'];

    public function index()
    {
        $rows = [];
        foreach (Bill::open()->with('payee')->get() as $bill) {
            $late   = $bill->due_date->isFuture() || $bill->due_date->isToday() ? 0 : (int) $bill->due_date->diffInDays(today());
            $bucket = match (true) {
                $late === 0 => 'current',
                $late <= 30 => '1_30',
                $late <= 60 => '31_60',
                $late <= 90 => '61_90',
                default     => '90_plus',
            };

            $row = $rows[$bill->payee_id] ??= ['payee' => $bill->payee, 'bills' => 0, 'total' => 0.0] + array_fill_keys(array_keys(self::BUCKETS), 0.0);
            $row['bills']++;
            $row[$bucket] += $bill->balance();
            $row['total'] += $bill->balance();
            $rows[$bill->payee_id] = $row;
        }

        $rows   = collect($rows)->sortByDesc('total')->values();
        $totals = array_map(fn ($key) => $rows->sum($key), array_combine(array_keys(self::BUCKETS), array_keys(self::BUCKETS))) + ['total' => $rows->sum('total')];

        return view('finance.creditors', ['rows' => $rows, 'totals' => $totals, 'buckets' => self::BUCKETS]);
    }
}
