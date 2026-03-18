@extends('layouts.app')

@section('title', 'Agents')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Agents</h1>
            <p class="text-sm text-gray-500 mt-1">Manage field agents and their float balances</p>
        </div>
        @can('create agents')
        <a href="{{ route('agents.create') }}" class="btn btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Agent
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <form method="GET" class="card p-4 flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or phone…" class="form-input w-full">
        </div>
        <div>
            <select name="status" class="form-input">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
        </div>
        <button class="btn btn-secondary">Filter</button>
        <a href="{{ route('agents.index') }}" class="btn btn-secondary">Clear</a>
    </form>

    <div class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Agent</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Branch</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Float Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($agents as $agent)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4">
                        <a href="{{ route('agents.show', $agent) }}" class="font-medium text-blue-600 hover:underline">{{ $agent->full_name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $agent->phone }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $agent->branch?->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-right font-mono">₦{{ number_format($agent->float_balance, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="badge {{ $agent->status === 'active' ? 'badge-active' : ($agent->status === 'suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-200 hover:bg-gray-300 text-gray-800') }} px-2 py-1 rounded text-xs font-medium">
                            {{ ucfirst($agent->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('agents.show', $agent) }}" class="text-blue-600 hover:underline mr-3">View</a>
                        @can('edit agents')
                        <a href="{{ route('agents.edit', $agent) }}" class="text-gray-600 hover:underline">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-400">No agents found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $agents->links() }}</div>
    </div>
</div>
@endsection
