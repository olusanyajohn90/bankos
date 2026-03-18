<?php

namespace App\Services;

use App\Jobs\DeliverWebhook;
use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Supported webhook events.
     */
    public const EVENTS = [
        // Account lifecycle
        'account.opened',
        'account.frozen',
        'account.unfrozen',

        // Transfers
        'transfer.completed',
        'transfer.failed',

        // Loans
        'loan.applied',
        'loan.approved',
        'loan.disbursed',
        'loan.repayment',
        'loan.overdue',

        // KYC
        'kyc.submitted',
        'kyc.approved',
        'kyc.rejected',

        // Customers
        'customer.created',
        'customer.portal_activated',
    ];

    /**
     * Dispatch a webhook event to all active tenant endpoints subscribed to it.
     *
     * @param string $tenantId   The tenant UUID
     * @param string $event      One of WebhookService::EVENTS
     * @param array  $payload    The event data to deliver
     */
    public static function dispatch(string $tenantId, string $event, array $payload): void
    {
        // Enrich payload with standard metadata
        $payload = array_merge([
            'event'      => $event,
            'tenant_id'  => $tenantId,
            'timestamp'  => now()->toIso8601String(),
        ], $payload);

        // Find all active endpoints for this tenant that are subscribed to this event
        $endpoints = WebhookEndpoint::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereJsonContains('events', $event)
            ->get();

        if ($endpoints->isEmpty()) {
            return;
        }

        foreach ($endpoints as $endpoint) {
            DeliverWebhook::dispatch(
                $tenantId,
                $endpoint->id,
                $event,
                $payload
            );
        }

        Log::info('WebhookService: dispatched event to endpoints', [
            'tenant_id'      => $tenantId,
            'event'          => $event,
            'endpoint_count' => $endpoints->count(),
        ]);
    }

    /**
     * Sign a payload for webhook verification.
     *
     * Returns 'sha256=' . hash_hmac('sha256', json_encode($payload), $secret)
     *
     * Consumers verify by computing the same HMAC with their stored secret
     * and comparing it to the X-BankOS-Signature header.
     *
     * @param array  $payload  The payload that was delivered
     * @param string $secret   The endpoint's stored secret
     */
    public static function sign(array $payload, string $secret): string
    {
        return 'sha256=' . hash_hmac('sha256', json_encode($payload), $secret);
    }
}
