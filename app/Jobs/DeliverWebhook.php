<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DeliverWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 5;

    /**
     * The number of seconds to wait before retrying the job.
     * Progressive backoff: 30s, 1m, 2m, 5m, 10m
     */
    public array $backoff = [30, 60, 120, 300, 600];

    public function __construct(
        public readonly string $tenantId,
        public readonly string $webhookEndpointId,
        public readonly string $event,
        public readonly array $payload,
    ) {
        $this->onQueue('webhooks');
    }

    /**
     * Execute the job.
     * POSTs the payload to the webhook URL with HMAC-SHA256 signature header.
     * Retries on 5xx or network errors.
     */
    public function handle(): void
    {
        $endpoint = WebhookEndpoint::find($this->webhookEndpointId);

        if (!$endpoint || !$endpoint->is_active) {
            Log::info('DeliverWebhook: endpoint not found or inactive, skipping', [
                'endpoint_id' => $this->webhookEndpointId,
                'event'       => $this->event,
            ]);
            return;
        }

        $signature = WebhookService::sign($this->payload, $endpoint->secret);
        $startedAt = now();

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type'            => 'application/json',
                    'X-BankOS-Signature'      => $signature,
                    'X-BankOS-Event'          => $this->event,
                    'X-BankOS-Delivery'       => $this->job?->getJobId() ?? uniqid(),
                    'User-Agent'              => 'bankOS-Webhooks/1.0',
                ])
                ->post($endpoint->url, $this->payload);

            $responseCode = $response->status();
            $responseBody = substr($response->body(), 0, 2000);
            $delivered    = $response->successful();

            // Update endpoint metadata
            $endpoint->update([
                'last_triggered_at' => now(),
                'failure_count'     => $delivered ? 0 : DB::raw('failure_count + 1'),
            ]);

            // Log delivery
            DB::table('webhook_delivery_logs')->insert([
                'tenant_id'     => $this->tenantId,
                'endpoint_id'   => $this->webhookEndpointId,
                'event'         => $this->event,
                'payload'       => json_encode($this->payload),
                'response_code' => $responseCode,
                'response_body' => $responseBody,
                'attempt_count' => $this->attempts(),
                'delivered_at'  => $delivered ? now() : null,
                'failed_at'     => !$delivered ? now() : null,
                'created_at'    => $startedAt,
            ]);

            if (!$delivered) {
                Log::warning('DeliverWebhook: non-2xx response', [
                    'endpoint_id'   => $this->webhookEndpointId,
                    'event'         => $this->event,
                    'response_code' => $responseCode,
                ]);

                // Retry on 5xx errors
                if ($responseCode >= 500) {
                    $this->release($this->backoff[$this->attempts() - 1] ?? 600);
                }
            }
        } catch (\Throwable $e) {
            Log::error('DeliverWebhook: network/exception error', [
                'endpoint_id' => $this->webhookEndpointId,
                'event'       => $this->event,
                'error'       => $e->getMessage(),
            ]);

            // Log failed attempt
            DB::table('webhook_delivery_logs')->insert([
                'tenant_id'     => $this->tenantId,
                'endpoint_id'   => $this->webhookEndpointId,
                'event'         => $this->event,
                'payload'       => json_encode($this->payload),
                'response_code' => null,
                'response_body' => $e->getMessage(),
                'attempt_count' => $this->attempts(),
                'delivered_at'  => null,
                'failed_at'     => now(),
                'created_at'    => $startedAt,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('DeliverWebhook: all retries exhausted', [
            'tenant_id'   => $this->tenantId,
            'endpoint_id' => $this->webhookEndpointId,
            'event'       => $this->event,
            'error'       => $exception->getMessage(),
        ]);

        // Increment failure count on the endpoint
        WebhookEndpoint::where('id', $this->webhookEndpointId)
            ->increment('failure_count');
    }
}
