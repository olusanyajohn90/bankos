@extends('layouts.app')

@section('title', 'Policy — ' . $insurancePolicy->policy_number)

@section('content')
<div class="max-w-2xl space-y-6">
    <div>
        <a href="{{ route('insurance.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $insurancePolicy->policy_number }}</h1>
    </div>

    <div class="card p-6">
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Customer</dt>
                <dd class="font-medium mt-1">{{ $insurancePolicy->customer?->full_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Linked Loan</dt>
                <dd class="font-mono mt-1">{{ $insurancePolicy->loan?->loan_account_number ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Product</dt>
                <dd class="mt-1 capitalize">{{ str_replace('_', ' ', $insurancePolicy->product) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Provider</dt>
                <dd class="mt-1 capitalize">{{ $insurancePolicy->provider }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Sum Assured</dt>
                <dd class="font-mono mt-1">₦{{ number_format($insurancePolicy->sum_assured, 2) }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Premium</dt>
                <dd class="font-mono mt-1">₦{{ number_format($insurancePolicy->premium, 2) }} / {{ $insurancePolicy->premium_frequency }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Period</dt>
                <dd class="mt-1">{{ $insurancePolicy->start_date->format('d M Y') }} → {{ $insurancePolicy->end_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="mt-1">
                    <span class="text-xs px-2 py-1 rounded font-medium
                        {{ $insurancePolicy->status === 'active' ? 'badge-active' : ($insurancePolicy->status === 'claimed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 hover:bg-gray-300 text-gray-800') }}">
                        {{ ucfirst($insurancePolicy->status) }}
                    </span>
                </dd>
            </div>
        </dl>
        @if($insurancePolicy->notes)
        <div class="mt-4 pt-4 border-t">
            <p class="text-sm text-gray-500">Notes</p>
            <p class="text-sm mt-1">{{ $insurancePolicy->notes }}</p>
        </div>
        @endif
    </div>

    @can('edit insurance')
    <div class="card p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Update Status</h3>
        <form action="{{ route('insurance.update', $insurancePolicy) }}" method="POST" class="flex gap-4 items-end">
            @csrf
            @method('PATCH')
            <div class="flex-1">
                <select name="status" class="form-input w-full">
                    <option value="active" @selected($insurancePolicy->status === 'active')>Active</option>
                    <option value="lapsed" @selected($insurancePolicy->status === 'lapsed')>Lapsed</option>
                    <option value="claimed" @selected($insurancePolicy->status === 'claimed')>Claimed</option>
                    <option value="cancelled" @selected($insurancePolicy->status === 'cancelled')>Cancelled</option>
                </select>
            </div>
            <div class="flex-1">
                <input type="text" name="notes" placeholder="Notes…" class="form-input w-full" value="{{ $insurancePolicy->notes }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
    @endcan
</div>
@endsection
