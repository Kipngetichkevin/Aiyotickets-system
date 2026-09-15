<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdminOrMerchant
{
    /**
     * Allow access only to users with 'admin' or 'merchant' role.
     * Everyone else → redirected to /dashboard with an error.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Not logged in → login
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin or merchant → allow
        if ($user->hasAnyRole(['admin', 'merchant'])) {
            return $next($request);
        }

        // Everything else → redirect to their dashboard
        return redirect()
            ->route('dashboard')
            ->with('error', 'You do not have permission to access the admin panel.');
    }
}