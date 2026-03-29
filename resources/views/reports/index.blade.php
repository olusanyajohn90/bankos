<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Reports Hub') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Access operational, financial, and regulatory reports</p>
            </div>
            
            <div class="text-sm font-medium text-bankos-muted flex items-center gap-2 bg-gray-50 dark:bg-bankos-dark-bg/50 px-3 py-1.5 rounded-lg border border-bankos-border dark:border-bankos-dark-border">
                <div class="h-2 w-2 rounded-full bg-bankos-success animate-pulse"></div>
                Live Data Connected
            </div>
        </div>
    </x-slot>

    <!-- Operational Reports -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4 px-2">Operational Reports</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- Account Statement -->
            <a href="{{ route('reports.account-statement') }}" class="card hover:border-bankos-primary/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-blue-50 text-bankos-primary group-hover:bg-bankos-primary group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-bankos-primary transition-colors">Account Statement</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Detailed transaction history for a specific customer account across a date range.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-bankos-primary flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Branch Performance -->
            <a href="{{ route('reports.branch-performance') }}" class="card hover:border-teal-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-teal-50 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-teal-600 transition-colors">Branch Performance</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Compare deposit growth, loan portfolio size, and PAR ratio across your different physical branches.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-teal-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Daily Transaction Journal -->
            <a href="{{ route('reports.transaction-journal') }}" class="card hover:border-gray-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 group-hover:bg-gray-700 group-hover:text-white transition-colors dark:bg-gray-800 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Daily Transaction Journal</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Chronological log of every single transaction that occurred across the entire bank on a specific day.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Dormant Accounts -->
            <a href="{{ route('reports.dormant-accounts') }}" class="card hover:border-amber-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-amber-50 text-amber-600 group-hover:bg-amber-500 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-amber-600 transition-colors">Dormant Accounts</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Accounts that have had no customer-initiated transactions for a specified period.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-amber-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Loan Cashflow Log -->
            <a href="{{ route('reports.loan-disbursements-repayments') }}" class="card hover:border-blue-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-blue-600 transition-colors">Loan Cashflow Log</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Summary of money out (disbursements) versus money in (repayments) for a given date range.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-blue-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Loan Portfolio -->
            <a href="{{ route('reports.loan-portfolio') }}" class="card hover:border-bankos-primary/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-blue-50 text-bankos-primary group-hover:bg-bankos-primary group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-bankos-primary transition-colors">Loan Portfolio</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Distribution of your active lending portfolio by status, size, and product type.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-bankos-primary flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Overdrawn Accounts -->
            <a href="{{ route('reports.overdrawn-accounts') }}" class="card hover:border-red-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-red-600 transition-colors">Overdrawn Accounts</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">List of all customer accounts where the available balance has dropped below zero.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-red-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- PAR & Aging -->
            <a href="{{ route('reports.par-aging') }}" class="card hover:border-red-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2v6.5a7 7 0 1 1-8 11.5"></path><path d="M9.01 11a5 5 0 1 0 2.82-4.14C9.5 8.16 8 8.68 6 11.1"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-red-600 transition-colors">PAR & Aging Analysis</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Portfolio At Risk. Overdue loans grouped into aging buckets (1-30, 31-60, 61-90, 90+ days).</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-red-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Suspicious Activity (AML) -->
            <a href="{{ route('reports.suspicious-activity') }}" class="card hover:border-gray-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 group-hover:bg-gray-700 group-hover:text-white transition-colors dark:bg-gray-800 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Suspicious Activity (AML)</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Flags large transactions and identifies high-frequency accounts that indicate potential structuring.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

        </div>
    </div>

    <!-- Lending Reports -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4 px-2">Lending Reports</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Loan Repayment Schedule -->
            <a href="{{ route('reports.loan-repayment-schedule') }}" class="card hover:border-blue-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-blue-50 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-blue-600 transition-colors">Loan Repayment Schedule</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Full amortization table for any loan — instalment breakdown of principal, interest, and status per period.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-blue-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Collections Report -->
            <a href="{{ route('reports.collections') }}" class="card hover:border-green-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-green-50 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-green-600 transition-colors">Collections Report</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Expected vs actual repayments collected in a period — shows collection efficiency per loan officer and branch.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-green-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Product Performance -->
            <a href="{{ route('reports.product-performance') }}" class="card hover:border-indigo-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-indigo-600 transition-colors">Product Performance</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">PAR, disbursements, and outstanding balance by loan product — shows which products are driving risk vs growth.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-indigo-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Maturity Profile -->
            <a href="{{ route('reports.maturity-profile') }}" class="card hover:border-orange-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-orange-50 text-orange-600 group-hover:bg-orange-500 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-orange-600 transition-colors">Maturity Profile</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Loan and term-deposit maturities bucketed by time horizon (30/60/90/180/365+ days) for liquidity planning.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-orange-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

        </div>
    </div>

    <!-- Compliance & Operations Reports -->
    <div class="mb-8">
        <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4 px-2">Compliance & Operations</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- KYC Summary -->
            <a href="{{ route('reports.kyc-summary') }}" class="card hover:border-violet-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-violet-50 text-violet-600 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-violet-600 transition-colors">Customer KYC Summary</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Tier 1/2/3 distribution, BVN/NIN coverage, KYC verification status, and pending verification queue.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-violet-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Fee & Charges Register -->
            <a href="{{ route('reports.fee-charges-register') }}" class="card hover:border-yellow-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600 group-hover:bg-yellow-500 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-yellow-600 transition-colors">Fee & Charges Register</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">All fees and charges collected by type (processing, maintenance, penalty, transfer) in a date range.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-yellow-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Staff Activity Audit -->
            <a href="{{ route('reports.staff-activity-audit') }}" class="card hover:border-slate-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-slate-100 text-slate-600 group-hover:bg-slate-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-slate-600 transition-colors">Staff Activity Audit</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Track approvals, rejections, and system actions per staff member — supports internal audit and fraud detection.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-slate-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

        </div>
    </div>

    <!-- Financial & Regulatory Reports -->
    <div>
        <h3 class="text-sm font-semibold text-bankos-text uppercase tracking-wider mb-4 px-2">Financial & Regulatory</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <!-- GL Movements -->
            <a href="{{ route('reports.gl-movements') }}" class="card hover:border-bankos-primary/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path><path d="M22 12A10 10 0 0 0 12 2v10z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-indigo-600 transition-colors">GL Account Movements</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Detailed double-entry postings to individual General Ledger accounts over a date range.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-indigo-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- IFRS 9 ECL -->
            <a href="{{ route('reports.ifrs9') }}" class="card hover:border-bankos-primary/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-emerald-50 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-emerald-600 transition-colors">IFRS 9 Expected Credit Loss</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Regulatory ECL compliance report outlining staging (1/2/3), provision amounts, and coverage ratios.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-emerald-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Interest Accrual -->
            <a href="{{ route('reports.interest-accrual') }}" class="card hover:border-bankos-primary/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-purple-50 text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-purple-600 transition-colors">Interest Accrual</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Daily breakdown of interest expected (accrued) vs interest actually posted to the ledger.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-purple-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- Trial Balance -->
            <a href="{{ route('reports.trial-balance') }}" class="card hover:border-bankos-primary/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-green-50 text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-green-600 transition-colors">Trial Balance</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Double-entry accounting summary of all active General Ledger accounts showing debits vs credits.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-green-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            <!-- CBN Single Obligor Limit -->
            <a href="{{ route('reports.single-obligor-limit') }}" class="card hover:border-red-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-red-50 text-red-600 group-hover:bg-red-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-red-600 transition-colors">CBN Single Obligor Limit</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Identifies customers whose total loan exposure exceeds 20% of shareholders' equity — CBN regulatory compliance.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-red-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            {{-- Loan Due Today by Officer --}}
            <a href="{{ route('reports.loan-due-today') }}" class="card hover:border-orange-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-orange-50 text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-orange-600 transition-colors">Loans Due Today by Officer</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Daily view of loan repayments due, grouped by account officer — helps collections teams prioritise follow-ups.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-orange-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

            {{-- Fixed Assets Register --}}
            <a href="{{ route('reports.fixed-assets') }}" class="card hover:border-indigo-500/50 transition-all hover:shadow-md group block h-full">
                <div class="flex items-start gap-4">
                    <div class="p-3 rounded-lg bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-bankos-text group-hover:text-indigo-600 transition-colors">Fixed Assets Register</h4>
                        <p class="text-sm text-bankos-text-sec mt-1 line-clamp-2">Full inventory of fixed assets grouped by category — cost, accumulated depreciation, net book value, and status.</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-xs font-semibold text-indigo-600 flex items-center justify-between opacity-0 group-hover:opacity-100 transition-opacity">
                    <span>Generate Report</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </div>
            </a>

        </div>
    </div>
</x-app-layout>
