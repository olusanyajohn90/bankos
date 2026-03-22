@extends('layouts.app')

@section('title', 'Exit Request — ' . $exit->first_name . ' ' . $exit->last_name)

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('cooperative.exits.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $exit->first_name }} {{ $exit->last_name }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Exit request &middot; {{ $exit->customer_number ?? '' }}
                    &middot;
                    @if($exit->exit_type === 'voluntary')
                        Voluntary Withdrawal
                    @elseif($exit->exit_type === 'expelled')
                        Expelled
                    @elseif($exit->exit_type === 'deceased')
                        Deceased
                    @elseif($exit->exit_type === 'transferred')
                        Transferred
                    @endif
                </p>
            </div>
        </div>
        <div class="flex gap-3">
            @if($exit->status === 'pending')
                <form action="{{ route('cooperative.exits.approve', $exit->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this exit request?')">
                    @csrf
                    <button type="submit" class="btn btn-primary">Approve Exit</button>
                </form>
            @elseif($exit->status === 'approved')
                <form action="{{ route('cooperative.exits.settle', $exit->id) }}" method="POST" onsubmit="return confirm('This will close all member accounts and set the member to inactive. This action cannot be undone. Proceed?')">
                    @csrf
                    <button type="submit" class="btn btn-primary bg-green-600 hover:bg-green-700">Process Settlement</button>
                </form>
            @endif
        </div>
    </div>

    {{-- Status Banner --}}
    @if($exit->status === 'pending')
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">This exit request is pending approval.</p>
        </div>
    @elseif($exit->status === 'approved')
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <p class="text-sm font-semibold text-blue-800 dark:text-blue-300">Exit approved on {{ $exit->exit_date }}. Ready for settlement.</p>
        </div>
    @elseif($exit->status === 'settled')
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm font-semibold text-green-800 dark:text-green-300">Settlement completed on {{ $exit->settlement_date }}. Member accounts closed.</p>
        </div>
    @elseif($exit->status === 'rejected')
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">This exit request was rejected.</p>
        </div>
    @endif

    {{-- Settlement Breakdown --}}
    <div class="card p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Settlement Breakdown</h3>
        <div class="space-y-4">
            {{-- Credits --}}
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Share Capital Refund</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Value of member's shares to be redeemed</p>
                </div>
                <p class="font-mono text-lg font-semibold text-green-600">+ &#8358;{{ number_format($exit->share_refund, 2) }}</p>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Savings Balance</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total savings across all accounts</p>
                </div>
                <p class="font-mono text-lg font-semibold text-green-600">+ &#8358;{{ number_format($exit->savings_balance, 2) }}</p>
            </div>

            {{-- Debits --}}
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Outstanding Loans</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total outstanding loan balance to be deducted</p>
                </div>
                <p class="font-mono text-lg font-semibold text-red-600">- &#8358;{{ number_format($exit->outstanding_loans, 2) }}</p>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">Pending Contributions</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Unpaid mandatory contributions for current period</p>
                </div>
                <p class="font-mono text-lg font-semibold text-red-600">- &#8358;{{ number_format($exit->pending_contributions, 2) }}</p>
            </div>

            {{-- Net --}}
            <div class="flex items-center justify-between py-4 border-t-2 border-gray-300 dark:border-gray-600">
                <div>
                    <p class="text-lg font-bold text-gray-900 dark:text-white">Net Settlement</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        @if($exit->net_settlement >= 0)
                            Amount payable to member
                        @else
                            Amount owed by member
                        @endif
                    </p>
                </div>
                <p class="font-mono text-2xl font-bold {{ $exit->net_settlement >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    &#8358;{{ number_format(abs($exit->net_settlement), 2) }}
                    @if($exit->net_settlement < 0)
                        <span class="text-sm font-normal">(deficit)</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- Member Information --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Details --}}
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Exit Details</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Exit Type</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ str_replace('_', ' ', $exit->exit_type) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white capitalize">{{ $exit->status }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Exit Date</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $exit->exit_date ?? 'Not yet approved' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Settlement Date</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ $exit->settlement_date ?? 'Not yet settled' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Created</dt>
                    <dd class="text-sm font-medium text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($exit->created_at)->format('d M Y H:i') }}</dd>
                </div>
                @if($exit->reason)
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Reason</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $exit->reason }}</dd>
                </div>
                @endif
                @if($exit->notes)
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400 mb-1">Notes</dt>
                    <dd class="text-sm text-gray-900 dark:text-white">{{ $exit->notes }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Member Accounts --}}
        <div class="card p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Member Accounts</h3>
            @if($accounts->count() > 0)
                <div class="space-y-3">
                    @foreach($accounts as $account)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $account->product_name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $account->account_number ?? '' }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-mono text-sm font-semibold text-gray-900 dark:text-white">&#8358;{{ number_format($account->balance, 2) }}</p>
                            <p class="text-xs capitalize {{ $account->status === 'active' ? 'text-green-600' : 'text-gray-400' }}">{{ $account->status }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400 dark:text-gray-500">No accounts found.</p>
            @endif

            @if($loans->count() > 0)
                <h4 class="text-md font-semibold text-gray-900 dark:text-white mt-6 mb-3">Active Loans</h4>
                <div class="space-y-3">
                    @foreach($loans as $loan)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $loan->loan_account_number ?? 'Loan' }}</p>
                            <p class="text-xs capitalize {{ $loan->status === 'overdue' ? 'text-red-600' : 'text-amber-600' }}">{{ $loan->status }}</p>
                        </div>
                        <p class="font-mono text-sm font-semibold text-red-600">&#8358;{{ number_format($loan->outstanding_balance, 2) }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
