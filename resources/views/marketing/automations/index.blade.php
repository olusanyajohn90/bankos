<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Marketing Automations</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Automated workflows triggered by customer events</p>
            </div>
            <a href="{{ route('marketing.automations.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Automation
            </a>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-sm text-green-700 dark:text-green-400">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Trigger</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Enrolled</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Completed</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($automations as $auto)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3">
                            <div class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $auto->name }}</div>
                            @if($auto->description)
                            <div class="text-xs text-bankos-muted mt-0.5">{{ Str::limit($auto->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text dark:text-bankos-dark-text">
                                {{ str_replace('_', ' ', ucfirst($auto->trigger['type'] ?? 'unknown')) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right text-bankos-text dark:text-bankos-dark-text">{{ number_format($auto->enrolled_count) }}</td>
                        <td class="px-6 py-3 text-right text-bankos-text dark:text-bankos-dark-text">{{ number_format($auto->completed_count) }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $auto->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $auto->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('marketing.automations.logs', $auto->id) }}" class="text-bankos-primary hover:underline text-xs">Logs</a>
                                <form action="{{ route('marketing.automations.toggle', $auto->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white text-xs">
                                        {{ $auto->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-bankos-muted">No automations yet. Create your first workflow.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($automations->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $automations->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
