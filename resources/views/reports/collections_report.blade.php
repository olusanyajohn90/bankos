<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Collections Report</h2>
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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Collected</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">₦{{ number_format($totalCollected, 2) }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $actualCollections->count() }} repayment(s)</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Expected (Monthly)</p>
            <p class="text-2xl font-extrabold text-bankos-text mt-1">₦{{ number_format($totalExpected, 2) }}</p>
            <p class="text-xs text-bankos-muted mt-1">Based on instalment schedules</p>
        </div>
        <div class="card p-5 {{ $collectionEfficiency < 70 ? 'border-red-300' : ($collectionEfficiency < 90 ? 'border-amber-300' : '') }}">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Collection Rate</p>
            <p class="text-2xl font-extrabold {{ $collectionEfficiency >= 90 ? 'text-emerald-600' : ($collectionEfficiency >= 70 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ number_format($collectionEfficiency, 1) }}%</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $collectionEfficiency >= 90 ? 'On target' : ($collectionEfficiency >= 70 ? 'Below target' : 'Critical') }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Overdue Loans</p>
            <p class="text-2xl font-extrabold text-red-600 mt-1">{{ $loanCollections->where('is_overdue', true)->count() }}</p>
            <p class="text-xs text-bankos-muted mt-1">Require immediate action</p>
        </div>
    </div>

    {{-- Actual Collections --}}
    <div class="card p-0 overflow-hidden mb-8 shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Repayments Received in Period</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Date</th>
                        <th class="px-6 py-3 font-semibold">Customer</th>
                        <th class="px-6 py-3 font-semibold">Reference</th>
                        <th class="px-6 py-3 font-semibold text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($actualCollections as $txn)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-3 text-bankos-text-sec text-xs">{{ $txn->created_at->format('d M Y, H:i') }}</td>
                        <td class="px-6 py-3 font-medium text-bankos-text">{{ $txn->account?->customer?->full_name ?? '—' }}</td>
                        <td class="px-6 py-3 font-mono text-xs text-bankos-muted">{{ $txn->reference ?? '—' }}</td>
                        <td class="px-6 py-3 text-right font-mono font-semibold text-emerald-600">₦{{ number_format(abs($txn->amount), 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-10 text-center text-bankos-muted">No repayments found for the selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Loan Collection Status --}}
    <div class="card p-0 overflow-hidden shadow-md">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Active Loan Portfolio — Collection Status</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Customer / Product</th>
                        <th class="px-6 py-3 font-semibold text-right">Monthly Instalment</th>
                        <th class="px-6 py-3 font-semibold text-right">Total Paid</th>
                        <th class="px-6 py-3 font-semibold text-right">Outstanding</th>
                        <th class="px-6 py-3 font-semibold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($loanCollections as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 {{ $row['is_overdue'] ? 'bg-red-50/40' : '' }}">
                        <td class="px-6 py-3">
                            <p class="font-medium text-bankos-text">{{ $row['loan']->customer?->full_name ?? '—' }}</p>
                            <p class="text-xs text-bankos-muted">{{ $row['loan']->loanProduct?->name ?? '—' }} · {{ $row['loan']->loan_number }}</p>
                        </td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-text-sec">₦{{ number_format($row['installment'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-emerald-600">₦{{ number_format($row['amount_paid'], 2) }}</td>
                        <td class="px-6 py-3 text-right font-mono text-bankos-primary font-semibold">₦{{ number_format($row['outstanding'], 2) }}</td>
                        <td class="px-6 py-3 text-center">
                            @if($row['is_overdue'])
                                <span class="text-xs text-red-700 bg-red-100 px-2 py-0.5 rounded-full font-semibold">Overdue</span>
                            @else
                                <span class="text-xs text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full font-semibold">Active</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
