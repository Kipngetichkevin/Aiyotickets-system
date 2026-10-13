<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lipa mdogo mdogo: pay a deposit, then the balance in any instalments before a due date.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Merchant settings per event / experience
        foreach (['events', 'experiences'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->boolean('allow_instalments')->default(false);
                $t->unsignedTinyInteger('instalment_deposit_percent')->default(30);
                $t->unsignedSmallInteger('instalment_due_days')->default(7);   // balance due this many days before start
            });
        }

        Schema::table('orders', function (Blueprint $t) {
            $t->string('payment_plan', 20)->nullable()->index();             // null = paid in full, 'instalments'
            $t->unsignedBigInteger('seller_id')->nullable()->index();        // merchant (user) of the event/experience
            $t->unsignedBigInteger('experience_id')->nullable();
            $t->decimal('deposit_amount', 18, 2)->default(0);
            $t->decimal('amount_paid', 18, 2)->default(0);
            $t->decimal('balance_due', 18, 2)->default(0);
            $t->dateTime('due_date')->nullable()->index();
            $t->json('reminders_sent')->nullable();                          // e.g. [14, 7]
            $t->timestamp('lapsed_at')->nullable();
            $t->string('refund_reference')->nullable();                      // 'refund' column holds the amount due back
            $t->timestamp('refunded_at')->nullable();
        });

        // Instalment payments (several per order; order_id stays unique for the first payment)
        Schema::table('mpesa_stks', function (Blueprint $t) {
            $t->unsignedBigInteger('instalment_order_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('mpesa_stks', fn (Blueprint $t) => $t->dropColumn('instalment_order_id'));

        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn([
            'payment_plan', 'seller_id', 'experience_id', 'deposit_amount', 'amount_paid', 'balance_due',
            'due_date', 'reminders_sent', 'lapsed_at', 'refund_reference', 'refunded_at',
        ]));

        foreach (['events', 'experiences'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(['allow_instalments', 'instalment_deposit_percent', 'instalment_due_days']));
        }
    }
};
