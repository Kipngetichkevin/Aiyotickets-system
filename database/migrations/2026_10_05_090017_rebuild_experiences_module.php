<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ============================================================
        // 1. Drop the old event_experience pivot (many-to-many approach)
        // ============================================================
        Schema::dropIfExists('event_experience');

        // ============================================================
        // 2. Drop and recreate experience tables with new schema
        // ============================================================
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('experience_bookings');
        Schema::dropIfExists('experience_categories');
        Schema::dropIfExists('experience_activities');
        Schema::dropIfExists('experience_days');
        Schema::dropIfExists('experience_packages');
        Schema::dropIfExists('experiences');

        // ============================================================
        // 3. experiences — the parent (Serengeti Safari Marathon)
        // ============================================================
        Schema::create('experiences', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('slug')->unique();

            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('merchant_id')->nullable()
                  ->constrained('merchants')->nullOnDelete();

            $table->string('name');
            $table->string('short_description')->nullable();   // "Run wild"
            $table->text('description')->nullable();
            $table->string('location')->nullable();            // "Serengeti, Tanzania"

            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();

            $table->string('image')->nullable();               // cover
            $table->string('poster_image')->nullable();        // official poster
            $table->string('banner')->nullable();              // wide banner
            $table->json('gallery')->nullable();

            $table->integer('total_capacity')->nullable();     // 300

            $table->string('status', 20)->default('active');
            $table->boolean('featured')->default(false);

            $table->timestamps();

            $table->index('status');
            $table->index('featured');
        });

        // ============================================================
        // 4. experience_packages — Bronze / Silver / Gold / Platinum
        // ============================================================
        Schema::create('experience_packages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('experience_id')
                  ->constrained('experiences')->cascadeOnDelete();

            $table->string('name');                             // Bronze / Silver / Gold / Platinum
            $table->integer('tier')->default(1);                // ordering 1..4
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();

            $table->decimal('price', 12, 2)->default(0);
            $table->string('currency', 10)->default('KES');

            $table->string('badge_color', 20)->nullable();      // hex for badge
            $table->string('transport_type')->nullable();       // Shuttle / Van / Car / Flight
            $table->string('accommodation_type')->nullable();   // Hostel dorm / 3★ lodge / 5★ suite
            $table->string('accommodation_sharing')->nullable();// Shared / Private / Suite
            $table->json('includes')->nullable();               // extra perks array

            $table->integer('max_capacity')->nullable();        // per-package limit
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['experience_id', 'is_active']);
        });

        // ============================================================
        // 5. experience_days — Day 1 / Day 2 / Day 3 / Day 4
        // ============================================================
        Schema::create('experience_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')
                  ->constrained('experiences')->cascadeOnDelete();

            $table->integer('day_number');                      // 1, 2, 3, 4
            $table->date('date')->nullable();
            $table->string('title');                            // "Nairobi → Bunda"
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['experience_id', 'day_number']);
        });

        // ============================================================
        // 6. experience_activities — schedule items (pure content)
        // ============================================================
        Schema::create('experience_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')
                  ->constrained('experiences')->cascadeOnDelete();
            $table->foreignId('day_id')
                  ->constrained('experience_days')->cascadeOnDelete();

            $table->string('title');                            // "Cross-Border Drive"
            $table->string('time_label')->nullable();           // "05:30 AM" or "Daytime"
            $table->text('description')->nullable();
            $table->json('tags')->nullable();                   // ["Scenic", "Border Crossing"]
            $table->text('pro_tip')->nullable();
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['experience_id', 'day_id']);
        });

        // ============================================================
        // 7. experience_categories — 42km / 21km / 10km / 5km
        // ============================================================
        Schema::create('experience_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_id')
                  ->constrained('experiences')->cascadeOnDelete();

            $table->string('name');                             // "42km Marathon"
            $table->integer('distance_km')->nullable();
            $table->decimal('price_surcharge', 12, 2)->default(0);
            $table->integer('max_capacity')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['experience_id', 'is_active']);
        });

        // ============================================================
        // 8. experience_bookings — one row per registration
        // ============================================================
        Schema::create('experience_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('experience_id')
                  ->constrained('experiences')->cascadeOnDelete();
            $table->foreignId('package_id')->nullable()
                  ->constrained('experience_packages')->nullOnDelete();
            $table->foreignId('category_id')->nullable()
                  ->constrained('experience_categories')->nullOnDelete();
            $table->foreignId('order_id')->nullable()
                  ->constrained('orders')->nullOnDelete();

            $table->string('name');
            $table->string('gender', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->integer('qty')->default(1);
            $table->decimal('package_price', 12, 2)->default(0);
            $table->decimal('category_surcharge', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->string('payment_status', 20)->default('pending');
            $table->string('payment_mode', 50)->nullable();
            $table->foreignId('payment_mode_id')->nullable()
                  ->constrained('payment_modes')->nullOnDelete();
            $table->string('transaction_code')->nullable();

            $table->string('qr_code')->nullable();
            $table->string('status', 20)->default('active');

            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('payment_status');
            $table->index('status');
            $table->index('checked_in');
            $table->index('order_id');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('experience_bookings');
        Schema::dropIfExists('experience_categories');
        Schema::dropIfExists('experience_activities');
        Schema::dropIfExists('experience_days');
        Schema::dropIfExists('experience_packages');
        Schema::dropIfExists('experiences');
        Schema::enableForeignKeyConstraints();
    }
};