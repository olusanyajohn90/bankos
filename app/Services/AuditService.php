<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditService
{
    /**
     * Record a financial audit event.
     *
     * @param  string      $tenantId
     * @param  string      $entityType   e.g. Account|Loan|Transaction|Customer|FeatureFlag|FeeRule|Limit
     * @param  string      $entityId     UUID of the affected record
     * @param  string      $action       e.g. created|updated|deleted|approved|rejected|disbursed|frozen|unfrozen|reversed|exported
     * @param  array       $beforeState  Relevant fields before the change
     * @param  array       $afterState   Relevant fields after the change
     * @param  string|null $customerId   Customer ID if the action originates from the portal
     * @param  array       $metadata     Any extra context (e.g. reason, reference)
     */
    public static function log(
        string $tenantId,
        string $entityType,
        string $entityId,
        string $action,
        array $beforeState = [],
        array $afterState = [],
        ?string $customerId = null,
        array $metadata = []
    ): void {
        $userId = null;

        // Prefer admin user, fall back to portal customer
        if (auth()->check()) {
            $userId = auth()->id();
        } elseif (auth('customer')->check()) {
            $customerId = $customerId ?? auth('customer')->id();
        }

        $request = request();

        DB::table('financial_audit_log')->insert([
            'id'          => (string) Str::uuid(),
            'tenant_id'   => $tenantId,
            'user_id'     => $userId,
            'customer_id' => $customerId,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'action'      => $action,
            'before_state' => $beforeState ? json_encode($beforeState) : null,
            'after_state'  => $afterState  ? json_encode($afterState)  : null,
            'ip_address'  => $request ? ($request->ip() ?? '0.0.0.0') : '0.0.0.0',
            'user_agent'  => $request ? $request->userAgent() : null,
            'request_url' => $request ? substr((string) $request->fullUrl(), 0, 500) : null,
            'metadata'    => $metadata ? json_encode($metadata) : null,
            'created_at'  => now(),
        ]);
    }
}
