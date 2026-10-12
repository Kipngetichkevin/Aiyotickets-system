<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Make sure the client role exists and give it to any account
     * that was created without a role (e.g. before registration assigned one).
     */
    public function up(): void
    {
        Role::findOrCreate('user', 'web');

        User::doesntHave('roles')->each(fn (User $user) => $user->assignRole('user'));
    }

    public function down(): void
    {
        // Role assignments are data; nothing to reverse.
    }
};
