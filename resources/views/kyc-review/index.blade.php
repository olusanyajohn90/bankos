<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">KYC Upgrade Requests</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Review customer-submitted KYC upgrade documents and manage tier levels</p>
            </div>
            <button x-data x-ref="noop" @click="$dispatch('open-kyc-modal')"
                    class="btn btn-primary text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                Manual Tier Adjustment
            </button>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- Analytics Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Pending Review</p>
            <p class="text-3xl font-black {{ $stats['pending'] > 0 ? 'text-amber-600' : 'text-bankos-text dark:text-bankos-dark-text' }}">{{ $stats['pending'] }}</p>
            <p class="text-xs text-bankos-muted mt-1">Awaiting decision</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Approved This Month</p>
            <p class="text-3xl font-black text-green-600">{{ $stats['approved_month'] }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ now()->format('F Y') }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Rejected This Month</p>
            <p class="text-3xl font-black text-red-600">{{ $stats['rejected_month'] }}</p>
            <p class="text-xs text-bankos-muted mt-1">{{ now()->format('F Y') }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-1">Total Requests</p>
            <p class="text-3xl font-black text-bankos-text dark:text-bankos-dark-text">{{ $stats['total_requests'] }}</p>
            <p class="text-xs text-bankos-muted mt-1">All time</p>
        </div>
    </div>

    {{-- Tier Distribution --}}
    <div class="card p-5 mb-6">
        <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-3">Customer KYC Tier Distribution</p>
        <div class="grid grid-cols-3 gap-4">
            @php
            $tierLabels = ['level_1' => ['label' => 'Tier 1', 'color' => 'bg-gray-100 text-gray-700', 'bar' => 'bg-gray-400'], 'level_2' => ['label' => 'Tier 2', 'color' => 'bg-blue-100 text-blue-700', 'bar' => 'bg-blue-500'], 'level_3' => ['label' => 'Tier 3', 'color' => 'bg-green-100 text-green-700', 'bar' => 'bg-green-500']];
            $totalCustomers = $tierDist->sum() ?: 1;
            @endphp
            @foreach($tierLabels as $key => $t)
            @php $cnt = $tierDist->get($key, 0); $pct = round(($cnt / $totalCustomers) * 100); @endphp
            <div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-xs font-semibold {{ $t['color'] }} px-2 py-0.5 rounded-full">{{ $t['label'] }}</span>
                    <span class="text-sm font-bold text-bankos-text dark:text-bankos-dark-text">{{ number_format($cnt) }}</span>
                </div>
                <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="{{ $t['bar'] }} h-full rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs text-bankos-muted mt-1">{{ $pct }}% of customers</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Requests Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/30 flex gap-3 items-center justify-between">
            <form action="{{ route('kyc-review.index') }}" method="GET" class="flex gap-3 items-center">
                <select name="status" onchange="this.form.submit()" class="form-select text-sm py-2">
                    <option value="">All Statuses</option>
                    @foreach(['pending','approved','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                @if(request('status'))
                <a href="{{ route('kyc-review.index') }}" class="text-sm text-bankos-primary hover:underline">Clear</a>
                @endif
            </form>
            @if($stats['pending'] > 0)
            <span class="bg-amber-100 text-amber-700 text-sm font-bold px-3 py-1 rounded-full">{{ $stats['pending'] }} Pending</span>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Customer</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-center">Current Tier</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider text-center">Target Tier</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">ID Type</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Submitted</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($requests as $req)
                    @php
                    $sc = ['pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300', 'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300', 'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300'][$req->status] ?? 'bg-gray-100 text-gray-500';
                    $tierLabels2 = ['level_1' => 'Tier 1', 'level_2' => 'Tier 2', 'level_3' => 'Tier 3'];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-4 py-3">
                            <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $req->customer_name }}</p>
                            <p class="text-xs text-bankos-muted">{{ $req->customer_phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">{{ $tierLabels2[$req->current_tier] ?? $req->current_tier }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 px-2 py-0.5 rounded-full">{{ $tierLabels2[$req->target_tier] ?? $req->target_tier }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-bankos-text-sec capitalize">{{ str_replace('_',' ', $req->id_type ?? '—') }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-semibold px-2 py-1 rounded-full {{ $sc }}">{{ strtoupper($req->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-bankos-muted">{{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('kyc-review.show', $req->id) }}" class="text-xs font-semibold text-bankos-primary hover:underline">Review →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-12 text-center text-bankos-muted text-sm">No KYC upgrade requests found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">{{ $requests->links() }}</div>
    </div>

    {{-- Manual Tier Adjustment Modal --}}
    <div x-data="{ open: false }" @open-kyc-modal.window="open = true" x-cloak>
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-lg mx-4 p-6" @click.outside="open = false">
            <div class="flex justify-between items-center mb-5">
                <div>
                    <h3 class="text-base font-bold text-bankos-text dark:text-bankos-dark-text">Manual KYC Tier Adjustment</h3>
                    <p class="text-xs text-bankos-muted mt-0.5">Admin-initiated tier change — bypasses customer request flow</p>
                </div>
                <button @click="open = false" class="text-bankos-muted hover:text-bankos-text">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('kyc-review.manual-adjust') }}" id="manualAdjustForm">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Customer <span class="text-red-500">*</span></label>
                    <select name="customer_id" id="manualCustomerSelect" required
                            class="form-select w-full text-sm"
                            onchange="updateCurrentTier(this)">
                        <option value="">— Select a customer —</option>
                        @foreach($customers as $c)
                        <option value="{{ $c->id }}"
                                data-tier="{{ $c->kyc_tier }}"
                                data-num="{{ $c->customer_number }}">
                            {{ $c->name }} ({{ $c->customer_number }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Current Tier</label>
                        <div id="currentTierDisplay" class="form-input bg-gray-50 dark:bg-gray-800 text-sm text-bankos-muted cursor-not-allowed">—</div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">New Tier <span class="text-red-500">*</span></label>
                        <select name="new_tier" required class="form-select w-full text-sm">
                            <option value="">— Select —</option>
                            <option value="level_1">Tier 1 (Basic)</option>
                            <option value="level_2">Tier 2 (Enhanced)</option>
                            <option value="level_3">Tier 3 (Full KYC)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-semibold text-bankos-text-sec uppercase tracking-wider mb-1">Reason / Justification <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="3" required
                              class="form-input w-full text-sm resize-none"
                              placeholder="Document the reason for this manual tier change…"></textarea>
                </div>

                <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg mb-5 text-xs text-amber-800 dark:text-amber-300">
                    <strong>Note:</strong> This action is logged as a synthetic KYC request for audit purposes. The change takes effect immediately.
                </div>

                <div class="flex gap-3 justify-end">
                    <button type="button" @click="open = false"
                            class="btn btn-secondary text-sm">Cancel</button>
                    <button type="submit" class="btn btn-primary text-sm"
                            onclick="return confirm('Apply this manual KYC tier change? This is logged for audit purposes.')">
                        Apply Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
    </div>{{-- end x-data modal wrapper --}}

    @push('scripts')
    <script>
    const kycTierLabels = { level_1: 'Tier 1 (Basic)', level_2: 'Tier 2 (Enhanced)', level_3: 'Tier 3 (Full KYC)' };
    function updateCurrentTier(select) {
        const opt = select.options[select.selectedIndex];
        const tier = opt.dataset.tier;
        document.getElementById('currentTierDisplay').textContent = tier ? (kycTierLabels[tier] || tier) : '—';
    }
    </script>
    @endpush
</x-app-layout>
