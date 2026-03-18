<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Customers Directory') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage individuals and corporate clients</p>
            </div>
            
            @can('customers.create')
            <a href="{{ route('customers.create') }}" class="btn btn-primary flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Customer
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex flex-col sm:flex-row justify-between items-center gap-4">
            
            <form action="{{ route('customers.index') }}" method="GET" class="flex w-full sm:w-auto items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-10 w-full" placeholder="Search name, phone, email...">
                </div>
                
                <select name="status" class="form-select w-32" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending KYC</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                
                @if(request()->has('search') || request()->has('status'))
                    <a href="{{ route('customers.index') }}" class="text-sm text-bankos-primary hover:underline my-auto ml-2">Clear</a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Customer</th>
                        <th class="px-6 py-4 font-semibold">Contact</th>
                        <th class="px-6 py-4 font-semibold">KYC Status</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($customers as $customer)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-bankos-primary flex items-center justify-center font-bold">
                                    {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-bankos-text dark:text-white">{{ $customer->first_name }} {{ $customer->last_name }}</p>
                                    <p class="text-xs text-bankos-muted flex items-center gap-1 mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                        {{ $customer->customer_number }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-bankos-text-sec">
                            <p>{{ $customer->phone }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $customer->email ?? 'No email' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if(in_array($customer->kyc_status, ['approved', 'auto_approved']))
                                <span class="badge badge-active flex w-max items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Approved
                                </span>
                            @elseif($customer->kyc_status === 'manual_review')
                                <span class="badge badge-pending">Pending Review</span>
                            @elseif($customer->kyc_status === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-pending">{{ ucfirst(str_replace('_', ' ', $customer->kyc_status)) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($customer->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @elseif($customer->status === 'pending')
                                <span class="badge badge-pending">Pending</span>
                            @else
                                <span class="badge badge-danger">Suspended</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('customers.show', $customer) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View Profile</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-bankos-muted">
                            <div class="flex flex-col items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mb-4 text-gray-300 dark:text-gray-600"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                <p class="text-lg font-medium text-bankos-text dark:text-white">No customers found</p>
                                <p class="text-sm mt-1">Try adjusting your filters or create a new customer.</p>
                                
                                @can('customers.create')
                                <a href="{{ route('customers.create') }}" class="btn btn-primary mt-6">Create Customer</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $customers->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
