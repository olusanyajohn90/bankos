<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ $fixedAsset->name }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">
                    {{ $fixedAsset->asset_tag ? $fixedAsset->asset_tag . ' &bull; ' : '' }}{{ $fixedAsset->category->name ?? 'Uncategorised' }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                @if ($fixedAsset->status === 'active')
                    <button @click="$dispatch('open-modal', 'revalue-asset')" class="btn btn-primary">
                        Revalue Asset
                    </button>
                    <button @click="$dispatch('open-modal', 'dispose-asset')" class="btn btn-secondary">
                        Dispose Asset
                    </button>
                @endif
                <a href="{{ route('fixed-assets.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── Asset Details ────────────────────────────────────────── --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="card p-6">
                <h3 class="font-bold text-base mb-4">Asset Information</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Status</dt>
                        <dd>
                            @if ($fixedAsset->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @elseif ($fixedAsset->status === 'fully_depreciated')
                                <span class="badge bg-blue-100 text-blue-700 border border-blue-200">Fully Depreciated</span>
                            @elseif ($fixedAsset->status === 'disposed')
                                <span class="badge bg-gray-100 text-gray-500 border border-gray-200">Disposed</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Branch</dt>
                        <dd class="font-medium">{{ $fixedAsset->branch->name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Purchase Date</dt>
                        <dd class="font-medium">{{ $fixedAsset->purchase_date->format('d M Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Purchase Cost</dt>
                        <dd class="font-bold">₦{{ number_format($fixedAsset->purchase_cost, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Residual Value</dt>
                        <dd class="font-medium">₦{{ number_format($fixedAsset->residual_value, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Useful Life</dt>
                        <dd class="font-medium">{{ $fixedAsset->useful_life_years }} years</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Method</dt>
                        <dd class="font-medium capitalize">{{ str_replace('_', ' ', $fixedAsset->depreciation_method) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Added By</dt>
                        <dd class="font-medium">{{ $fixedAsset->purchasedBy->name ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- NBV Gauge Card --}}
            <div class="card p-6">
                <h3 class="font-bold text-base mb-4">Current Values</h3>
                @php
                    $nbvPct = $fixedAsset->purchase_cost > 0
                        ? round((float)$fixedAsset->current_book_value / (float)$fixedAsset->purchase_cost * 100, 1)
                        : 0;
                @endphp
                <div class="space-y-3">
                    <div>
                        <div class="flex justify-between text-xs text-bankos-muted mb-1">
                            <span>Net Book Value</span>
                            <span>{{ $nbvPct }}% of cost</span>
                        </div>
                        <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-bankos-success rounded-full" style="width: {{ $nbvPct }}%"></div>
                        </div>
                        <p class="text-lg font-bold mt-1 text-bankos-success">₦{{ number_format($fixedAsset->current_book_value, 2) }}</p>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-bankos-border dark:border-bankos-dark-border">
                        <div>
                            <p class="text-xs text-bankos-muted uppercase tracking-wider">Acc. Depreciation</p>
                            <p class="font-bold text-accent-purple">₦{{ number_format($fixedAsset->accumulated_depreciation, 2) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-bankos-muted uppercase tracking-wider">Monthly Charge</p>
                            <p class="font-bold">₦{{ number_format($fixedAsset->monthly_depreciation, 2) }}</p>
                        </div>
                    </div>
                    <div class="text-xs text-bankos-muted">
                        Last depreciated: {{ $fixedAsset->last_depreciation_date?->format('d M Y') ?? 'Never' }}
                    </div>
                </div>
            </div>

            @if ($fixedAsset->status === 'disposed')
            <div class="card p-6 border-t-4 border-t-gray-400">
                <h3 class="font-bold text-base mb-3">Disposal Details</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Disposal Date</dt>
                        <dd>{{ $fixedAsset->disposed_at?->format('d M Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-bankos-muted">Proceeds</dt>
                        <dd class="font-bold">₦{{ number_format($fixedAsset->disposal_value ?? 0, 2) }}</dd>
                    </div>
                    @php
                        $gain = (float)($fixedAsset->disposal_value ?? 0) - (float)$fixedAsset->current_book_value;
                    @endphp
                    <div class="flex justify-between border-t border-bankos-border dark:border-bankos-dark-border pt-2">
                        <dt class="font-semibold">Gain / (Loss)</dt>
                        <dd class="font-bold {{ $gain >= 0 ? 'text-bankos-success' : 'text-accent-crimson' }}">
                            ₦{{ number_format(abs($gain), 2) }} {{ $gain >= 0 ? '(Gain)' : '(Loss)' }}
                        </dd>
                    </div>
                </dl>
            </div>
            @endif
        </div>

        {{-- ─── Depreciation Schedule ────────────────────────────────── --}}
        <div class="lg:col-span-2 card p-0 overflow-hidden">
            <div class="p-5 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-bold text-base">Depreciation Schedule</h3>
                <p class="text-xs text-bankos-muted mt-0.5">Projected annual depreciation over useful life</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-5 py-3 font-semibold">Year</th>
                            <th class="px-5 py-3 font-semibold text-right">Opening NBV</th>
                            <th class="px-5 py-3 font-semibold text-right">Depr. Charge</th>
                            <th class="px-5 py-3 font-semibold text-right">Acc. Depr.</th>
                            <th class="px-5 py-3 font-semibold text-right">Closing NBV</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse ($schedule as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-5 py-3 font-semibold">{{ $row['year'] }}</td>
                            <td class="px-5 py-3 text-right font-mono">₦{{ number_format($row['opening_nbv'], 2) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-accent-purple">₦{{ number_format($row['charge'], 2) }}</td>
                            <td class="px-5 py-3 text-right font-mono text-bankos-muted">₦{{ number_format($row['accumulated'], 2) }}</td>
                            <td class="px-5 py-3 text-right font-mono font-bold {{ $row['closing_nbv'] <= $fixedAsset->residual_value ? 'text-bankos-muted' : 'text-bankos-success' }}">
                                ₦{{ number_format($row['closing_nbv'], 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-bankos-muted">No depreciation schedule available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if (!empty($schedule))
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 text-xs font-semibold text-bankos-text-sec">
                            <td class="px-5 py-3">Totals</td>
                            <td class="px-5 py-3 text-right">—</td>
                            <td class="px-5 py-3 text-right font-mono">₦{{ number_format(collect($schedule)->sum('charge'), 2) }}</td>
                            <td class="px-5 py-3 text-right">—</td>
                            <td class="px-5 py-3 text-right font-mono">₦{{ number_format(collect($schedule)->last()['closing_nbv'] ?? 0, 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- ─── Revaluation History ─────────────────────────────────────── --}}
    @if ($revaluations->count())
    <div class="card p-0 overflow-hidden mt-6">
        <div class="p-5 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-bold text-base">Revaluation History</h3>
            <p class="text-xs text-bankos-muted mt-0.5">Past asset revaluations and impairments</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-3 font-semibold">Date</th>
                        <th class="px-5 py-3 font-semibold text-right">Previous Value</th>
                        <th class="px-5 py-3 font-semibold text-right">New Value</th>
                        <th class="px-5 py-3 font-semibold text-right">Change</th>
                        <th class="px-5 py-3 font-semibold">Reason</th>
                        <th class="px-5 py-3 font-semibold">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @foreach ($revaluations as $reval)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-3 font-medium">{{ $reval->revalued_at->format('d M Y, H:i') }}</td>
                        <td class="px-5 py-3 text-right font-mono">₦{{ number_format($reval->previous_book_value, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold">₦{{ number_format($reval->new_book_value, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold {{ (float) $reval->revaluation_amount >= 0 ? 'text-bankos-success' : 'text-accent-crimson' }}">
                            {{ (float) $reval->revaluation_amount >= 0 ? '+' : '' }}₦{{ number_format($reval->revaluation_amount, 2) }}
                        </td>
                        <td class="px-5 py-3 text-bankos-muted max-w-xs truncate">{{ $reval->reason ?? '—' }}</td>
                        <td class="px-5 py-3">{{ $reval->revaluedBy->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ─── Revalue Asset Modal ──────────────────────────────────────── --}}
    @if ($fixedAsset->status === 'active')
    <div
        x-data="{ show: false }"
        @open-modal.window="if ($event.detail === 'revalue-asset') show = true"
        @keydown.escape.window="show = false"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Revalue Asset</h3>
                <button @click="show = false" class="text-bankos-muted hover:text-bankos-text">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('fixed-assets.revalue', $fixedAsset) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 text-sm text-blue-800 dark:text-blue-300">
                    Current Book Value: <strong>₦{{ number_format($fixedAsset->current_book_value, 2) }}</strong>
                </div>
                <div>
                    <label class="form-label">New Book Value (₦) <span class="text-red-500">*</span></label>
                    <input type="number" name="new_book_value" class="form-input" step="0.01" min="0" required>
                    <span class="form-hint">Enter the new fair market value of the asset</span>
                </div>
                <div>
                    <label class="form-label">Reason</label>
                    <textarea name="reason" class="form-input" rows="2" maxlength="500" placeholder="Reason for revaluation, e.g. market appraisal, impairment..."></textarea>
                </div>
                <button type="submit" class="btn w-full btn-primary">Confirm Revaluation</button>
            </form>
        </div>
    </div>
    @endif

    {{-- ─── Dispose Asset Modal ──────────────────────────────────────── --}}
    @if ($fixedAsset->status === 'active')
    <div
        x-data="{ show: false }"
        @open-modal.window="if ($event.detail === 'dispose-asset') show = true"
        @keydown.escape.window="show = false"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Dispose Asset</h3>
                <button @click="show = false" class="text-bankos-muted hover:text-bankos-text">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('fixed-assets.dispose', $fixedAsset) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div class="p-3 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 text-sm text-yellow-800 dark:text-yellow-300">
                    Current NBV: <strong>₦{{ number_format($fixedAsset->current_book_value, 2) }}</strong>
                </div>
                <div>
                    <label class="form-label">Disposal Date <span class="text-red-500">*</span></label>
                    <input type="date" name="disposed_at" class="form-input" value="{{ now()->toDateString() }}" required>
                </div>
                <div>
                    <label class="form-label">Sale / Disposal Value (₦) <span class="text-red-500">*</span></label>
                    <input type="number" name="disposal_value" class="form-input" step="0.01" min="0" value="0" required>
                    <span class="form-hint">Enter 0 for a write-off with zero proceeds</span>
                </div>
                <div>
                    <label class="form-label">Notes</label>
                    <textarea name="disposal_notes" class="form-input" rows="2" placeholder="Reason, buyer details, etc."></textarea>
                </div>
                <button type="submit" class="btn w-full bg-orange-500 hover:bg-orange-600 text-white">Confirm Disposal</button>
            </form>
        </div>
    </div>
    @endif
</x-app-layout>
