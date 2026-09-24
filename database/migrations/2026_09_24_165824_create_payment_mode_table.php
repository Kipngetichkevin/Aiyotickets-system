<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_mode', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->nullable();
            $table->string('description')->nullable();
            $table->tinyInteger('status')->default(1); // 1 = active
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_mode');
    }
};