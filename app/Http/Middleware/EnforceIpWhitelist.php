<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnforceIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        // Super admins bypass IP check
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $tenantId = $user->tenant_id;

        if (!$tenantId) {
            return $next($request);
        }

        // Check if tenant has any active whitelist entries (opt-in feature)
        $whitelistCount = DB::table('tenant_ip_whitelist')
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        if ($whitelistCount === 0) {
            return $next($request);
        }

        $clientIp = $request->ip();

        $allowed = DB::table('tenant_ip_whitelist')
            ->where('tenant_id', $tenantId)
            ->where('ip_address', $clientIp)
            ->where('is_active', true)
            ->exists();

        if (!$allowed) {
            return response()->view('errors.ip-blocked', ['ip' => $clientIp], 403);
        }

        return $next($request);
    }
}
