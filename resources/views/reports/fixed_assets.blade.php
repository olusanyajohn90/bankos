<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Fixed Assets Register</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Asset inventory grouped by category</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-between items-center mb-6 print:hidden">
        <form method="GET" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2 ml-2">
                <label class="text-xs font-semibold text-bankos-text-sec">Category</label>
                <select name="category" class="form-select text-sm border-none shadow-none">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryFilter == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4">Filter</button>
        </form>
        <button class="btn btn-secondary text-sm flex items-center gap-2" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Assets</p>
            <p class="text-2xl font-extrabold text-bankos-primary mt-1">{{ $totalAssets }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Cost</p>
            <p class="text-2xl font-extrabold text-blue-600 mt-1">&#8358;{{ number_format($totalCost, 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Acc. Depreciation</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">&#8358;{{ number_format($totalDepreciation, 2) }}</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Net Book Value</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">&#8358;{{ number_format($totalBookValue, 2) }}</p>
        </div>
    </div>

    {{-- Category Summary --}}
    @if ($categorySummary->count() > 1)
    <div class="card mb-6">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border">
            <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">Category Summary</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text-sec">
                        <th class="text-left px-4 py-2 font-semibold">Category</th>
                        <th class="text-right px-4 py-2 font-semibold">Assets</th>
                        <th class="text-right px-4 py-2 font-semibold">Total Cost</th>
                        <th class="text-right px-4 py-2 font-semibold">Depreciation</th>
                        <th class="text-right px-4 py-2 font-semibold">Book Value</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categorySummary as $summary)
                    <tr class="border-t border-bankos-border/50 dark:border-bankos-dark-border/50">
                        <td class="px-4 py-2.5 font-semibold">{{ $summary['category'] }}</td>
                        <td class="px-4 py-2.5 text-right">{{ $summary['count'] }}</td>
                        <td class="px-4 py-2.5 text-right font-mono">&#8358;{{ number_format($summary['total_cost'], 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono text-amber-600">&#8358;{{ number_format($summary['total_depreciation'], 2) }}</td>
                        <td class="px-4 py-2.5 text-right font-mono font-semibold">&#8358;{{ number_format($summary['total_book_value'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Detail by Category --}}
    @forelse ($byCategory as $catName => $catAssets)
        <div class="card mb-6">
            <div class="flex items-center justify-between p-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $catName ?: 'Uncategorized' }}</h3>
                        <p class="text-xs text-bankos-text-sec">{{ $catAssets->count() }} asset(s)</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-bankos-bg dark:bg-bankos-dark-bg text-bankos-text-sec">
                            <th class="text-left px-4 py-2 font-semibold">Asset Tag</th>
                            <th class="text-left px-4 py-2 font-semibold">Name</th>
                            <th class="text-left px-4 py-2 font-semibold">Purchase Date</th>
                            <th class="text-right px-4 py-2 font-semibold">Cost</th>
                            <th class="text-right px-4 py-2 font-semibold">Depreciation</th>
                            <th class="text-right px-4 py-2 font-semibold">Book Value</th>
                            <th class="text-left px-4 py-2 font-semibold">Method</th>
                            <th class="text-center px-4 py-2 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($catAssets as $asset)
                        <tr class="border-t border-bankos-border/50 dark:border-bankos-dark-border/50 hover:bg-bankos-bg/50 dark:hover:bg-bankos-dark-bg/50">
                            <td class="px-4 py-2.5 font-mono text-xs">{{ $asset->asset_tag ?? '—' }}</td>
                            <td class="px-4 py-2.5">{{ $asset->name }}</td>
                            <td class="px-4 py-2.5 text-bankos-text-sec">{{ \Carbon\Carbon::parse($asset->purchase_date)->format('d M Y') }}</td>
                            <td class="px-4 py-2.5 text-right font-mono">&#8358;{{ number_format($asset->purchase_cost, 2) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono text-amber-600">&#8358;{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                            <td class="px-4 py-2.5 text-right font-mono font-semibold">&#8358;{{ number_format($asset->current_book_value, 2) }}</td>
                            <td class="px-4 py-2.5 text-xs">{{ str_replace('_', ' ', ucfirst($asset->depreciation_method)) }}</td>
                            <td class="px-4 py-2.5 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                    @if($asset->status === 'active') bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400
                                    @elseif($asset->status === 'disposed') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400
                                    @else bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400
                                    @endif">
                                    {{ str_replace('_', ' ', ucfirst($asset->status)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="card p-12 text-center">
            <svg class="mx-auto mb-4 text-bankos-muted" xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <p class="text-bankos-text-sec">No fixed assets found</p>
        </div>
    @endforelse
</x-app-layout>
