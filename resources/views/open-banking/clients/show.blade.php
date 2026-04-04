<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $client->name }}</h2>
                <p class="text-sm text-bankos-text-sec mt-1">API Client Details</p>
            </div>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('open-banking.clients.toggle', $client->id) }}">@csrf @method('PATCH')
                    <button type="submit" class="btn {{ $client->is_active ? 'btn-outline text-red-600' : 'btn-primary' }} text-sm">{{ $client->is_active ? 'Deactivate' : 'Activate' }}</button>
                </form>
                <a href="{{ route('open-banking.clients') }}" class="btn btn-outline text-sm">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 card p-6">
            <h3 class="text-lg font-semibold mb-4">Client Info</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-bankos-muted">Client ID</dt><dd class="font-mono text-xs break-all">{{ $client->client_id }}</dd></div>
                <div><dt class="text-bankos-muted">Status</dt><dd><span class="badge {{ $client->is_active ? 'badge-green' : 'badge-red' }}">{{ $client->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
                <div><dt class="text-bankos-muted">Total Requests</dt><dd class="font-bold">{{ number_format($client->total_requests) }}</dd></div>
                <div><dt class="text-bankos-muted">Rate Limit</dt><dd>{{ $client->rate_limit_per_minute }}/min</dd></div>
                <div><dt class="text-bankos-muted">Webhook URL</dt><dd class="text-xs break-all">{{ $client->webhook_url ?? 'Not set' }}</dd></div>
                <div><dt class="text-bankos-muted">Last Request</dt><dd>{{ $client->last_request_at ? $client->last_request_at->format('d M Y H:i') : 'Never' }}</dd></div>
                <div><dt class="text-bankos-muted">Scopes</dt><dd>@if($client->allowed_scopes)@foreach($client->allowed_scopes as $s)<span class="badge badge-blue mr-1 mb-1">{{ $s }}</span>@endforeach @else N/A @endif</dd></div>
                <div><dt class="text-bankos-muted">Created by</dt><dd>{{ $client->creator->name ?? 'N/A' }}</dd></div>
            </dl>
        </div>
        <div class="card p-6">
            <h3 class="text-lg font-semibold mb-4">Usage (7 days)</h3>
            <canvas id="usageChart" height="200"></canvas>
        </div>
    </div>

    <div class="card overflow-hidden">
        <h3 class="p-4 text-lg font-semibold border-b border-bankos-border dark:border-bankos-dark-border">Recent Request Logs</h3>
        <table class="bankos-table w-full text-sm">
            <thead><tr><th>Time</th><th>Method</th><th>Endpoint</th><th>Status</th><th>Response Time</th><th>IP</th></tr></thead>
            <tbody>
            @forelse($recentLogs as $log)
                <tr>
                    <td>{{ $log->created_at->format('d M H:i:s') }}</td>
                    <td><span class="badge badge-blue">{{ $log->method }}</span></td>
                    <td class="font-mono text-xs">{{ Str::limit($log->endpoint, 50) }}</td>
                    <td><span class="badge {{ $log->status_code < 400 ? 'badge-green' : 'badge-red' }}">{{ $log->status_code }}</span></td>
                    <td>{{ $log->response_time_ms }}ms</td>
                    <td>{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-4 text-bankos-muted">No logs yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new Chart(document.getElementById('usageChart'), {
            type: 'bar',
            data: { labels: @json($dailyUsage->pluck('date')), datasets: [{ label: 'Requests', data: @json($dailyUsage->pluck('count')), backgroundColor: 'rgba(59,130,246,0.7)', borderRadius: 4 }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    });
    </script>
</x-app-layout>
