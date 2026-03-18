<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center text-amber-600">
                    Dormant Accounts Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Active customer accounts with no transaction history for {{ $months }} months or longer.</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.dormant-accounts') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">Dormancy Period (Months)</label>
                <select name="months" class="form-select text-sm border-none shadow-none focus:ring-bankos-primary w-[100px]">
                    <option value="3" {{ $months == 3 ? 'selected' : '' }}>3 Months</option>
                    <option value="6" {{ $months == 6 ? 'selected' : '' }}>6 Months</option>
                    <option value="12" {{ $months == 12 ? 'selected' : '' }}>1 Year</option>
                    <option value="24" {{ $months == 24 ? 'selected' : '' }}>2 Years</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <!-- Accounts Table -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md border-t-4 border-t-amber-500">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Dormant Account Registry</h3>
            <div class="flex items-center gap-4 text-sm font-mono text-bankos-text-sec">
                <span class="text-xs font-bold text-amber-600 bg-amber-100 dark:bg-amber-900/30 px-2.5 py-1 rounded-full">{{ $dormantAccounts->count() }} Accounts</span>
                <span class="h-4 w-px bg-bankos-border"></span>
                <span>Total Balances: <strong class="text-bankos-text">₦{{ number_format($totalDormantBalance, 2) }}</strong></span>
            </div>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-amber-200 dark:border-amber-900/50 tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Customer Name</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Account Details</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Days Dormant</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Last Activity</th>
                        <th class="px-6 py-4 font-bold text-right text-amber-600">Current Balance (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($dormantAccounts as $account)
                    <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition-colors">
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900/50 dark:text-amber-300 flex items-center justify-center font-bold text-xs">
                                    {{ substr($account->customer?->first_name ?? '?', 0, 1) }}{{ substr($account->customer?->last_name ?? '', 0, 1) }}
                                </div>
                                @if($account->customer)
                                <a href="{{ route('customers.show', $account->customer->id) }}" class="font-medium text-bankos-primary hover:underline">
                                    {{ $account->customer->first_name }} {{ $account->customer->last_name }}
                                </a>
                                @else
                                <span class="text-bankos-muted text-xs italic">Unknown</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-mono text-bankos-text">{{ $account->account_number }}</span>
                                <span class="text-xs text-bankos-text-sec mt-0.5">{{ $account->savingsProduct?->name ?? 'Standard Account' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-md text-xs font-bold font-mono bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 w-16">
                                {{ number_format($account->days_dormant) }} 
                            </span>
                            <span class="text-xs text-bankos-text-sec ml-1">days</span>
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800 font-medium text-bankos-text">
                            @if($account->days_dormant == now()->diffInDays($account->created_at))
                                <span class="italic text-bankos-text-sec text-xs" title="Created on {{ \Carbon\Carbon::parse($account->last_activity_date)->format('Y-m-d') }}">Never Transacted</span>
                            @else
                                {{ \Carbon\Carbon::parse($account->last_activity_date)->format('d M Y') }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-bankos-text bg-amber-50/10 dark:bg-amber-900/5">
                            {{ number_format($account->available_balance, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <p class="text-lg font-medium text-gray-900 dark:text-gray-100">No Dormant Accounts</p>
                                <p class="text-sm mt-1">All active accounts have had transactions within the last {{ $months }} months.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
