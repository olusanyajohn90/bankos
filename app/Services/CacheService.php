<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CacheService
{
    // Cache TTLs
    const BANK_LIST_TTL       = 86400;  // 24h
    const EXCHANGE_RATE_TTL   = 3600;   // 1h
    const FEATURE_FLAGS_TTL   = 300;    // 5m
    const LOAN_PRODUCTS_TTL   = 600;    // 10m

    /**
     * Get Nigerian bank list (cached).
     */
    public static function bankList(): array
    {
        return Cache::remember('bank_list', self::BANK_LIST_TTL, function () {
            $banks = DB::table('banks')->orderBy('name')->get(['code', 'name'])->toArray();
            return array_map(fn($b) => (array) $b, $banks);
        });
    }

    /**
     * Get exchange rates for a tenant (cached per tenant).
     */
    public static function exchangeRates(int $tenantId): array
    {
        return Cache::remember("exchange_rates:{$tenantId}", self::EXCHANGE_RATE_TTL, function () use ($tenantId) {
            return DB::table('exchange_rates')
                ->where('tenant_id', $tenantId)
                ->get(['from_currency', 'to_currency', 'rate', 'updated_at'])
                ->keyBy('from_currency')
                ->toArray();
        });
    }

    /**
     * Get feature flags for a tenant (cached).
     */
    public static function featureFlags(int $tenantId): array
    {
        return Cache::remember("feature_flags:{$tenantId}", self::FEATURE_FLAGS_TTL, function () use ($tenantId) {
            return DB::table('tenant_feature_flags')
                ->where('tenant_id', $tenantId)
                ->whereNull('customer_id')
                ->pluck('is_enabled', 'feature_key')
                ->toArray();
        });
    }

    /**
     * Get loan products for a tenant (cached).
     */
    public static function loanProducts(int $tenantId): array
    {
        return Cache::remember("loan_products:{$tenantId}", self::LOAN_PRODUCTS_TTL, function () use ($tenantId) {
            return DB::table('loan_products')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->get()
                ->toArray();
        });
    }

    /**
     * Invalidate feature flags cache for a tenant.
     */
    public static function clearFeatureFlags(int $tenantId): void
    {
        Cache::forget("feature_flags:{$tenantId}");
    }

    /**
     * Invalidate exchange rates cache for a tenant.
     */
    public static function clearExchangeRates(int $tenantId): void
    {
        Cache::forget("exchange_rates:{$tenantId}");
    }

    /**
     * Invalidate all caches for a tenant.
     */
    public static function clearTenant(int $tenantId): void
    {
        Cache::forget("feature_flags:{$tenantId}");
        Cache::forget("exchange_rates:{$tenantId}");
        Cache::forget("loan_products:{$tenantId}");
    }
}
