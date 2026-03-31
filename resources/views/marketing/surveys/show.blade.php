<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('marketing.surveys') }}" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $survey->title }}</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $survey->type === 'nps' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' : ($survey->type === 'csat' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400') }}">{{ strtoupper($survey->type) }}</span>
                    Created by {{ $survey->createdBy?->name ?? 'System' }} on {{ $survey->created_at->format('M d, Y') }}
                </p>
            </div>
        </div>
    </x-slot>

    {{-- NPS Score Gauge --}}
    @if($survey->type === 'nps' && $npsBreakdown)
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5 text-center">
            <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec mb-1">NPS Score</p>
            <p class="text-4xl font-bold {{ $npsBreakdown['npsScore'] >= 50 ? 'text-green-600' : ($npsBreakdown['npsScore'] >= 0 ? 'text-yellow-600' : 'text-red-600') }}">
                {{ $npsBreakdown['npsScore'] }}
            </p>
            <p class="text-xs text-bankos-muted mt-1">{{ $npsBreakdown['total'] }} responses</p>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec mb-1">Promoters (9-10)</p>
            <p class="text-2xl font-bold text-green-600">{{ $npsBreakdown['promoters'] }}</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $npsBreakdown['total'] > 0 ? round(($npsBreakdown['promoters'] / $npsBreakdown['total']) * 100) : 0 }}%"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec mb-1">Passives (7-8)</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $npsBreakdown['passives'] }}</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $npsBreakdown['total'] > 0 ? round(($npsBreakdown['passives'] / $npsBreakdown['total']) * 100) : 0 }}%"></div>
            </div>
        </div>
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
            <p class="text-xs text-bankos-muted dark:text-bankos-dark-text-sec mb-1">Detractors (0-6)</p>
            <p class="text-2xl font-bold text-red-600">{{ $npsBreakdown['detractors'] }}</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $npsBreakdown['total'] > 0 ? round(($npsBreakdown['detractors'] / $npsBreakdown['total']) * 100) : 0 }}%"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Questions --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-6 mb-6">
        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4">Questions ({{ count($survey->questions ?? []) }})</h3>
        <div class="space-y-3">
            @foreach($survey->questions ?? [] as $i => $q)
            <div class="flex items-start gap-3 p-3 bg-bankos-bg dark:bg-bankos-dark-bg rounded-lg">
                <span class="text-xs font-bold text-bankos-muted mt-0.5">{{ $i + 1 }}.</span>
                <div>
                    <p class="text-sm text-bankos-text dark:text-bankos-dark-text">{{ $q['text'] ?? '' }}</p>
                    <span class="text-xs text-bankos-muted">{{ ucfirst(str_replace('_', ' ', $q['type'] ?? 'text')) }}</span>
                    @if(!empty($q['options']))
                    <div class="mt-1 flex flex-wrap gap-1">
                        @foreach($q['options'] as $opt)
                        <span class="text-xs px-2 py-0.5 bg-white dark:bg-bankos-dark-surface rounded border border-bankos-border dark:border-bankos-dark-border">{{ $opt }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Responses --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Responses</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-bankos-bg dark:bg-bankos-dark-bg">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Customer</th>
                        @if($survey->type === 'nps')
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">NPS Score</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Feedback</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($responses as $resp)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $resp->customer?->first_name }} {{ $resp->customer?->last_name }}
                        </td>
                        @if($survey->type === 'nps')
                        <td class="px-6 py-3 text-right">
                            @php
                                $scoreColor = ($resp->nps_score >= 9) ? 'text-green-600' : (($resp->nps_score >= 7) ? 'text-yellow-600' : 'text-red-600');
                            @endphp
                            <span class="font-bold {{ $scoreColor }}">{{ $resp->nps_score }}</span>
                        </td>
                        @endif
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec">
                            {{ Str::limit($resp->feedback, 80) ?: '-' }}
                        </td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec">{{ $resp->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $survey->type === 'nps' ? 4 : 3 }}" class="px-6 py-8 text-center text-bankos-muted">No responses yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($responses->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $responses->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
