@extends('layouts.app')

@section('title', 'Credit Policies')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-bankos-text dark:text-bankos-dark-text">Credit Policies</h1>
            <p class="text-sm text-bankos-muted mt-0.5">Manage automated credit scoring policies and rule sets.</p>
        </div>
        <a href="{{ route('credit.policies.create') }}" class="btn btn-primary flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Policy
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 px-4 py-3 flex items-start gap-3 text-sm text-green-800 dark:text-green-300">
            <svg class="h-5 w-5 flex-shrink-0 text-green-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 px-4 py-3 flex items-start gap-3 text-sm text-red-800 dark:text-red-300">
            <svg class="h-5 w-5 flex-shrink-0 text-red-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Policies Table --}}
    <div class="card p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-auto w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Policy Name</th>
                        <th class="px-6 py-4 font-semibold">Loan Product</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Auto-Approve &ge;</th>
                        <th class="px-6 py-4 font-semibold text-center">Auto-Decline &lt;</th>
                        <th class="px-6 py-4 font-semibold text-center">Rules</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($policies as $policy)
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-bankos-text dark:text-bankos-dark-text">{{ $policy->name }}</p>
                            @if($policy->description)
                                <p class="text-xs text-bankos-muted mt-0.5 truncate max-w-xs">{{ $policy->description }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($policy->loanProduct)
                                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 border border-blue-200 dark:border-blue-700">
                                    {{ $policy->loanProduct->name }}
                                </span>
                            @else
                                <span class="text-xs text-bankos-muted italic">All Products</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($policy->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($policy->auto_approve_above !== null)
                                <span class="font-semibold text-green-600 dark:text-green-400">{{ $policy->auto_approve_above }}</span>
                            @else
                                <span class="text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($policy->auto_decline_below !== null)
                                <span class="font-semibold text-red-600 dark:text-red-400">{{ $policy->auto_decline_below }}</span>
                            @else
                                <span class="text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="font-bold text-bankos-primary">{{ $policy->rules_count }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('credit.policies.show', $policy) }}"
                               class="text-bankos-primary hover:text-blue-700 font-medium text-sm border border-bankos-border dark:border-bankos-dark-border px-3 py-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-14 text-center">
                            <div class="flex flex-col items-center gap-3 text-bankos-muted">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="opacity-30"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                <p class="font-medium">No credit policies defined yet.</p>
                                <a href="{{ route('credit.policies.create') }}" class="btn btn-primary text-xs">Create First Policy</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($policies->hasPages())
        <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $policies->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
