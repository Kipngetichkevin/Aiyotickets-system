<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * mpesa_stks.status becomes the app's payment state (pending / completed / failed).
     * result_code previously doubled as a "pending" marker ('1'), which is also Safaricom's
     * code for insufficient funds; it now only holds Safaricom's actual result.
     */
    public function up(): void
    {
        // Rows still waiting on the customer: clear the fake result code.
        DB::table('mpesa_stks')
            ->where('result_code', '1')
            ->where('result_desc', 'Awaiting payment confirmation')
            ->update(['status' => 'pending', 'result_code' => null]);

        DB::table('mpesa_stks')->where('result_code', '0')->update(['status' => 'completed']);

        DB::table('mpesa_stks')
            ->whereNotNull('result_code')
            ->where('result_code', '!=', '0')
            ->update(['status' => 'failed']);

        DB::table('mpesa_stks')->whereNull('result_code')->whereNull('status')->update(['status' => 'pending']);

        // One payment row per STK push, one order per payment, one use per receipt.
        Schema::table('mpesa_stks', function (Blueprint $table) {
            $table->unique('checkout_request_id', 'mpesa_stks_checkout_request_id_unique');
            $table->unique('order_id', 'mpesa_stks_order_id_unique');
            $table->unique('mpesa_receipt_number', 'mpesa_stks_mpesa_receipt_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('mpesa_stks', function (Blueprint $table) {
            $table->dropUnique('mpesa_stks_checkout_request_id_unique');
            $table->dropUnique('mpesa_stks_order_id_unique');
            $table->dropUnique('mpesa_stks_mpesa_receipt_number_unique');
        });

        DB::table('mpesa_stks')
            ->where('status', 'pending')
            ->whereNull('result_code')
            ->update(['result_code' => '1']);
    }
};
