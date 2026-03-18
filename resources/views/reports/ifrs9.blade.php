<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    IFRS 9 Expected Credit Loss
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Staging and provision compliance report as of {{ now()->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-6 border-l-4 border-l-bankos-primary">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Exposure At Default (EAD)</h3>
            <p class="text-2xl font-bold text-bankos-text">₦{{ number_format($totalExposure, 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Total outstanding balance of portfolio</p>
        </div>

        <div class="card p-6 border-l-4 border-l-bankos-warning">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Expected Credit Loss</h3>
            <p class="text-2xl font-bold text-bankos-warning">₦{{ number_format($totalEcl, 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-2">Total provision required</p>
        </div>

        <div class="card p-6 border-l-4 border-l-emerald-500">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Overall Coverage Ratio</h3>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($overallCoverageRatio, 2) }}%</p>
            <p class="text-xs text-bankos-text-sec mt-2">ECL / EAD</p>
        </div>
    </div>

    <!-- IFRS 9 Staging Table -->
    <div class="card p-0 overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Asset Classifications & Staging</h3>
            <button class="btn btn-secondary text-xs flex items-center gap-2 print:hidden" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Print Report
            </button>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-bankos-border dark:border-bankos-dark-border tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Stage</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Description</th>
                        <th class="px-6 py-4 font-bold text-center border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Count</th>
                        <th class="px-6 py-4 font-bold text-right border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Exposure (₦)</th>
                        <th class="px-6 py-4 font-bold text-center border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Prov. Rate</th>
                        <th class="px-6 py-4 font-bold text-right text-bankos-warning">ECL Provision (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <!-- Stage 1 -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-green-600 border-r border-dashed border-gray-200 dark:border-gray-800">Stage 1</td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="block font-medium text-bankos-text">Performing</span>
                            <span class="text-xs text-bankos-text-sec block mt-1">Initial recognition or 0-30 days past due (12-month ECL)</span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($stages['stage_1']['count']) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-medium border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($stages['stage_1']['exposure'], 2) }}</td>
                        <td class="px-6 py-4 text-center font-mono text-xs border-r border-dashed border-gray-200 dark:border-gray-800">{{ $stages['stage_1']['provision_rate'] * 100 }}%</td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-bankos-warning">₦{{ number_format($stages['stage_1']['ecl'], 2) }}</td>
                    </tr>

                    <!-- Stage 2 -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-orange-500 border-r border-dashed border-gray-200 dark:border-gray-800">Stage 2</td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="block font-medium text-bankos-text">Underperforming (SICR)</span>
                            <span class="text-xs text-bankos-text-sec block mt-1">Significant Increase in Credit Risk. 31-90 days past due (Lifetime ECL)</span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($stages['stage_2']['count']) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-medium border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($stages['stage_2']['exposure'], 2) }}</td>
                        <td class="px-6 py-4 text-center font-mono text-xs border-r border-dashed border-gray-200 dark:border-gray-800">{{ $stages['stage_2']['provision_rate'] * 100 }}%</td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-bankos-warning">₦{{ number_format($stages['stage_2']['ecl'], 2) }}</td>
                    </tr>

                    <!-- Stage 3 -->
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4 font-bold text-red-600 border-r border-dashed border-gray-200 dark:border-gray-800">Stage 3</td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="block font-medium text-bankos-text">Non-Performing (Default)</span>
                            <span class="text-xs text-bankos-text-sec block mt-1">Credit Impaired. 90+ days past due (Lifetime ECL)</span>
                        </td>
                        <td class="px-6 py-4 text-center font-mono border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($stages['stage_3']['count']) }}</td>
                        <td class="px-6 py-4 text-right font-mono font-medium border-r border-dashed border-gray-200 dark:border-gray-800">{{ number_format($stages['stage_3']['exposure'], 2) }}</td>
                        <td class="px-6 py-4 text-center font-mono text-xs border-r border-dashed border-gray-200 dark:border-gray-800">{{ $stages['stage_3']['provision_rate'] * 100 }}%</td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-bankos-warning">₦{{ number_format($stages['stage_3']['ecl'], 2) }}</td>
                    </tr>

                </tbody>
                <tfoot>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-t border-bankos-text dark:border-bankos-dark-text text-bankos-text font-bold text-base">
                        <td colspan="2" class="px-6 py-5 uppercase tracking-wider text-sm border-r border-bankos-border/50 dark:border-bankos-dark-border/50 text-right">
                            Totals
                        </td>
                        <td class="px-6 py-5 text-center font-mono border-r border-bankos-border/50 dark:border-bankos-dark-border/50 border-double">
                            {{ number_format($stages['stage_1']['count'] + $stages['stage_2']['count'] + $stages['stage_3']['count']) }}
                        </td>
                        <td class="px-6 py-5 text-right font-mono border-r border-bankos-border/50 dark:border-bankos-dark-border/50 border-double">
                            ₦{{ number_format($totalExposure, 2) }}
                        </td>
                        <td class="px-6 py-5 border-r border-bankos-border/50 dark:border-bankos-dark-border/50"></td>
                        <td class="px-6 py-5 text-right font-mono text-bankos-warning border-double">
                            ₦{{ number_format($totalEcl, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-app-layout>
