<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
        // ✅ Register the 'admin' gate — used by ->middleware('can:admin')
        Gate::define('admin', function ($user) {
            return $user->hasRole('admin');
        });

        // Auto-clear system cache whenever the settings row is updated
        System::updated(function ($system) {
            SystemHelper::clearCache();
        });
    }
}