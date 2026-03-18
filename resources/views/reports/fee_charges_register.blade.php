<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Fee & Charges Register</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ \Carbon\Carbon::parse($startDate)->format('d M') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2 ml-2">
                <label class="text-xs font-semibold text-bankos-text-sec">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input text-sm border-none shadow-none">
            </div>
            <div class="h-6 w-px bg-bankos-border"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input text-sm border-none shadow-none">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4">Filter</button>
        </form>
        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="card p-5 border-t-4 border-t-yellow-500">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Fees Collected</p>
            <p class="text-3xl font-extrabold text-bankos-text mt-1">₦{{ number_format($totalFees, 2) }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $feeTransactions->count() }} transactions</p>
        </div>
        <div class="card p-5 border-t-4 border-t-blue-500">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Processing Fees (Loans)</p>
            <p class="text-3xl font-extrabold text-blue-600 mt-1">₦{{ number_format($loanProcessingFees, 2) }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $disbursedLoans->count() }} loan(s) disbursed in period</p>
        </div>
        <div class="card p-5 border-t-4 border-t-emerald-500">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Fee Types</p>
            <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $byType->count() }}</p>
            <p class="text-xs text-bankos-muted mt-1">Distinct charge categories</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Breakdown by type --}}
        <div class="card p-0 overflow-hidden shadow-md md:col-span-1">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                <h3 class="text-sm font-semibold text-bankos-text">By Fee Type</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($byType as $type => $data)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-bankos-text capitalize">{{ str_replace('_', ' ', $type) }}</p>
                        <p class="text-xs text-bankos-muted">{{ $data['count'] }} transaction(s)</p>
                    </div>
                    <p class="font-bold text-yellow-600">₦{{ number_format($data['amount'], 0) }}</p>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-bankos-muted text-sm">No fee transactions found.</div>
                @endforelse
                @if($loanProcessingFees > 0)
                <div class="px-6 py-4 flex items-center justify-between bg-blue-50/50 dark:bg-blue-900/10">
                    <div>
                        <p class="text-sm font-medium text-bankos-text">Processing Fee (Loans)</p>
                        <p class="text-xs text-bankos-muted">{{ $disbursedLoans->count() }} disbursement(s)</p>
                    </div>
                    <p class="font-bold text-blue-600">₦{{ number_format($loanProcessingFees, 0) }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Transaction detail --}}
        <div class="card p-0 overflow-hidden shadow-md md:col-span-2">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                <h3 class="text-sm font-semibold text-bankos-text">Fee Transactions</h3>
            </div>
            <div class="overflow-x-auto max-h-96 overflow-y-auto">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0">
                        <tr class="bg-bankos-light dark:bg-bankos-dark-bg border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold">Customer</th>
                            <th class="px-6 py-3 font-semibold">Type</th>
                            <th class="px-6 py-3 font-semibold text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($feeTransactions as $txn)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-6 py-3 text-xs text-bankos-text-sec">{{ $txn->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-sm font-medium text-bankos-text">{{ $txn->account?->customer?->full_name ?? '—' }}</td>
                            <td class="px-6 py-3 text-xs capitalize text-bankos-text-sec">{{ str_replace('_', ' ', $txn->type) }}</td>
                            <td class="px-6 py-3 text-right font-mono font-semibold text-yellow-600">₦{{ number_format(abs($txn->amount), 2) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-10 text-center text-bankos-muted">No fee transactions found for the selected period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Loan disbursements with processing fees --}}
    @if($disbursedLoans->isNotEmpty())
    <div class="card p-0 overflow-hidden shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Processing Fees — Loans Disbursed in Period</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Loan / Customer</th>
                        <th class="px-6 py-3 font-semibold">Product</th>
                        <th class="px-6 py-3 font-semibold text-right">Principal</th>
                        <th class="px-6 py-3 font-semibold text-right">Processing Fee</th>
                        <th class="px-6 py-3 font-semibold text-right">Disbursed At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($disbursedLoans as $loan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-bankos-text">{{ $loan->customer?->full_name ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted font-mono">{{ $loan->loan_number }}</p>
                        </td>
                        <td class="px-6 py-3 text-xs text-bankos-text-sec">{{ $loan->loanProduct?->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text">₦{{ number_format($loan->principal_amount, 0) }}</td>
                        <td class="px-6 py-3 text-right font-mono font-semibold text-blue-600">₦{{ number_format($loan->loanProduct?->processing_fee ?? 0, 2) }}</td>
                        <td class="px-6 py-3 text-right text-xs text-bankos-text-sec">{{ $loan->disbursed_at ? \Carbon\Carbon::parse($loan->disbursed_at)->format('d M Y') : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-app-layout>
