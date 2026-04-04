<x-app-layout>
    <x-slot name="header">Compliance Audit Trail</x-slot>

    <div class="space-y-6">

        {{-- Filters --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-4">
            <form method="GET" class="flex flex-wrap items-center gap-3">
                <select name="event_type" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Events</option>
                    <option value="breach" {{ request('event_type') === 'breach' ? 'selected' : '' }}>Breach</option>
                    <option value="warning" {{ request('event_type') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="check_passed" {{ request('event_type') === 'check_passed' ? 'selected' : '' }}>Check Passed</option>
                    <option value="evidence_added" {{ request('event_type') === 'evidence_added' ? 'selected' : '' }}>Evidence Added</option>
                    <option value="status_changed" {{ request('event_type') === 'status_changed' ? 'selected' : '' }}>Status Changed</option>
                    <option value="framework_scored" {{ request('event_type') === 'framework_scored' ? 'selected' : '' }}>Framework Scored</option>
                    <option value="checks_run" {{ request('event_type') === 'checks_run' ? 'selected' : '' }}>Checks Run</option>
                </select>
                <select name="entity_type" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                    <option value="">All Entities</option>
                    <option value="monitor" {{ request('entity_type') === 'monitor' ? 'selected' : '' }}>Monitor</option>
                    <option value="control" {{ request('entity_type') === 'control' ? 'selected' : '' }}>Control</option>
                    <option value="framework" {{ request('entity_type') === 'framework' ? 'selected' : '' }}>Framework</option>
                    <option value="system" {{ request('entity_type') === 'system' ? 'selected' : '' }}>System</option>
                </select>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border border-bankos-border dark:border-bankos-dark-border bg-white dark:bg-bankos-dark-bg text-sm px-3 py-2">
                <button type="submit" class="px-4 py-2 bg-bankos-primary text-white rounded-lg text-sm hover:bg-bankos-primary/90">Filter</button>
                <a href="{{ route('compliance-auto.audit-trail') }}" class="px-4 py-2 bg-gray-100 dark:bg-bankos-dark-bg text-bankos-text-sec rounded-lg text-sm hover:bg-gray-200">Reset</a>
            </form>
        </div>

        {{-- Timeline --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">
            <div class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                @forelse($trail as $entry)
                <div class="p-4 flex items-start gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-3 h-3 rounded-full flex-shrink-0
                            {{ $entry->event_type === 'breach' ? 'bg-red-500' :
                               ($entry->event_type === 'warning' ? 'bg-amber-500' :
                               ($entry->event_type === 'check_passed' ? 'bg-green-500' :
                               ($entry->event_type === 'evidence_added' ? 'bg-blue-500' :
                               ($entry->event_type === 'status_changed' ? 'bg-purple-500' :
                               ($entry->event_type === 'framework_scored' ? 'bg-indigo-500' :
                               ($entry->event_type === 'checks_run' ? 'bg-teal-500' : 'bg-gray-400')))))) }}"></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $entry->event_type === 'breach' ? 'bg-red-100 text-red-800' :
                                   ($entry->event_type === 'warning' ? 'bg-amber-100 text-amber-800' :
                                   ($entry->event_type === 'check_passed' ? 'bg-green-100 text-green-800' :
                                   ($entry->event_type === 'evidence_added' ? 'bg-blue-100 text-blue-800' :
                                   ($entry->event_type === 'status_changed' ? 'bg-purple-100 text-purple-800' :
                                   ($entry->event_type === 'framework_scored' ? 'bg-indigo-100 text-indigo-800' :
                                   'bg-gray-100 text-gray-800'))))) }}">
                                {{ str_replace('_', ' ', ucfirst($entry->event_type)) }}
                            </span>
                            <span class="text-xs text-bankos-muted">{{ $entry->entity_type }}</span>
                        </div>
                        <p class="text-sm text-bankos-text dark:text-bankos-dark-text mt-1">{{ $entry->description }}</p>
                        <p class="text-xs text-bankos-muted mt-1">{{ $entry->created_at->format('M d, Y H:i:s') }} ({{ $entry->created_at->diffForHumans() }})</p>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-bankos-muted">No audit trail entries found.</div>
                @endforelse
            </div>
            @if($trail->hasPages())
            <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $trail->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
