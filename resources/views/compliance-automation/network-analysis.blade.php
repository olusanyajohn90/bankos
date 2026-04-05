<x-app-layout>
    <x-slot name="header">Network / Link Analysis</x-slot>

    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Total Relationships</p>
                <p class="text-3xl font-bold text-bankos-text dark:text-bankos-dark-text mt-1">{{ $relationships->count() }}</p>
            </div>
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase">Suspicious Links</p>
                <p class="text-3xl font-bold text-red-600 mt-1">{{ $suspiciousCount }}</p>
            </div>
        </div>

        {{-- Relationships Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-4 py-3 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Entity Relationships</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Entity A</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Relationship</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Entity B</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Strength</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Txn Count</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Volume</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase">Suspicious</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($relationships as $r)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg {{ $r->is_suspicious ? 'bg-red-50 dark:bg-red-900/10' : '' }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-sm">{{ $customerNames[$r->entity_a_id] ?? Str::limit($r->entity_a_id, 8) }}</div>
                                <div class="text-xs text-bankos-muted">{{ $r->entity_a_type }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">{{ str_replace('_', ' ', $r->relationship_type) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-sm">{{ $customerNames[$r->entity_b_id] ?? Str::limit($r->entity_b_id, 8) }}</div>
                                <div class="text-xs text-bankos-muted">{{ $r->entity_b_type }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-12 bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $r->strength }}%"></div>
                                    </div>
                                    <span class="text-xs">{{ $r->strength }}%</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ number_format($r->transaction_count) }}</td>
                            <td class="px-4 py-3 font-mono text-xs">NGN {{ number_format($r->total_volume, 2) }}</td>
                            <td class="px-4 py-3">
                                @if($r->is_suspicious)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">SUSPICIOUS</span>
                                @else
                                <span class="text-xs text-bankos-muted">Normal</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-bankos-muted">No relationships found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
