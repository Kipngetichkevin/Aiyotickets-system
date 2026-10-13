<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * The gate scanner identifies an attendee by qr_code, so every code must exist and be unique.
     * Older rows have no code, a shared code, or an SVG image stored instead of a code.
     */
    public function up(): void
    {
        $seen = [];

        DB::table('attendees')->orderBy('id')->select('id', 'qr_code', 'order_id')->each(function ($a) use (&$seen) {
            $code  = (string) $a->qr_code;
            $valid = $code !== '' && strlen($code) <= 64 && !str_contains($code, '<') && !isset($seen[$code]);

            if (!$valid) {
                $orderNo = $a->order_id ? DB::table('orders')->where('id', $a->order_id)->value('order_no') : null;
                $code    = 'QR-' . ($orderNo ?: 'A' . $a->id) . '-' . strtoupper(Str::random(6));
                DB::table('attendees')->where('id', $a->id)->update(['qr_code' => $code]);
            }

            $seen[$code] = true;
        });

        Schema::table('attendees', function (Blueprint $table) {
            $table->string('qr_code', 64)->nullable()->change();
            $table->unique('qr_code', 'attendees_qr_code_unique');
        });

        Schema::table('experience_bookings', function (Blueprint $table) {
            $table->unique('qr_code', 'experience_bookings_qr_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('experience_bookings', function (Blueprint $table) {
            $table->dropUnique('experience_bookings_qr_code_unique');
        });

        Schema::table('attendees', function (Blueprint $table) {
            $table->dropUnique('attendees_qr_code_unique');
            $table->longText('qr_code')->nullable()->change();
        });
    }
};
