<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center text-indigo-600">
                    Branch Performance Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Comparing deposit growth, loan portfolio size, and PAR ratio across branches for {{ \Carbon\Carbon::parse($date)->format('F Y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.branch-performance') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">Month</label>
                <input type="month" name="date" value="{{ $date }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <!-- Branch Performance Table -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md border-t-4 border-t-indigo-500">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Branch Metrics Overview</h3>
            <span class="text-xs font-bold text-indigo-600 bg-indigo-100 dark:bg-indigo-900/30 px-2.5 py-1 rounded-full">{{ count($branchStats) }} Branches Analyzed</span>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-indigo-200 dark:border-indigo-900/50 tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Rank</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Branch Details</th>
                        <th class="px-6 py-4 font-bold text-center border-r border-bankos-border/50 dark:border-bankos-dark-border/50">New Accounts<br><span class="text-[10px] font-normal lowercase">(this month)</span></th>
                        <th class="px-6 py-4 font-bold text-right border-r border-bankos-border/50 dark:border-bankos-dark-border/50 text-emerald-600">Total Deposits (₦)</th>
                        <th class="px-6 py-4 font-bold text-right border-r border-bankos-border/50 dark:border-bankos-dark-border/50 text-blue-600">Loan Portfolio (₦)</th>
                        <th class="px-6 py-4 font-bold text-center text-red-600">PAR Ratio (%)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($branchStats as $index => $stat)
                    <tr class="hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-colors">
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800 text-center">
                            @if($index == 0)
                                <div class="w-8 h-8 mx-auto rounded-full bg-yellow-100 text-yellow-600 flex items-center justify-center font-bold" title="Top Performing Branch by Deposits">1</div>
                            @elseif($index == 1)
                                <div class="w-8 h-8 mx-auto rounded-full bg-gray-200 hover:bg-gray-300 text-gray-800 flex items-center justify-center font-bold">2</div>
                            @elseif($index == 2)
                                <div class="w-8 h-8 mx-auto rounded-full bg-orange-100 text-orange-700 flex items-center justify-center font-bold">3</div>
                            @else
                                <div class="w-8 h-8 mx-auto rounded-full text-bankos-text-sec flex items-center justify-center font-medium">{{ $index + 1 }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-bold text-bankos-text text-base">{{ $stat['name'] }}</span>
                                <span class="text-xs text-bankos-text-sec mt-0.5">{{ $stat['code'] }} • {{ $stat['city'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                +{{ number_format($stat['new_accounts']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 border-r border-dashed border-gray-200 dark:border-gray-800 bg-emerald-50/10 dark:bg-emerald-900/5">
                            {{ number_format($stat['total_deposits'], 2) }}
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-blue-600 dark:text-blue-400 border-r border-dashed border-gray-200 dark:border-gray-800 bg-blue-50/10 dark:bg-blue-900/5">
                            {{ number_format($stat['total_loans'], 2) }}
                        </td>
                        <td class="px-6 py-4 text-center font-mono font-bold">
                            @if($stat['par_ratio'] > 10)
                                <span class="text-red-600 bg-red-100 px-2 py-1 rounded">{{ number_format($stat['par_ratio'], 2) }}%</span>
                            @elseif($stat['par_ratio'] > 5)
                                <span class="text-amber-600">{{ number_format($stat['par_ratio'], 2) }}%</span>
                            @else
                                <span class="text-green-600">{{ number_format($stat['par_ratio'], 2) }}%</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                            No branches configured in the system.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
