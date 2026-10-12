<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('mpesa_stks', 'pending_data')) {
            return;
        }

        Schema::table('mpesa_stks', function (Blueprint $table) {
            $table->json('pending_data')->nullable()->after('response');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('mpesa_stks', 'pending_data')) {
            return;
        }

        Schema::table('mpesa_stks', function (Blueprint $table) {
            $table->dropColumn('pending_data');
        });
    }
};
