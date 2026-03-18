{{--
    Lien & PND Panel Partial
    Usage: @include('accounts._lien_panel', ['account' => $account])
    Expects $account with activeLiens relationship loaded.
--}}
<div class="card p-0 overflow-hidden">
    <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
        <div>
            <h3 class="font-bold text-lg text-bankos-text dark:text-bankos-dark-text">Liens &amp; PND Controls</h3>
            <p class="text-xs text-bankos-muted mt-0.5">Restrict account funds via liens or Post-No-Debit flags</p>
        </div>
        @if($account->pnd_active)
        <span class="badge badge-danger flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
            PND Active
        </span>
        @endif
    </div>

    {{-- Flash messages (partial scope) --}}
    @if(session('success'))
    <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mx-6 mt-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-300">
        {{ session('error') }}
    </div>
    @endif

    {{-- ── PND Section ── --}}
    <div class="px-6 py-5 border-b border-bankos-border dark:border-bankos-dark-border">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text mb-1">Post-No-Debit (PND)</p>
                @if($account->pnd_active)
                <p class="text-sm text-bankos-text-sec">
                    <span class="font-medium text-bankos-text dark:text-white">Reason:</span> {{ $account->pnd_reason }}
                </p>
                <p class="text-xs text-bankos-muted mt-1">
                    Placed {{ $account->pnd_placed_at ? $account->pnd_placed_at->format('d M Y, H:i') : '—' }}
                </p>
                @else
                <p class="text-sm text-bankos-muted">No PND restriction currently active on this account.</p>
                @endif
            </div>
            @can('accounts.edit')
            <div class="flex-shrink-0">
                @if($account->pnd_active)
                {{-- Toggle OFF form (reason not required to lift) --}}
                <form action="{{ route('accounts.liens.pnd', $account) }}" method="POST">
                    @csrf
                    <input type="hidden" name="reason" value="lifted">
                    <button type="submit"
                            class="btn btn-secondary text-sm text-bankos-success border-bankos-success hover:bg-green-50"
                            onclick="return confirm('Remove PND from this account?')">
                        Remove PND
                    </button>
                </form>
                @else
                {{-- Toggle ON --}}
                <button onclick="document.getElementById('pnd-form-{{ $account->id }}').classList.toggle('hidden')"
                        class="btn btn-secondary text-sm text-red-600 border-red-300 hover:bg-red-50 dark:hover:bg-red-900/20">
                    Place PND
                </button>
                @endif
            </div>
            @endcan
        </div>

        {{-- PND placement form (hidden by default) --}}
        @if(!$account->pnd_active)
        @can('accounts.edit')
        <form id="pnd-form-{{ $account->id }}"
              action="{{ route('accounts.liens.pnd', $account) }}" method="POST"
              class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-200 dark:border-red-800 space-y-3">
            @csrf
            <p class="text-sm font-semibold text-red-700 dark:text-red-400">Place Post-No-Debit Restriction</p>
            <div>
                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Reason <span class="text-red-500">*</span>
                </label>
                <input type="text" name="reason" maxlength="255"
                       class="form-input w-full" required placeholder="e.g. Court order ref. FHC/2026/001">
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary bg-red-600 hover:bg-red-700 border-red-600 text-sm">
                    Confirm — Place PND
                </button>
                <button type="button"
                        onclick="document.getElementById('pnd-form-{{ $account->id }}').classList.add('hidden')"
                        class="btn btn-secondary text-sm">Cancel</button>
            </div>
        </form>
        @endcan
        @endif
    </div>

    {{-- ── Active Liens List ── --}}
    <div class="px-6 py-5 border-b border-bankos-border dark:border-bankos-dark-border">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-bankos-text dark:text-bankos-dark-text">Active Liens</p>
            @if($account->activeLiens->isNotEmpty())
            <span class="badge badge-danger text-xs">
                Total Liened: {{ number_format($account->total_lien_amount, 2) }}
            </span>
            @endif
        </div>

        @if($account->activeLiens->isEmpty())
        <p class="text-sm text-bankos-muted py-2">No active liens on this account.</p>
        @else
        <div class="space-y-3">
            @foreach($account->activeLiens as $lien)
            <div class="flex items-start justify-between gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800/40 border border-bankos-border dark:border-bankos-dark-border">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-bold text-bankos-text dark:text-white text-sm">
                            {{ number_format($lien->amount, 2) }}
                        </span>
                        <span class="badge badge-pending text-xs uppercase">{{ str_replace('_',' ', $lien->lien_type) }}</span>
                        @if($lien->expires_at && $lien->expires_at->isPast())
                        <span class="badge badge-danger text-xs">Expired</span>
                        @endif
                    </div>
                    <p class="text-sm text-bankos-text-sec mt-1 truncate">{{ $lien->reason }}</p>
                    <div class="flex items-center gap-3 mt-1.5 text-xs text-bankos-muted flex-wrap">
                        @if($lien->reference)
                        <span class="font-mono">Ref: {{ $lien->reference }}</span>
                        <span>•</span>
                        @endif
                        @if($lien->placedBy)
                        <span>By {{ $lien->placedBy->name }}</span>
                        @endif
                        @if($lien->expires_at)
                        <span>• Expires {{ $lien->expires_at->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
                @can('accounts.edit')
                <form action="{{ route('accounts.liens.lift', $lien) }}" method="POST" class="flex-shrink-0">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="text-xs text-bankos-success hover:underline font-medium"
                            onclick="return confirm('Lift this lien?')">
                        Lift
                    </button>
                </form>
                @endcan
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- ── Add Lien Form ── --}}
    @can('accounts.edit')
    <div class="px-6 py-5">
        <button onclick="document.getElementById('add-lien-form-{{ $account->id }}').classList.toggle('hidden')"
                class="flex items-center gap-2 text-sm font-semibold text-bankos-primary hover:text-blue-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Place New Lien
        </button>

        <form id="add-lien-form-{{ $account->id }}"
              action="{{ route('accounts.liens.store', $account) }}" method="POST"
              class="hidden mt-4 space-y-4">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                        Amount <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="amount" min="1" step="0.01"
                           class="form-input w-full" required placeholder="0.00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                        Lien Type <span class="text-red-500">*</span>
                    </label>
                    <select name="lien_type" class="form-input w-full" required>
                        <option value="">— Select type —</option>
                        <option value="loan_collateral">Loan Collateral</option>
                        <option value="court_order">Court Order</option>
                        <option value="regulatory">Regulatory</option>
                        <option value="internal">Internal</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">
                    Reason <span class="text-red-500">*</span>
                </label>
                <input type="text" name="reason" maxlength="500"
                       class="form-input w-full" required placeholder="Describe the reason for this lien">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Reference</label>
                    <input type="text" name="reference" maxlength="100"
                           class="form-input w-full" placeholder="e.g. Loan #, Court File #">
                </div>
                <div>
                    <label class="block text-sm font-medium text-bankos-text dark:text-bankos-dark-text mb-1">Expires On</label>
                    <input type="date" name="expires_at" class="form-input w-full">
                </div>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary text-sm">Place Lien</button>
                <button type="button"
                        onclick="document.getElementById('add-lien-form-{{ $account->id }}').classList.add('hidden')"
                        class="btn btn-secondary text-sm">Cancel</button>
            </div>
        </form>
    </div>
    @endcan
</div>
