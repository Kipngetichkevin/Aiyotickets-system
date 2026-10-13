<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Promo codes can target one experience (as well as one event, or everything)
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->foreignId('experience_id')->nullable()->after('event_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('experience_id');
        });
    }
};
