<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class PaymentModeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('payment_mode')->insert([
            ['name' => 'Crypto', 'logo' => 'path/to/crypto_logo.png'],
            ['name' => 'Mobile Money', 'logo' => 'path/to/mobile_money_logo.png'],
            ['name' => 'Card', 'logo' => 'path/to/card_logo.png'],
        ]);
    }
}
