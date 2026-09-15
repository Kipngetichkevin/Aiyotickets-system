<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;

class EventTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('event_types')->insert([
            [
                'name' => 'Concert',
                'description' => 'Live music performances.',
                'icon' => 'path/to/concert-icon.png',
            ],
            [
                'name' => 'Workshop',
                'description' => 'Hands-on learning experiences.',
                'icon' => 'path/to/workshop-icon.png',
            ],
            [
                'name' => 'Conference',
                'description' => 'Formal meetings for discussion.',
                'icon' => 'path/to/conference-icon.png',
            ],
            // Add more event types as needed
        ]);
    }
}