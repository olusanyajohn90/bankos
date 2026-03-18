<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center text-red-600">
                    Suspicious Activity (AML) Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Flagged transactions and high-frequency accounts for the period {{ \Carbon\Carbon::parse($startDate)->format('d M y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.suspicious-activity') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <div class="h-6 w-px bg-bankos-border dark:bg-bankos-dark-border"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <div class="h-6 w-px bg-bankos-border dark:bg-bankos-dark-border"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec">Threshold (₦)</label>
                <input type="number" name="threshold" value="{{ $threshold }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary w-32" step="10000">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <!-- Rule 1: Single Large Transactions -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md border-t-4 border-t-red-600">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <div>
                <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Rule 1: Single Large Transactions</h3>
                <p class="text-xs text-bankos-text-sec mt-1">Transactions exceeding the defined threshold of ₦{{ number_format($threshold, 2) }}</p>
            </div>
            <span class="text-xs font-bold text-red-600 bg-red-100 dark:bg-red-900/30 px-2.5 py-1 rounded-full">{{ $largeTransactions->count() }} Flags</span>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-red-200 dark:border-red-900 tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Date / Ref</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Customer Name</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Account Details</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Type</th>
                        <th class="px-6 py-4 font-bold text-right text-red-600">Amount (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($largeTransactions as $txn)
                    <tr class="hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-colors">
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-medium text-bankos-text">{{ $txn->created_at->format('Y-m-d H:i') }}</span>
                                <span class="text-xs text-bankos-text-sec font-mono mt-0.5">{{ $txn->reference }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            @if($txn->account?->customer)
                            <a href="{{ route('customers.show', $txn->account->customer->id) }}" class="font-medium text-bankos-primary hover:underline">
                                {{ $txn->account->customer->first_name }} {{ $txn->account->customer->last_name }}
                            </a>
                            @else
                            <span class="text-bankos-muted text-xs italic">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="font-mono text-bankos-text">{{ $txn->account?->account_number }}</span>
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                                {{ ucfirst(str_replace('_', ' ', $txn->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-red-600 dark:text-red-400 bg-red-50/20 dark:bg-red-900/5">
                            {{ number_format($txn->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                            No single large transactions found exceeding the threshold in this period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Rule 2: High Frequency Transactions -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md border-t-4 border-t-amber-500">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <div>
                <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Rule 2: High Frequency Accounts (Structuring Risk)</h3>
                <p class="text-xs text-bankos-text-sec mt-1">Accounts with 10 or more successful transactions in a single day.</p>
            </div>
            <span class="text-xs font-bold text-amber-600 bg-amber-100 dark:bg-amber-900/30 px-2.5 py-1 rounded-full">{{ $highFrequencyAccounts->count() }} Flags</span>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-amber-200 dark:border-amber-900/50 tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Flag Date</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Customer Name</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Account Number</th>
                        <th class="px-6 py-4 font-bold text-center border-r border-bankos-border/50 dark:border-bankos-dark-border/50 text-amber-600">Daily Txn Count</th>
                        <th class="px-6 py-4 font-bold text-right">Daily Total Volume (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($highFrequencyAccounts as $flag)
                    <tr class="hover:bg-amber-50/30 dark:hover:bg-amber-900/10 transition-colors">
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800 font-medium text-bankos-text">
                            {{ \Carbon\Carbon::parse($flag->txn_date)->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800">
                            @if($flag->account?->customer)
                            <a href="{{ route('customers.show', $flag->account->customer->id) }}" class="font-medium text-bankos-primary hover:underline">
                                {{ $flag->account->customer->first_name }} {{ $flag->account->customer->last_name }}
                            </a>
                            @else
                            <span class="text-bankos-muted text-xs italic">Unknown</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 border-r border-dashed border-gray-200 dark:border-gray-800 font-mono text-bankos-text">
                            {{ $flag->account?->account_number }}
                        </td>
                        <td class="px-6 py-4 text-center border-r border-dashed border-gray-200 dark:border-gray-800">
                            <span class="px-3 py-1 inline-flex text-sm leading-5 font-bold rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                {{ $flag->txn_count }} txns
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right font-mono font-bold text-bankos-text">
                            {{ number_format($flag->total_volume, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                            No high-frequency transaction accounts detected in this period.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
