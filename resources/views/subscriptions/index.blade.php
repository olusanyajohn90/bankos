<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Subscription Management</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Manage tenant subscriptions and billing</p>
            </div>
            <a href="{{ route('subscriptions.plans') }}" class="btn btn-sm bg-gray-100 hover:bg-gray-200 hover:bg-gray-300 text-gray-800">Manage Plans</a>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 flex items-center gap-3 px-4 py-3 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="card text-center">
            <p class="text-3xl font-bold text-bankos-primary">{{ $subscriptions->where('status', 'active')->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">Active Subscriptions</p>
        </div>
        <div class="card text-center">
            <p class="text-3xl font-bold text-yellow-600">{{ $subscriptions->where('status', 'trial')->count() }}</p>
            <p class="text-sm text-gray-500 mt-1">On Trial</p>
        </div>
        <div class="card text-center">
            <p class="text-3xl font-bold text-green-600">₦{{ number_format($subscriptions->where('status', 'active')->sum(fn($s) => $s->plan?->monthly_price ?? 0), 0) }}</p>
            <p class="text-sm text-gray-500 mt-1">Monthly Recurring Revenue</p>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-bankos-border flex items-center justify-between">
            <p class="font-bold text-sm">All Tenant Subscriptions ({{ $subscriptions->total() }})</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Institution</th>
                    <th class="px-4 py-3 text-left">Plan</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">MRR</th>
                    <th class="px-4 py-3 text-left">Renews</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($subscriptions as $sub)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold">{{ $sub->tenant?->name ?? 'Unknown' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">{{ $sub->plan?->name ?? 'N/A' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold
                            {{ $sub->status === 'active' ? 'bg-green-100 text-green-700' :
                               ($sub->status === 'trial' ? 'bg-yellow-100 text-yellow-700' :
                               ($sub->status === 'past_due' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500')) }}">
                            {{ ucfirst($sub->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right font-mono">₦{{ number_format($sub->plan?->monthly_price ?? 0, 0) }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ $sub->current_period_end ? \Carbon\Carbon::parse($sub->current_period_end)->format('d M Y') : '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('subscriptions.show', $sub->tenant_id) }}" class="text-blue-600 hover:underline text-xs font-semibold">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">No subscriptions found.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($subscriptions->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $subscriptions->links() }}</div>
        @endif
    </div>
</x-app-layout>
