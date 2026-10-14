<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Money we paid out: against a bill, a salary, an advance, or anything else. */
class FinancePayment extends Model
{
    public const KINDS = [
        'bill'    => 'Bill payment',
        'salary'  => 'Salary',
        'advance' => 'Advance',
        'other'   => 'Other expense',
    ];

    /** Payslip deductions offered on the form; amounts are entered, not calculated. */
    public const DEDUCTIONS = ['PAYE', 'NSSF', 'SHIF', 'Housing levy', 'Other'];

    protected $fillable = [
        'payee_id', 'bill_id', 'category_id', 'kind', 'period', 'gross', 'deductions',
        'amount', 'method', 'reference', 'paid_on', 'notes',
    ];

    protected $casts = [
        'gross'      => 'decimal:2',
        'amount'     => 'decimal:2',
        'deductions' => 'array',
        'paid_on'    => 'date',
    ];

    public function payee()
    {
        return $this->belongsTo(Payee::class);
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function category()
    {
        return $this->belongsTo(FinanceCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
