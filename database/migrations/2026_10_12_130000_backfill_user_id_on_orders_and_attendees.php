<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Orders and attendees created while user_id was missing from $fillable were saved
     * without an owner. Recover it where a reliable link exists:
     * order ← the M-Pesa transaction that paid for it, attendee ← its order.
     */
    public function up(): void
    {
        DB::table('mpesa_stks')
            ->whereNotNull('order_id')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->each(function ($stk) {
                DB::table('orders')
                    ->where('id', $stk->order_id)
                    ->whereNull('user_id')
                    ->update(['user_id' => $stk->user_id]);
            });

        DB::table('orders')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->each(function ($order) {
                DB::table('attendees')
                    ->where('order_id', $order->id)
                    ->whereNull('user_id')
                    ->update(['user_id' => $order->user_id]);
            });
    }

    public function down(): void
    {
        // Data repair; nothing to reverse.
    }
};
