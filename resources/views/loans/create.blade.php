<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
            {{ __('Loan Application Origination') }}
        </h2>
    </x-slot>

    <div class="max-w-4xl mx-auto card p-8 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Applicant Summary Column -->
        <div class="lg:col-span-1 border-r border-bankos-border dark:border-bankos-dark-border pr-8 hidden lg:block">
            <h3 class="text-xs uppercase tracking-widest font-bold text-bankos-muted mb-4">Applicant Profile</h3>
            
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-bankos-primary flex items-center justify-center font-bold text-sm ring-2 ring-white">
                    {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-bold leading-tight truncate w-32" title="{{ $customer->first_name }} {{ $customer->last_name }}">{{ $customer->first_name }} {{ $customer->last_name }}</h3>
                    <p class="text-[10px] text-bankos-muted mt-0.5 font-mono">{{ $customer->customer_number }}</p>
                </div>
            </div>

            <div class="space-y-4 text-sm mt-6 pt-6 border-t border-dashed border-bankos-border dark:border-bankos-dark-border">
                <div>
                    <p class="text-bankos-text-sec text-xs mb-1">Exposure / Active Loans</p>
                    <p class="font-bold text-bankos-text dark:text-white">{{ $customer->loans->where('status', 'active')->count() }} Facilities</p>
                </div>
                <div>
                    <p class="text-bankos-text-sec text-xs mb-1">Estimated DTI Ratio</p>
                    <p class="font-bold text-bankos-success flex items-center gap-1">24.5% <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-500"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg></p>
                </div>
                <div>
                    <p class="text-bankos-text-sec text-xs mb-1">KYC Tier</p>
                    <p class="font-bold"><span class="badge badge-active text-[10px]">Tier {{ $customer->kyc_tier }} Verified</span></p>
                </div>
            </div>
        </div>

        <!-- Application Form Column -->
        <div class="lg:col-span-2">
            <form action="{{ route('loans.store') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                <div class="space-y-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Loan Product <span class="text-red-500">*</span></label>
                        <select name="loan_product_id" class="form-select w-full" required>
                            <option value="">Select a facility type...</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">
                                    {{ $product->name }} ({{ number_format($product->interest_rate, 2) }}% - Max ₦{{ number_format($product->max_amount) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">Principal Amount Requested <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted font-bold">₦</div>
                                <input type="number" step="0.01" min="1" name="principal_amount" value="{{ old('principal_amount') }}" class="form-input pl-8 w-full font-bold text-lg" placeholder="0.00" required>
                            </div>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-bankos-text-sec mb-2">Requested Tenure (Duration) <span class="text-red-500">*</span></label>
                            <div class="flex">
                                <select name="duration" class="form-select rounded-r-none w-full border-r-0 focus:ring-0" required>
                                    <option value="">Select duration...</option>
                                    @for($i = 1; $i <= 120; $i++)
                                        <option value="{{ $i }}" {{ old('duration') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                <div class="bg-gray-50 border border-bankos-border border-l-0 rounded-r px-3 py-2 text-bankos-muted uppercase text-xs flex items-center">
                                    Units
                                </div>
                            </div>
                            <span class="text-[10px] text-bankos-muted mt-1">Check product specs for allowed tenure units (Months/Weeks).</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Loan Purpose <span class="text-red-500">*</span></label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}" class="form-input w-full" placeholder="e.g. Working Capital for Business Expansion" required>
                    </div>

                    <div class="pt-6 border-t border-bankos-border dark:border-bankos-dark-border mt-6">
                        <label class="block text-sm font-medium text-bankos-text-sec mb-2">Disbursement & Repayment Account <span class="text-red-500">*</span></label>
                        <select name="disbursement_account_id" class="form-select w-full" required>
                            <option value="">Select linked account...</option>
                            @foreach($customer->accounts as $account)
                                <option value="{{ $account->id }}" {{ $account->status !== 'active' ? 'disabled' : '' }}>
                                    {{ $account->account_number }} - {{ $account->account_name }} (Avail: {{ $account->currency }} {{ number_format($account->available_balance, 2) }}) {{ $account->status !== 'active' ? '[INACTIVE]' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <span class="text-xs text-bankos-muted mt-2 block">If approved, funds will be disbursed here. Scheduled repayments will also be swept from this account.</span>
                    </div>

                </div>

                <div class="flex justify-end gap-4 pt-8 mt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Application</button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
