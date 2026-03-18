<?php

namespace App\Http\Middleware;

use App\Services\TenantBrandingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->tenant_id) {
            // Ensure tenant_id is always in session so all controllers can rely on session('tenant_id')
            if (!session()->has('tenant_id')) {
                session(['tenant_id' => auth()->user()->tenant_id]);
            }

            $tenant = auth()->user()->tenant;

            // Store tenant context for use throughout the request
            app()->instance('current_tenant', $tenant);

            // Share branding variables with all views
            if ($tenant) {
                view()->share('tenantBranding', [
                    'primary_color'   => $tenant->primary_color   ?? '#2563eb',
                    'secondary_color' => $tenant->secondary_color ?? '#0c2461',
                    'logo_path'       => $tenant->logo_path,
                    'logo_url'        => TenantBrandingService::getLogoUrl($tenant),
                    'name'            => $tenant->name,
                ]);
            }
        }

        // Provide a safe default for views that render without a tenant context
        if (!view()->shared('tenantBranding')) {
            view()->share('tenantBranding', [
                'primary_color'   => '#2563eb',
                'secondary_color' => '#0c2461',
                'logo_path'       => null,
                'logo_url'        => asset('images/bankos-logo.png'),
                'name'            => config('app.name', 'bankOS'),
            ]);
        }

        return $next($request);
    }
}
