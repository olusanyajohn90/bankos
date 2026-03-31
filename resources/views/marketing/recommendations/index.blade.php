<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Product Recommendations</h1>
                <p class="text-sm text-bankos-muted dark:text-bankos-dark-text-sec mt-1">AI-generated product recommendations based on customer behaviour</p>
            </div>
            <form action="{{ route('marketing.recommendations.generate') }}" method="POST">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    Generate Recommendations
                </button>
            </form>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-bankos-muted uppercase">Reason</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Confidence</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-bankos-muted uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-bankos-muted uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($recommendations as $rec)
                    <tr class="hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                        <td class="px-6 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">
                            {{ $rec->customer?->first_name }} {{ $rec->customer?->last_name }}
                        </td>
                        <td class="px-6 py-3">
                            <div class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $rec->product_name }}</div>
                            <div class="text-xs text-bankos-muted">{{ str_replace('_', ' ', ucfirst($rec->product_type)) }}</div>
                        </td>
                        <td class="px-6 py-3 text-bankos-text-sec dark:text-bankos-dark-text-sec text-xs max-w-xs">
                            {{ $rec->reason }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            @php
                                $score = (float) $rec->confidence_score;
                                $barColor = $score >= 0.8 ? 'bg-green-500' : ($score >= 0.6 ? 'bg-yellow-500' : 'bg-red-500');
                            @endphp
                            <div class="flex items-center justify-end gap-2">
                                <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                                    <div class="{{ $barColor }} h-1.5 rounded-full" style="width: {{ $score * 100 }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-bankos-text dark:text-bankos-dark-text">{{ number_format($score * 100) }}%</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-center">
                            @php
                                $recStatusColors = [
                                    'active'    => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'contacted' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    'converted' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'dismissed' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $recStatusColors[$rec->status] ?? '' }}">
                                {{ ucfirst($rec->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-right">
                            @if($rec->status !== 'dismissed')
                            <form action="{{ route('marketing.recommendations.dismiss', $rec->id) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="text-bankos-muted hover:text-red-600 text-xs">Dismiss</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-bankos-muted">No active recommendations. Click "Generate" to create them.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($recommendations->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $recommendations->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
