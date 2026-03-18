<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Subscription Plans</h2>
            <p class="text-sm text-bankos-text-sec mt-1">Manage SaaS pricing tiers</p>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        @foreach($plans as $plan)
        <div class="card border-2 {{ $plan->name === 'Enterprise' ? 'border-bankos-primary' : 'border-transparent' }}">
            @if($plan->name === 'Enterprise')
            <span class="block text-center text-xs font-bold text-bankos-primary mb-3 uppercase tracking-wider">Most Popular</span>
            @endif
            <h3 class="text-xl font-bold text-center mb-1">{{ $plan->name }}</h3>
            <p class="text-3xl font-bold text-center mb-1">₦{{ number_format($plan->monthly_price, 0) }}<span class="text-sm font-normal text-gray-400">/mo</span></p>
            <p class="text-center text-xs text-gray-400 mb-4">Up to {{ $plan->max_customers ? number_format($plan->max_customers) : 'Unlimited' }} customers</p>
            <ul class="space-y-2 text-sm mb-6">
                @foreach(json_decode($plan->features ?? '[]') as $feature)
                <li class="flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
            <p class="text-center text-xs text-gray-400">{{ $plan->tenant_count ?? 0 }} tenants on this plan</p>
        </div>
        @endforeach
    </div>
</x-app-layout>
