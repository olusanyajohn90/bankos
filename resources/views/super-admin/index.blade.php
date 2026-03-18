<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-bankos-text dark:text-bankos-dark-text">Platform Control Tower</h2>
                <p class="text-sm text-bankos-muted mt-0.5">Global overview — All Banks</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-bankos-primary/10 text-bankos-primary text-sm font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/></svg>
                    {{ $totalTenants }} Banks
                </span>
                <a href="{{ route('super-admin.export') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-bankos-primary text-white text-sm font-medium rounded-lg hover:bg-bankos-primary/90 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.29"/></svg>
                    Export for AI/Analytics
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Alpine state for the drill-down modal and table sorting --}}
    <div x-data="{
        modalOpen: false,
        modalLoading: false,
        modalData: null,
        openDrill(tenantId) {
            this.modalOpen = true;
            this.modalLoading = true;
            this.modalData = null;
            fetch('/super-admin/tenant/' + tenantId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => { this.modalData = data; this.modalLoading = false; })
                .catch(() => { this.modalLoading = false; });
        },
        sortCol: 'customer_count',
        sortDir: 'desc',
        sortBy(col) {
            if (this.sortCol === col) {
                this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortCol = col;
                this.sortDir = 'desc';
            }
        }
    }" class="p-6 space-y-6">

        {{-- ── KPI Bar ───────────────────────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">

            {{-- 1. Total Banks --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Banks</p>
                <p class="text-3xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ $totalTenants }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-1 text-xs text-emerald-600 font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        {{ $activeTenants }} active
                    </span>
                    @if($totalTenants - $activeTenants > 0)
                    <span class="inline-flex items-center gap-1 text-xs text-red-500 font-medium">
                        <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                        {{ $totalTenants - $activeTenants }} other
                    </span>
                    @endif
                </div>
            </div>

            {{-- 2. Total Customers --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Customers</p>
                <p class="text-3xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($totalCustomers) }}</p>
                <p class="text-xs text-bankos-muted mt-2">
                    {{ number_format($totalPortalActive) }} portal active
                    @if($totalCustomers > 0)
                        <span class="text-bankos-primary font-semibold">({{ round(($totalPortalActive / $totalCustomers) * 100, 1) }}%)</span>
                    @endif
                </p>
            </div>

            {{-- 3. Platform Deposits --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Platform Deposits</p>
                <p class="text-3xl font-bold text-bankos-text dark:text-bankos-dark-text">₦{{ number_format($platformSavings / 1000000, 1) }}M</p>
                <p class="text-xs text-bankos-muted mt-2">Total savings book</p>
            </div>

            {{-- 4. Platform Loan Book --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Loan Book</p>
                <p class="text-3xl font-bold text-bankos-text dark:text-bankos-dark-text">₦{{ number_format($platformLoanBook / 1000000, 1) }}M</p>
                <p class="text-xs mt-2 {{ $platformNplRatio > 5 ? 'text-red-500 font-semibold' : 'text-bankos-muted' }}">
                    NPL: {{ $platformNplRatio }}%
                </p>
            </div>

            {{-- 5. Transactions Today --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Transactions Today</p>
                <p class="text-3xl font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($transactionsToday) }}</p>
                <p class="text-xs text-bankos-muted mt-2">
                    Month vol: ₦{{ number_format($transactionsThisMonthVolume / 1000000, 1) }}M
                </p>
            </div>
        </div>

        {{-- ── Tenant Scorecards Table ───────────────────────────────────────── --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Tenant Scorecards</h3>
                <p class="text-xs text-bankos-muted">Click column headers to sort</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">
                                <button @click="sortBy('name')" class="flex items-center gap-1 hover:text-bankos-primary transition-colors">
                                    Bank Name
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Status</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">
                                <button @click="sortBy('customer_count')" class="flex items-center gap-1 ml-auto hover:text-bankos-primary transition-colors">
                                    Customers
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Portal Active</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">
                                <button @click="sortBy('savings_book')" class="flex items-center gap-1 ml-auto hover:text-bankos-primary transition-colors">
                                    Savings Book
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">
                                <button @click="sortBy('loan_book')" class="flex items-center gap-1 ml-auto hover:text-bankos-primary transition-colors">
                                    Loan Book
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </button>
                            </th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">NPL%</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody x-data="{
                        rows: {{ Js::from($tenantStats->map(function($t) {
                            $nplRatio = $t->loan_book > 0 ? round(($t->npl_book / $t->loan_book) * 100, 2) : 0;
                            return [
                                'id' => $t->id,
                                'name' => $t->name,
                                'short_name' => $t->short_name,
                                'type' => $t->type,
                                'status' => $t->status,
                                'domain' => $t->domain,
                                'customer_count' => $t->customer_count,
                                'portal_active' => $t->portal_active,
                                'savings_book' => (float)$t->savings_book,
                                'loan_book' => (float)$t->loan_book,
                                'npl_book' => (float)$t->npl_book,
                                'npl_ratio' => $nplRatio,
                            ];
                        })) }},
                        get sorted() {
                            return [...this.rows].sort((a, b) => {
                                let av = a[this.$root.sortCol] ?? '';
                                let bv = b[this.$root.sortCol] ?? '';
                                if (typeof av === 'string') av = av.toLowerCase();
                                if (typeof bv === 'string') bv = bv.toLowerCase();
                                if (av < bv) return this.$root.sortDir === 'asc' ? -1 : 1;
                                if (av > bv) return this.$root.sortDir === 'asc' ? 1 : -1;
                                return 0;
                            });
                        }
                    }" class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        <template x-for="row in sorted" :key="row.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                                <td class="px-4 py-3">
                                    <a :href="row.domain ? 'https://' + row.domain : '#'" target="_blank"
                                       class="font-medium text-bankos-primary hover:underline" x-text="row.name"></a>
                                    <p class="text-xs text-bankos-muted" x-text="row.short_name"></p>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-bankos-dark-bg text-bankos-muted capitalize" x-text="row.type || '—'"></span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium"
                                          :class="row.status === 'active' ? 'text-emerald-600' : 'text-red-500'">
                                        <span class="w-2 h-2 rounded-full inline-block"
                                              :class="row.status === 'active' ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        <span x-text="row.status" class="capitalize"></span>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium" x-text="row.customer_count.toLocaleString()"></td>
                                <td class="px-4 py-3 text-right">
                                    <span x-text="row.portal_active.toLocaleString()"></span>
                                    <span class="text-xs text-bankos-muted ml-1"
                                          x-text="row.customer_count > 0 ? '(' + Math.round((row.portal_active / row.customer_count) * 100) + '%)' : ''"></span>
                                </td>
                                <td class="px-4 py-3 text-right font-medium"
                                    x-text="'₦' + (row.savings_book / 1000000).toFixed(1) + 'M'"></td>
                                <td class="px-4 py-3 text-right font-medium"
                                    x-text="'₦' + (row.loan_book / 1000000).toFixed(1) + 'M'"></td>
                                <td class="px-4 py-3 text-right">
                                    <span :class="row.npl_ratio > 5 ? 'text-red-500 font-semibold' : 'text-bankos-text dark:text-bankos-dark-text'"
                                          x-text="row.npl_ratio.toFixed(1) + '%'"></span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button @click="$root.openDrill(row.id)"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-bankos-primary border border-bankos-primary/30 rounded-lg hover:bg-bankos-primary hover:text-white transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                        Drill Down
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ── Performance Leaderboard ───────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Top by Deposits --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h4 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                    Top by Deposits
                </h4>
                <ol class="space-y-3">
                    @foreach($topByDeposits as $i => $t)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-bankos-primary/10 text-bankos-primary text-xs font-bold grid place-items-center flex-shrink-0">{{ $i + 1 }}</span>
                            <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $t->short_name ?: $t->name }}</span>
                        </div>
                        <span class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">₦{{ number_format($t->savings_book / 1000000, 1) }}M</span>
                    </li>
                    @endforeach
                </ol>
            </div>

            {{-- Top by Customers --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h4 class="font-semibold text-bankos-text dark:text-bankos-dark-text mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Top by Customer Count
                </h4>
                <ol class="space-y-3">
                    @foreach($topByCustomers as $i => $t)
                    <li class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-bankos-primary/10 text-bankos-primary text-xs font-bold grid place-items-center flex-shrink-0">{{ $i + 1 }}</span>
                            <span class="text-sm font-medium text-bankos-text dark:text-bankos-dark-text">{{ $t->short_name ?: $t->name }}</span>
                        </div>
                        <span class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">{{ number_format($t->customer_count) }}</span>
                    </li>
                    @endforeach
                </ol>
            </div>

            {{-- Alert Banks --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h4 class="font-semibold text-red-500 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-500"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    Banks with Alerts
                </h4>
                @if($alertBanks->isEmpty())
                    <p class="text-sm text-bankos-muted text-center py-4">No alerts — all banks healthy</p>
                @else
                    <div class="space-y-2">
                        @foreach($alertBanks as $t)
                        @php
                            $nplPct = $t->loan_book > 0 ? round(($t->npl_book / $t->loan_book) * 100, 1) : 0;
                        @endphp
                        <div class="rounded-lg bg-red-50 dark:bg-red-900/10 border border-red-200 dark:border-red-800/30 px-3 py-2">
                            <p class="text-sm font-semibold text-red-700 dark:text-red-400">{{ $t->name }}</p>
                            <div class="flex flex-wrap gap-x-3 gap-y-0.5 mt-0.5">
                                @if($t->status !== 'active')
                                    <span class="text-xs text-red-500">Status: {{ $t->status }}</span>
                                @endif
                                @if($nplPct > 10)
                                    <span class="text-xs text-red-500">NPL: {{ $nplPct }}%</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ── Recent Platform Events ────────────────────────────────────────── --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Recent Platform Events</h3>
                <p class="text-xs text-bankos-muted mt-0.5">Last 20 events across all tenants</p>
            </div>
            @if($recentEvents->isEmpty())
                <div class="px-6 py-10 text-center text-bankos-muted text-sm">
                    No platform events recorded yet. Events will appear here as they are captured.
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-bankos-dark-bg">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Time</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Bank</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Event Type</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Entity</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @foreach($recentEvents as $event)
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                            <td class="px-4 py-3 text-bankos-muted text-xs whitespace-nowrap">{{ \Carbon\Carbon::parse($event->created_at)->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-bankos-text dark:text-bankos-dark-text">{{ $event->tenant_short_name ?: $event->tenant_name }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-bankos-primary/10 text-bankos-primary">
                                    {{ $event->event_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-bankos-muted capitalize">{{ $event->entity_type }}</td>
                            <td class="px-4 py-3 text-right font-medium">
                                {{ $event->amount ? '₦' . number_format($event->amount, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- ── Drill Down Modal ─────────────────────────────────────────────── --}}
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
             @keydown.escape.window="modalOpen = false"
             style="display: none;">

            <div @click.outside="modalOpen = false"
                 class="bg-white dark:bg-bankos-dark-surface rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">

                {{-- Modal header --}}
                <div class="flex items-center justify-between px-6 py-5 border-b border-bankos-border dark:border-bankos-dark-border sticky top-0 bg-white dark:bg-bankos-dark-surface z-10">
                    <div>
                        <h3 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text"
                            x-text="modalData ? modalData.tenant.name : 'Loading…'"></h3>
                        <p class="text-sm text-bankos-muted"
                           x-text="modalData ? (modalData.tenant.type || '') + ' — ' + (modalData.tenant.status || '') : ''"></p>
                    </div>
                    <button @click="modalOpen = false" class="text-bankos-muted hover:text-bankos-text dark:hover:text-white p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-bankos-dark-bg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                {{-- Loading spinner --}}
                <div x-show="modalLoading" class="flex items-center justify-center py-20">
                    <div class="w-10 h-10 border-4 border-bankos-primary border-t-transparent rounded-full animate-spin"></div>
                </div>

                {{-- Modal content --}}
                <div x-show="modalData && !modalLoading" class="p-6 space-y-6">

                    {{-- Tenant info bar --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-xs text-bankos-muted mb-0.5">Domain</p>
                            <p class="font-medium text-bankos-text dark:text-bankos-dark-text" x-text="modalData?.tenant.domain || '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-bankos-muted mb-0.5">CBN License</p>
                            <p class="font-medium text-bankos-text dark:text-bankos-dark-text" x-text="modalData?.tenant.cbn_license || '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-bankos-muted mb-0.5">Joined</p>
                            <p class="font-medium text-bankos-text dark:text-bankos-dark-text" x-text="modalData?.tenant.joined ? new Date(modalData.tenant.joined).toLocaleDateString() : '—'"></p>
                        </div>
                        <div>
                            <p class="text-xs text-bankos-muted mb-0.5">Status</p>
                            <p class="font-medium capitalize"
                               :class="modalData?.tenant.status === 'active' ? 'text-emerald-600' : 'text-red-500'"
                               x-text="modalData?.tenant.status || '—'"></p>
                        </div>
                    </div>

                    {{-- Metrics grid --}}
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Customers</p>
                            <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text" x-text="modalData?.metrics.customers.total?.toLocaleString()"></p>
                            <p class="text-xs text-bankos-muted mt-1" x-text="(modalData?.metrics.customers.portal_active || 0).toLocaleString() + ' portal active'"></p>
                            <p class="text-xs text-bankos-muted" x-text="(modalData?.metrics.customers.kyc_verified || 0).toLocaleString() + ' KYC verified'"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Savings Book</p>
                            <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text"
                               x-text="'₦' + ((modalData?.metrics.deposits.savings || 0) / 1000000).toFixed(1) + 'M'"></p>
                            <p class="text-xs text-bankos-muted mt-1"
                               x-text="'Current: ₦' + ((modalData?.metrics.deposits.current || 0) / 1000000).toFixed(1) + 'M'"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Loan Book</p>
                            <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text"
                               x-text="'₦' + ((modalData?.metrics.loans.book || 0) / 1000000).toFixed(1) + 'M'"></p>
                            <p class="text-xs mt-1"
                               :class="(modalData?.metrics.loans.npl_ratio || 0) > 5 ? 'text-red-500 font-semibold' : 'text-bankos-muted'"
                               x-text="'NPL: ' + (modalData?.metrics.loans.npl_ratio || 0).toFixed(1) + '%'"></p>
                            <p class="text-xs text-bankos-muted"
                               x-text="(modalData?.metrics.loans.active_count || 0) + ' active loans'"></p>
                        </div>
                        <div class="bg-gray-50 dark:bg-bankos-dark-bg rounded-xl p-4">
                            <p class="text-xs text-bankos-muted mb-1">Transactions (30d)</p>
                            <p class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text"
                               x-text="(modalData?.metrics.transactions_30d.count || 0).toLocaleString()"></p>
                            <p class="text-xs text-bankos-muted mt-1"
                               x-text="'Vol: ₦' + ((modalData?.metrics.transactions_30d.volume || 0) / 1000000).toFixed(1) + 'M'"></p>
                            <p class="text-xs text-bankos-muted"
                               x-text="'Fees: ₦' + ((modalData?.metrics.transactions_30d.fee_revenue || 0) / 1000).toFixed(0) + 'K'"></p>
                        </div>
                    </div>

                    {{-- Daily series mini chart (sparkline as bars) --}}
                    <div>
                        <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-3">Daily Transaction Volume (Last 30 Days)</p>
                        <div class="flex items-end gap-0.5 h-16 w-full" x-show="modalData?.daily_series_30d?.length > 0">
                            <template x-for="(day, idx) in modalData?.daily_series_30d || []" :key="idx">
                                <div class="flex-1 bg-bankos-primary/70 hover:bg-bankos-primary rounded-t transition-colors cursor-default relative group"
                                     :style="'height: ' + (modalData.daily_series_30d.reduce((m,d) => Math.max(m, d.txn_volume), 1) > 0 ? Math.max(4, (day.txn_volume / modalData.daily_series_30d.reduce((m,d) => Math.max(m, d.txn_volume), 1)) * 100) : 4) + '%;'">
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 hidden group-hover:block bg-gray-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap z-10">
                                        <span x-text="day.date"></span><br>
                                        <span x-text="'₦' + (day.txn_volume / 1000).toFixed(0) + 'K, ' + day.txn_count + ' txns'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <div class="flex justify-between mt-1">
                            <span class="text-xs text-bankos-muted" x-text="modalData?.daily_series_30d?.[0]?.date || ''"></span>
                            <span class="text-xs text-bankos-muted" x-text="modalData?.daily_series_30d?.[modalData.daily_series_30d.length - 1]?.date || ''"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
