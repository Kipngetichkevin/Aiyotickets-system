<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendeesTable extends Migration
{
    public function up()
    {
        Schema::create('attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->onDelete('cascade'); // Foreign key referencing tickets
            $table->string('name');  // Attendee's name
            $table->string('gender');  // Gender of the attendee
            $table->string('phone');  // Phone number
            $table->string('email');  // Email address
            $table->string('qr_code')->nullable(); // QR code for the ticket
            $table->string('payment_mode_id'); // Payment mode (e.g., credit card, PayPal, etc.)
            $table->string('transaction_code'); // Transaction ID or reference
            $table->integer('qty'); // Quantity of tickets purchased
            $table->decimal('total_price', 10, 2); // Total price of the purchase
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendees');
    }
}