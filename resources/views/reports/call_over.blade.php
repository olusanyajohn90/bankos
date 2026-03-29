<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Call-Over Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">End-of-day reconciliation for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.call-over') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Report
        </button>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card p-5 border-l-4 border-l-bankos-primary bg-gradient-to-br from-white to-gray-50 dark:from-bankos-dark-bg dark:to-gray-900 shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Total Transactions</h3>
            <p class="text-xl font-bold text-bankos-text">{{ number_format($totalCount) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Total Credits</h3>
            <p class="text-xl font-bold text-green-600">&#8358;{{ number_format($totalCredits, 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-red-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Total Debits</h3>
            <p class="text-xl font-bold text-red-500">&#8358;{{ number_format(abs($totalDebits), 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-cyan-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Net Movement</h3>
            <p class="text-xl font-bold {{ $netMovement >= 0 ? 'text-green-600' : 'text-red-500' }}">&#8358;{{ number_format(abs($netMovement), 2) }}</p>
        </div>
    </div>

    <!-- Call-Over Table -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Transaction Details</h3>
        </div>

        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Time</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Reference</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Account</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Type</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Debit</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Credit</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Channel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($transactions as $txn)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50 transition-colors">
                            <td class="px-4 py-3 text-bankos-text whitespace-nowrap">{{ $txn->created_at->format('H:i:s') }}</td>
                            <td class="px-4 py-3 text-bankos-text font-mono text-xs">{{ $txn->reference ?? '-' }}</td>
                            <td class="px-4 py-3 text-bankos-text">{{ $txn->account->account_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-bankos-text">
                                @if($txn->account && $txn->account->customer)
                                    {{ $txn->account->customer->first_name }} {{ $txn->account->customer->last_name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $txn->type === 'credit' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ ucfirst($txn->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-bankos-text-sec text-xs max-w-[200px] truncate">{{ $txn->description ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-mono text-red-600 font-semibold">
                                {{ $txn->amount < 0 ? '&#8358;' . number_format(abs($txn->amount), 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-green-600 font-semibold">
                                {{ $txn->amount > 0 ? '&#8358;' . number_format($txn->amount, 2) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-bankos-text-sec text-xs">{{ ucfirst($txn->channel ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-bankos-muted">No transactions found for this date.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($transactions->count())
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/30 border-t-2 border-bankos-border dark:border-bankos-dark-border font-bold">
                        <td colspan="6" class="px-4 py-3 text-bankos-text text-right uppercase text-xs tracking-wider">Totals</td>
                        <td class="px-4 py-3 text-right font-mono text-red-600">&#8358;{{ number_format(abs($totalDebits), 2) }}</td>
                        <td class="px-4 py-3 text-right font-mono text-green-600">&#8358;{{ number_format($totalCredits, 2) }}</td>
                        <td class="px-4 py-3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    <!-- Channel Breakdown -->
    @if($byChannel->count() > 1)
    <div class="card p-0 overflow-hidden shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Breakdown by Channel</h3>
        </div>
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border dark:border-bankos-dark-border">
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Channel</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Count</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Credits</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Debits</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-right">Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($byChannel as $channel => $txns)
                        @php
                            $chCredits = $txns->where('amount', '>', 0)->sum('amount');
                            $chDebits = $txns->where('amount', '<', 0)->sum('amount');
                            $chNet = $chCredits + $chDebits;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg/50 transition-colors">
                            <td class="px-4 py-3 text-bankos-text font-semibold">{{ ucfirst($channel ?: 'Unknown') }}</td>
                            <td class="px-4 py-3 text-right text-bankos-text">{{ number_format($txns->count()) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-green-600">&#8358;{{ number_format($chCredits, 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono text-red-600">&#8358;{{ number_format(abs($chDebits), 2) }}</td>
                            <td class="px-4 py-3 text-right font-mono font-semibold {{ $chNet >= 0 ? 'text-green-600' : 'text-red-600' }}">&#8358;{{ number_format(abs($chNet), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-app-layout>
