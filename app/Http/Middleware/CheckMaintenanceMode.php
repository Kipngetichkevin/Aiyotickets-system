<?php

namespace App\Http\Middleware;

use App\Helpers\SystemHelper;
use Closure;
use Illuminate\Http\Request;

class CheckMaintenanceMode
{
    /**
     * Paths that must never be blocked, no matter what.
     * Paths are safer than route names — they can't be renamed by mistake.
     */
    protected array $exemptPaths = [
        'up',                       // Laravel's built-in health probe
        'health',                   // our own health endpoint
        'mpesa/callback',           // Safaricom STK callback
        'mpesa/c2b/confirmation',   // Safaricom C2B confirmation
        'mpesa/c2b/validation',     // Safaricom C2B validation
    ];

    /**
     * Route names that must never be blocked.
     */
    protected array $exemptRouteNames = [
        'login',
        'logout',
        'password.*',
        'mpesa.callback',
        'mpesa.c2b.*',
        'health',
        // Safety net: if an admin's session is broken or their role isn't detected,
        // these still need to be reachable so they can turn maintenance off.
        'system.index',
        'system.toggle-maintenance',
    ];

    public function handle(Request $request, Closure $next)
    {
        // 1. Never block webhooks, health probes, auth, or the toggle itself.
        if ($request->is(...$this->exemptPaths)
            || $request->routeIs(...$this->exemptRouteNames)) {
            return $next($request);
        }

        // 2. Maintenance off → nothing to do.
        if (! $this->isMaintenanceModeEnabled()) {
            return $next($request);
        }

        // 3. Maintenance on → let admins through.
        if ($this->hasAdminAccess($request)) {
            return $next($request);
        }

        // 4. Everyone else sees the maintenance page.
        return $this->maintenanceResponse();
    }

    protected function isMaintenanceModeEnabled(): bool
    {
        try {
            return SystemHelper::isMaintenanceMode();
        } catch (\Throwable $e) {
            // System table missing or DB down — fall back to config so we still serve the app.
            return (bool) config('app.maintenance_mode', false);
        }
    }

    protected function hasAdminAccess(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        // Spatie role check — most direct.
        if (method_exists($user, 'hasRole')) {
            try {
                return $user->hasRole('admin') || $user->hasRole('super-admin');
            } catch (\Throwable $e) {
                // Role doesn't exist — fall through to other checks.
            }
        }

        // Custom method on User model.
        if (method_exists($user, 'isAdmin')) {
            return (bool) $user->isAdmin();
        }

        // Permission-based fallback.
        if (method_exists($user, 'hasPermissionTo')) {
            try {
                return $user->hasPermissionTo('access_maintenance_mode');
            } catch (\Throwable $e) {
                // Permission not defined — fall through.
            }
        }

        // Last-resort email allowlist.
        return in_array($user->email, config('app.maintenance_allowed_emails', []), true);
    }

    protected function maintenanceResponse()
    {
        try {
            $data = [
                'title'         => SystemHelper::appName() . ' - Maintenance',
                'message'       => SystemHelper::get('maintenance_message'),
                'timezone'      => SystemHelper::timezone(),
                'contact_email' => SystemHelper::contactEmail(),
                'contact_phone' => SystemHelper::contactPhone(),
            ];
        } catch (\Throwable $e) {
            // SystemHelper failed — show a sane fallback so the user still gets a 503.
            $data = [
                'title'         => config('app.name', 'AiyoTicket') . ' - Maintenance',
                'message'       => 'We are performing scheduled maintenance. Please check back shortly.',
                'timezone'      => config('app.timezone'),
                'contact_email' => null,
                'contact_phone' => null,
            ];
        }

        return response()->view('maintenance', $data, 503);
    }
}