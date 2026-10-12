<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE experiences MODIFY COLUMN status ENUM('active','draft','archived') NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE experiences MODIFY COLUMN status ENUM('active','draft','archived') NOT NULL DEFAULT 'draft'");
    }
};