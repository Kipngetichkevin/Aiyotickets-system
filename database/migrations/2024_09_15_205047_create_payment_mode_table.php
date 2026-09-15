<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('payment_mode', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the payment mode
            $table->string('logo')->nullable(); // Logo for the payment mode (optional)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_mode');
    }
};