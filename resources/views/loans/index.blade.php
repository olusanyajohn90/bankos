<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    {{ __('Loan Pipeline & Portfolio') }}
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage applications, active facilities, and arrears</p>
            </div>
            
            @can('loans.create')
            <a href="{{ route('customers.index') }}" class="btn btn-primary flex items-center gap-2" title="Select a customer first to begin a loan application">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                Select Borrower
            </a>
            @endcan
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        <!-- Filter Bar -->
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20 flex flex-col sm:flex-row gap-4 justify-between sm:items-center">
            
            <form action="{{ route('loans.index') }}" method="GET" class="flex gap-4 items-center w-full sm:w-auto">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-input pl-10 w-full text-sm" placeholder="Search reference, customer...">
                </div>

                <select name="status" class="form-select text-sm py-2" onchange="this.form.submit()">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Review</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved / Awaiting Disbursal</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active / Performing</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>In Arrears / Default</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Fully Paid / Closed</option>
                </select>
                
                @if(request('search') || (request('status') && request('status') != 'all'))
                    <a href="{{ route('loans.index') }}" class="text-sm text-bankos-primary hover:underline whitespace-nowrap">Clear</a>
                @endif
            </form>
        </div>

        <!-- Loans Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Reference & Type</th>
                        <th class="px-6 py-4 font-semibold">Borrower</th>
                        <th class="px-6 py-4 font-semibold text-right">Principal</th>
                        <th class="px-6 py-4 font-semibold text-right">Outstanding</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($loans as $loan)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-bankos-primary">{{ $loan->loan_reference }}</p>
                            <p class="text-xs text-bankos-muted mt-1 uppercase">{{ $loan->loanProduct?->name ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @if($loan->customer)
                            <a href="{{ route('customers.show', $loan->customer) }}" class="font-bold text-bankos-text dark:text-white block hover:underline">{{ $loan->customer->first_name }} {{ $loan->customer->last_name }}</a>
                            <p class="text-[10px] text-bankos-muted mt-1 uppercase tracking-widest font-mono">ID: {{ $loan->customer->customer_number }}</p>
                            @else
                            <span class="text-bankos-muted text-xs italic">Unknown Customer</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="font-bold text-bankos-text dark:text-gray-300">₦ {{ number_format($loan->principal_amount, 2) }}</p>
                            <p class="text-xs text-bankos-success mt-0.5">{{ number_format($loan->interest_rate, 1) }}% <span class="text-[10px] uppercase">int</span></p>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <p class="font-bold {{ $loan->status === 'overdue' ? 'text-red-600' : 'text-bankos-text dark:text-gray-300' }}">
                                ₦ {{ number_format($loan->total_payable - $loan->amount_paid, 2) }}
                            </p>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1 mt-1.5 overflow-hidden">
                                @php
                                    $progress = $loan->total_payable > 0 ? ($loan->amount_paid / $loan->total_payable) * 100 : 0;
                                @endphp
                                <div class="bg-bankos-primary h-1 rounded-full" style="width: {{ $progress }}%"></div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($loan->status === 'active')
                                <span class="badge badge-active">Active</span>
                            @elseif($loan->status === 'pending')
                                <span class="badge badge-pending">Pending Review</span>
                            @elseif($loan->status === 'approved')
                                <span class="badge bg-blue-100 text-blue-800">Awaiting Disbursal</span>
                            @elseif($loan->status === 'overdue')
                                <span class="badge badge-danger">Overdue / Arrears</span>
                            @else
                                <span class="badge bg-gray-200 hover:bg-gray-300 text-gray-800">Closed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('loans.show', $loan) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm border border-bankos-border dark:border-bankos-dark-border px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">Manage</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-bankos-muted">
                            <p class="mb-4">No loan applications found.</p>
                            @can('loans.create')
                            <a href="{{ route('customers.index') }}" class="btn btn-secondary text-sm">Select Borrower to Apply</a>
                            @endcan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($loans->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $loans->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
