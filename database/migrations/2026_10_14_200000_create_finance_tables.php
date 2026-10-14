<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The platform's own money out (accounts payable), alongside the ticket money it
 * already tracks: who we pay (suppliers, staff, contractors), what we owe them
 * (bills, approved before payment), and what we've paid (payments, incl. salaries).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_categories', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('payees', function (Blueprint $t) {
            $t->id();
            $t->string('type', 20)->index();                     // supplier | staff | contractor | other
            $t->string('name');
            $t->string('phone', 20)->nullable();
            $t->string('email')->nullable();
            $t->string('kra_pin', 20)->nullable();
            $t->string('id_number', 50)->nullable();
            $t->string('job_title')->nullable();                 // staff
            $t->decimal('monthly_pay', 12, 2)->nullable();       // staff: usual gross pay
            $t->string('payment_method', 10)->default('mpesa'); // mpesa | bank | cash
            $t->string('mpesa_phone', 20)->nullable();
            $t->string('bank_name')->nullable();
            $t->string('bank_account_name')->nullable();
            $t->string('bank_account_number', 50)->nullable();
            $t->foreignId('default_category_id')->nullable()->constrained('finance_categories')->nullOnDelete();
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::create('bills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('payee_id')->constrained('payees')->restrictOnDelete();
            $t->foreignId('category_id')->constrained('finance_categories')->restrictOnDelete();
            $t->string('reference')->nullable();                // the supplier's invoice number
            $t->string('description');
            $t->date('bill_date');
            $t->date('due_date');
            $t->decimal('amount', 12, 2);
            $t->decimal('amount_paid', 12, 2)->default(0);
            $t->string('status', 20)->default('awaiting_approval')->index(); // awaiting_approval | approved | void
            $t->string('attachment')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('approved_at')->nullable();
            $t->string('void_reason', 500)->nullable();
            $t->timestamps();
            $t->index('due_date');
        });

        Schema::create('finance_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('payee_id')->constrained('payees')->restrictOnDelete();
            $t->foreignId('bill_id')->nullable()->constrained('bills')->restrictOnDelete();
            $t->foreignId('category_id')->constrained('finance_categories')->restrictOnDelete();
            $t->string('kind', 20)->index();                   // bill | salary | advance | other
            $t->string('period', 7)->nullable();               // salary month, YYYY-MM
            $t->decimal('gross', 12, 2)->nullable();           // salary before deductions
            $t->json('deductions')->nullable();                // {"PAYE": 1234, "NSSF": 480, ...}
            $t->decimal('amount', 12, 2);                      // what actually left our account
            $t->string('method', 10);                          // mpesa | bank | cash
            $t->string('reference')->nullable();               // M-Pesa / bank reference
            $t->date('paid_on')->index();
            $t->string('notes', 1000)->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        $now = now();
        DB::table('finance_categories')->insert(array_map(fn ($name) => ['name' => $name, 'created_at' => $now, 'updated_at' => $now], [
            'Salaries & wages', 'Contractors & freelancers', 'Rent & office', 'Marketing & advertising',
            'Event production & equipment', 'Transport & travel', 'Software & hosting', 'M-Pesa & bank charges',
            'Professional fees (legal, audit)', 'Utilities & internet', 'Taxes & licences', 'Other',
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_payments');
        Schema::dropIfExists('bills');
        Schema::dropIfExists('payees');
        Schema::dropIfExists('finance_categories');
    }
};
