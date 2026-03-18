<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiTenantScope
{
    /**
     * Resolve tenant from:
     *   1. X-Tenant-ID header (primary)
     *   2. Authenticated user's tenant_id (fallback)
     *
     * Aborts with 401 if no tenant can be resolved.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');

        // Fallback: derive from authenticated user (customer or staff)
        if (!$tenantId && $request->user()) {
            $tenantId = $request->user()->tenant_id ?? null;
        }

        if (!$tenantId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Tenant context could not be resolved. Provide X-Tenant-ID header.',
            ], 401);
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid tenant.',
            ], 401);
        }

        // Bind tenant to service container so controllers/models can access it
        app()->instance('current_tenant', $tenant);

        // Share tenant_id on the request for convenience
        $request->attributes->set('tenant_id', $tenantId);

        return $next($request);
    }
}
