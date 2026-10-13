<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();          // merchant being paid
            $table->decimal('amount', 12, 2);
            $table->string('method', 20);                                           // mpesa | bank
            $table->string('destination');                                          // phone, or bank + account
            $table->string('status', 20)->default('requested')->index();            // requested | approved | paid | rejected
            $table->string('reference')->nullable();                                // M-Pesa / bank transaction reference
            $table->text('note')->nullable();                                       // admin note (e.g. rejection reason)
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
