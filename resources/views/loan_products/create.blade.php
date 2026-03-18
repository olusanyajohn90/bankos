<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Create Loan Product') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto card p-8">
        <form action="{{ route('loan-products.store') }}" method="POST">
            @csrf

            <!-- SECTION 1: Basic Info -->
            <h3 class="font-bold text-lg mb-4 border-b border-bankos-border dark:border-bankos-dark-border pb-2">1. Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Product Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-input w-full" placeholder="e.g. Personal Unsecured Loan" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Product Code <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" class="form-input w-full font-mono uppercase" placeholder="e.g. LNP-PER" required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Product Description</label>
                    <textarea name="description" class="form-input w-full" rows="2">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- SECTION 2: Financials & Tenure -->
            <h3 class="font-bold text-lg mb-4 border-b border-bankos-border dark:border-bankos-dark-border pb-2">2. Financial Rules</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Interest Rate <span class="text-red-500">*</span></label>
                    <div class="flex">
                        <input type="number" step="0.01" name="interest_rate" value="{{ old('interest_rate') }}" class="form-input rounded-r-none w-2/3 border-r-0 focus:ring-0 focus:border-bankos-primary" placeholder="e.g. 5.5" required>
                        <select name="interest_type" class="form-select rounded-l-none bg-gray-50 border-l-0 w-1/3">
                            <option value="flat">% (Flat)</option>
                            <option value="reducing_balance">% (Reducing Bal)</option>
                        </select>
                    </div>
                </div>

                <div class="min-w-0"></div> <!-- spacer -->

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Min Amount <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">₦</div>
                        <input type="number" step="0.01" name="min_amount" value="{{ old('min_amount') }}" class="form-input pl-8 w-full" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Max Amount <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">₦</div>
                        <input type="number" step="0.01" name="max_amount" value="{{ old('max_amount') }}" class="form-input pl-8 w-full" required>
                    </div>
                </div>

                <div class="min-w-0 md:col-span-1"></div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Min Duration <span class="text-red-500">*</span></label>
                    <input type="number" name="min_duration" value="{{ old('min_duration') }}" class="form-input w-full" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Max Duration <span class="text-red-500">*</span></label>
                    <input type="number" name="max_duration" value="{{ old('max_duration') }}" class="form-input w-full" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Tenure Unit <span class="text-red-500">*</span></label>
                    <select name="duration_type" class="form-select w-full" required>
                        <option value="months">Months</option>
                        <option value="days">Days</option>
                        <option value="weeks">Weeks</option>
                        <option value="years">Years</option>
                    </select>
                </div>
            </div>

            <!-- SECTION 3: Risk & Compliance -->
            <h3 class="font-bold text-lg mb-4 border-b border-bankos-border dark:border-bankos-dark-border pb-2">3. Risk Parameters</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                <div class="flex items-center gap-3">
                    <input type="hidden" name="requires_collateral" value="0">
                    <input type="checkbox" name="requires_collateral" value="1" id="req_col" class="rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                    <label for="req_col" class="text-sm font-medium text-bankos-text-sec">Requires Collateral</label>
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="require_guarantor" value="0">
                    <input type="checkbox" name="require_guarantor" value="1" id="req_gua" class="rounded border-bankos-border text-bankos-primary focus:ring-bankos-primary">
                    <label for="req_gua" class="text-sm font-medium text-bankos-text-sec">Requires Guarantor</label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-bankos-text-sec mb-2">Max DTI Ratio (%) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.1" name="max_dti_ratio" value="{{ old('max_dti_ratio', '33.3') }}" class="form-input w-full" required>
                    <span class="text-[10px] text-bankos-muted mt-1 block leading-tight">Max Debt-to-Income ratio allowed for applicant</span>
                </div>

            </div>

            <div class="flex justify-end gap-4 pt-6 border-t border-bankos-border dark:border-bankos-dark-border">
                <a href="{{ route('loan-products.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</x-app-layout>
