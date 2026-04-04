<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">API Clients</h2></div>
            <a href="{{ route('open-banking.clients.create') }}" class="btn btn-primary text-sm">New Client</a>
        </div>
    </x-slot>

    <div class="card overflow-hidden">
        <table class="bankos-table w-full">
            <thead><tr><th>Name</th><th>Client ID</th><th>Requests</th><th>Rate Limit</th><th>Status</th><th>Last Request</th><th></th></tr></thead>
            <tbody>
            @forelse($clients as $c)
                <tr>
                    <td class="font-medium">{{ $c->name }}</td>
                    <td class="font-mono text-xs">{{ Str::limit($c->client_id, 20) }}</td>
                    <td>{{ number_format($c->total_requests) }}</td>
                    <td>{{ $c->rate_limit_per_minute }}/min</td>
                    <td><span class="badge {{ $c->is_active ? 'badge-green' : 'badge-red' }}">{{ $c->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>{{ $c->last_request_at ? $c->last_request_at->diffForHumans() : 'Never' }}</td>
                    <td><a href="{{ route('open-banking.clients.show', $c->id) }}" class="text-bankos-primary text-sm hover:underline">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-8 text-bankos-muted">No API clients.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $clients->links() }}</div>
</x-app-layout>
