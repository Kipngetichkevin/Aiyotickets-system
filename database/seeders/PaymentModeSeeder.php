<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentModeSeeder extends Seeder
{
    public function run(): void
    {
        $modes = [
            ['name' => 'Mobile Money', 'code' => 'MPESA',   'description' => 'M-Pesa STK push', 'status' => 1],
            ['name' => 'Card',         'code' => 'CARD',    'description' => 'Debit/Credit card', 'status' => 1],
            ['name' => 'Crypto',       'code' => 'CRYPTO',  'description' => 'Cryptocurrency',    'status' => 0],
            ['name' => 'Cash',         'code' => 'CASH',    'description' => 'Cash payment',      'status' => 1],
        ];

        foreach ($modes as $mode) {
            DB::table('payment_mode')->updateOrInsert(
                ['name' => $mode['name']],
                array_merge($mode, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}