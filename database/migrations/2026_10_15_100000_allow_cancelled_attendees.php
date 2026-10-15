<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tickets of a cancelled event, or refunded on request, are marked 'cancelled'.
 * attendees.payment_status is a MySQL enum, so the value has to be allowed first.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::statement("ALTER TABLE attendees MODIFY payment_status ENUM('pending','partial','paid','refunded','failed','overdue','cancelled') DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql' || DB::getDriverName() === 'mariadb') {
            DB::table('attendees')->where('payment_status', 'cancelled')->update(['payment_status' => 'refunded']);
            DB::statement("ALTER TABLE attendees MODIFY payment_status ENUM('pending','partial','paid','refunded','failed','overdue') DEFAULT 'pending'");
        }
    }
};
