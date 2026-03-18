<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight flex items-center gap-2">
                    {{ $customer->first_name }} {{ $customer->last_name }}
                    @if($customer->kyc_status === 'approved')
                        <span class="badge badge-active text-xs w-max flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            KYC Verified
                        </span>
                    @elseif($customer->kyc_status === 'manual_review')
                        <span class="badge badge-pending text-xs">KYC Pending Review</span>
                    @else
                        <span class="badge badge-danger text-xs">KYC Rejected</span>
                    @endif
                </h2>
                <div class="flex items-center gap-2 mt-1 text-sm text-bankos-text-sec">
                    <a href="{{ route('customers.index') }}" class="hover:text-bankos-primary">Customers</a>
                    <span>/</span>
                    <span class="text-bankos-text dark:text-white font-medium">{{ $customer->customer_number }}</span>
                </div>
            </div>
            
            <div class="flex gap-2">
                @can('customers.edit')
                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-secondary flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                    Edit Profile
                </a>
                @endcan
                @can('accounts.create')
                <a href="{{ route('accounts.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary flex items-center gap-2">
                    Open Account
                </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <!-- Alpine Component for Tab Switching -->
    <div x-data="{ activeTab: 'overview' }">
        
        <!-- Tab Navigation -->
        <div class="border-b border-bankos-border dark:border-bankos-dark-border mb-6 flex overflow-x-auto hide-scrollbar">
            <button @click="activeTab = 'overview'" 
                    :class="activeTab === 'overview' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-3 px-6 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
                Profile Overview
            </button>
            <button @click="activeTab = 'accounts'" 
                    :class="activeTab === 'accounts' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-3 px-6 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
                Accounts <span class="ml-1 bg-gray-100 dark:bg-gray-800 text-xs py-0.5 px-2 rounded-full">{{ $customer->accounts->count() }}</span>
            </button>
            <button @click="activeTab = 'loans'" 
                    :class="activeTab === 'loans' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-3 px-6 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
                Loans <span class="ml-1 bg-gray-100 dark:bg-gray-800 text-xs py-0.5 px-2 rounded-full">{{ $customer->loans->count() }}</span>
            </button>
            <button @click="activeTab = 'kyc'" 
                    :class="activeTab === 'kyc' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-3 px-6 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
                KYC & Documents <span class="ml-1 bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400 text-xs py-0.5 px-2 rounded-full @if($customer->kyc_status === 'approved') hidden @endif">Requires Action</span>
            </button>
            <button @click="activeTab = 'ai_insights'"
                    :class="activeTab === 'ai_insights' ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-3 px-6 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
                AI Profile Review <span class="ml-1 bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400 text-[10px] font-bold py-0.5 px-2 rounded-full">✨ NEW</span>
            </button>
            <button @click="activeTab = 'portal'"
                    :class="activeTab === 'portal' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-3 px-6 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
                Portal Access
                @if($customer->portal_active)
                    <span class="ml-1 bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400 text-[10px] font-bold py-0.5 px-2 rounded-full">Active</span>
                @endif
            </button>
            <button @click="activeTab = 'portal360'"
                    :class="activeTab === 'portal360' ? 'border-bankos-primary text-bankos-primary' : 'border-transparent text-bankos-text-sec hover:text-bankos-text dark:hover:text-gray-300 hover:border-gray-300'"
                    class="py-3 px-6 font-medium text-sm border-b-2 whitespace-nowrap outline-none transition-colors">
                Portal 360
            </button>
        </div>

        <!-- OVERVIEW TAB -->
        <div x-show="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Bio Card -->
            <div class="card p-6 lg:col-span-1 border-t-4 border-t-bankos-primary">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/30 text-bankos-primary flex items-center justify-center font-bold text-xl ring-2 ring-white dark:ring-bankos-dark-bg">
                        {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-lg leading-tight">{{ $customer->first_name }} {{ $customer->last_name }}</h3>
                        <p class="text-xs text-bankos-muted mt-1 uppercase tracking-wider">{{ ucfirst($customer->type) }} Client</p>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <p class="text-bankos-text-sec text-xs uppercase tracking-wider font-semibold mb-1">Contact Details</p>
                        <p class="flex items-center gap-2"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg> {{ $customer->phone }}</p>
                        <p class="flex items-center gap-2 mt-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg> {{ $customer->email ?? 'N/A' }}</p>
                    </div>

                    <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                        <p class="text-bankos-text-sec text-xs uppercase tracking-wider font-semibold mb-1">Demographics</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-bankos-text-sec text-xs">Date of Birth</p>
                                <p class="font-medium">{{ \Carbon\Carbon::parse($customer->date_of_birth)->format('d M, Y') }}</p>
                            </div>
                            <div>
                                <p class="text-bankos-text-sec text-xs">Gender</p>
                                <p class="font-medium capitalize">{{ $customer->gender }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                        <p class="text-bankos-text-sec text-xs uppercase tracking-wider font-semibold mb-1">Residential Address</p>
                        <p class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted mt-0.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg> 
                            <span>
                                {{ $customer->address['street'] ?? '' }}<br>
                                {{ $customer->address['lga'] ?? '' }}, {{ $customer->address['state'] ?? '' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="card p-4">
                        <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Ledger Bal</p>
                        <h4 class="text-xl font-bold mt-1 text-bankos-text dark:text-white">₦{{ number_format($totalLedgerBal, 2) }}</h4>
                    </div>
                    <div class="card p-4">
                        <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Total Available Bal</p>
                        <h4 class="text-xl font-bold mt-1 text-bankos-success">₦{{ number_format($totalAvailableBal, 2) }}</h4>
                    </div>
                    <div class="card p-4">
                        <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Active Loans Bal</p>
                        <h4 class="text-xl font-bold mt-1 text-bankos-warning">₦{{ number_format($activeLoansBal, 2) }}</h4>
                    </div>
                    <div class="card p-4 flex flex-col justify-center">
                        <div class="w-full bg-gray-200 rounded-full h-1.5 mb-2 mt-auto">
                            <!-- Example DTI Bar -->
                            <div class="bg-bankos-primary h-1.5 rounded-full" style="width: 0%"></div>
                        </div>
                        <p class="text-xs font-semibold text-bankos-muted flex justify-between"><span>DTI Ratio</span> <span>0%</span></p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card p-0 flex flex-col overflow-hidden">
                    <div class="p-6 border-b border-bankos-border dark:border-bankos-dark-border">
                        <h3 class="font-bold text-lg">Recent Activity</h3>
                    </div>
                    
                    @if($recentTransactions->isEmpty())
                    <div class="flex-1 flex flex-col items-center justify-center text-bankos-muted p-12">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-gray-300 dark:text-gray-600"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        <p class="text-sm font-medium text-bankos-text dark:text-white">No recent activity</p>
                        <p class="text-xs mt-1 text-center">Customer has not performed any transactions.</p>
                    </div>
                    @else
                    <div class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @foreach($recentTransactions as $txn)
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $txn->amount > 0 || in_array($txn->type, ['deposit']) ? 'bg-green-100 text-green-600 dark:bg-green-900/30' : 'bg-red-100 text-red-600 dark:bg-red-900/30' }}">
                                    @if($txn->amount > 0 || in_array($txn->type, ['deposit']))
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><polyline points="19 12 12 19 5 12"></polyline></svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-sm text-bankos-text dark:text-white">{{ Str::limit($txn->description, 40) }}</p>
                                    <p class="text-xs text-bankos-muted mt-0.5">{{ $txn->created_at->format('d M g:ia') }} · <span class="uppercase font-bold">{{ $txn->type }}</span></p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-sm {{ $txn->amount > 0 || in_array($txn->type, ['deposit']) ? 'text-bankos-success' : 'text-bankos-text dark:text-gray-300' }}">
                                    {{ $txn->amount > 0 || in_array($txn->type, ['deposit']) ? '+' : '' }}{{ number_format($txn->amount, 2) }}
                                </p>
                                <p class="text-[10px] uppercase font-bold text-bankos-muted mt-0.5">{{ $txn->status }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

            </div>
        </div>

        <!-- ACCOUNTS TAB -->
        <div x-show="activeTab === 'accounts'" style="display: none;">
            <div class="card p-0 overflow-hidden">
                <div class="p-6 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center">
                    <h3 class="font-bold text-lg">Customer Accounts</h3>
                </div>
                
                @if($customer->accounts->isEmpty())
                    <div class="p-12 text-center text-bankos-muted">
                        <p class="mb-4">This customer has no active accounts.</p>
                        @can('accounts.create')
                        <a href="{{ route('accounts.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary">Open Sub-Account</a>
                        @endcan
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                                    <th class="px-6 py-4 font-semibold">Account Number</th>
                                    <th class="px-6 py-4 font-semibold">Product</th>
                                    <th class="px-6 py-4 font-semibold text-right">Ledger Balance</th>
                                    <th class="px-6 py-4 font-semibold text-right">Available Balance</th>
                                    <th class="px-6 py-4 font-semibold">Status</th>
                                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                                @foreach($customer->accounts as $acc)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="font-bold font-mono">{{ $acc->account_number }}</p>
                                        <p class="text-xs text-bankos-muted mt-0.5">{{ $acc->account_name }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block bg-gray-100 dark:bg-gray-800 text-xs px-2 py-0.5 rounded text-bankos-text-sec uppercase">{{ $acc->type }}</span>
                                        <p class="text-xs font-medium mt-1">{{ $acc->savingsProduct?->name ?? 'Default' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-bankos-text dark:text-gray-300">
                                        {{ $acc->currency }} {{ number_format($acc->ledger_balance, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-bankos-success">
                                        {{ $acc->currency }} {{ number_format($acc->available_balance, 2) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($acc->status === 'active')
                                            <span class="badge badge-active">Active</span>
                                        @elseif($acc->status === 'dormant')
                                            <span class="badge badge-warning">Dormant</span>
                                        @else
                                            <span class="badge badge-danger">{{ ucfirst($acc->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <a href="{{ route('accounts.show', $acc) }}" class="text-bankos-primary hover:text-indigo-900 font-medium text-sm flex items-center justify-end gap-1">
                                            View Account <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- LOANS TAB -->
        <div x-show="activeTab === 'loans'" style="display: none;">
            <div class="card p-0 overflow-hidden">
                <div class="p-6 border-b border-bankos-border dark:border-bankos-dark-border flex justify-between items-center">
                    <h3 class="font-bold text-lg">Loan Facilities</h3>
                    @can('loans.create')
                    <a href="{{ route('loans.create', ['customer_id' => $customer->id]) }}" class="btn btn-primary text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        New Loan Application
                    </a>
                    @endcan
                </div>

                @if($customer->loans->isEmpty())
                    <div class="p-12 text-center text-bankos-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-gray-300 dark:text-gray-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                        <p class="text-lg font-medium text-bankos-text dark:text-white">No loan facilities</p>
                        <p class="text-sm mt-1">This customer has no loan history.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                                    <th class="px-6 py-4 font-semibold">Loan Number</th>
                                    <th class="px-6 py-4 font-semibold">Product</th>
                                    <th class="px-6 py-4 font-semibold text-right">Principal</th>
                                    <th class="px-6 py-4 font-semibold text-right">Outstanding</th>
                                    <th class="px-6 py-4 font-semibold">Tenure</th>
                                    <th class="px-6 py-4 font-semibold">Performance</th>
                                    <th class="px-6 py-4 font-semibold">Status</th>
                                    <th class="px-6 py-4 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                                @foreach($customer->loans as $loan)
                                @php $perf = $loan->performance_class; @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="font-bold font-mono text-bankos-text dark:text-white">{{ $loan->loan_number }}</p>
                                        <p class="text-xs text-bankos-muted mt-0.5">{{ $loan->disbursed_at ? 'Disbursed ' . $loan->disbursed_at->format('d M Y') : 'Not disbursed' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <p class="font-medium">{{ $loan->loanProduct?->name ?? 'N/A' }}</p>
                                        <p class="text-xs text-bankos-muted mt-0.5">{{ number_format($loan->interest_rate, 1) }}% p.a.</p>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-bankos-text dark:text-gray-300 whitespace-nowrap">
                                        ₦{{ number_format($loan->principal_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap">
                                        <span class="font-bold {{ $loan->outstanding_balance > 0 ? 'text-bankos-warning' : 'text-bankos-success' }}">
                                            ₦{{ number_format($loan->outstanding_balance, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="font-medium">{{ $loan->tenure_days }} months</p>
                                        @if($loan->expected_maturity_date)
                                        <p class="text-xs text-bankos-muted mt-0.5">Matures {{ \Carbon\Carbon::parse($loan->expected_maturity_date)->format('d M Y') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-xs font-semibold px-2 py-1 rounded-full border {{ $perf['badge'] }}">{{ $perf['label'] }}</span>
                                        @if($perf['dpd'] > 0)
                                        <p class="text-xs text-bankos-muted mt-1">DPD: {{ $perf['dpd'] }}d</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($loan->status === 'active')
                                            <span class="badge badge-active">Active</span>
                                        @elseif($loan->status === 'pending')
                                            <span class="badge badge-pending">Pending</span>
                                        @elseif($loan->status === 'approved')
                                            <span class="badge" style="background:#e0f2fe;color:#0369a1;">Approved</span>
                                        @elseif($loan->status === 'overdue')
                                            <span class="badge badge-danger">Overdue</span>
                                        @elseif($loan->status === 'closed')
                                            <span class="badge" style="background:#f3f4f6;color:#6b7280;">Closed</span>
                                        @elseif($loan->status === 'written_off')
                                            <span class="badge badge-danger">Written Off</span>
                                        @else
                                            <span class="badge badge-pending">{{ ucfirst($loan->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('loans.show', $loan) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- KYC & DOCUMENTS TAB -->
        <div x-show="activeTab === 'kyc'" style="display: none;" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="card p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-lg">Identity Verification</h3>
                    <span class="badge {{ $customer->kyc_status === 'approved' ? 'badge-active' : 'badge-pending' }} uppercase tracking-widest text-[10px]">{{ $customer->kyc_status }}</span>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between p-3 bg-gray-50 dark:bg-bankos-dark-bg/50 rounded-lg items-center">
                        <div>
                            <p class="text-xs text-bankos-muted uppercase font-bold tracking-wider">BVN Status</p>
                            <p class="font-medium font-mono text-sm mt-1">{{ $customer->bvn ?? 'Not Provided' }}</p>
                        </div>
                        <div>
                            @if($customer->bvn_verified)
                                <span class="badge badge-active flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Verified</span>
                            @else
                                <span class="badge badge-pending">Unverified</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-between p-3 bg-gray-50 dark:bg-bankos-dark-bg/50 rounded-lg items-center">
                        <div>
                            <p class="text-xs text-bankos-muted uppercase font-bold tracking-wider">NIN Status</p>
                            <p class="font-medium font-mono text-sm mt-1">{{ $customer->nin ?? 'Not Provided' }}</p>
                        </div>
                        <div>
                            @if($customer->nin_verified)
                                <span class="badge badge-active flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Verified</span>
                            @else
                                <span class="badge badge-pending">Unverified</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-6" x-data="{ showUploadModal: false }">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-lg">Documents Upload</h3>
                    @can('customers.edit')
                    <button @click="showUploadModal = true" class="text-bankos-primary text-sm font-medium hover:underline flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                        Upload Document
                    </button>
                    @endcan
                </div>

                @if($customer->kycDocuments->isEmpty())
                <div class="text-center p-8 border-2 border-dashed border-bankos-border dark:border-bankos-dark-border rounded-lg">
                    <p class="text-bankos-muted text-sm">No identification documents have been uploaded.</p>
                </div>
                @else
                <div class="space-y-3">
                    @foreach($customer->kycDocuments as $doc)
                        <div class="flex items-center justify-between p-3 border border-bankos-border dark:border-bankos-dark-border rounded-lg bg-gray-50 dark:bg-bankos-dark-bg/50">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-bankos-dark-surface shadow-sm border border-bankos-border dark:border-bankos-dark-border flex items-center justify-center text-bankos-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold uppercase">{{ str_replace('_', ' ', $doc->document_type) }}</h4>
                                    <p class="text-xs text-bankos-muted">No: {{ $doc->document_number ?? 'N/A' }} | {{ \Carbon\Carbon::parse($doc->created_at)->format('d M, Y') }}</p>
                                </div>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-bankos-primary hover:underline text-xs font-bold uppercase px-3 py-1.5 rounded-md hover:bg-bankos-light dark:hover:bg-bankos-primary/20 transition">View / Download</a>
                        </div>
                    @endforeach
                </div>
                @endif
                
                <!-- Upload Form Modal -->
                <div x-show="showUploadModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden bg-black/50 p-4">
                    <div @click.away="showUploadModal = false" class="relative w-full max-w-md rounded-xl bg-white dark:bg-bankos-dark-surface p-6 shadow-2xl ring-1 ring-bankos-border dark:ring-bankos-dark-border">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-bankos-text dark:text-bankos-dark-text">Upload KYC Document</h3>
                            <button @click="showUploadModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>
                        <form action="{{ route('customers.documents.upload', $customer) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-sm font-medium text-bankos-text-sec mb-2">Document Type <span class="text-red-500">*</span></label>
                                <select name="document_type" class="form-select w-full" required>
                                    <option value="">Select Document...</option>
                                    <option value="nin">National ID Card (NIN)</option>
                                    <option value="passport">International Passport</option>
                                    <option value="drivers_license">Driver's License</option>
                                    <option value="voters_card">Voter's Card</option>
                                    <option value="utility_bill">Utility Bill (Address)</option>
                                    <option value="bank_statement">Bank Statement / CAC</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text-sec mb-2">Document Number (if applicable)</label>
                                <input type="text" name="document_number" class="form-input w-full" placeholder="e.g. A01234567">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text-sec mb-2">Expiry Date (if applicable)</label>
                                <input type="date" name="expiry_date" class="form-input w-full">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-bankos-text-sec mb-2">File <span class="text-red-500">*</span></label>
                                <input type="file" name="document_file" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-bankos-light file:text-bankos-primary hover:file:bg-bankos-light/80 dark:file:bg-bankos-primary/20 dark:file:text-bankos-primary" required>
                                <p class="mt-1 text-xs text-bankos-muted">Max size: 5MB. Formats: JPG, PNG, PDF.</p>
                            </div>
                            <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                                <button type="button" @click="showUploadModal = false" class="btn btn-secondary">Cancel</button>
                                <button type="submit" class="btn btn-primary">Upload File</button>
                            </div>
                        </form>
                    </div>
                </div>
                @if($customer->kyc_status === 'manual_review')
                    @can('kyc.approve')
                    <div class="mt-6 pt-6 border-t border-bankos-border dark:border-bankos-dark-border text-center">
                        <p class="text-sm font-medium mb-4">Compliance Actions</p>
                        <div class="flex flex-wrap justify-center gap-4">
                            <form action="{{ route('customers.kyc', $customer) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject">
                                <button type="submit" class="btn btn-secondary text-red-600 hover:border-red-600 hover:bg-red-50">Reject KYC</button>
                            </form>
                            <form action="{{ route('customers.kyc', $customer) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve_t1">
                                <button type="submit" class="btn btn-primary">Approve KYC Tier 1</button>
                            </form>
                             <form action="{{ route('customers.kyc', $customer) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve_t2">
                                <button type="submit" class="btn btn-primary bg-accent-indigo hover:bg-indigo-700">Approve Tier 2</button>
                            </form>
                             <form action="{{ route('customers.kyc', $customer) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="approve_t3">
                                <button type="submit" class="btn btn-primary bg-accent-purple hover:bg-purple-700">Approve Tier 3</button>
                            </form>
                        </div>
                    </div>
                    @endcan
                @endif
            </div>

        </div>

        <!-- PORTAL ACCESS TAB -->
        <div x-show="activeTab === 'portal'" style="display: none;" class="space-y-6">
            <div class="card p-6 max-w-xl">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-lg">Customer Portal Access</h3>
                    @if($customer->portal_active)
                        <span class="badge badge-active flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Active
                        </span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </div>

                <div class="space-y-3 text-sm mb-6">
                    <div class="flex justify-between py-2 border-b border-bankos-border dark:border-bankos-dark-border">
                        <span class="text-bankos-text-sec">Email (login)</span>
                        <span class="font-mono font-medium">{{ $customer->email ?? '— not set —' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-bankos-border dark:border-bankos-dark-border">
                        <span class="text-bankos-text-sec">Portal status</span>
                        <span class="font-medium">{{ $customer->portal_active ? 'Enabled' : 'Disabled' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-bankos-text-sec">Last login</span>
                        <span class="font-medium">{{ $customer->last_login_at ? $customer->last_login_at->diffForHumans() : 'Never' }}</span>
                    </div>
                </div>

                @if(!$customer->email)
                    <div class="rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4 text-sm text-amber-800 dark:text-amber-300 mb-6">
                        <strong>Note:</strong> This customer has no email address on file. Add one before activating portal access.
                    </div>
                @endif

                <div class="space-y-3">
                    @if(!$customer->portal_active)
                        <form action="{{ route('customers.portal.activate', $customer) }}" method="POST">
                            @csrf
                            <button type="submit" {{ !$customer->email ? 'disabled' : '' }}
                                    class="w-full btn btn-primary flex items-center justify-center gap-2 {{ !$customer->email ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    onclick="return confirm('Activate portal access for {{ addslashes($customer->first_name . ' ' . $customer->last_name) }}? A temporary password will be generated.')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Activate Portal Access
                            </button>
                        </form>
                    @else
                        <form action="{{ route('customers.portal.reset-password', $customer) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn btn-secondary flex items-center justify-center gap-2"
                                    onclick="return confirm('Generate a new temporary password for this customer?')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                                Reset Password
                            </button>
                        </form>
                        <form action="{{ route('customers.portal.deactivate', $customer) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full btn btn-secondary text-red-600 hover:border-red-500 flex items-center justify-center gap-2"
                                    onclick="return confirm('Deactivate portal access? The customer will not be able to log in until reactivated.')">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path><line x1="12" y1="15" x2="12" y2="17"></line></svg>
                                Deactivate Portal Access
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ── FEATURE ACCESS OVERRIDES ──────────────────────────────────── --}}
            <div class="card p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="font-bold text-lg">Customer Feature Overrides</h3>
                    <form action="{{ route('customers.feature-flags.update', $customer) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="reset_all" value="1">
                        <button type="submit"
                                onclick="return confirm('Reset all feature overrides for this customer? They will inherit tenant-level settings.')"
                                class="btn btn-secondary text-sm flex items-center gap-1.5">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 .49-3.87"></path></svg>
                            Reset to Tenant Defaults
                        </button>
                    </form>
                </div>
                <p class="text-sm text-bankos-text-sec mb-6">Override tenant-level features for this specific customer. Overridden features take precedence over global tenant settings.</p>

                <form action="{{ route('customers.feature-flags.update', $customer) }}" method="POST">
                    @csrf

                    @foreach($customerFeatures as $group => $flags)
                        <div class="mb-6">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-bankos-text-sec mb-3 pb-1 border-b border-bankos-border dark:border-bankos-dark-border">{{ $group }}</h4>
                            <div class="space-y-3">
                                @foreach($flags as $flag)
                                    <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-bankos-bg dark:hover:bg-bankos-dark-bg transition-colors">
                                        <div class="flex-1 min-w-0 pr-4">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-medium">{{ $flag['label'] }}</span>
                                                @if($flag['has_override'])
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                        Overridden
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center text-[10px] font-semibold px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                                        Tenant default
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-bankos-text-sec mt-0.5">{{ $flag['desc'] }}</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                                            <input type="checkbox"
                                                   name="flags[]"
                                                   value="{{ $flag['key'] }}"
                                                   class="sr-only peer"
                                                   {{ $flag['enabled'] ? 'checked' : '' }}>
                                            <div class="w-10 h-5 bg-gray-200 peer-focus:outline-none rounded-full peer
                                                        dark:bg-gray-700
                                                        peer-checked:after:translate-x-5
                                                        peer-checked:after:border-white
                                                        after:content-['']
                                                        after:absolute
                                                        after:top-0.5
                                                        after:left-0.5
                                                        after:bg-white
                                                        after:border-gray-300
                                                        after:border
                                                        after:rounded-full
                                                        after:h-4
                                                        after:w-4
                                                        after:transition-all
                                                        peer-checked:bg-bankos-primary"></div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-4 border-t border-bankos-border dark:border-bankos-dark-border">
                        <button type="submit" class="btn btn-primary flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Save Customer Overrides
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- PORTAL 360 TAB -->
        <div x-show="activeTab === 'portal360'" style="display: none;">

            {{-- ── Proxy Workspace Header Banner ── --}}
            <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 px-5 py-4 flex items-start gap-3">
                <div class="mt-0.5 shrink-0 text-amber-600 dark:text-amber-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-amber-800 dark:text-amber-300">Acting on behalf of: {{ $customer->first_name }} {{ $customer->last_name }} ({{ $customer->customer_number }})</p>
                    <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">All proxy actions are logged with your name, timestamp, and IP address. Use this workspace only when authorised by the customer or as per institutional policy.</p>
                </div>
                <a href="{{ route('proxy.log', $customer) }}" class="shrink-0 text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline whitespace-nowrap">View full log &rarr;</a>
            </div>

            {{-- ── Flash messages (proxy-specific, shown when on this tab) ── --}}
            @if(session('success'))
            <div class="mb-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-300 dark:border-green-700 px-4 py-3 text-sm text-green-800 dark:text-green-300 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-700 px-4 py-3 text-sm text-red-800 dark:text-red-300">
                <p class="font-semibold mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif

            {{-- ── Quick Actions Grid ── --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-8">

                {{-- 1. Transfer Funds --}}
                <div x-data="{ open: false }" class="card overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm">Transfer Funds</p>
                                <p class="text-xs text-bankos-muted">Move funds between accounts on the customer's behalf</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform text-bankos-muted"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition class="border-t border-bankos-border dark:border-bankos-dark-border p-4">
                        <form method="POST" action="{{ route('proxy.transfer', $customer) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">From Account</label>
                                <select name="from_account_id" required class="input w-full text-sm">
                                    <option value="">Select source account&hellip;</option>
                                    @foreach($customer->accounts->where('status', 'active') as $acct)
                                    <option value="{{ $acct->id }}">{{ $acct->account_number }} &mdash; {{ $acct->account_name }} (&#8358;{{ number_format($acct->available_balance, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Destination Account Number</label>
                                <input type="text" name="to_account_number" required placeholder="e.g. 2012345678" class="input w-full text-sm">
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Amount (&#8358;)</label>
                                    <input type="number" name="amount" min="1" step="0.01" required class="input w-full text-sm" placeholder="0.00">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Description</label>
                                    <input type="text" name="description" class="input w-full text-sm" placeholder="Optional">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Reason <span class="text-red-500">*</span></label>
                                <textarea name="reason" required rows="2" maxlength="500" class="input w-full text-sm" placeholder="Why are you performing this transfer on behalf of the customer?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-full text-sm">Execute Transfer</button>
                        </form>
                    </div>
                </div>

                {{-- 2. Open New Account --}}
                <div x-data="{ open: false }" class="card overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm">Open New Account</p>
                                <p class="text-xs text-bankos-muted">Create a new savings, current, domiciliary, or kids account</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform text-bankos-muted"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition class="border-t border-bankos-border dark:border-bankos-dark-border p-4">
                        <form method="POST" action="{{ route('proxy.open-account', $customer) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-2">Account Type</label>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach(['savings' => 'Savings', 'current' => 'Current', 'domiciliary' => 'Domiciliary (USD)', 'kids' => 'Kids'] as $val => $label)
                                    <label class="flex items-center gap-2 border border-bankos-border dark:border-bankos-dark-border rounded-lg p-3 cursor-pointer hover:border-bankos-primary transition-colors has-[:checked]:border-bankos-primary has-[:checked]:bg-blue-50 dark:has-[:checked]:bg-blue-900/20">
                                        <input type="radio" name="type" value="{{ $val }}" class="text-bankos-primary" required>
                                        <span class="text-sm font-medium">{{ $label }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Reason <span class="text-red-500">*</span></label>
                                <textarea name="reason" required rows="2" maxlength="500" class="input w-full text-sm" placeholder="Why are you opening this account on behalf of the customer?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-full text-sm">Open Account</button>
                        </form>
                    </div>
                </div>

                {{-- 3. Freeze / Unfreeze Account --}}
                <div x-data="{ open: false }" class="card overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-cyan-100 dark:bg-cyan-900/30 text-cyan-600 dark:text-cyan-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20M12 2v20M4.93 4.93l14.14 14.14M19.07 4.93 4.93 19.07"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm">Freeze / Unfreeze Account</p>
                                <p class="text-xs text-bankos-muted">Temporarily restrict or restore access to an account</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform text-bankos-muted"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition class="border-t border-bankos-border dark:border-bankos-dark-border p-4 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Account</label>
                            <select id="freeze_account_id" class="input w-full text-sm">
                                <option value="">Select account&hellip;</option>
                                @foreach($customer->accounts as $acct)
                                <option value="{{ $acct->id }}">{{ $acct->account_number }} &mdash; {{ ucfirst($acct->type) }} [{{ strtoupper($acct->status) }}]</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Reason <span class="text-red-500">*</span></label>
                            <textarea id="freeze_reason" rows="2" maxlength="500" class="input w-full text-sm" placeholder="Reason for freezing/unfreezing&hellip;"></textarea>
                        </div>
                        <div class="flex gap-2">
                            <form method="POST" action="{{ route('proxy.freeze-account', $customer) }}" class="flex-1"
                                  onsubmit="return proxyFreezeSync(this)">
                                @csrf
                                <input type="hidden" name="account_id" class="js-freeze-acct">
                                <input type="hidden" name="reason" class="js-freeze-reason">
                                <button type="submit" class="btn w-full text-sm bg-cyan-600 hover:bg-cyan-700 text-white border-cyan-600">Freeze Account</button>
                            </form>
                            <form method="POST" action="{{ route('proxy.unfreeze-account', $customer) }}" class="flex-1"
                                  onsubmit="return proxyFreezeSync(this)">
                                @csrf
                                <input type="hidden" name="account_id" class="js-freeze-acct">
                                <input type="hidden" name="reason" class="js-freeze-reason">
                                <button type="submit" class="btn w-full text-sm bg-green-600 hover:bg-green-700 text-white border-green-600">Unfreeze Account</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- 4. Reset PIN --}}
                <div x-data="{ open: false }" class="card overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm">Reset Portal PIN</p>
                                <p class="text-xs text-bankos-muted">Set a new 4&ndash;6 digit PIN for the customer's portal</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform text-bankos-muted"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition class="border-t border-bankos-border dark:border-bankos-dark-border p-4">
                        <form method="POST" action="{{ route('proxy.update-pin', $customer) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">New PIN</label>
                                <input type="password" name="new_pin" required minlength="4" maxlength="6" pattern="\d{4,6}" class="input w-full text-sm" placeholder="4&ndash;6 digits">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Reason <span class="text-red-500">*</span></label>
                                <textarea name="reason" required rows="2" maxlength="500" class="input w-full text-sm" placeholder="Why are you resetting the PIN?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-full text-sm">Reset PIN</button>
                        </form>
                    </div>
                </div>

                {{-- 5. Loan Repayment --}}
                <div x-data="{ open: false }" class="card overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm">Proxy Loan Repayment</p>
                                <p class="text-xs text-bankos-muted">Debit a customer account to repay a loan</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform text-bankos-muted"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition class="border-t border-bankos-border dark:border-bankos-dark-border p-4">
                        <form method="POST" action="{{ route('proxy.loan-repayment', $customer) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Loan</label>
                                <select name="loan_id" required class="input w-full text-sm">
                                    <option value="">Select loan&hellip;</option>
                                    @foreach($customer->loans->whereIn('status', ['active', 'overdue']) as $ln)
                                    <option value="{{ $ln->id }}">{{ $ln->loan_number }} &mdash; Outstanding: &#8358;{{ number_format($ln->outstanding_balance, 2) }} [{{ strtoupper($ln->status) }}]</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Debit From Account</label>
                                <select name="account_id" required class="input w-full text-sm">
                                    <option value="">Select account&hellip;</option>
                                    @foreach($customer->accounts->where('status', 'active') as $acct)
                                    <option value="{{ $acct->id }}">{{ $acct->account_number }} (&#8358;{{ number_format($acct->available_balance, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Amount (&#8358;)</label>
                                <input type="number" name="amount" min="1" step="0.01" required class="input w-full text-sm" placeholder="0.00">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Reason <span class="text-red-500">*</span></label>
                                <textarea name="reason" required rows="2" maxlength="500" class="input w-full text-sm" placeholder="Why are you processing this repayment?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-full text-sm">Process Repayment</button>
                        </form>
                    </div>
                </div>

                {{-- 6. Waive Fee --}}
                <div x-data="{ open: false }" class="card overflow-hidden">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm">Waive Fee</p>
                                <p class="text-xs text-bankos-muted">Reverse a charged fee transaction back to the customer</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform text-bankos-muted"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition class="border-t border-bankos-border dark:border-bankos-dark-border p-4">
                        <form method="POST" action="{{ route('proxy.waive-fee', $customer) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Fee Transaction</label>
                                <select name="transaction_id" required class="input w-full text-sm">
                                    <option value="">Select fee to waive&hellip;</option>
                                    @forelse($feeTransactions ?? [] as $fee)
                                    <option value="{{ $fee->id }}">{{ $fee->created_at->format('d M Y') }} &mdash; &#8358;{{ number_format(abs($fee->amount), 2) }} &mdash; {{ Str::limit($fee->description, 40) }}</option>
                                    @empty
                                    <option disabled>No active fee transactions found</option>
                                    @endforelse
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Reason <span class="text-red-500">*</span></label>
                                <textarea name="reason" required rows="2" maxlength="500" class="input w-full text-sm" placeholder="Why is this fee being waived?"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-full text-sm">Waive Fee</button>
                        </form>
                    </div>
                </div>

                {{-- 7. Close Account --}}
                <div x-data="{ open: false }" class="card overflow-hidden lg:col-span-2">
                    <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <div class="flex items-center gap-3">
                            <span class="w-9 h-9 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-sm text-red-700 dark:text-red-400">Close Account</p>
                                <p class="text-xs text-bankos-muted">Permanently close a zero-balance account</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="open ? 'rotate-180' : ''" class="transition-transform text-bankos-muted"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="open" x-transition class="border-t border-bankos-border dark:border-bankos-dark-border p-4 bg-red-50 dark:bg-red-900/10">
                        <form method="POST" action="{{ route('proxy.close-account', $customer) }}" class="space-y-3"
                              onsubmit="return confirm('Are you absolutely sure you want to close this account? This action cannot be undone.')">
                            @csrf
                            <p class="text-xs text-red-700 dark:text-red-400 font-medium">Only accounts with a zero balance (&#8358;0.00) can be closed. This action is irreversible.</p>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Account to Close</label>
                                <select name="account_id" required class="input w-full text-sm border-red-300 dark:border-red-700">
                                    <option value="">Select account&hellip;</option>
                                    @foreach($customer->accounts->whereNotIn('status', ['closed']) as $acct)
                                    <option value="{{ $acct->id }}" {{ ($acct->available_balance != 0 || $acct->ledger_balance != 0) ? 'disabled' : '' }}>
                                        {{ $acct->account_number }} &mdash; {{ ucfirst($acct->type) }}
                                        @if($acct->available_balance != 0 || $acct->ledger_balance != 0)
                                        (balance &ne; 0 &mdash; ineligible)
                                        @else
                                        (&#8358;0.00 &mdash; eligible)
                                        @endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-bankos-text-sec mb-1">Reason <span class="text-red-500">*</span></label>
                                <textarea name="reason" required rows="2" maxlength="500" class="input w-full text-sm border-red-300 dark:border-red-700" placeholder="Reason for account closure&hellip;"></textarea>
                            </div>
                            <button type="submit" class="btn w-full text-sm bg-red-600 hover:bg-red-700 text-white border-red-600">Confirm Account Closure</button>
                        </form>
                    </div>
                </div>

            </div>{{-- /.grid quick-actions --}}

            {{-- ── Recent Proxy Actions Log ── --}}
            <div class="card overflow-hidden mb-8">
                <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
                    <h4 class="font-bold text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                        Recent Proxy Actions
                    </h4>
                    <a href="{{ route('proxy.log', $customer) }}" class="text-xs text-bankos-primary hover:underline">View full log &rarr;</a>
                </div>
                @if(isset($proxyActions) && $proxyActions->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-gray-800/40">
                                <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Date / Time</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Action</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Reason</th>
                                <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Performed By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                            @foreach($proxyActions as $pa)
                            @php
                                $actionColors = [
                                    'transfer'         => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    'open_account'     => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'close_account'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                    'freeze_account'   => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                                    'unfreeze_account' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
                                    'reset_pin'        => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    'loan_repayment'   => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                    'waive_fee'        => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                ];
                                $actionLabels = [
                                    'transfer'         => 'Transfer',
                                    'open_account'     => 'Open Account',
                                    'close_account'    => 'Close Account',
                                    'freeze_account'   => 'Freeze Account',
                                    'unfreeze_account' => 'Unfreeze Account',
                                    'reset_pin'        => 'Reset PIN',
                                    'loan_repayment'   => 'Loan Repayment',
                                    'waive_fee'        => 'Waive Fee',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 py-3 text-xs text-bankos-muted font-mono whitespace-nowrap">{{ \Carbon\Carbon::parse($pa->created_at)->format('d M Y, H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $actionColors[$pa->action] ?? 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}">
                                        {{ $actionLabels[$pa->action] ?? ucfirst(str_replace('_', ' ', $pa->action)) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-bankos-text dark:text-gray-300 max-w-xs">{{ Str::limit($pa->reason, 60) }}</td>
                                <td class="px-4 py-3 text-xs font-medium text-bankos-text dark:text-gray-300 whitespace-nowrap">{{ $pa->actor_name }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="p-10 text-center text-bankos-muted text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 text-gray-300 dark:text-gray-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    No proxy actions have been performed for this customer yet.
                </div>
                @endif
            </div>

            {{-- ── Portal Activity Summary (retained existing stats) ── --}}
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="card p-4">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Savings Challenges</p>
                    <p class="text-2xl font-bold mt-1">{{ $portal360['challenges_count'] ?? 0 }}</p>
                    <p class="text-xs text-bankos-muted mt-1">{{ $portal360['active_challenges'] ?? 0 }} active</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Airtime Orders</p>
                    <p class="text-2xl font-bold mt-1">{{ $portal360['airtime_count'] ?? 0 }}</p>
                    <p class="text-xs text-bankos-muted mt-1">&#8358;{{ number_format($portal360['airtime_total'] ?? 0, 0) }} total</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Scheduled Txfrs</p>
                    <p class="text-2xl font-bold mt-1">{{ $portal360['scheduled_count'] ?? 0 }}</p>
                    <p class="text-xs text-bankos-muted mt-1">{{ $portal360['scheduled_pending'] ?? 0 }} pending</p>
                </div>
                <div class="card p-4">
                    <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider">Open Disputes</p>
                    <p class="text-2xl font-bold mt-1 {{ ($portal360['open_disputes'] ?? 0) > 0 ? 'text-red-600' : '' }}">{{ $portal360['open_disputes'] ?? 0 }}</p>
                    <p class="text-xs text-bankos-muted mt-1">{{ $portal360['total_disputes'] ?? 0 }} all-time</p>
                </div>
            </div>

        </div>

        <script>
        function proxyFreezeSync(form) {
            var acctId = document.getElementById('freeze_account_id').value;
            var reason = document.getElementById('freeze_reason').value;
            if (!acctId) { alert('Please select an account.'); return false; }
            if (!reason.trim()) { alert('Please enter a reason.'); return false; }
            form.querySelector('.js-freeze-acct').value = acctId;
            form.querySelector('.js-freeze-reason').value = reason;
            return true;
        }
        </script>

        <!-- AI INSIGHTS TAB -->
        <div x-show="activeTab === 'ai_insights'" style="display: none;" 
             x-data="{ 
                loading: false, 
                content: '', 
                hasGenerated: false,
                generateReview() {
                    this.loading = true;
                    fetch('{{ route('customers.ai.review', $customer) }}')
                        .then(res => res.json())
                        .then(data => {
                            this.content = data.review;
                            this.loading = false;
                            this.hasGenerated = true;
                        });
                }
             }">
            <div class="card p-8 min-h-[400px] flex flex-col relative overflow-hidden ring-1 ring-indigo-500/20 shadow-[0_0_40px_-10px_rgba(79,70,229,0.15)]">
                <!-- Decorative AI Background -->
                <div class="absolute top-0 right-0 -mt-20 -mr-20 opacity-[0.03] dark:opacity-[0.05] pointer-events-none transition-transform duration-1000" :class="loading ? 'animate-spin' : ''">
                    <svg width="400" height="400" viewBox="0 0 24 24" fill="none" class="text-indigo-600"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>

                <div class="flex justify-between items-center mb-6 z-10">
                    <h3 class="font-bold text-xl flex items-center gap-2">
                        <span class="p-2 bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                        </span>
                        BankOS Cortex™ AI Insight
                    </h3>
                    <button @click="generateReview()" x-show="!loading" class="btn btn-primary bg-indigo-600 border-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="hasGenerated ? 'rotate-180 transition-transform duration-500' : ''"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                        <span x-text="hasGenerated ? 'Regenerate Analysis' : 'Run Deep Profile Scan'"></span>
                    </button>
                </div>

                <div x-show="!hasGenerated && !loading" class="flex-1 flex flex-col items-center justify-center text-center max-w-lg mx-auto z-10 transition-opacity" x-transition.opacity.duration.500ms>
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 flex items-center justify-center mb-6 shadow-inner ring-1 ring-indigo-100 dark:ring-indigo-900/50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="url(#indigo-grad)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <defs>
                                <linearGradient id="indigo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#818cf8" />
                                    <stop offset="100%" stop-color="#4f46e5" />
                                </linearGradient>
                            </defs>
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        </svg>
                    </div>
                    <h4 class="text-xl font-bold mb-3 text-bankos-text dark:text-bankos-dark-text">AI-Powered Customer Intelligence</h4>
                    <p class="text-sm text-bankos-text-sec leading-relaxed">Our proprietary algorithm analyzes the customer's demographics, KYC status, historical transactions, and active credit facilities to predict risk and suggest actionable product offerings.</p>
                </div>

                <div x-show="loading" class="flex-1 flex flex-col items-center justify-center space-y-6 z-10 transition-opacity" x-transition.opacity.duration.300ms>
                    <div class="relative w-16 h-16">
                        <div class="absolute inset-0 border-4 border-indigo-200 dark:border-indigo-900/50 rounded-full"></div>
                        <div class="absolute inset-0 border-4 border-indigo-600 rounded-full border-t-transparent animate-spin"></div>
                    </div>
                    <p class="text-indigo-600 font-medium animate-pulse tracking-wider text-sm font-mono uppercase">Cortex™ is analyzing metrics...</p>
                </div>

                <div x-show="hasGenerated && !loading" class="prose dark:prose-invert prose-indigo prose-sm max-w-none prose-headings:font-bold prose-h3:text-indigo-700 dark:prose-h3:text-indigo-400 prose-ul:mt-0 bg-white dark:bg-bankos-dark-bg p-8 rounded-xl ring-1 ring-gray-200 dark:ring-gray-800 shadow-sm z-10 transition-opacity" x-transition.opacity.duration.500ms x-html="content">
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
