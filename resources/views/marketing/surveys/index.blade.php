<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Surveys & Feedback</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">Collect customer feedback with NPS, CSAT, and custom surveys</p>
            </div>
            <a href="{{ route('marketing.surveys.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Survey
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Questions</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Responses</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Avg Score</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($surveys as $survey)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                            <a href="{{ route('marketing.surveys.show', $survey->id) }}" class="hover:text-bankos-primary">{{ $survey->title }}</a>
                        </td>
                        <td class="px-6 py-3">
                            @php
                                $stColors = [
                                    'nps'    => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'csat'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'custom' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $stColors[$survey->type] ?? '' }}">{{ strtoupper($survey->type) }}</span>
                        </td>
                        <td class="px-6 py-3 text-right text-bankos-text dark:text-bankos-dark-text">{{ count($survey->questions ?? []) }}</td>
                        <td class="px-6 py-3 text-right text-bankos-text dark:text-bankos-dark-text">{{ $survey->responses_count }}</td>
                        <td class="px-6 py-3 text-right text-bankos-text dark:text-bankos-dark-text">{{ $survey->average_score !== null ? number_format($survey->average_score, 1) : '-' }}</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $survey->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
                                {{ $survey->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('marketing.surveys.show', $survey->id) }}" class="text-bankos-primary hover:underline text-xs">View</a>
                                <form action="{{ route('marketing.surveys.toggle', $survey->id) }}" method="POST" class="inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white text-xs">
                                        {{ $survey->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-bankos-muted">No surveys yet. Create your first survey.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($surveys->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $surveys->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
