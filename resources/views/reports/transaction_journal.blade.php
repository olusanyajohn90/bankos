<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    Daily Transaction Journal
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Chronological log of all system transactions for {{ \Carbon\Carbon::parse($date)->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.transaction-journal') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">Date</label>
                <input type="date" name="date" value="{{ $date }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Journal
        </button>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card p-5 border-l-4 border-l-bankos-primary bg-gradient-to-br from-white to-gray-50 dark:from-bankos-dark-bg dark:to-gray-900 shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Total Volume</h3>
            <p class="text-xl font-bold text-bankos-text">₦{{ number_format($summary['total_volume'], 2) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-blue-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Transactions</h3>
            <p class="text-xl font-bold text-bankos-text">{{ number_format($summary['total_count']) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-green-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Successful</h3>
            <p class="text-xl font-bold text-green-600">{{ number_format($summary['successful_count']) }}</p>
        </div>
        <div class="card p-5 border-l-4 border-l-red-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Failed</h3>
            <p class="text-xl font-bold text-red-500">{{ number_format($summary['failed_count']) }}</p>
        </div>
    </div>

    <!-- Journal Table -->
    <div class="card p-0 overflow-hidden mb-8 shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text tracking-wider">Transaction Log</h3>
        </div>
        
        <div class="overflow-x-auto print:overflow-visible">
            <table class="w-full text-left text-sm print:text-black">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b-2 border-bankos-border dark:border-bankos-dark-border tracking-wider text-bankos-text-sec text-xs uppercase">
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Time / Ref</th>
                        <th class="px-6 py-4 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Details</th>
                        <th class="px-6 py-4 font-bold text-center border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Type</th>
                        <th class="px-6 py-4 font-bold text-center border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Status</th>
                        <th class="px-6 py-4 font-bold text-right">Amount (₦)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($transactions as $txn)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-3 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-medium text-bankos-text">{{ $txn->created_at->format('H:i:s') }}</span>
                                <span class="text-xs text-bankos-text-sec font-mono mt-0.5">{{ $txn->reference }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 border-r border-dashed border-gray-200 dark:border-gray-800">
                            <div class="flex flex-col">
                                <span class="font-medium text-bankos-text line-clamp-1" title="{{ $txn->description }}">{{ $txn->description }}</span>
                                @if($txn->account)
                                    <span class="text-xs text-bankos-text-sec font-mono mt-0.5">Acc: {{ $txn->account->account_number }} - {{ $txn->account->customer?->first_name }} {{ $txn->account->customer?->last_name }}</span>
                                @else
                                    <span class="text-xs text-bankos-text-sec font-mono mt-0.5 italic">System Transaction</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3 text-center border-r border-dashed border-gray-200 dark:border-gray-800">
                            @php
                                $typeMap = [
                                    'deposit' => ['text' => 'Deposit', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'],
                                    'withdrawal' => ['text' => 'Withdrawal', 'color' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'],
                                    'transfer_in' => ['text' => 'Transfer In', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'],
                                    'transfer_out' => ['text' => 'Transfer Out', 'color' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'],
                                    'loan_repayment' => ['text' => 'Loan Repay', 'color' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'],
                                    'disbursement' => ['text' => 'Disbursement', 'color' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'],
                                    'interest_credit' => ['text' => 'Interest Paid', 'color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'],
                                    'fee' => ['text' => 'Fee', 'color' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'],
                                ];
                                $mapped = $typeMap[$txn->type] ?? ['text' => ucfirst(str_replace('_', ' ', $txn->type)), 'color' => 'bg-gray-100 text-gray-800'];
                            @endphp
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $mapped['color'] }}">
                                {{ $mapped['text'] }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center border-r border-dashed border-gray-200 dark:border-gray-800">
                            @if($txn->status === 'success')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Success</span>
                            @elseif($txn->status === 'failed')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Failed</span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">{{ ucfirst($txn->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right font-mono font-bold {{ in_array($txn->type, ['withdrawal', 'transfer_out', 'fee']) ? 'text-bankos-text' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ in_array($txn->type, ['withdrawal', 'transfer_out', 'fee']) ? '-' : '+' }} {{ number_format($txn->amount, 2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                            No transactions recorded on this date.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
