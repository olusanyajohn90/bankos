<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center text-purple-600">
                    Loan Disbursements & Repayments Log
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Detailed cashflow report for lending operations between {{ \Carbon\Carbon::parse($startDate)->format('d M y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d M y') }}</p>
            </div>
        </div>
    </x-slot>

    <!-- Filter & Print actions -->
    <div class="flex justify-between items-center mb-6">
        <form method="GET" action="{{ route('reports.loan-disbursements-repayments') }}" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec ml-2">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <div class="h-6 w-px bg-bankos-border dark:bg-bankos-dark-border"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input text-sm border-none shadow-none focus:ring-bankos-primary">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4 shadow-md hover:-translate-y-0.5 transition-transform">Filter</button>
        </form>

        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden bg-white shadow-sm" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print Log
        </button>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-5 border-l-4 border-l-blue-500 bg-gradient-to-br from-white to-gray-50 dark:from-bankos-dark-bg dark:to-gray-900 shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Money Out: Disbursements</h3>
            <p class="text-xl font-bold text-blue-600">₦{{ number_format($totalDisbursed, 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ $disbursements->count() }} transactions</p>
        </div>
        <div class="card p-5 border-l-4 border-l-purple-500 bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Money In: Repayments</h3>
            <p class="text-xl font-bold text-purple-600">₦{{ number_format($totalRepaid, 2) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">{{ $repayments->count() }} transactions</p>
        </div>
        <div class="card p-5 border-l-4 {{ $netCashflow >= 0 ? 'border-l-emerald-500' : 'border-l-red-500' }} bg-white dark:bg-bankos-dark-bg shadow-sm">
            <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Net Cashflow</h3>
            <p class="text-xl font-bold {{ $netCashflow >= 0 ? 'text-emerald-600' : 'text-red-500' }}">
                {{ $netCashflow >= 0 ? '+' : '-' }} ₦{{ number_format(abs($netCashflow), 2) }}
            </p>
            <p class="text-xs text-bankos-text-sec mt-1">Repayments minus Disbursements</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        
        <!-- Disbursements Table (Money Out) -->
        <div class="card p-0 overflow-hidden shadow-md">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-blue-50 dark:bg-blue-900/10">
                <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-300 tracking-wider">Disbursements (Money Out)</h3>
            </div>
            <div class="overflow-x-auto print:overflow-visible h-[400px] overflow-y-auto">
                <table class="w-full text-left text-sm print:text-black relative">
                    <thead class="sticky top-0 bg-white dark:bg-bankos-dark-bg">
                        <tr class="border-b-2 border-blue-200 dark:border-blue-900/50 tracking-wider text-bankos-text-sec text-xs uppercase shadow-sm">
                            <th class="px-4 py-3 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Date</th>
                            <th class="px-4 py-3 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Customer</th>
                            <th class="px-4 py-3 font-bold text-right text-blue-600">Amount (₦)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($disbursements as $txn)
                        <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                            <td class="px-4 py-3 border-r border-dashed border-gray-200 dark:border-gray-800 text-xs">
                                {{ $txn->created_at->format('M d, H:i') }}
                            </td>
                            <td class="px-4 py-3 border-r border-dashed border-gray-200 dark:border-gray-800">
                                <div class="flex flex-col">
                                    <span class="font-medium text-bankos-text line-clamp-1 text-xs">{{ $txn->account?->customer?->first_name }} {{ $txn->account?->customer?->last_name }}</span>
                                    <span class="text-[10px] text-bankos-text-sec font-mono mt-0.5">Acc: {{ $txn->account?->account_number }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-blue-600 dark:text-blue-400">
                                {{ number_format($txn->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                                No disbursements in this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Repayments Table (Money In) -->
        <div class="card p-0 overflow-hidden shadow-md">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-purple-50 dark:bg-purple-900/10">
                <h3 class="text-sm font-semibold text-purple-800 dark:text-purple-300 tracking-wider">Repayments (Money In)</h3>
            </div>
            <div class="overflow-x-auto print:overflow-visible h-[400px] overflow-y-auto">
                <table class="w-full text-left text-sm print:text-black relative">
                    <thead class="sticky top-0 bg-white dark:bg-bankos-dark-bg">
                        <tr class="border-b-2 border-purple-200 dark:border-purple-900/50 tracking-wider text-bankos-text-sec text-xs uppercase shadow-sm">
                            <th class="px-4 py-3 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Date</th>
                            <th class="px-4 py-3 font-bold border-r border-bankos-border/50 dark:border-bankos-dark-border/50">Customer</th>
                            <th class="px-4 py-3 font-bold text-right text-purple-600">Amount (₦)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($repayments as $txn)
                        <tr class="hover:bg-purple-50/30 dark:hover:bg-purple-900/10 transition-colors">
                            <td class="px-4 py-3 border-r border-dashed border-gray-200 dark:border-gray-800 text-xs">
                                {{ $txn->created_at->format('M d, H:i') }}
                            </td>
                            <td class="px-4 py-3 border-r border-dashed border-gray-200 dark:border-gray-800">
                                <div class="flex flex-col">
                                    <span class="font-medium text-bankos-text line-clamp-1 text-xs">{{ $txn->account?->customer?->first_name }} {{ $txn->account?->customer?->last_name }}</span>
                                    <span class="text-[10px] text-bankos-text-sec font-mono mt-0.5">Acc: {{ $txn->account?->account_number }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-purple-600 dark:text-purple-400">
                                {{ number_format($txn->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-bankos-text-sec bg-gray-50/50 dark:bg-gray-800/30">
                                No repayments in this period.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
