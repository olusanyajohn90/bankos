<aside class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-bankos-dark-surface border-r border-bankos-border dark:border-bankos-dark-border transform transition-transform duration-200 ease-in-out md:translate-x-0 overflow-y-auto" :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">
    <!-- Logo -->
    <div class="flex items-center gap-3 h-16 px-6 border-b border-bankos-border dark:border-bankos-dark-border">
        <div class="w-8 h-8 rounded bg-bankos-primary grid place-items-center flex-shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="text-xl font-bold tracking-tight text-bankos-text dark:text-bankos-dark-text">bank<span class="text-bankos-primary">OS</span></span>

        <!-- Mobile close button -->
        <button @click="sidebarOpen = false" class="md:hidden ml-auto text-bankos-muted hover:text-bankos-text dark:hover:text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="p-4 space-y-1">

        {{-- ── CORE ─────────────────────────────────────────────── --}}
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
            <span>Dashboard</span>
        </a>

        @can('customers.view')
        <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('customers.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Customers</span>
        </a>
        @endcan

        @can('accounts.view')
        <a href="{{ route('accounts.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('accounts.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
            <span>Accounts</span>
        </a>
        @endcan

        @can('transactions.view')
        <a href="{{ route('transactions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('transactions.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
            <span>Transactions</span>
        </a>
        @endcan

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── CREDIT & LENDING ─────────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('loans.*','loan-applications.*','groups.*','centres.*','credit.policies.*','par-dashboard.*','ecl.*','bureau.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                    <span class="font-medium text-sm">Credit &amp; Lending</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                @can('loans.view')
                <a href="{{ route('loans.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('loans.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Loans</span>
                </a>
                @endcan
                <a href="{{ route('loan-applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('loan-applications.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Loan Applications</span>
                    @php try { $pendingApps = DB::table('loan_applications')->where('status','pending')->count(); } catch(\Exception $e) { $pendingApps = 0; } @endphp
                    @if($pendingApps > 0)
                    <span class="ml-auto text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $pendingApps }}</span>
                    @endif
                </a>
                <a href="{{ route('groups.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('groups.*','centres.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Groups &amp; Centres</span>
                </a>
                <a href="{{ route('credit.policies.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('credit.policies.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Credit Policies</span>
                </a>
                <a href="{{ route('par-dashboard.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('par-dashboard.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>PAR Dashboard</span>
                </a>
                <a href="{{ route('ecl.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('ecl.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>IFRS 9 / ECL</span>
                </a>
                <a href="{{ route('bureau.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('bureau.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Credit Bureau</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── BANKING PRODUCTS ─────────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('fixed-deposits.*','standing-orders.*','overdrafts.*','cheques.*','fixed-assets.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    <span class="font-medium text-sm">Banking Products</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('fixed-deposits.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('fixed-deposits.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Fixed Deposits</span>
                </a>
                <a href="{{ route('standing-orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('standing-orders.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Standing Orders</span>
                </a>
                <a href="{{ route('overdrafts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('overdrafts.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Overdraft Facilities</span>
                </a>
                <a href="{{ route('cheques.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('cheques.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Cheques</span>
                </a>
                <a href="{{ route('fixed-assets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('fixed-assets.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Fixed Assets</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── OPERATIONS & ANALYTICS ───────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('teller.*','nip.*','mandates.*','collections.*','inbound-transfers.*','posting-files.*','agents.*','insurance.*','reports.*','custom-reports.*','board-pack.*','portal-analytics.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                    <span class="font-medium text-sm">Operations &amp; Analytics</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('teller.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('teller.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Teller</span>
                </a>
                <a href="{{ route('nip.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('nip.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>NIP Transfers</span>
                </a>
                <a href="{{ route('mandates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('mandates.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Mandates</span>
                </a>
                <a href="{{ route('collections.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('collections.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Collections</span>
                </a>
                <a href="{{ route('inbound-transfers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('inbound-transfers.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Inbound Transfers</span>
                </a>
                <a href="{{ route('posting-files.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('posting-files.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Posting Files</span>
                </a>
                <a href="{{ route('agents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('agents.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Agent Banking</span>
                </a>
                <a href="{{ route('insurance.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('insurance.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Insurance</span>
                </a>
                @can('reports.view')
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('reports.*') && !request()->routeIs('custom-reports.*','board-pack.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Reports</span>
                </a>
                @endcan
                <a href="{{ route('custom-reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('custom-reports.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Custom Reports</span>
                </a>
                <a href="{{ route('board-pack.generate') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('board-pack.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Board Pack</span>
                </a>
                <a href="{{ route('portal-analytics.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('portal-analytics.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Portal Analytics</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── COMPLIANCE & RISK ────────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('aml.*','kyc-review.*','transaction-monitor.*','workflows.*','portal-disputes.*','compliance.*','referral-rewards.*','audit-log.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    <span class="font-medium text-sm">Compliance &amp; Risk</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('aml.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('aml.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>AML / Compliance</span>
                    @php try { $amlAlerts = \App\Models\AmlAlert::where('tenant_id', auth()->user()->tenant_id)->whereIn('status',['open','escalated'])->whereIn('severity',['critical','high'])->count(); } catch(\Exception $e) { $amlAlerts = 0; } @endphp
                    @if($amlAlerts > 0)
                    <span class="ml-auto text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $amlAlerts }}</span>
                    @endif
                </a>
                <a href="{{ route('kyc-review.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('kyc-review.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>KYC Review</span>
                    @php try { $pendingKyc = DB::table('kyc_upgrade_requests')->where('status','pending')->count(); } catch (\Exception $e) { $pendingKyc = 0; } @endphp
                    @if($pendingKyc > 0)
                    <span class="ml-auto text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $pendingKyc }}</span>
                    @endif
                </a>
                <a href="{{ route('transaction-monitor.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('transaction-monitor.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Transaction Monitor</span>
                </a>
                @can('workflows.view')
                <a href="{{ route('workflows.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('workflows.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Workflows</span>
                    @php try { $pendingWorkflows = \App\Models\WorkflowInstance::where('status','pending')->whereIn('assigned_role', auth()->user()->getRoleNames()->toArray())->count(); } catch(\Exception $e) { $pendingWorkflows = 0; } @endphp
                    @if($pendingWorkflows > 0)
                    <span class="ml-auto text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $pendingWorkflows }}</span>
                    @endif
                </a>
                @endcan
                <a href="{{ route('portal-disputes.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('portal-disputes.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Portal Disputes</span>
                    @php try { $openDisputes = DB::table('portal_disputes')->where('status','open')->count(); } catch (\Exception $e) { $openDisputes = 0; } @endphp
                    @if($openDisputes > 0)
                    <span class="ml-auto text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $openDisputes }}</span>
                    @endif
                </a>
                <a href="{{ route('compliance.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('compliance.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Regulatory Reports</span>
                </a>
                <a href="{{ route('referral-rewards.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('referral-rewards.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Referral Rewards</span>
                    @php try { $pendingRewards = DB::table('referral_rewards')->where('status','pending')->count(); } catch(\Exception $e) { $pendingRewards = 0; } @endphp
                    @if($pendingRewards > 0)
                    <span class="ml-auto text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $pendingRewards }}</span>
                    @endif
                </a>
                <a href="{{ route('audit-log.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('audit-log.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Audit Log</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── HUMAN RESOURCES & PAYROLL ────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('hr.*','payroll.*','kpi.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span class="font-medium text-sm">HR &amp; Payroll</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <p class="px-3 pt-2 pb-1 text-xs font-semibold text-bankos-muted/70 uppercase tracking-wider">People</p>
                <a href="{{ route('hr.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.dashboard') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>HR Dashboard</span>
                </a>
                <a href="{{ route('hr.org.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.org.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Org Structure</span>
                </a>
                <a href="{{ route('hr.leave.requests.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.leave.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Leave Management</span>
                </a>
                <a href="{{ route('hr.leave.my-requests') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.leave.my-requests') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>My Leave</span>
                </a>
                <a href="{{ route('hr.attendance.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.attendance.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Attendance</span>
                </a>
                <a href="{{ route('hr.performance.cycles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.performance.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Performance Reviews</span>
                </a>
                <a href="{{ route('hr.training.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.training.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Training &amp; Certs</span>
                </a>
                <a href="{{ route('hr.disciplinary.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.disciplinary.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Disciplinary</span>
                </a>
                <a href="{{ route('hr.id-cards.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.id-cards.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>ID Cards</span>
                </a>

                <p class="px-3 pt-3 pb-1 text-xs font-semibold text-bankos-muted/70 uppercase tracking-wider">Payroll</p>
                <a href="{{ route('payroll.runs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('payroll.runs.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Payroll Runs</span>
                </a>
                <a href="{{ route('payroll.my-payslips') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('payroll.my-payslips','payroll.payslip.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>My Payslips</span>
                </a>
                <a href="{{ route('payroll.setup.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('payroll.setup.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Payroll Setup</span>
                </a>
                <a href="{{ route('hr.expense-claims.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.expense-claims.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Expense Claims</span>
                </a>
                <a href="{{ route('hr.salary-advances.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.salary-advances.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Salary Advances</span>
                </a>

                <p class="px-3 pt-3 pb-1 text-xs font-semibold text-bankos-muted/70 uppercase tracking-wider">Approvals</p>
                <a href="{{ route('hr.approvals.requests') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.approvals.requests*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Approval Inbox</span>
                </a>
                <a href="{{ route('hr.approvals.matrix') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.approvals.matrix*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Approval Matrix</span>
                </a>
                <a href="{{ route('hr.announcements.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.announcements.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Notice Board</span>
                </a>
                <a href="{{ route('hr.holidays.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('hr.holidays.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Public Holidays</span>
                </a>

                <p class="px-3 pt-3 pb-1 text-xs font-semibold text-bankos-muted/70 uppercase tracking-wider">KPI</p>
                <a href="{{ route('kpi.me') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('kpi.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>My Performance</span>
                    @php try { $kpiAlerts = \App\Models\KpiAlert::where('recipient_id', auth()->id())->where('severity','red')->where('status','unread')->count(); } catch(\Exception $e) { $kpiAlerts = 0; } @endphp
                    @if($kpiAlerts > 0)
                    <span class="ml-auto text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $kpiAlerts }}</span>
                    @endif
                </a>
                <a href="{{ route('kpi.hq') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('kpi.hq') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>HQ KPI Overview</span>
                </a>
                <a href="{{ route('kpi.definitions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('kpi.definitions.*','kpi.targets.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>KPI Setup</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── INTERNAL WORKSPACE ───────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('documents.*','comms.*','chat.*','notifications.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span class="font-medium text-sm">Internal Workspace</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('documents.index') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Documents</span>
                </a>
                <a href="{{ route('documents.my-actions') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('documents.my-actions') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>My Doc Actions</span>
                    @php try { $pendingDocActions = DB::table('document_workflow_steps')->where('assignee_id', auth()->id())->where('status','pending')->count(); } catch(\Exception $e) { $pendingDocActions = 0; } @endphp
                    @if($pendingDocActions > 0)
                    <span class="ml-auto text-xs bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $pendingDocActions }}</span>
                    @endif
                </a>
                <a href="{{ route('documents.workflows.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('documents.workflows.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Doc Workflows</span>
                </a>
                <a href="{{ route('documents.checklists.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('documents.checklists.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Doc Checklists</span>
                </a>
                <a href="{{ route('comms.inbox.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('comms.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Comms</span>
                    @php try { $unreadComms = DB::table('comm_messages')->where('recipient_id', auth()->id())->where('read_at', null)->count(); } catch(\Exception $e) { $unreadComms = 0; } @endphp
                    @if($unreadComms > 0)
                    <span class="ml-auto text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $unreadComms }}</span>
                    @endif
                </a>
                <a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('chat.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Chat</span>
                    @php try { $unreadChat = DB::table('chat_messages')->where('recipient_id', auth()->id())->whereNull('read_at')->count(); } catch(\Exception $e) { $unreadChat = 0; } @endphp
                    @if($unreadChat > 0)
                    <span class="ml-auto text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $unreadChat }}</span>
                    @endif
                </a>
                <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('notifications.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Notifications</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── CRM & GROWTH ─────────────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('crm.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                    <span class="font-medium text-sm">CRM &amp; Growth</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('crm.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('crm.dashboard') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>CRM Dashboard</span>
                </a>
                <a href="{{ route('crm.leads') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('crm.leads*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Leads &amp; Pipeline</span>
                </a>
                <a href="{{ route('crm.interactions') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('crm.interactions*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Interactions</span>
                </a>
                <a href="{{ route('crm.pipeline.settings') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('crm.pipeline.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Pipeline Stages</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── SUPPORT ──────────────────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('support.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                    <span class="font-medium text-sm">Support</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('support.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('support.dashboard') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Support Dashboard</span>
                    @php try { $openTickets = DB::table('support_tickets')->where('tenant_id', auth()->user()->tenant_id)->whereIn('status',['open','in_progress'])->count(); } catch(\Exception $e) { $openTickets = 0; } @endphp
                    @if($openTickets > 0)
                    <span class="ml-auto text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $openTickets }}</span>
                    @endif
                </a>
                <a href="{{ route('support.tickets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('support.tickets.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Tickets</span>
                </a>
                <a href="{{ route('support.teams.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('support.teams.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Teams</span>
                </a>
                <a href="{{ route('support.sla.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('support.sla.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>SLA Policies</span>
                </a>
                <a href="{{ route('support.categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('support.categories.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Categories</span>
                </a>
                <a href="{{ route('support.kb.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('support.kb.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Knowledge Base</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── VISITOR MANAGEMENT ───────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('visitor.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><line x1="19" y1="8" x2="19" y2="14"></line><line x1="22" y1="11" x2="16" y2="11"></line></svg>
                    <span class="font-medium text-sm">Visitor Management</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('visitor.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('visitor.dashboard') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>VMS Dashboard</span>
                    @php $checkedInVisits = DB::table('visitor_visits')->where('status','checked_in')->count(); @endphp
                    @if($checkedInVisits > 0)
                    <span class="ml-auto text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 font-semibold px-1.5 py-0.5 rounded-full">{{ $checkedInVisits }}</span>
                    @endif
                </a>
                <a href="{{ route('visitor.visits') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('visitor.visits') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Visit Log</span>
                </a>
                <a href="{{ route('visitor.visitors') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('visitor.visitors') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Visitor Registry</span>
                </a>
                <a href="{{ route('visitor.meetings') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('visitor.meetings') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Meetings</span>
                </a>
                <a href="{{ route('visitor.rooms') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('visitor.rooms') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Meeting Rooms</span>
                </a>
                <a href="{{ route('visitor.watchlist') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('visitor.watchlist') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Watchlist</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── ASSETS ───────────────────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('assets.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    <span class="font-medium text-sm">Assets</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('assets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('assets.index','assets.show') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Asset Register</span>
                </a>
                <a href="{{ route('assets.procurement') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('assets.procurement*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Procurement</span>
                </a>
                <a href="{{ route('assets.categories') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('assets.categories*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Categories</span>
                </a>
            </div>
        </div>

        <div class="border-t border-bankos-border dark:border-bankos-dark-border my-1"></div>

        {{-- ── ADMINISTRATION ───────────────────────────────────── --}}
        <div x-data="{ open: {{ request()->routeIs('users.*','roles.*','branches.*','gl-accounts.*','savings-products.*','loan-products.*','investment-products.*','fee-rules.*','feature-flags.*','webhooks.*','ip-whitelist.*','two-factor.*','tenants.*','super-admin.*','subscriptions.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                <span class="flex items-center gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14M12 2v2M12 20v2M2 12h2M20 12h2"></path></svg>
                    <span class="font-medium text-sm">Administration</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="open ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
            </button>
            <div x-show="open" x-transition class="mt-1 space-y-0.5 ml-6 pl-3 border-l border-bankos-border dark:border-bankos-dark-border">
                @can('users.view')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('users.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Users</span>
                </a>
                @endcan
                @if(auth()->user()->hasRole(['tenant_admin','super_admin']))
                <a href="{{ route('roles.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('roles.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Roles &amp; Permissions</span>
                </a>
                @endif
                @can('branches.view')
                <div x-data="{ branchOpen: {{ request()->routeIs('branches.*') ? 'true' : 'false' }} }">
                    <button @click="branchOpen = !branchOpen" class="w-full flex items-center justify-between px-3 py-2 rounded-lg transition-colors text-sm text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                        <span>Branches</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200" :class="branchOpen ? 'rotate-180' : ''"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </button>
                    <div x-show="branchOpen" x-transition class="mt-0.5 space-y-0.5 ml-4 pl-2 border-l border-bankos-border dark:border-bankos-dark-border">
                        <a href="{{ route('branches.index') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors text-sm {{ request()->routeIs('branches.index') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                            <span>Manage Branches</span>
                        </a>
                        <a href="{{ route('branches.analytics') }}" class="flex items-center gap-3 px-3 py-1.5 rounded-lg transition-colors text-sm {{ request()->routeIs('branches.analytics') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                            <span>Branch Analytics</span>
                        </a>
                    </div>
                </div>
                @endcan
                @can('gl.view')
                <a href="{{ route('gl-accounts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('gl-accounts.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>GL Accounts</span>
                </a>
                @endcan
                @if(auth()->user()->can('settings.manage') || auth()->user()->hasRole('tenant_admin'))
                <a href="{{ route('savings-products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('savings-products.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Savings Products</span>
                </a>
                <a href="{{ route('loan-products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('loan-products.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Loan Products</span>
                </a>
                <a href="{{ route('investment-products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('investment-products.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Investment Products</span>
                </a>
                <a href="{{ route('fee-rules.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('fee-rules.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Fee Rules</span>
                </a>
                @endif
                <a href="{{ route('feature-flags.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('feature-flags.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Feature Flags</span>
                </a>
                <a href="{{ route('webhooks.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('webhooks.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Webhooks</span>
                </a>
                <a href="{{ route('ip-whitelist.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('ip-whitelist.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>IP Whitelist</span>
                </a>
                <a href="{{ route('two-factor.show') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('two-factor.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Two-Factor Auth</span>
                    @if(auth()->user()->two_factor_confirmed_at)
                    <span class="ml-auto text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 font-semibold px-1.5 py-0.5 rounded-full">ON</span>
                    @endif
                </a>
                @if(auth()->user()->hasRole('super_admin'))
                <a href="{{ route('tenants.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('tenants.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Institutions</span>
                </a>
                <a href="{{ route('super-admin.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('super-admin.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Platform Control Tower</span>
                </a>
                <a href="{{ route('subscriptions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg transition-colors text-sm {{ request()->routeIs('subscriptions.*') ? 'bg-bankos-light dark:bg-primary/20 text-bankos-primary font-medium' : 'text-bankos-text-sec dark:text-bankos-dark-text-sec hover:bg-gray-50 dark:hover:bg-bankos-dark-bg' }}">
                    <span>Subscriptions</span>
                </a>
                @endif
            </div>
        </div>

        {{-- Bottom padding --}}
        <div class="h-4"></div>

    </nav>
</aside>
