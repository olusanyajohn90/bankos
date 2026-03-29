<?php

namespace App\Services\TransferProviders;

use App\Contracts\TransferProviderInterface;
use App\Models\TransferProvider;
use Illuminate\Database\Eloquent\Collection;

class TransferProviderManager
{
    private array $drivers = [];

    /**
     * Resolve the driver instance for a given TransferProvider model.
     */
    public function resolve(TransferProvider $provider): TransferProviderInterface
    {
        $class = $provider->provider_class;

        if (!isset($this->drivers[$class])) {
            $this->drivers[$class] = app($class);
        }

        return $this->drivers[$class];
    }

    /**
     * Get the default (or highest-priority active) provider for a tenant.
     */
    public function getDefaultProvider(string $tenantId): ?TransferProvider
    {
        return TransferProvider::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first()
            ?? TransferProvider::where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->orderByDesc('priority')
                ->first();
    }

    /**
     * Get all active providers for a tenant, ordered by priority.
     */
    public function getActiveProviders(string $tenantId): Collection
    {
        return TransferProvider::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get();
    }

    /**
     * Select the best provider for a given transfer amount.
     * Respects min/max amount limits and picks the highest-priority match.
     */
    public function selectProviderForAmount(string $tenantId, float $amount): ?TransferProvider
    {
        return TransferProvider::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            })
            ->where('min_amount', '<=', $amount)
            ->orderByDesc('priority')
            ->first();
    }
}
