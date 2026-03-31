<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Customer Segments</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Define and manage customer segments for targeted campaigns</p>
            </div>
            <a href="{{ route('marketing.segments.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Segment
            </a>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Description</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Rules</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Customers</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Type</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Campaigns</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($segments as $segment)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $segment->name }}</td>
                        <td class="px-6 py-3 text-bankos-muted max-w-xs truncate">{{ $segment->description ?? '-' }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">
                                {{ count($segment->rules ?? []) }} {{ Str::plural('rule', count($segment->rules ?? [])) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right font-semibold text-bankos-text dark:text-bankos-dark-text">
                            {{ number_format($segment->cached_count) }}
                            @if($segment->count_computed_at)
                            <span class="text-xs text-bankos-muted block">{{ $segment->count_computed_at->diffForHumans() }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($segment->is_system)
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400">System</span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">Custom</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center text-bankos-muted">{{ $segment->campaigns_count }}</td>
                        <td class="px-6 py-3 text-right">
                            @unless($segment->is_system)
                            <form action="{{ route('marketing.segments.delete', $segment->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this segment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 dark:text-red-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                            @endunless
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-2">No segments defined yet.</p>
                            <a href="{{ route('marketing.segments.create') }}" class="text-bankos-primary hover:underline">Create your first segment</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($segments->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $segments->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
