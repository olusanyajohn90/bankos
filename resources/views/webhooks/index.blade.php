<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Webhook Endpoints</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Receive real-time event notifications from bankOS</p>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Add Endpoint --}}
        <div class="card">
            <h3 class="font-bold mb-4">Register Endpoint</h3>
            <form method="POST" action="{{ route('webhooks.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">URL *</label>
                    <input type="url" name="url" placeholder="https://yourapp.com/webhook" class="input w-full" required>
                    <x-input-error :messages="$errors->get('url')" class="mt-1" />
                </div>
                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Description</label>
                    <input type="text" name="description" placeholder="Production webhook" class="input w-full">
                </div>
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-500 mb-2">Events to Subscribe</label>
                    @foreach(['transaction.completed', 'loan.disbursed', 'loan.repaid', 'loan.overdue', 'account.frozen', 'customer.created', 'aml.alert'] as $event)
                    <label class="flex items-center gap-2 text-sm mb-1">
                        <input type="checkbox" name="events[]" value="{{ $event }}" class="rounded"> {{ $event }}
                    </label>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary w-full">Register</button>
            </form>
        </div>

        {{-- Endpoints list --}}
        <div class="lg:col-span-2 space-y-4">
            @forelse($endpoints as $endpoint)
            <div class="card">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <p class="font-semibold text-sm font-mono text-blue-700 break-all">{{ $endpoint->url }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $endpoint->description }}</p>
                    </div>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $endpoint->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $endpoint->is_active ? 'Active' : 'Paused' }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-1 mb-3">
                    @foreach(json_decode($endpoint->events ?? '[]') as $ev)
                    <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded-full font-mono">{{ $ev }}</span>
                    @endforeach
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('webhooks.toggle', $endpoint->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm {{ $endpoint->is_active ? 'bg-yellow-50 text-yellow-700 hover:bg-yellow-100' : 'btn-primary' }}">
                            {{ $endpoint->is_active ? 'Pause' : 'Resume' }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('webhooks.destroy', $endpoint->id) }}" onsubmit="return confirm('Delete webhook?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm bg-red-50 text-red-600 hover:bg-red-100">Delete</button>
                    </form>
                    <span class="text-xs text-gray-400">{{ $endpoint->delivery_count ?? 0 }} deliveries · Last: {{ $endpoint->last_triggered_at ? \Carbon\Carbon::parse($endpoint->last_triggered_at)->diffForHumans() : 'Never' }}</span>
                </div>
            </div>
            @empty
            <div class="card text-center text-gray-400 py-12">
                <p class="font-medium mb-1">No webhook endpoints yet</p>
                <p class="text-sm">Register an endpoint to start receiving events.</p>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
