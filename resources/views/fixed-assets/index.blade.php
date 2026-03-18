<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Fixed Assets Register</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Track assets, depreciation schedules, and disposals</p>
            </div>
            <div class="flex gap-3">
                <button @click="$dispatch('open-modal', 'add-category')" class="btn btn-secondary">
                    Add Category
                </button>
                <a href="{{ route('fixed-assets.create') }}" class="btn btn-primary">
                    Register Asset
                </a>
            </div>
        </div>
    </x-slot>

    {{-- ─── Summary KPIs ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-5">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Active Assets</p>
            <h3 class="text-2xl font-bold mt-1">{{ number_format($summary['active_count']) }}</h3>
        </div>
        <div class="card p-5 border-t-4 border-t-bankos-primary">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Cost</p>
            <h3 class="text-2xl font-bold mt-1">₦{{ number_format($summary['total_cost'], 0) }}</h3>
        </div>
        <div class="card p-5 border-t-4 border-t-accent-purple">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Accumulated Depr.</p>
            <h3 class="text-2xl font-bold mt-1 text-accent-purple">₦{{ number_format($summary['total_depr'], 0) }}</h3>
        </div>
        <div class="card p-5 border-t-4 border-t-bankos-success">
            <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Net Book Value</p>
            <h3 class="text-2xl font-bold mt-1 text-bankos-success">₦{{ number_format($summary['total_nbv'], 0) }}</h3>
        </div>
    </div>

    {{-- ─── Filter Bar ───────────────────────────────────────────────── --}}
    <div class="card p-0 overflow-hidden">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <form action="{{ route('fixed-assets.index') }}" method="GET" class="flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-48">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Asset name or tag...">
                </div>
                <div>
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="fully_depreciated" @selected(request('status') === 'fully_depreciated')>Fully Depreciated</option>
                        <option value="disposed" @selected(request('status') === 'disposed')>Disposed</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('fixed-assets.index') }}" class="btn btn-secondary">Clear</a>
            </form>
        </div>

        {{-- ─── Assets Table ─────────────────────────────────────────── --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-3 font-semibold">Asset</th>
                        <th class="px-5 py-3 font-semibold">Category</th>
                        <th class="px-5 py-3 font-semibold text-right">Cost</th>
                        <th class="px-5 py-3 font-semibold text-right">Acc. Depr.</th>
                        <th class="px-5 py-3 font-semibold text-right">NBV</th>
                        <th class="px-5 py-3 font-semibold">Method</th>
                        <th class="px-5 py-3 font-semibold">Last Depreciated</th>
                        <th class="px-5 py-3 font-semibold">Status</th>
                        <th class="px-5 py-3 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse ($assets as $asset)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-5 py-3">
                            <p class="font-semibold text-bankos-text dark:text-white">{{ $asset->name }}</p>
                            @if ($asset->asset_tag)
                                <p class="text-xs text-bankos-muted font-mono mt-0.5">{{ $asset->asset_tag }}</p>
                            @endif
                            @if ($asset->branch)
                                <p class="text-xs text-bankos-muted mt-0.5">{{ $asset->branch->name }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-bankos-muted text-xs">{{ $asset->category->name ?? '—' }}</td>
                        <td class="px-5 py-3 text-right font-mono">₦{{ number_format($asset->purchase_cost, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono text-accent-purple">₦{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                        <td class="px-5 py-3 text-right font-mono font-bold {{ $asset->current_book_value <= 0 ? 'text-bankos-muted' : 'text-bankos-success' }}">
                            ₦{{ number_format($asset->current_book_value, 2) }}
                        </td>
                        <td class="px-5 py-3 text-xs text-bankos-muted capitalize">{{ str_replace('_', ' ', $asset->depreciation_method) }}</td>
                        <td class="px-5 py-3 text-xs text-bankos-muted">
                            {{ $asset->last_depreciation_date?->format('d M Y') ?? 'Never' }}
                        </td>
                        <td class="px-5 py-3">
                            @if ($asset->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @elseif ($asset->status === 'fully_depreciated')
                                <span class="badge bg-blue-100 text-blue-700 border border-blue-200">Fully Dep.</span>
                            @elseif ($asset->status === 'disposed')
                                <span class="badge bg-gray-100 text-gray-500 border border-gray-200">Disposed</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('fixed-assets.show', $asset) }}" class="text-bankos-primary text-xs font-medium hover:underline">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-bankos-muted">
                            <p class="mb-4">No assets found.</p>
                            <a href="{{ route('fixed-assets.create') }}" class="btn btn-secondary text-sm">Register First Asset</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($assets->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $assets->links() }}
        </div>
        @endif
    </div>

    {{-- ─── Asset Categories Panel ───────────────────────────────────── --}}
    @if ($categories->isNotEmpty())
    <div class="mt-6 card p-0 overflow-hidden">
        <div class="p-5 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-bold text-base">Asset Categories</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-3 font-semibold">Category</th>
                        <th class="px-5 py-3 font-semibold text-center">Useful Life</th>
                        <th class="px-5 py-3 font-semibold">Method</th>
                        <th class="px-5 py-3 font-semibold text-center">Residual Rate</th>
                        <th class="px-5 py-3 font-semibold text-center">Assets</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @foreach ($categories as $cat)
                    <tr>
                        <td class="px-5 py-3 font-semibold">{{ $cat->name }}</td>
                        <td class="px-5 py-3 text-center text-bankos-muted">{{ $cat->useful_life_years }} yrs</td>
                        <td class="px-5 py-3 text-bankos-muted capitalize text-xs">{{ str_replace('_', ' ', $cat->depreciation_method) }}</td>
                        <td class="px-5 py-3 text-center text-bankos-muted">{{ $cat->residual_rate }}%</td>
                        <td class="px-5 py-3 text-center">{{ $cat->assets->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ─── Add Category Modal ───────────────────────────────────────── --}}
    <div
        x-data="{ show: false }"
        @open-modal.window="if ($event.detail === 'add-category') show = true"
        @keydown.escape.window="show = false"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" @click="show = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-2xl w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="font-bold text-lg">Add Asset Category</h3>
                <button @click="show = false" class="text-bankos-muted hover:text-bankos-text">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('fixed-assets.categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="form-input" placeholder="e.g. Motor Vehicles" required>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Useful Life (years)</label>
                        <input type="number" name="useful_life_years" class="form-input" value="5" min="1" max="100">
                    </div>
                    <div>
                        <label class="form-label">Residual Rate (%)</label>
                        <input type="number" name="residual_rate" class="form-input" value="0" min="0" max="100" step="0.01">
                    </div>
                </div>
                <div>
                    <label class="form-label">Depreciation Method</label>
                    <select name="depreciation_method" class="form-select">
                        <option value="straight_line">Straight Line</option>
                        <option value="declining_balance">Declining Balance</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-full">Create Category</button>
            </form>
        </div>
    </div>
</x-app-layout>
