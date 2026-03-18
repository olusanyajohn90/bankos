<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Financial Audit Log</h2>
                <p class="text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec mt-0.5">Immutable record of all financial actions and state changes.</p>
            </div>
            <a href="{{ route('audit-log.export', request()->query()) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </div>
    </x-slot>

    <div class="space-y-5">

        {{-- ── Filters ──────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-2xl border border-bankos-border dark:border-bankos-dark-border shadow-sm p-5">
            <form method="GET" action="{{ route('audit-log.index') }}" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 items-end">

                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Entity Type</label>
                    <select name="entity_type" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        @foreach($entityTypes as $type)
                            <option value="{{ $type }}" @selected(request('entity_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Action</label>
                    <select name="action" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Actor</label>
                    <select name="user_id" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Users</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected(request('user_id') === $u->id)>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-bankos-text-sec mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">Filter</button>
                    <a href="{{ route('audit-log.index') }}" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">Clear</a>
                </div>

            </form>
        </div>

        {{-- ── Table ────────────────────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-2xl border border-bankos-border dark:border-bankos-dark-border shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                <p class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">
                    {{ number_format($logs->total()) }} {{ Str::plural('entry', $logs->total()) }}
                </p>
            </div>

            @if($logs->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">No audit log entries found for the selected filters.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Timestamp</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Actor</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Entity Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Entity ID</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">Changes Summary</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-bankos-text-sec uppercase tracking-wider">IP Address</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                            @foreach($logs as $log)
                            @php
                                $actionColors = [
                                    'created'    => 'bg-blue-100 text-blue-700',
                                    'updated'    => 'bg-yellow-100 text-yellow-700',
                                    'deleted'    => 'bg-red-100 text-red-700',
                                    'approved'   => 'bg-green-100 text-green-700',
                                    'rejected'   => 'bg-red-100 text-red-700',
                                    'disbursed'  => 'bg-green-100 text-green-700',
                                    'reversed'   => 'bg-orange-100 text-orange-700',
                                    'frozen'     => 'bg-purple-100 text-purple-700',
                                    'unfrozen'   => 'bg-teal-100 text-teal-700',
                                    'exported'   => 'bg-gray-200 hover:bg-gray-300 text-gray-800',
                                ];
                                $color = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600';

                                $changes = [];
                                if (!empty($log->before_state) && !empty($log->after_state)) {
                                    foreach ($log->after_state as $key => $val) {
                                        $before = $log->before_state[$key] ?? null;
                                        if ($before !== null && (string)$before !== (string)$val) {
                                            $changes[] = "{$key}: {$before} → {$val}";
                                        }
                                    }
                                }
                            @endphp
                            <tr x-data="{ expanded: false }" class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-medium text-bankos-text dark:text-bankos-dark-text">{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y') }}</div>
                                    <div class="text-xs text-bankos-text-sec">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-xs font-medium text-bankos-text dark:text-bankos-dark-text">{{ $log->actor_name ?? 'System' }}</div>
                                    @if($log->actor_email)
                                        <div class="text-xs text-bankos-text-sec">{{ $log->actor_email }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-medium text-bankos-text dark:text-bankos-dark-text">{{ $log->entity_type }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono text-bankos-text-sec" title="{{ $log->entity_id }}">{{ substr($log->entity_id, 0, 8) }}…</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 max-w-[220px]">
                                    @if(count($changes) > 0)
                                        <div class="space-y-0.5">
                                            @foreach(array_slice($changes, 0, 2) as $change)
                                                <p class="text-xs text-bankos-text-sec truncate" title="{{ $change }}">{{ $change }}</p>
                                            @endforeach
                                            @if(count($changes) > 2)
                                                <p class="text-xs text-blue-500">+{{ count($changes) - 2 }} more</p>
                                            @endif
                                        </div>
                                    @elseif(!empty($log->after_state))
                                        <p class="text-xs text-bankos-text-sec italic">New record</p>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-mono text-bankos-text-sec">{{ $log->ip_address }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="expanded = !expanded" type="button" class="inline-flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 transition-transform" :class="expanded ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                        Details
                                    </button>
                                </td>
                            </tr>
                            <tr x-show="expanded" x-transition style="display:none;" class="bg-gray-50 dark:bg-gray-800/50">
                                <td colspan="8" class="px-6 py-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @if(!empty($log->before_state))
                                        <div>
                                            <p class="text-xs font-semibold text-red-600 uppercase mb-2">Before</p>
                                            <pre class="text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 overflow-auto max-h-48 text-gray-700 dark:text-gray-300">{{ json_encode($log->before_state, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                        @endif
                                        @if(!empty($log->after_state))
                                        <div>
                                            <p class="text-xs font-semibold text-green-600 uppercase mb-2">After</p>
                                            <pre class="text-xs bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-3 overflow-auto max-h-48 text-gray-700 dark:text-gray-300">{{ json_encode($log->after_state, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                        @endif
                                    </div>
                                    @if($log->request_url)
                                        <p class="mt-3 text-xs text-gray-500"><span class="font-semibold">URL:</span> {{ $log->request_url }}</p>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                <div class="px-5 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    {{ $logs->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
