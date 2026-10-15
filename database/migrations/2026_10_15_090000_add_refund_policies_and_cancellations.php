<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each listing states its refund policy; organisers can cancel or postpone with
 * buyers told and refunds recorded; orders say why money is owed back.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['events', 'experiences'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('refund_policy', 20)->default('none');   // none | 7_days | 48_hours
                $t->timestamp('cancelled_at')->nullable();
                $t->string('cancel_reason', 500)->nullable();
                $t->timestamp('postponed_at')->nullable();           // buyers may ask for a refund for a while after
            });
        }

        Schema::table('orders', function (Blueprint $t) {
            $t->string('refund_reason')->nullable()->after('refund');
        });
    }

    public function down(): void
    {
        foreach (['events', 'experiences'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn(['refund_policy', 'cancelled_at', 'cancel_reason', 'postponed_at']));
        }
        Schema::table('orders', fn (Blueprint $t) => $t->dropColumn('refund_reason'));
    }
};
