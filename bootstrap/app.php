<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Spatie Permission + Laravel auth middleware aliases
        $middleware->alias([
            'role'               => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'         => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'can'                => \Illuminate\Auth\Middleware\Authorize::class,
        ]);

        // Admin "maintenance mode" switch — but NEVER block webhooks/health.
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        if ($proxies = env('TRUSTED_PROXIES')) {
            $middleware->trustProxies(
                at: $proxies === '*' ? '*' : array_map('trim', explode(',', $proxies))
            );
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(fn (\Throwable $e) => \App\Support\ErrorAlerts::report($e));
    })->create();