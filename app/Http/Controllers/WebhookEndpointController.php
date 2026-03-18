<?php

namespace App\Http\Controllers;

use App\Models\WebhookDeliveryLog;
use App\Models\WebhookEndpoint;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    /**
     * List the current tenant's webhook endpoints.
     */
    public function index()
    {
        $tenantId  = auth()->user()->tenant_id;
        $endpoints = WebhookEndpoint::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();

        return view('webhooks.index', [
            'endpoints'      => $endpoints,
            'availableEvents'=> WebhookService::EVENTS,
        ]);
    }

    /**
     * Create a new webhook endpoint.
     */
    public function store(Request $r)
    {
        $r->validate([
            'url'    => ['required', 'url', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['in:' . implode(',', WebhookService::EVENTS)],
        ]);

        $tenantId = auth()->user()->tenant_id;

        $endpoint = WebhookEndpoint::create([
            'id'        => Str::uuid()->toString(),
            'tenant_id' => $tenantId,
            'url'       => $r->url,
            'secret'    => Str::random(32),
            'events'    => $r->events,
            'is_active' => true,
        ]);

        // Return the secret once — it is hidden in all subsequent responses
        if ($r->wantsJson()) {
            return response()->json([
                'message'  => 'Webhook endpoint created.',
                'endpoint' => $endpoint->makeVisible('secret'),
            ], 201);
        }

        return redirect()->route('webhooks.index')
            ->with('success', 'Webhook endpoint created.')
            ->with('new_secret', $endpoint->secret)
            ->with('new_endpoint_id', $endpoint->id);
    }

    /**
     * Delete a webhook endpoint.
     */
    public function destroy(string $id)
    {
        $endpoint = $this->resolveEndpoint($id);
        $endpoint->delete();

        if (request()->wantsJson()) {
            return response()->json(['message' => 'Webhook endpoint deleted.']);
        }

        return redirect()->route('webhooks.index')
            ->with('success', 'Webhook endpoint deleted.');
    }

    /**
     * Toggle a webhook endpoint active/inactive.
     */
    public function toggle(string $id)
    {
        $endpoint = $this->resolveEndpoint($id);
        $endpoint->update(['is_active' => !$endpoint->is_active]);

        if (request()->wantsJson()) {
            return response()->json([
                'is_active' => $endpoint->is_active,
                'message'   => $endpoint->is_active ? 'Endpoint enabled.' : 'Endpoint disabled.',
            ]);
        }

        return redirect()->route('webhooks.index')
            ->with('success', $endpoint->is_active ? 'Endpoint enabled.' : 'Endpoint disabled.');
    }

    /**
     * Delivery logs for an endpoint (last 20).
     */
    public function logs(string $id)
    {
        $endpoint = $this->resolveEndpoint($id);

        $logs = WebhookDeliveryLog::where('endpoint_id', $endpoint->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        if (request()->wantsJson()) {
            return response()->json(['logs' => $logs]);
        }

        return view('webhooks.logs', [
            'endpoint' => $endpoint,
            'logs'     => $logs,
        ]);
    }

    /**
     * Resolve an endpoint belonging to the current tenant or abort 404.
     */
    private function resolveEndpoint(string $id): WebhookEndpoint
    {
        return WebhookEndpoint::where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->firstOrFail();
    }
}
