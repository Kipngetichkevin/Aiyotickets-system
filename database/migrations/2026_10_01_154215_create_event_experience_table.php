<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_experience', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                  ->constrained('events')
                  ->cascadeOnDelete();

            $table->foreignId('experience_id')
                  ->constrained('experiences')
                  ->cascadeOnDelete();

            // Optional overrides when this experience is attached to this event
            $table->decimal('price_override', 12, 2)->nullable();
            $table->integer('capacity_override')->nullable();

            // Enable/disable per-event without deleting the link
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Prevent duplicate pairs
            $table->unique(['event_id', 'experience_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_experience');
    }
};