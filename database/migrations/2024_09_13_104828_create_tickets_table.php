<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->enum('currency', ['KES', 'USD', 'USDT', 'EUROS', 'POUND'])->default('KES'); // Added currency with allowed values
            $table->decimal('price', 8, 2);
            $table->integer('quantity');
            $table->dateTime('start_date')->nullable(); // Added start_date
            $table->dateTime('end_date')->nullable();   // Added end_date
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};