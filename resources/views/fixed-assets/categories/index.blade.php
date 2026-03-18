<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('fixed-assets.index') }}"
                   class="text-bankos-muted hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                        Asset Categories
                    </h2>
                    <p class="text-sm text-bankos-text-sec mt-1">Configure depreciation rules by category</p>
                </div>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg text-green-800 dark:text-green-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-800 dark:text-red-200 text-sm flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Categories Table --}}
        <div class="lg:col-span-2 card p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
                <h3 class="font-semibold text-bankos-text dark:text-white">Existing Categories</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-5 py-4 font-semibold">Category Name</th>
                            <th class="px-5 py-4 font-semibold">Method</th>
                            <th class="px-5 py-4 font-semibold text-center">Useful Life</th>
                            <th class="px-5 py-4 font-semibold text-center">Residual %</th>
                            <th class="px-5 py-4 font-semibold">GL Codes</th>
                            <th class="px-5 py-4 font-semibold text-center">Assets</th>
                            <th class="px-5 py-4 font-semibold text-right">Edit</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
                            x-data="{ editing: false }">
                            {{-- View row --}}
                            <td class="px-5 py-4 font-semibold text-bankos-text dark:text-white" x-show="!editing">{{ $category->name }}</td>
                            <td class="px-5 py-4 text-xs text-bankos-text-sec capitalize" x-show="!editing">
                                {{ str_replace('_', ' ', $category->depreciation_method) }}
                            </td>
                            <td class="px-5 py-4 text-center" x-show="!editing">{{ $category->useful_life_years }} yrs</td>
                            <td class="px-5 py-4 text-center text-bankos-muted" x-show="!editing">{{ $category->residual_rate }}%</td>
                            <td class="px-5 py-4 text-xs text-bankos-muted font-mono" x-show="!editing">
                                @if($category->gl_asset_code)
                                    <span class="block">A: {{ $category->gl_asset_code }}</span>
                                @endif
                                @if($category->gl_depreciation_code)
                                    <span class="block">D: {{ $category->gl_depreciation_code }}</span>
                                @endif
                                @if(!$category->gl_asset_code && !$category->gl_depreciation_code)
                                    —
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center font-semibold" x-show="!editing">{{ $category->assets_count }}</td>
                            <td class="px-5 py-4 text-right" x-show="!editing">
                                <button @click="editing = true"
                                        class="text-bankos-primary hover:text-blue-700 font-medium text-sm border border-bankos-border dark:border-bankos-dark-border px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                                    Edit
                                </button>
                            </td>

                            {{-- Inline edit form --}}
                            <td colspan="7" x-show="editing" class="px-5 py-4 bg-blue-50/50 dark:bg-blue-900/10">
                                <form action="{{ route('fixed-asset-categories.update', $category) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-3">
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Name *</label>
                                            <input type="text" name="name" value="{{ $category->name }}" class="form-input w-full text-sm" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Method *</label>
                                            <select name="depreciation_method" class="form-select w-full text-sm" required>
                                                <option value="straight_line" {{ $category->depreciation_method === 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                                                <option value="declining_balance" {{ $category->depreciation_method === 'declining_balance' ? 'selected' : '' }}>Declining Balance</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Useful Life (yrs) *</label>
                                            <input type="number" name="useful_life_years" value="{{ $category->useful_life_years }}" min="1" max="100" class="form-input w-full text-sm" required>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">Residual Rate (%)</label>
                                            <input type="number" name="residual_rate" value="{{ $category->residual_rate }}" min="0" max="100" step="0.01" class="form-input w-full text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">GL Asset Code</label>
                                            <input type="text" name="gl_asset_code" value="{{ $category->gl_asset_code }}" maxlength="20" class="form-input w-full text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-bankos-text-sec mb-1">GL Depreciation Code</label>
                                            <input type="text" name="gl_depreciation_code" value="{{ $category->gl_depreciation_code }}" maxlength="20" class="form-input w-full text-sm">
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="submit" class="btn btn-primary text-sm py-2">Save</button>
                                        <button type="button" @click="editing = false" class="btn btn-secondary text-sm py-2">Cancel</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-bankos-muted text-sm">
                                No asset categories configured yet. Create one using the form.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Create Category Form --}}
        <div class="card p-6">
            <h3 class="font-semibold text-bankos-text dark:text-white mb-5 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-primary"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Category
            </h3>
            <form action="{{ route('fixed-asset-categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Category Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="e.g. Motor Vehicles" required>
                </div>
                <div>
                    <label class="form-label">Depreciation Method <span class="text-red-500">*</span></label>
                    <select name="depreciation_method" class="form-select" required>
                        <option value="straight_line" {{ old('depreciation_method', 'straight_line') === 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                        <option value="declining_balance" {{ old('depreciation_method') === 'declining_balance' ? 'selected' : '' }}>Declining Balance (DDB)</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Useful Life (years) <span class="text-red-500">*</span></label>
                    <input type="number" name="useful_life_years" value="{{ old('useful_life_years', 5) }}" min="1" max="100" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Residual Rate (% of cost)</label>
                    <input type="number" name="residual_rate" value="{{ old('residual_rate', 0) }}" min="0" max="100" step="0.01" class="form-input">
                    <span class="form-hint">Estimated salvage value as % of purchase cost</span>
                </div>
                <div>
                    <label class="form-label">GL Asset Code</label>
                    <input type="text" name="gl_asset_code" value="{{ old('gl_asset_code') }}" maxlength="20" class="form-input" placeholder="e.g. 16100">
                </div>
                <div>
                    <label class="form-label">GL Depreciation Code</label>
                    <input type="text" name="gl_depreciation_code" value="{{ old('gl_depreciation_code') }}" maxlength="20" class="form-input" placeholder="e.g. 16190">
                </div>
                <button type="submit" class="btn btn-primary w-full">Create Category</button>
            </form>
        </div>
    </div>
</x-app-layout>
