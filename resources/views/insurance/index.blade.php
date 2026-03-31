@extends('layouts.app')

@section('title', 'Insurance Policies')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Insurance Policies</h1>
            <p class="text-sm text-gray-500 mt-1">Embedded credit life, health, and asset insurance</p>
        </div>
        <a href="{{ route('insurance.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Policy
        </a>
    </div>

    <form method="GET" class="card p-4 flex flex-wrap gap-4">
        <div>
            <select name="product" class="form-input">
                <option value="">All Products</option>
                <option value="credit_life" @selected(request('product') === 'credit_life')>Credit Life</option>
                <option value="health" @selected(request('product') === 'health')>Health</option>
                <option value="asset" @selected(request('product') === 'asset')>Asset</option>
            </select>
        </div>
        <div>
            <select name="status" class="form-input">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="lapsed" @selected(request('status') === 'lapsed')>Lapsed</option>
                <option value="claimed" @selected(request('status') === 'claimed')>Claimed</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
        </div>
        <button class="btn btn-secondary">Filter</button>
        <a href="{{ route('insurance.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Policy No.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Provider</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sum Assured</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Premium</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($policies as $policy)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $policy->policy_number }}</td>
                    <td class="px-6 py-4 text-sm font-medium">{{ $policy->customer?->full_name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm capitalize">{{ str_replace('_', ' ', $policy->product) }}</td>
                    <td class="px-6 py-4 text-sm capitalize">{{ $policy->provider }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">₦{{ number_format($policy->sum_assured, 0) }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">₦{{ number_format($policy->premium, 2) }}/{{ $policy->premium_frequency }}</td>
                    <td class="px-6 py-4 text-sm {{ $policy->end_date < now() ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                        {{ $policy->end_date->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-1 rounded font-medium
                            {{ $policy->status === 'active' ? 'badge-active' : ($policy->status === 'claimed' ? 'bg-blue-100 text-blue-800' : ($policy->status === 'lapsed' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-200 hover:bg-gray-300 text-gray-800')) }}">
                            {{ ucfirst($policy->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <a href="{{ route('insurance.show', $policy) }}" class="text-blue-600 hover:underline text-sm">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-6 py-12 text-center text-gray-400">No insurance policies found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $policies->links() }}</div>
    </div>
</div>
@endsection
