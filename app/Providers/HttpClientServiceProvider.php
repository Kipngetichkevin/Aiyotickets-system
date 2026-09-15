<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class HttpClientServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Http::macro('safaricom', function () {
            return Http::withOptions(['verify' => 'C:\cacert.pem']);
        });
    }
}