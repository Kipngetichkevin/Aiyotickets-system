<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * Data the app needs to run on a fresh database. Safe to run repeatedly.
 */
class ReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        // Roles used by routes, the sidebar and registration
        foreach (['admin', 'merchant', 'user'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        // Payment modes — order items and attendees fall back to id 1 (Mobile Money)
        $modes = [
            1 => ['name' => 'Mobile Money', 'code' => 'MPESA',  'description' => 'M-Pesa STK push',   'status' => 1],
            2 => ['name' => 'Card',         'code' => 'CARD',   'description' => 'Debit/Credit card', 'status' => 1],
            3 => ['name' => 'Crypto',       'code' => 'CRYPTO', 'description' => 'Cryptocurrency',    'status' => 0],
            4 => ['name' => 'Cash',         'code' => 'CASH',   'description' => 'Cash payment',      'status' => 1],
        ];
        foreach ($modes as $id => $mode) {
            DB::table('payment_mode')->updateOrInsert(['id' => $id], $mode + ['created_at' => now(), 'updated_at' => now()]);
        }

        // Event types
        $types = ['Concert', 'Conference', 'Workshop', 'Seminar', 'Sports', 'Festival', 'Exhibition', 'Networking',
            'Charity', 'Corporate', 'Theater', 'Comedy', 'Family', 'Music', 'Food', 'Fashion', 'Tech', 'Health',
            'Education', 'Art', 'Culture', 'Wedding', 'Birthday', 'Party', 'General'];
        foreach ($types as $i => $name) {
            if (!DB::table('event_types')->where('name', $name)->exists()) {
                DB::table('event_types')->insert([
                    'name'       => $name,
                    'slug'       => Str::slug($name),
                    'sort_order' => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // First admin, only when ADMIN_EMAIL and ADMIN_PASSWORD are set in .env
        $email    = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');
        if ($email && $password) {
            $admin = User::firstOrCreate(
                ['email' => $email],
                ['name' => 'Administrator', 'password' => Hash::make($password)]
            );
            $admin->assignRole('admin');
        }
    }
}
