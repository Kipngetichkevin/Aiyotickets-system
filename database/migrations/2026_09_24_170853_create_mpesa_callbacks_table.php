<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_callbacks', function (Blueprint $table) {
            $table->id();

            // Extracted from the payload
            $table->string('checkout_request_id')->nullable()->index();
            $table->string('merchant_request_id')->nullable()->index();
            $table->string('result_code')->nullable();
            $table->text('result_desc')->nullable();
            $table->string('mpesa_receipt_number')->nullable()->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('phone_number')->nullable()->index();

            // Raw request data for forensics
            $table->json('raw_payload');
            $table->json('headers')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();

            // Processing outcome
            $table->enum('processing_status', ['received', 'processed', 'failed'])->default('received');
            $table->text('processing_error')->nullable();

            // Links back to your records
            $table->foreignId('mpesa_stk_id')->nullable()->constrained('mpesa_stks')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->timestamps();

            $table->index(['processing_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_callbacks');
    }
};