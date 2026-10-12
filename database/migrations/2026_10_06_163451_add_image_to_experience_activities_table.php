<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experience_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('experience_activities', 'image')) {
                $table->string('image')->nullable()->after('pro_tip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('experience_activities', function (Blueprint $table) {
            if (Schema::hasColumn('experience_activities', 'image')) {
                $table->dropColumn('image');
            }
        });
    }
};