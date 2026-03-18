<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Loan Products') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure lending limits, durations, and interest rates</p>
            </div>
            
            @if(auth()->user()->can('settings.manage') || auth()->user()->hasRole('tenant_admin'))
            <a href="{{ route('loan-products.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Product
            </a>
            @endif
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Product Name</th>
                        <th class="px-6 py-4 font-semibold">Interest & Tenure</th>
                        <th class="px-6 py-4 font-semibold">Limits</th>
                        <th class="px-6 py-4 font-semibold">Risk Rules</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-primary">{{ $product->name }}</p>
                            <p class="text-xs text-bankos-muted mt-1 font-mono hover:text-bankos-text">{{ $product->code }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-success">{{ number_format($product->interest_rate, 2) }}% <span class="text-xs font-normal text-bankos-muted">({{ $product->interest_type }})</span></p>
                            <p class="text-xs mt-1 text-bankos-text-sec">{{ $product->min_duration }} - {{ $product->max_duration }} {{ $product->duration_type }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-bankos-text dark:text-gray-300">Max: ₦{{ number_format($product->max_amount) }}</p>
                            <p class="text-[10px] text-bankos-muted mt-1 uppercase tracking-widest">Min: ₦{{ number_format($product->min_amount) }}</p>
                        </td>
                        <td class="px-6 py-4 text-xs">
                            <div class="space-y-1">
                                <p class="flex justify-between w-28"><span class="text-bankos-muted">Collateral:</span> <span class="{{ $product->requires_collateral ? 'text-bankos-text font-medium' : 'text-gray-400' }}">{{ $product->requires_collateral ? 'Yes' : 'No' }}</span></p>
                                <p class="flex justify-between w-28"><span class="text-bankos-muted">Guarantor:</span> <span class="{{ $product->require_guarantor ? 'text-bankos-text font-medium' : 'text-gray-400' }}">{{ $product->require_guarantor ? 'Yes' : 'No' }}</span></p>
                                <p class="flex justify-between w-28"><span class="text-bankos-muted">Max DTI:</span> <span class="font-bold text-blue-600">{{ $product->max_dti_ratio }}%</span></p>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            @if(auth()->user()->can('settings.manage') || auth()->user()->hasRole('tenant_admin'))
                            <a href="{{ route('loan-products.edit', $product) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">Edit Rules</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-4">No loan products configured.</p>
                            @if(auth()->user()->can('settings.manage') || auth()->user()->hasRole('tenant_admin'))
                            <a href="{{ route('loan-products.create') }}" class="btn btn-primary">Create Loan Product</a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($products->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $products->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
