<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight w-full flex justify-between items-center">
                    Interest Accrual Report
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Expected vs Posted Interest over the lifetime of active facilities</p>
            </div>
        </div>
    </x-slot>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-6 border-t-4 border-t-bankos-primary">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Expected Interest</h3>
                    <p class="text-2xl font-bold text-bankos-text mt-1">₦{{ number_format($expectedInterest, 2) }}</p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-full text-bankos-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card p-6 border-t-4 border-t-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Interest Posted (Collected)</h3>
                    <p class="text-2xl font-bold text-green-600 mt-1">₦{{ number_format($postedInterest, 2) }}</p>
                </div>
                <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-full text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
            </div>
        </div>

        <div class="card p-6 border-t-4 border-t-bankos-warning">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Accrued but Unpaid</h3>
                    <p class="text-2xl font-bold text-bankos-warning mt-1">₦{{ number_format($accruedUnpaid, 2) }}</p>
                </div>
                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 rounded-full text-amber-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Progress -->
    <div class="card md:w-2/3">
        <h3 class="text-sm font-semibold text-bankos-text mb-4">Interest Collection Rate</h3>
        
        <div class="flex justify-between items-end mb-2">
            <div>
                <span class="text-3xl font-bold {{ $collectionRate >= 75 ? 'text-green-500' : ($collectionRate >= 50 ? 'text-bankos-warning' : 'text-red-500') }}">{{ number_format($collectionRate, 1) }}%</span>
                <span class="text-sm text-bankos-text-sec ml-2">collected</span>
            </div>
            <div class="text-sm text-bankos-text-sec">Target: 100%</div>
        </div>
        
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 mb-4 overflow-hidden shadow-inner">
            <div class="h-4 rounded-full transition-all duration-1000 ease-out flex justify-end {{ $collectionRate >= 75 ? 'bg-green-500' : ($collectionRate >= 50 ? 'bg-bankos-warning' : 'bg-red-500') }}" style="width: {{ min(100, $collectionRate) }}%"></div>
        </div>

        <div class="grid grid-cols-2 gap-4 mt-8 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
            <div>
                <p class="text-xs text-bankos-text-sec uppercase tracking-wider">Methodology</p>
                <p class="text-sm text-bankos-muted mt-2">
                    This report calculates the total expected interest over the lifetime of all currently active and overdue loans, and compares it against the total interest payments successfully posted to the ledger via transaction records (`type: interest_payment`).
                </p>
            </div>
            <div>
                <p class="text-xs text-bankos-text-sec uppercase tracking-wider">Discrepancies</p>
                <p class="text-sm text-bankos-muted mt-2">
                    Accrued but unpaid interest represents the remaining interest expected to be collected before the active facilities mature or are liquidated. High values relative to expected interest indicate a young portfolio.
                </p>
            </div>
        </div>
    </div>

</x-app-layout>
