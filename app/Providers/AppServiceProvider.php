<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\System;
use App\Helpers\SystemHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        System::updated(function ($system) {
            SystemHelper::clearCache();
        });

        // Behind ngrok, Cloudflare or a load balancer the request reaches us as plain http.
        // When the site is https, every generated link (gate links, QR codes, emails) must be
        // too: phones only allow the camera, and M-Pesa only calls back, over https.
        if (str_starts_with((string) config('app.url'), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
