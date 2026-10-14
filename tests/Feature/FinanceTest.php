<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\FinanceCategory;
use App\Models\FinancePayment;
use App\Models\Payee;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    private function payee(array $attributes = []): Payee
    {
        return Payee::create(array_merge([
            'type' => 'supplier', 'name' => 'Sauti Sound Ltd', 'payment_method' => 'mpesa', 'mpesa_phone' => '0712345678', 'is_active' => true,
        ], $attributes));
    }

    private function category(string $name = 'Event production & equipment'): FinanceCategory
    {
        return FinanceCategory::where('name', $name)->firstOrFail();
    }

    private function bill(array $attributes = []): Bill
    {
        $bill = new Bill(array_merge([
            'payee_id' => $this->payee()->id, 'category_id' => $this->category()->id, 'description' => 'Sound system hire',
            'bill_date' => today(), 'due_date' => today()->addDays(30), 'amount' => 50000,
        ], $attributes));
        $bill->forceFill(['status' => Bill::AWAITING])->save();

        return $bill;
    }

    public function test_only_admins_reach_finance(): void
    {
        foreach (['finance.overview', 'finance.bills.index', 'finance.payments.index', 'finance.payees.index', 'finance.creditors', 'finance.categories.index'] as $route) {
            $this->actingAs($this->user('merchant'))->get(route($route))->assertForbidden();
        }
    }

    public function test_every_finance_page_renders_for_an_admin(): void
    {
        $admin = $this->user('admin');
        $bill  = $this->bill();

        foreach ([
            route('finance.overview'), route('finance.overview', ['period' => 'all']),
            route('finance.bills.index'), route('finance.bills.create'), route('finance.bills.show', $bill), route('finance.bills.edit', $bill),
            route('finance.payments.index'), route('finance.payments.create'), route('finance.payments.create', ['kind' => 'other']),
            route('finance.payees.index'), route('finance.payees.create'), route('finance.payees.show', $bill->payee), route('finance.payees.edit', $bill->payee),
            route('finance.creditors'), route('finance.categories.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_a_bill_is_approved_then_paid_in_parts_and_never_overpaid(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)->post(route('finance.bills.store'), [
            'payee_id' => $this->payee()->id, 'category_id' => $this->category()->id, 'description' => 'Stage lights',
            'bill_date' => today()->toDateString(), 'due_date' => today()->addDays(14)->toDateString(), 'amount' => 30000,
        ])->assertRedirect();
        $bill = Bill::latest('id')->first();
        $this->assertSame(Bill::AWAITING, $bill->status);

        $pay = fn ($amount) => $this->actingAs($admin)->post(route('finance.bills.pay', $bill), [
            'amount' => $amount, 'method' => 'mpesa', 'reference' => 'RKX' . $amount, 'paid_on' => today()->toDateString(),
        ]);

        $pay(10000)->assertSessionHasErrors('amount');                // not approved yet
        $this->actingAs($admin)->post(route('finance.bills.approve', $bill))->assertSessionHas('success');
        $this->assertSame($admin->id, $bill->fresh()->approved_by);

        $pay(10000)->assertSessionHas('success');
        $this->assertSame('Part paid', $bill->fresh()->statusLabel());
        $pay(25000)->assertSessionHasErrors('amount');                // only 20,000 left
        $pay(20000)->assertSessionHas('success');

        $bill->refresh();
        $this->assertTrue($bill->isPaid());
        $this->assertEquals(30000, FinancePayment::where('bill_id', $bill->id)->sum('amount'));
        $this->assertSame(0.0, $bill->payee->owed());

        // A bill with payments can't be voided
        $this->actingAs($admin)->post(route('finance.bills.void', $bill), ['reason' => 'oops'])->assertSessionHasErrors('reason');
    }

    public function test_salary_records_net_pay_after_deductions(): void
    {
        $staff = $this->payee(['type' => 'staff', 'name' => 'Achieng', 'monthly_pay' => 60000]);

        $this->actingAs($this->user('admin'))->post(route('finance.payments.store'), [
            'kind' => 'salary', 'payee_id' => $staff->id, 'category_id' => $this->category('Salaries & wages')->id,
            'period' => now()->format('Y-m'), 'gross' => 60000,
            'deductions' => ['PAYE' => 9000, 'NSSF' => 3600, 'SHIF' => 1650, 'Housing levy' => 900, 'Other' => ''],
            'method' => 'mpesa', 'reference' => 'SAL123', 'paid_on' => today()->toDateString(),
        ])->assertRedirect(route('finance.payments.index'));

        $payment = FinancePayment::firstOrFail();
        $this->assertEquals(44850, $payment->amount);
        $this->assertEquals(60000, $payment->gross);
        $this->assertSame(['PAYE' => 9000, 'NSSF' => 3600, 'SHIF' => 1650, 'Housing levy' => 900], $payment->deductions);
    }

    public function test_creditors_are_aged_by_days_overdue(): void
    {
        $supplier = $this->payee();
        foreach ([[5, 1000], [-10, 2000], [-45, 4000], [-120, 8000]] as [$days, $amount]) {
            $bill = $this->bill(['payee_id' => $supplier->id, 'amount' => $amount, 'bill_date' => today()->subDays(150), 'due_date' => today()->addDays($days)]);
            $bill->forceFill(['status' => Bill::APPROVED])->save();
        }
        $this->bill(['payee_id' => $supplier->id, 'amount' => 99999]);   // not approved: not owed yet

        $response = $this->actingAs($this->user('admin'))->get(route('finance.creditors'))->assertOk();
        $totals   = $response->viewData('totals');

        $this->assertEquals(1000, $totals['current']);
        $this->assertEquals(2000, $totals['1_30']);
        $this->assertEquals(4000, $totals['31_60']);
        $this->assertEquals(8000, $totals['90_plus']);
        $this->assertEquals(15000, $totals['total']);
    }

    public function test_payments_export_is_csv_and_formula_safe(): void
    {
        $payee = $this->payee(['name' => '=HYPERLINK("x")']);
        $payment = new FinancePayment(['payee_id' => $payee->id, 'category_id' => $this->category()->id, 'kind' => 'other',
            'amount' => 500, 'method' => 'cash', 'paid_on' => today()]);
        $payment->save();

        $csv = $this->actingAs($this->user('admin'))->get(route('finance.payments.index', ['export' => 'csv']))->assertOk()->streamedContent();

        $this->assertStringContainsString("'=HYPERLINK", $csv);
    }

    public function test_sidebar_groups_finance_for_admins_only(): void
    {
        $this->actingAs($this->user('admin'))->get(route('finance.overview'))
            ->assertSee('Suppliers &amp; staff', false)->assertSee('Creditors')->assertSee('Organiser payouts');

        $this->actingAs($this->user('merchant'))->get(route('payouts.index'))
            ->assertOk()->assertDontSee('Creditors')->assertSee('Payouts');
    }
}
