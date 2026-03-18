<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Register Fixed Asset</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Add a new asset to the fixed assets register</p>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="card p-6 md:p-8">
            <form action="{{ route('fixed-assets.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Basic Info --}}
                <div>
                    <h3 class="font-semibold text-base border-b border-bankos-border dark:border-bankos-dark-border pb-3 mb-4">Asset Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Asset Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-input" placeholder="e.g. Toyota Hilux 2024" required>
                        </div>
                        <div>
                            <label class="form-label">Asset Tag / Serial No.</label>
                            <input type="text" name="asset_tag" value="{{ old('asset_tag') }}" class="form-input" placeholder="e.g. AST-2024-001" maxlength="50">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="2" class="form-input" placeholder="Brief description...">{{ old('description') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label">Category <span class="text-red-500">*</span></label>
                            <select name="category_id" class="form-select" required>
                                <option value="">— Select Category —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id') === $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select">
                                <option value="">— Select Branch —</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected(old('branch_id') === $branch->id)>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Financials --}}
                <div>
                    <h3 class="font-semibold text-base border-b border-bankos-border dark:border-bankos-dark-border pb-3 mb-4">Financial Details</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Purchase Date <span class="text-red-500">*</span></label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date') }}" class="form-input" required>
                        </div>
                        <div>
                            <label class="form-label">Purchase Cost (₦) <span class="text-red-500">*</span></label>
                            <input type="number" name="purchase_cost" value="{{ old('purchase_cost') }}" class="form-input" step="0.01" min="0.01" placeholder="0.00" required>
                        </div>
                        <div>
                            <label class="form-label">Residual Value (₦)</label>
                            <input type="number" name="residual_value" value="{{ old('residual_value', 0) }}" class="form-input" step="0.01" min="0" placeholder="0.00">
                            <span class="form-hint">Estimated salvage value at end of useful life</span>
                        </div>
                        <div>
                            <label class="form-label">Useful Life (years) <span class="text-red-500">*</span></label>
                            <input type="number" name="useful_life_years" value="{{ old('useful_life_years', 5) }}" class="form-input" min="1" max="100" required>
                        </div>
                        <div>
                            <label class="form-label">Depreciation Method <span class="text-red-500">*</span></label>
                            <select name="depreciation_method" class="form-select" required>
                                <option value="straight_line" @selected(old('depreciation_method', 'straight_line') === 'straight_line')>Straight Line</option>
                                <option value="declining_balance" @selected(old('depreciation_method') === 'declining_balance')>Declining Balance (Double)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('fixed-assets.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Register Asset</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
