<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Owner (merchant/admin who created it)
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            // Core fields
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('KES');

            // Details
            $table->integer('duration_minutes')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('location')->nullable();

            // Media
            $table->string('image')->nullable();
            $table->json('gallery')->nullable();

            // Status & flags
            $table->enum('status', ['active', 'draft', 'archived'])->default('draft');
            $table->boolean('featured')->default(false);

            $table->timestamps();

            $table->index('status');
            $table->index('featured');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experiences');
    }
};