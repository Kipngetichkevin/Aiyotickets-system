<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Organisers apply from the public site and an admin approves them. The merchant
 * row is the application: pending → approved | rejected, approved → suspended.
 * Merchants that already exist were set up by an admin, so they start approved.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $t) {
            $t->string('status', 20)->default('approved')->index()->after('location');
            $t->string('business_type', 20)->nullable()->after('status');      // individual | company
            $t->string('id_number', 50)->nullable();                            // national ID / passport, or company reg. no.
            $t->string('kra_pin', 20)->nullable();
            $t->string('website')->nullable();
            $t->string('payout_method', 10)->nullable();                        // mpesa | bank
            $t->string('payout_phone', 20)->nullable();
            $t->string('bank_name')->nullable();
            $t->string('bank_account_name')->nullable();
            $t->string('bank_account_number', 50)->nullable();
            $t->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('reviewed_at')->nullable();
            $t->string('review_note', 500)->nullable();                         // reason for a rejection or suspension
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $t) {
            $t->dropConstrainedForeignId('reviewed_by');
            $t->dropColumn([
                'status', 'business_type', 'id_number', 'kra_pin', 'website', 'payout_method', 'payout_phone',
                'bank_name', 'bank_account_name', 'bank_account_number', 'reviewed_at', 'review_note',
            ]);
        });
    }
};
