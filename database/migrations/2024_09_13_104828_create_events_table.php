<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id(); // Creates an auto-incrementing ID column
            $table->unsignedBigInteger('merchant_id'); // Foreign key for merchant
            $table->unsignedBigInteger('user_id'); // Foreign key for user
            $table->unsignedBigInteger('event_type_id'); // Foreign key for event type
            $table->string('name'); // Event name
            $table->text('description'); // Event description
            $table->date('date'); // Event date
            $table->date('end_date'); // Event end date
            $table->string('location'); // Event location
            $table->string('currency', 3); // Currency (ISO 4217 format)
            $table->string('status'); // Event status
            $table->string('image')->nullable(); // Event image (nullable)
            $table->string('banner')->nullable(); // Event banner (nullable)
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
