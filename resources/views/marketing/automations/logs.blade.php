<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.automations') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $automation->name }} - Logs</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">
                    Trigger: {{ str_replace('_', ' ', ucfirst($automation->trigger['type'] ?? 'unknown')) }}
                    | Enrolled: {{ number_format($automation->enrolled_count) }}
                    | Completed: {{ number_format($automation->completed_count) }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Customer</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Step</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Action</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Scheduled</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Executed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Result</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($logs as $log)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $log->customer?->first_name }} {{ $log->customer?->last_name }}
                        </td>
                        <td class="px-6 py-3 text-center text-bankos-text dark:text-bankos-dark-text">{{ $log->step_index + 1 }}</td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">
                                {{ str_replace('_', ' ', ucfirst($log->action_type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            @php
                                $statusColors = [
                                    'pending'   => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'failed'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'skipped'   => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusColors[$log->status] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec text-xs">{{ $log->scheduled_at?->format('M d, H:i') ?? '-' }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec text-xs">{{ $log->executed_at?->format('M d, H:i') ?? '-' }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec text-xs">{{ $log->result ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-bankos-muted">No execution logs yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
