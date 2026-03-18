<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Savings & Deposit Products') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Configure interest rates, fees, and product rules</p>
            </div>
            
            @can('settings.manage')
            <a href="{{ route('savings-products.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Product
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Product Name</th>
                        <th class="px-6 py-4 font-semibold">Type</th>
                        <th class="px-6 py-4 font-semibold">Interest Rate</th>
                        <th class="px-6 py-4 font-semibold">Min. Balance</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-primary">{{ $product->name }}</p>
                            <p class="text-xs text-bankos-muted mt-1 font-mono">{{ $product->code }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge badge-active uppercase tracking-wider text-[10px]">{{ $product->product_type }}</span>
                        </td>
                        <td class="px-6 py-4 font-medium text-bankos-success">
                            {{ number_format($product->interest_rate, 2) }}% p.a.
                        </td>
                        <td class="px-6 py-4 font-medium">
                            {{ $product->currency }} {{ number_format($product->min_balance, 2) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            @can('settings.manage')
                            <a href="{{ route('savings-products.edit', $product) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">Edit</a>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-4">No deposit products configured.</p>
                            @can('settings.manage')
                            <a href="{{ route('savings-products.create') }}" class="btn btn-primary">Create First Product</a>
                            @endcan
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
