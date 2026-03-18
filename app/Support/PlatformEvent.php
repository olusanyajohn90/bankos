<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlatformEvent
{
    public static function record(
        string $tenantId,
        string $eventType,
        string $entityType,
        string $entityId,
        string $actorType = 'system',
        ?string $actorId = null,
        array $metadata = [],
        ?float $amount = null
    ): void {
        DB::table('platform_events')->insert([
            'id'          => (string) Str::uuid(),
            'tenant_id'   => $tenantId,
            'event_type'  => $eventType,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'actor_type'  => $actorType,
            'actor_id'    => $actorId,
            'metadata'    => json_encode($metadata),
            'amount'      => $amount,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
