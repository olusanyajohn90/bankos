<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose Plan — bankOS Setup</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-4 py-12">

<div class="max-w-4xl mx-auto" x-data="{ selectedPlan: '{{ old('plan_slug', $data['step4']['plan_slug'] ?? 'starter') }}', billingCycle: '{{ old('billing_cycle', $data['step4']['billing_cycle'] ?? 'monthly') }}' }">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-8 h-8 rounded-lg bg-blue-600 grid place-items-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="text-lg font-bold text-gray-900">bank<span class="text-blue-600">OS</span></span>
    </div>

    @include('setup._stepper', ['current' => 4])

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 mt-6">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Choose Your Plan</h2>
            <p class="text-gray-500">Start with a 14-day free trial. No credit card required.</p>

            <!-- Billing Toggle -->
            <div class="inline-flex items-center gap-3 mt-5 bg-gray-100 rounded-xl p-1">
                <button type="button" @click="billingCycle = 'monthly'"
                        :class="billingCycle === 'monthly' ? 'bg-white shadow text-gray-900' : 'text-gray-500'"
                        class="px-5 py-2 rounded-lg text-sm font-medium transition-all">Monthly</button>
                <button type="button" @click="billingCycle = 'yearly'"
                        :class="billingCycle === 'yearly' ? 'bg-white shadow text-gray-900' : 'text-gray-500'"
                        class="px-5 py-2 rounded-lg text-sm font-medium transition-all">
                    Yearly
                    <span class="ml-1 bg-green-100 text-green-700 text-xs px-1.5 py-0.5 rounded-full font-semibold">Save up to 44%</span>
                </button>
            </div>
        </div>

        <form method="POST" action="{{ route('setup.step4.store') }}">
            @csrf
            <input type="hidden" name="plan_slug" :value="selectedPlan">
            <input type="hidden" name="billing_cycle" :value="billingCycle">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                @foreach($plans as $plan)
                @php
                    $popular = $plan->slug === 'growth';
                @endphp
                <div @click="selectedPlan = '{{ $plan->slug }}'"
                     :class="selectedPlan === '{{ $plan->slug }}' ? 'border-blue-600 ring-2 ring-blue-500 shadow-lg' : 'border-gray-200 hover:border-gray-300'"
                     class="relative border-2 rounded-2xl p-6 cursor-pointer transition-all">

                    @if($popular)
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <span class="bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-full">Most Popular</span>
                    </div>
                    @endif

                    <!-- Selected indicator -->
                    <div :class="selectedPlan === '{{ $plan->slug }}' ? 'opacity-100' : 'opacity-0'"
                         class="absolute top-4 right-4 w-6 h-6 bg-blue-600 rounded-full flex items-center justify-center transition-opacity">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>

                    <h3 class="font-bold text-lg text-gray-900 mb-1">{{ $plan->name }}</h3>

                    <div class="mb-4">
                        <template x-if="billingCycle === 'monthly'">
                            <div>
                                <span class="text-2xl font-bold text-gray-900">
                                    {{ $plan->price_monthly > 0 ? '₦' . number_format($plan->price_monthly, 0) : 'Custom' }}
                                </span>
                                @if($plan->price_monthly > 0)
                                <span class="text-gray-500 text-sm">/month</span>
                                @endif
                            </div>
                        </template>
                        <template x-if="billingCycle === 'yearly'">
                            <div>
                                <span class="text-2xl font-bold text-gray-900">
                                    {{ $plan->price_yearly > 0 ? '₦' . number_format($plan->price_yearly, 0) : 'Custom' }}
                                </span>
                                @if($plan->price_yearly > 0)
                                <span class="text-gray-500 text-sm">/year</span>
                                @endif
                            </div>
                        </template>
                    </div>

                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2 text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            {{ $plan->max_customers ? number_format($plan->max_customers) . ' customers' : 'Unlimited customers' }}
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            {{ $plan->max_staff_users ? $plan->max_staff_users . ' staff users' : 'Unlimited staff' }}
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            {{ $plan->max_branches ? $plan->max_branches . ' ' . Str::plural('branch', $plan->max_branches) : 'Unlimited branches' }}
                        </li>
                        <li class="flex items-center gap-2 text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            {{ $plan->max_transactions_monthly ? number_format($plan->max_transactions_monthly) . ' txns/mo' : 'Unlimited transactions' }}
                        </li>
                        @if($plan->slug === 'enterprise')
                        <li class="flex items-center gap-2 text-gray-600">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Dedicated support + SLA
                        </li>
                        @endif
                    </ul>
                </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between pt-6 border-t border-gray-100">
                <a href="{{ route('setup.step3') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Back
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-8 rounded-xl transition-colors shadow-sm">
                    Continue with <span x-text="selectedPlan.charAt(0).toUpperCase() + selectedPlan.slice(1)"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline ml-1"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
