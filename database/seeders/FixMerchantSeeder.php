<?php

namespace Database\Seeders;

use App\Models\Merchant;
use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Seeder;

class FixMerchantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        foreach ($users as $user) {
            // Check if user already has a merchant
            $merchant = Merchant::where('user_id', $user->id)->first();
            
            if (!$merchant) {
                // Create merchant for the user
                $merchant = Merchant::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? null,
                    'description' => null,
                    'location' => null,
                ]);
                
                $this->command->info("Created merchant for user: {$user->name}");
            } else {
                $this->command->info("Merchant already exists for user: {$user->name}");
            }
            
            // Update all events for this user to use the merchant
            $updated = Event::where('user_id', $user->id)
                ->update(['merchant_id' => $merchant->id]);
                
            if ($updated > 0) {
                $this->command->info("Updated {$updated} events for user: {$user->name}");
            }
        }
        
        // Also handle any events that might have user_id but no merchant
        $eventsWithoutMerchant = Event::whereNull('merchant_id')->orWhereDoesntHave('merchant')->get();
        
        foreach ($eventsWithoutMerchant as $event) {
            if ($event->user_id) {
                $merchant = Merchant::where('user_id', $event->user_id)->first();
                if ($merchant) {
                    $event->update(['merchant_id' => $merchant->id]);
                    $this->command->info("Fixed event: {$event->name} with merchant: {$merchant->name}");
                }
            }
        }
        
        $this->command->info('Merchant fixes completed!');
    }
}