<x-app-layout>
    <x-slot name="header">Autonomous Compliance Agents</x-slot>

    <div class="space-y-6">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Agent Types --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($agentTypes as $type => $info)
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h4 class="font-semibold text-sm text-bankos-text dark:text-bankos-dark-text">{{ $info['name'] }}</h4>
                <p class="text-xs text-bankos-muted mt-1 mb-3">{{ $info['description'] }}</p>
                <form method="POST" action="{{ route('compliance-auto.agents.run') }}">
                    @csrf
                    <input type="hidden" name="agent_type" value="{{ $type }}">
                    <button type="submit" class="w-full px-3 py-2 bg-bankos-primary text-white rounded-lg text-xs hover:bg-bankos-primary/90">Run Agent</button>
                </form>
            </div>
            @endforeach
        </div>

        {{-- Task History --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Agent Task History</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Agent Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Description</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Items Processed</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Issues Found</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Duration</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($tasks as $t)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ ucfirst(str_replace('_', ' ', $t->agent_type)) }}</span></td>
                            <td class="px-4 py-3 text-xs max-w-xs truncate">{{ $t->description }}</td>
                            <td class="px-4 py-3">
                                @php $stc = match($t->status) { 'completed' => 'bg-green-100 text-green-700', 'running' => 'bg-blue-100 text-blue-700', 'failed' => 'bg-red-100 text-red-700', default => 'bg-gray-100 text-gray-700' }; @endphp
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $stc }}">{{ strtoupper($t->status) }}</span>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ number_format($t->items_processed) }}</td>
                            <td class="px-4 py-3">
                                @if($t->issues_found > 0)
                                <span class="font-mono text-xs text-red-600 font-bold">{{ $t->issues_found }}</span>
                                @else
                                <span class="text-xs text-bankos-muted">0</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">
                                @if($t->started_at && $t->completed_at)
                                {{ $t->started_at->diffForHumans($t->completed_at, true) }}
                                @else
                                -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">{{ $t->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-bankos-muted">No agent tasks run yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
