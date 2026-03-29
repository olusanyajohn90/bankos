<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    ICard Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Internal account position report as at {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.icard') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">Account Type</label>
                <select name="account_type" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
                    <option value="">All Types</option>
                    @foreach($accountTypes as $type)
                        <option value="{{ $type }}" {{ $accountType === $type ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-5 border-l-4 border-l-bankos-primary bg-gradient-to-br from-white to-gray-50 dark:from-bankos-dark-bg dark:to-gray-900 shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Total Accounts</h3>
            <p class="text-xl font-bold text-bankos-text">{{ number_format($totalAccounts) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-slate-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Total Ledger Balance</h3>
            <p class="text-xl font-bold text-bankos-text">&#8358;{{ number_format($totalLedger, 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Total Available Balance</h3>
            <p class="text-xl font-bold text-green-600">&#8358;{{ number_format($totalAvailable, 2) }}</p>
        </div>
    </div>

    <!-- Type Summary -->
    @if($typeSummary->count() > 1)
    <div class="card p-0 overflow-hidden mb-8 shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Summary by Account Type</h3>
        </div>
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Account Type</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Count</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Total Ledger Balance</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Total Available Balance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($typeSummary as $summary)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50 transition-colors">
                            <td class="px-4 py-3 text-bankos-text font-semibold">{{ ucfirst(str_replace('_', ' ', $summary['type'])) }}</td>
                            <td class="px-4 py-3 text-right text-bankos-text">{{ number_format($summary['count']) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-bankos-text">&#8358;{{ number_format($summary['total_ledger'], 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-green-600">&#8358;{{ number_format($summary['total_available'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Detail Table grouped by type -->
    @foreach($byType as $type => $typeAccounts)
    <div class="card p-0 overflow-hidden mb-8 shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">{{ ucfirst(str_replace('_', ' ', $type)) }} Accounts</h3>
            <span class="text-xs font-medium text-bankos-muted bg-gray-200 dark:bg-gray-700 px-2 py-1 rounded-full">{{ number_format($typeAccounts->count()) }} accounts</span>
        </div>

        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Account #</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Customer Name</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Ledger Balance</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Available Balance</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Difference</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($typeAccounts as $account)
                        @php
                            $diff = $account->ledger_balance - $account->available_balance;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50 transition-colors">
                            <td class="px-4 py-3 text-bankos-text font-mono text-xs">{{ $account->account_number }}</td>
                            <td class="px-4 py-3 text-bankos-text">
                                @if($account->customer)
                                    {{ $account->customer->first_name }} {{ $account->customer->last_name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-bankos-text">&#8358;{{ number_format($account->ledger_balance, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-green-600">&#8358;{{ number_format($account->available_balance, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono {{ $diff != 0 ? 'text-amber-600 font-semibold' : 'text-bankos-muted' }}">
                                &#8358;{{ number_format(abs($diff), 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    {{ ucfirst($account->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/30 border-t-2 border-bankos-border dark:border-bankos-dark-border font-bold">
                        <td colspan="2" class="px-4 py-3 text-bankos-text text-right uppercase text-xs tracking-wider">Sub-total</td>
                        <td class="px-4 py-3 text-right font-mono text-bankos-text">&#8358;{{ number_format($typeAccounts->sum('ledger_balance'), 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-green-600">&#8358;{{ number_format($typeAccounts->sum('available_balance'), 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-amber-600">&#8358;{{ number_format(abs($typeAccounts->sum('ledger_balance') - $typeAccounts->sum('available_balance')), 2) }}</td>
                        <td class="px-4 py-3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endforeach
</x-app-layout>
