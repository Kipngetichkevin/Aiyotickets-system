<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gate links: scan-only access to one event or experience for door staff who have
 * no account. Each check-in records which link let the person in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gate_passes', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('event_id')->nullable()->index();
            $t->foreignId('experience_id')->nullable()->constrained('experiences')->cascadeOnDelete();
            $t->string('label', 100);                       // e.g. "Main gate — Brian"
            $t->string('token', 64)->unique();
            $t->string('pin_hash')->nullable();
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('revoked_at')->nullable();
            $t->timestamp('last_used_at')->nullable();
            $t->unsignedInteger('scans')->default(0);
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });

        foreach (['attendees', 'experience_bookings'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->foreignId('gate_pass_id')->nullable()->after('checked_in_by')->constrained('gate_passes')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['attendees', 'experience_bookings'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropConstrainedForeignId('gate_pass_id'));
        }
        Schema::dropIfExists('gate_passes');
    }
};
