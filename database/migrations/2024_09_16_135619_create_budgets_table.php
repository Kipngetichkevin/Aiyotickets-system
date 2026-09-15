<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_budgets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id'); // Foreign key to events table
            $table->string('name');
            $table->string('role')->nullable();
            $table->string('service_provider')->nullable();
            $table->text('description');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->enum('category', ['personnel', 'supplier', 'debtor', 'creditor']);
            $table->string('payment_mode_id'); // Payment mode (e.g., credit card, PayPal, etc.)

            $table->decimal('cost', 10, 2)->nullable();
            $table->string('contract_signature')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('event_id')->references('id')->on('events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('budgets');
    }
}