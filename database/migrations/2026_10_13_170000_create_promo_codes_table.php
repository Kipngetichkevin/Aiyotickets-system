<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();            // merchant who owns it
            $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete(); // null = all the merchant's events
            $table->string('code', 40)->unique();
            $table->string('type', 10);                                               // percent | fixed
            $table->decimal('value', 10, 2);
            $table->unsignedInteger('min_tickets')->default(1);                       // group discounts
            $table->unsignedInteger('max_uses')->nullable();                          // null = unlimited
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
