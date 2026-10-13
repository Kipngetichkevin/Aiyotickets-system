<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * experience_bookings.payment_mode_id referenced `payment_modes`, while the app
 * (PaymentMode model, attendees, seeders) uses `payment_mode`. On a fresh database
 * `payment_modes` is empty, so every experience booking failed to save.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experience_bookings', function (Blueprint $table) {
            $table->dropForeign('experience_bookings_payment_mode_id_foreign');
        });

        // Any value not in payment_mode falls back to Mobile Money (id 1)
        DB::table('experience_bookings')
            ->whereNotNull('payment_mode_id')
            ->whereNotIn('payment_mode_id', DB::table('payment_mode')->select('id'))
            ->update(['payment_mode_id' => DB::table('payment_mode')->where('name', 'Mobile Money')->value('id')]);

        Schema::table('experience_bookings', function (Blueprint $table) {
            $table->foreign('payment_mode_id')->references('id')->on('payment_mode')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('experience_bookings', function (Blueprint $table) {
            $table->dropForeign(['payment_mode_id']);
            $table->foreign('payment_mode_id', 'experience_bookings_payment_mode_id_foreign')->references('id')->on('payment_modes')->nullOnDelete();
        });
    }
};
