<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    PAR & Aging Summary
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Portfolio At Risk analysis and overdue buckets as of {{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-6 border-l-4 border-l-bankos-primary">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Active Portfolio (Outstanding)</h3>
            <p class="text-2xl font-bold text-bankos-text">₦{{ number_format($totalActiveOutstanding, 2) }}</p>
        </div>

        <div class="card p-6 border-l-4 border-l-red-500">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Overdue (Outstanding)</h3>
            <p class="text-2xl font-bold text-red-600">₦{{ number_format($totalOverdueOutstanding, 2) }}</p>
        </div>

        <div class="card p-6 border-l-4 {{ $parRatio > 10 ? 'border-l-red-500' : ($parRatio > 5 ? 'border-l-bankos-warning' : 'border-l-green-500') }}">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">PAR Ratio</h3>
            <div class="flex items-end gap-2">
                <p class="text-2xl font-bold {{ $parRatio > 10 ? 'text-red-500' : ($parRatio > 5 ? 'text-bankos-warning' : 'text-green-500') }}">{{ number_format($parRatio, 2) }}%</p>
                <p class="text-sm text-bankos-text-sec mb-1">of total portfolio</p>
            </div>
        </div>
    </div>

    <!-- Aging Buckets Table -->
    <div class="card p-0 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Aging Buckets Analysis</h3>
            <button class="btn btn-secondary text-xs flex items-center gap-2 print:hidden" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Report
            </button>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-bankos-border dark:border-bankos-dark-border tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Days Overdue</th>
                        <th class="px-6 py-4 font-bold text-center border-r border-bankos-border/50 dark:border-bankos-dark-border/50"># of Accounts</th>
                        <th class="px-6 py-4 font-bold text-right border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Principal At Risk (₦)</th>
                        <th class="px-6 py-4 font-bold text-right border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Outstanding Balance (₦)</th>
                        <th class="px-6 py-4 font-bold text-right">% of Total Overdue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <!-- 1-30 Days -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-bankos-warning flex items-center gap-2 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="w-3 h-3 rounded-full bg-bankos-warning/20 border border-bankos-warning"></div>
                            1 - 30 Days (PAR 30)
                        </td>
                        <td class="px-6 py-4 text-center font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['1_30']['count']) }}</td>
                        <td class="px-6 py-4 text-right font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['1_30']['principal'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-medium border-r border-dashed border-gray-200 dark:border-gray-800">₦{{ number_format($buckets['1_30']['outstanding'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono">
                            @if($totalOverdueOutstanding > 0)
                                {{ number_format(($buckets['1_30']['outstanding'] / $totalOverdueOutstanding) * 100, 1) }}%
                            @else - @endif
                        </td>
                    </tr>

                    <!-- 31-60 Days -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-orange-500 flex items-center gap-2 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="w-3 h-3 rounded-full bg-orange-500/20 border border-orange-500"></div>
                            31 - 60 Days (PAR 60)
                        </td>
                        <td class="px-6 py-4 text-center font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['31_60']['count']) }}</td>
                        <td class="px-6 py-4 text-right font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['31_60']['principal'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-medium border-r border-dashed border-gray-200 dark:border-gray-800">₦{{ number_format($buckets['31_60']['outstanding'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono">
                            @if($totalOverdueOutstanding > 0)
                                {{ number_format(($buckets['31_60']['outstanding'] / $totalOverdueOutstanding) * 100, 1) }}%
                            @else - @endif
                        </td>
                    </tr>

                    <!-- 61-90 Days -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-red-500 flex items-center gap-2 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="w-3 h-3 rounded-full bg-red-500/20 border border-red-500"></div>
                            61 - 90 Days (PAR 90)
                        </td>
                        <td class="px-6 py-4 text-center font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['61_90']['count']) }}</td>
                        <td class="px-6 py-4 text-right font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['61_90']['principal'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-medium border-r border-dashed border-gray-200 dark:border-gray-800">₦{{ number_format($buckets['61_90']['outstanding'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono">
                            @if($totalOverdueOutstanding > 0)
                                {{ number_format(($buckets['61_90']['outstanding'] / $totalOverdueOutstanding) * 100, 1) }}%
                            @else - @endif
                        </td>
                    </tr>

                    <!-- 90+ Days -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-red-700 dark:text-red-400 flex items-center gap-2 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="w-3 h-3 rounded-full bg-red-700/20 dark:bg-red-400/20 border border-red-700 dark:border-red-400"></div>
                            Over 90 Days (NPL)
                        </td>
                        <td class="px-6 py-4 text-center font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['90_plus']['count']) }}</td>
                        <td class="px-6 py-4 text-right font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($buckets['90_plus']['principal'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-red-600 dark:text-red-400 border-r border-dashed border-gray-200 dark:border-gray-800">₦{{ number_format($buckets['90_plus']['outstanding'], 2) }}</td>
                        <td class="px-6 py-4 text-right font-mono">
                            @if($totalOverdueOutstanding > 0)
                                {{ number_format(($buckets['90_plus']['outstanding'] / $totalOverdueOutstanding) * 100, 1) }}%
                            @else - @endif
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-t border-bankos-text dark:border-bankos-dark-text text-bankos-text font-bold text-base">
                        <td class="px-6 py-5 uppercase tracking-wider text-sm border-r border-bankos-border/50 dark:border-bankos-dark-border/50 text-right">
                            Total Overdue Portfolio
                        </td>
                        <td class="px-6 py-5 text-center font-mono border-r border-bankos-border/50 dark:border-bankos-dark-border/50 border-double">
                            {{ number_format(array_sum(array_column($buckets, 'count'))) }}
                        </td>
                        <td class="px-6 py-5 text-right font-mono border-r border-bankos-border/50 dark:border-bankos-dark-border/50 border-double">
                            ₦{{ number_format(array_sum(array_column($buckets, 'principal')), 2) }}
                        </td>
                        <td class="px-6 py-5 text-right font-mono border-r border-bankos-border/50 dark:border-bankos-dark-border/50 text-red-600 dark:text-red-500 border-double">
                            ₦{{ number_format($totalOverdueOutstanding, 2) }}
                        </td>
                        <td class="px-6 py-5 text-right font-mono">
                            100%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
