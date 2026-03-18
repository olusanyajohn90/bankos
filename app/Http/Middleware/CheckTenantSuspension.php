<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantSuspension
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        // Super admins bypass this check entirely
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        // Allow access to the suspended page itself to avoid redirect loops
        if ($request->routeIs('tenant.suspended')) {
            return $next($request);
        }

        // Check if the tenant is suspended
        if ($user->tenant_id) {
            $tenant = $user->tenant;

            if ($tenant && $tenant->suspended_at) {
                return redirect()->route('tenant.suspended');
            }
        }

        return $next($request);
    }
}
