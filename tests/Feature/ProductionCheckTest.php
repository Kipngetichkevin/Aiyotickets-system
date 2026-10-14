<?php

namespace Tests\Feature;

use App\Console\Commands\ProductionCheck;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ProductionCheckTest extends TestCase
{
    public function test_a_non_production_setup_is_not_ready(): void
    {
        config(['app.debug' => true, 'mail.default' => 'log', 'mpesa.environment' => 'sandbox']);

        $this->artisan('aiyo:check')
            ->expectsOutputToContain('APP_DEBUG is on')
            ->expectsOutputToContain('MPESA_ENVIRONMENT is "sandbox"')
            ->expectsOutputToContain('no email leaves the server')
            ->expectsOutputToContain('Scheduler has not run')
            ->assertFailed();
    }

    public function test_a_production_setup_passes(): void
    {
        $this->user('admin');
        Cache::forever(ProductionCheck::HEARTBEAT, now()->timestamp);
        config([
            'app.env' => 'production', 'app.debug' => false, 'app.url' => 'https://tickets.example.co.ke',
            'session.secure' => true, 'queue.default' => 'database',
            'mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.zoho.com', 'mail.from.address' => 'tickets@aiyotickets.co.ke',
            'mpesa.environment' => 'production', 'mpesa.consumer_key' => 'k', 'mpesa.consumer_secret' => 's',
            'mpesa.passkey' => 'p', 'mpesa.shortcode' => '600000', 'mpesa.transaction_type' => 'paybill',
            'mpesa.callback_url' => 'https://tickets.example.co.ke/mpesa/callback',
        ]);

        $command = $this->artisan('aiyo:check')->expectsOutputToContain('Scheduler is running');
        is_dir(public_path('storage')) ? $command->assertSuccessful() : $command->assertFailed();
    }

    public function test_sandbox_test_credentials_never_apply_in_production(): void
    {
        putenv('MPESA_ENVIRONMENT=production');
        $config = require config_path('mpesa.php');
        putenv('MPESA_ENVIRONMENT');

        if (!getenv('MPESA_PASSKEY') && !getenv('SAFARICOM_PASSKEY') && !getenv('MPESA_BUSINESS_SHORTCODE')) {
            $this->assertNull($config['passkey']);
            $this->assertNull($config['shortcode']);
        }
        $this->assertStringEndsWith('/mpesa/callback', $config['callback_url']);
    }
}
