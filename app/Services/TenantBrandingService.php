<?php

namespace App\Services;

use App\Models\Tenant;

class TenantBrandingService
{
    /**
     * Returns inline CSS variables for the tenant's colors.
     */
    public static function getCss(Tenant $tenant): string
    {
        $primary   = $tenant->primary_color   ?? '#2563eb';
        $secondary = $tenant->secondary_color ?? '#0c2461';

        return ":root { --primary: {$primary}; --secondary: {$secondary}; }";
    }

    /**
     * Returns the logo URL or default bankOS logo.
     */
    public static function getLogoUrl(Tenant $tenant): string
    {
        if ($tenant->logo_path) {
            return asset('storage/' . $tenant->logo_path);
        }

        return asset('images/bankos-logo.png');
    }

    /**
     * Replaces {BANK_NAME}, {PRIMARY_COLOR}, {LOGO_URL} placeholders in HTML.
     */
    public static function injectBranding(string $html, Tenant $tenant): string
    {
        $replacements = [
            '{BANK_NAME}'     => e($tenant->name),
            '{PRIMARY_COLOR}' => e($tenant->primary_color   ?? '#2563eb'),
            '{LOGO_URL}'      => e(static::getLogoUrl($tenant)),
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $html
        );
    }

    /**
     * Build the branding array shared with all views.
     */
    public static function brandingArray(Tenant $tenant): array
    {
        return [
            'primary_color'   => $tenant->primary_color   ?? '#2563eb',
            'secondary_color' => $tenant->secondary_color ?? '#0c2461',
            'logo_path'       => $tenant->logo_path,
            'logo_url'        => static::getLogoUrl($tenant),
            'name'            => $tenant->name,
        ];
    }
}
