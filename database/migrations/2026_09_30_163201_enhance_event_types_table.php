<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            if (! Schema::hasColumn('event_types', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('event_types', 'color')) {
                $table->string('color', 20)->default('blue')->after('icon');
            }
            if (! Schema::hasColumn('event_types', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0)->after('color');
            }
            if (! Schema::hasColumn('event_types', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            }
        });

        // Backfill slugs for existing rows
        foreach (DB::table('event_types')->get() as $row) {
            if (empty($row->slug)) {
                DB::table('event_types')
                    ->where('id', $row->id)
                    ->update(['slug' => Str::slug($row->name) . '-' . $row->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('event_types', function (Blueprint $table) {
            $table->dropColumn(['slug', 'color', 'sort_order', 'is_active']);
        });
    }
};