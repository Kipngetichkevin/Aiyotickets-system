<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('experience_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Links
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('experience_id')
                  ->constrained('experiences')->cascadeOnDelete();

            $table->foreignId('event_id')->nullable()
                  ->constrained('events')->nullOnDelete();

            $table->foreignId('order_id')->nullable()
                  ->constrained('orders')->nullOnDelete();

            // Attendee details (same as tickets)
            $table->string('name');
            $table->string('gender', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            // Purchase details
            $table->integer('qty')->default(1);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance_due', 12, 2)->default(0);

            // Payment
            $table->string('payment_status', 20)->default('pending');
            $table->string('payment_mode', 50)->nullable();
            $table->foreignId('payment_mode_id')->nullable()
                  ->constrained('payment_modes')->nullOnDelete();
            $table->string('transaction_code')->nullable();

            // QR & status
            $table->string('qr_code')->nullable();
            $table->string('status', 20)->default('active');

            // Check-in
            $table->boolean('checked_in')->default(false);
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()
                  ->constrained('users')->nullOnDelete();

            // Meta
            $table->boolean('invoice_sent')->default(false);
            $table->string('created_by')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index('payment_status');
            $table->index('status');
            $table->index('checked_in');
            $table->index(['experience_id', 'event_id']);
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('experience_bookings');
    }
};