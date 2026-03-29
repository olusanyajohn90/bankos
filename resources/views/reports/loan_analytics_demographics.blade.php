<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Loan Performance by Demographics</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Loan analytics broken down by age group and gender</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-end mb-6 print:hidden">
        <button class="btn btn-secondary text-sm flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Loans</p>
            <p class="text-2xl font-extrabold text-bankos-primary mt-1">{{ number_format($totalLoans) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Disbursed</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">&#8358;{{ number_format($totalDisbursed, 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Outstanding</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">&#8358;{{ number_format($totalOutstanding, 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Overdue Count</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">{{ number_format($totalOverdue) }}</p>
        </div>
    </div>

    {{-- By Gender --}}
    <div class="card mb-6">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">By Gender</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <th class="text-left px-4 py-3 font-semibold text-bankos-text-sec">Gender</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Count</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">% Share</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Disbursed</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Outstanding</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Overdue</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Written Off</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @foreach ($byGender as $gender => $data)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                            <td class="px-4 py-3 font-medium text-bankos-text dark:text-bankos-dark-text capitalize">{{ $gender }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($data['count']) }}</td>
                            <td class="px-4 py-3 text-right">{{ $totalLoans > 0 ? number_format(($data['count'] / $totalLoans) * 100, 1) : '0.0' }}%</td>
                            <td class="px-4 py-3 text-right">&#8358;{{ number_format($data['disbursed'], 2) }}</td>
                            <td class="px-4 py-3 text-right">&#8358;{{ number_format($data['outstanding'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-600 font-semibold">{{ number_format($data['overdue_count']) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-semibold">{{ number_format($data['written_off']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- By Age Group --}}
    <div class="card mb-6">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">By Age Group</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <th class="text-left px-4 py-3 font-semibold text-bankos-text-sec">Age Group</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Count</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">% Share</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Disbursed</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Outstanding</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Overdue</th>
                        <th class="text-right px-4 py-3 font-semibold text-bankos-text-sec">Written Off</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @foreach ($byAge as $ageGroup => $data)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                            <td class="px-4 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $ageGroup }}</td>
                            <td class="px-4 py-3 text-right">{{ number_format($data['count']) }}</td>
                            <td class="px-4 py-3 text-right">{{ $totalLoans > 0 ? number_format(($data['count'] / $totalLoans) * 100, 1) : '0.0' }}%</td>
                            <td class="px-4 py-3 text-right">&#8358;{{ number_format($data['disbursed'], 2) }}</td>
                            <td class="px-4 py-3 text-right">&#8358;{{ number_format($data['outstanding'], 2) }}</td>
                            <td class="px-4 py-3 text-right text-red-600 font-semibold">{{ number_format($data['overdue_count']) }}</td>
                            <td class="px-4 py-3 text-right text-gray-500 font-semibold">{{ number_format($data['written_off']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Cross-Tab: Age x Gender --}}
    <div class="card mb-6">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Cross-Tab: Age Group x Gender</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <th class="text-left px-4 py-3 font-semibold text-bankos-text-sec" rowspan="2">Age Group</th>
                        <th class="text-center px-4 py-2 font-semibold text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border" colspan="3">Male</th>
                        <th class="text-center px-4 py-2 font-semibold text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border" colspan="3">Female</th>
                        <th class="text-center px-4 py-2 font-semibold text-bankos-text-sec border-b border-bankos-border dark:border-bankos-dark-border" colspan="3">Unknown</th>
                    </tr>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg">
                        @foreach (['male', 'female', 'Unknown'] as $g)
                            <th class="text-right px-3 py-2 font-semibold text-bankos-text-sec text-xs">Count</th>
                            <th class="text-right px-3 py-2 font-semibold text-bankos-text-sec text-xs">Disbursed</th>
                            <th class="text-right px-3 py-2 font-semibold text-bankos-text-sec text-xs">Outstanding</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @foreach ($ageGroups as $ag)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50">
                            <td class="px-4 py-3 font-medium text-bankos-text dark:text-bankos-dark-text">{{ $ag }}</td>
                            @foreach (['male', 'female', 'Unknown'] as $g)
                                <td class="px-3 py-3 text-right">{{ number_format($crossTab[$ag][$g]['count']) }}</td>
                                <td class="px-3 py-3 text-right">&#8358;{{ number_format($crossTab[$ag][$g]['disbursed'], 2) }}</td>
                                <td class="px-3 py-3 text-right">&#8358;{{ number_format($crossTab[$ag][$g]['outstanding'], 2) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
