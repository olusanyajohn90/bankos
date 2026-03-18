<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Compliance Hub</h2>
            <p class="text-sm text-bankos-text-sec mt-1">NDIC &amp; NFIU regulatory reporting — {{ now()->format('F Y') }}</p>
        </div>
    </x-slot>

    {{-- ─── KPI Cards ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        {{-- Large Transactions (CTR) --}}
        <div class="card p-6 border-t-4 border-t-accent-crimson flex flex-col justify-between">
            <div class="w-11 h-11 rounded-lg bg-red-50 dark:bg-red-900/20 text-accent-crimson flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">CTR (Last 30 Days)</p>
                <h3 class="text-3xl font-bold mt-1 {{ $largeTxns30d > 0 ? 'text-accent-crimson' : 'text-bankos-text dark:text-white' }}">
                    {{ number_format($largeTxns30d) }}
                </h3>
                <p class="text-xs text-bankos-muted mt-1">Transactions ≥ ₦5M (NFIU)</p>
            </div>
        </div>

        {{-- Total Depositors --}}
        <div class="card p-6 border-t-4 border-t-bankos-primary flex flex-col justify-between">
            <div class="w-11 h-11 rounded-lg bg-blue-50 dark:bg-blue-900/20 text-bankos-primary flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Depositors</p>
                <h3 class="text-3xl font-bold mt-1">{{ number_format($totalDepositors) }}</h3>
                <p class="text-xs text-bankos-muted mt-1">Unique customers (NDIC)</p>
            </div>
        </div>

        {{-- Dormant Accounts --}}
        <div class="card p-6 border-t-4 border-t-bankos-warning flex flex-col justify-between">
            <div class="w-11 h-11 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 text-bankos-warning flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Dormant Accounts</p>
                <h3 class="text-3xl font-bold mt-1 {{ $dormantAccounts > 0 ? 'text-bankos-warning' : '' }}">{{ number_format($dormantAccounts) }}</h3>
                <p class="text-xs text-bankos-muted mt-1">Of {{ number_format($totalAccounts) }} active accounts</p>
            </div>
        </div>

        {{-- Total Deposits --}}
        <div class="card p-6 flex flex-col justify-between">
            <div class="w-11 h-11 rounded-lg bg-green-50 dark:bg-green-900/20 text-bankos-success flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Deposits</p>
                <h3 class="text-2xl font-bold mt-1">
                    @if ($totalDeposits >= 1_000_000_000)
                        ₦{{ number_format($totalDeposits / 1_000_000_000, 2) }}B
                    @elseif ($totalDeposits >= 1_000_000)
                        ₦{{ number_format($totalDeposits / 1_000_000, 2) }}M
                    @else
                        ₦{{ number_format($totalDeposits, 0) }}
                    @endif
                </h3>
                <p class="text-xs text-bankos-muted mt-1">Ledger balance, all active accounts</p>
            </div>
        </div>
    </div>

    {{-- ─── Report Access Cards ──────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- NDIC Report --}}
        <div class="card p-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-bankos-primary flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex-1">
                    <h3 class="font-bold text-lg">NDIC Depositors Report</h3>
                    <p class="text-sm text-bankos-muted mt-1">
                        Monthly summary of depositors by account type for Nigerian Deposit Insurance Corporation submission.
                    </p>
                    <div class="mt-4 flex gap-3">
                        <a href="{{ route('compliance.ndic') }}" class="btn btn-primary text-sm">View Report</a>
                        <a href="{{ route('compliance.ndic.download') }}" class="btn btn-secondary text-sm">Download CSV</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- NFIU CTR --}}
        <div class="card p-6 {{ $largeTxns30d > 0 ? 'border border-red-200 dark:border-red-800' : '' }}">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-xl bg-red-50 dark:bg-red-900/20 text-accent-crimson flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-lg">NFIU Currency Transaction Report</h3>
                        @if ($largeTxns30d > 0)
                            <span class="badge badge-danger">{{ $largeTxns30d }} flagged</span>
                        @endif
                    </div>
                    <p class="text-sm text-bankos-muted mt-1">
                        Transactions at or above ₦5,000,000 required for NFIU reporting. Includes customer BVN, account details, and transaction data.
                    </p>
                    <div class="mt-4 flex gap-3">
                        <a href="{{ route('compliance.nfiu-ctr') }}" class="btn btn-primary text-sm">View CTR Report</a>
                        <a href="{{ route('compliance.nfiu-ctr.download') }}" class="btn btn-secondary text-sm">Download CSV</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="card p-6">
            <h3 class="font-bold text-base mb-4">Related Reports</h3>
            <ul class="space-y-2 text-sm">
                <li>
                    <a href="{{ route('reports.dormant-accounts') }}" class="flex items-center gap-2 text-bankos-primary hover:underline">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Dormant Accounts Report
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.suspicious-activity') }}" class="flex items-center gap-2 text-bankos-primary hover:underline">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Suspicious Activity Report
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.kyc-summary') }}" class="flex items-center gap-2 text-bankos-primary hover:underline">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        KYC Compliance Summary
                    </a>
                </li>
                <li>
                    <a href="{{ route('reports.single-obligor-limit') }}" class="flex items-center gap-2 text-bankos-primary hover:underline">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        Single Obligor Limit Report
                    </a>
                </li>
            </ul>
        </div>

        {{-- Regulatory Calendar --}}
        <div class="card p-6">
            <h3 class="font-bold text-base mb-4">Reporting Calendar</h3>
            <ul class="space-y-3 text-sm">
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 rounded-full bg-bankos-primary mt-1.5 shrink-0"></span>
                    <div>
                        <p class="font-semibold">NDIC Monthly Return</p>
                        <p class="text-bankos-muted text-xs">Due by 10th of following month</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 rounded-full bg-accent-crimson mt-1.5 shrink-0"></span>
                    <div>
                        <p class="font-semibold">NFIU CTR Submission</p>
                        <p class="text-bankos-muted text-xs">Within 7 days of transaction date</p>
                    </div>
                </li>
                <li class="flex items-start gap-3">
                    <span class="w-2 h-2 rounded-full bg-bankos-warning mt-1.5 shrink-0"></span>
                    <div>
                        <p class="font-semibold">CBN Prudential Returns</p>
                        <p class="text-bankos-muted text-xs">Quarterly — via Reports module</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</x-app-layout>
