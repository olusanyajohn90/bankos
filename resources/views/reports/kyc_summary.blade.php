<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Customer KYC Summary</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Verification tier distribution, BVN/NIN coverage, and pending queue</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-end mb-6 print:hidden">
        <button class="btn btn-secondary text-sm flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- KPI Row --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Customers</p>
            <p class="text-3xl font-extrabold text-bankos-text mt-1">{{ number_format($customers->count()) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">KYC Verified</p>
            <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ number_format($statusDist['verified']) }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $customers->count() > 0 ? number_format($statusDist['verified'] / $customers->count() * 100, 1) : 0 }}% of total</p>
        </div>
        <div class="card p-5 {{ $statusDist['pending'] > 0 ? 'border-amber-300' : '' }}">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Pending KYC</p>
            <p class="text-3xl font-extrabold text-amber-600 mt-1">{{ number_format($statusDist['pending']) }}</p>
            <p class="text-xs text-bankos-muted mt-1">Awaiting review</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">BVN Coverage</p>
            <p class="text-3xl font-extrabold text-bankos-primary mt-1">{{ number_format($bvnCoverage, 1) }}%</p>
            <p class="text-xs text-bankos-muted mt-1">{{ $bvnVerified }} verified</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- KYC Tier Distribution --}}
        <div class="card p-0 overflow-hidden shadow-md md:col-span-1">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                <h3 class="text-sm font-semibold text-bankos-text">Tier Distribution</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach([1 => ['label'=>'Tier 1 — Basic', 'limit'=>'₦50k/day', 'color'=>'text-slate-600 bg-slate-100'], 2 => ['label'=>'Tier 2 — Standard', 'limit'=>'₦200k/day', 'color'=>'text-blue-600 bg-blue-100'], 3 => ['label'=>'Tier 3 — Premium', 'limit'=>'Unlimited', 'color'=>'text-violet-600 bg-violet-100']] as $tier => $meta)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <span class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full {{ $meta['color'] }}">{{ $meta['label'] }}</span>
                        <p class="text-xs text-bankos-muted mt-0.5">{{ $meta['limit'] }}</p>
                    </div>
                    <p class="text-2xl font-extrabold text-bankos-text">{{ number_format($tierDist[$tier]) }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Verification Status --}}
        <div class="card p-0 overflow-hidden shadow-md md:col-span-1">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
                <h3 class="text-sm font-semibold text-bankos-text">Verification Status</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                <div class="px-6 py-4 flex justify-between items-center">
                    <span class="text-sm font-medium text-emerald-600">Verified</span>
                    <p class="text-xl font-extrabold text-emerald-600">{{ number_format($statusDist['verified']) }}</p>
                </div>
                <div class="px-6 py-4 flex justify-between items-center">
                    <span class="text-sm font-medium text-amber-600">Pending</span>
                    <p class="text-xl font-extrabold text-amber-600">{{ number_format($statusDist['pending']) }}</p>
                </div>
                <div class="px-6 py-4 flex justify-between items-center">
                    <span class="text-sm font-medium text-red-600">Rejected</span>
                    <p class="text-xl font-extrabold text-red-600">{{ number_format($statusDist['rejected']) }}</p>
                </div>
                <div class="px-6 py-4 flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
                    <span class="text-sm font-semibold text-bankos-text">BVN Verified</span>
                    <p class="text-xl font-extrabold text-bankos-primary">{{ number_format($bvnVerified) }}</p>
                </div>
                <div class="px-6 py-4 flex justify-between items-center bg-gray-50 dark:bg-bankos-dark-bg/50">
                    <span class="text-sm font-semibold text-bankos-text">NIN Verified</span>
                    <p class="text-xl font-extrabold text-bankos-primary">{{ number_format($ninVerified) }}</p>
                </div>
            </div>
        </div>

        {{-- Pending KYC summary --}}
        <div class="card p-0 overflow-hidden shadow-md md:col-span-1">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-amber-50 dark:bg-amber-900/20">
                <h3 class="text-sm font-semibold text-amber-700 dark:text-amber-400">Pending Queue ({{ $pendingKyc->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800 max-h-64 overflow-y-auto">
                @forelse($pendingKyc as $c)
                <div class="px-6 py-3 flex justify-between items-center">
                    <div>
                        <p class="text-sm font-medium text-bankos-text">{{ $c->full_name }}</p>
                        <p class="text-xs text-bankos-muted">{{ $c->customer_number }} · Tier {{ $c->kyc_tier }}</p>
                    </div>
                    <p class="text-xs text-bankos-muted">{{ $c->created_at->diffForHumans() }}</p>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-bankos-muted text-sm">No pending KYC reviews.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
