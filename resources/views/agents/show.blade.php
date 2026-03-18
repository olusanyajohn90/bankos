@extends('layouts.app')

@section('title', $agent->full_name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('agents.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Agents
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $agent->full_name }}</h1>
            <p class="text-sm text-gray-500">{{ $agent->phone }}</p>
        </div>
        <div class="flex gap-3">
            @can('edit agents')
            <a href="{{ route('agents.edit', $agent) }}" class="btn btn-secondary">Edit</a>
            @endcan
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Float Balance</p>
            <p class="text-2xl font-bold text-green-600 mt-1">₦{{ number_format($agent->float_balance, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Commission</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">₦{{ number_format($agent->total_commission_earned, 2) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Commission Rate</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($agent->commission_rate * 100, 2) }}%</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-gray-500 uppercase font-medium">Status</p>
            <p class="mt-1">
                <span class="badge {{ $agent->status === 'active' ? 'badge-active' : 'bg-gray-200 hover:bg-gray-300 text-gray-800' }} px-2 py-1 rounded text-sm font-medium">
                    {{ ucfirst($agent->status) }}
                </span>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Profile --}}
        <div class="card p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Profile</h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Branch</dt>
                    <dd class="font-medium">{{ $agent->branch?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Email</dt>
                    <dd>{{ $agent->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">BVN</dt>
                    <dd class="font-mono">{{ $agent->bvn ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">NIN</dt>
                    <dd class="font-mono">{{ $agent->nin ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Address</dt>
                    <dd class="text-right max-w-xs">{{ $agent->address ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Daily Cash-In Limit</dt>
                    <dd>₦{{ number_format($agent->daily_cash_in_limit, 0) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Daily Cash-Out Limit</dt>
                    <dd>₦{{ number_format($agent->daily_cash_out_limit, 0) }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Daily Transfer Limit</dt>
                    <dd>₦{{ number_format($agent->daily_transfer_limit, 0) }}</dd>
                </div>
            </dl>
        </div>

        {{-- Fund Float --}}
        @can('manage agents')
        <div class="card p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Fund Float</h3>
            <form action="{{ route('agents.fund-float', $agent) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Amount (₦) <span class="text-red-500">*</span></label>
                    <input type="number" name="amount" class="form-input w-full" min="100" step="100" placeholder="50000">
                    @error('amount')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Narration</label>
                    <input type="text" name="narration" class="form-input w-full" value="Float top-up">
                </div>
                <button type="submit" class="btn btn-primary w-full">Fund Float</button>
            </form>
        </div>
        @endcan
    </div>

    {{-- Float Transactions --}}
    <div class="card">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="font-semibold text-gray-900">Float Transactions</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reference</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Narration</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance After</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($floatTransactions as $tx)
                <tr>
                    <td class="px-6 py-4 font-mono text-xs text-gray-600">{{ $tx->reference }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2 py-1 rounded font-medium
                            {{ $tx->type === 'fund' ? 'bg-green-100 text-green-800' : ($tx->type === 'commission' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800') }}">
                            {{ ucfirst($tx->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $tx->narration }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">₦{{ number_format($tx->amount, 2) }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">₦{{ number_format($tx->balance_after, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $tx->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No float transactions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $floatTransactions->links() }}</div>
    </div>

    {{-- Recent Visits --}}
    <div class="card">
        <div class="px-6 py-4 border-b">
            <h3 class="font-semibold text-gray-900">Recent Visits</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Address</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount Collected</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($visits as $visit)
                <tr>
                    <td class="px-6 py-4 text-sm">{{ $visit->visited_at->format('d M Y H:i') }}</td>
                    <td class="px-6 py-4 text-sm">{{ $visit->customer?->full_name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm capitalize">{{ str_replace('_', ' ', $visit->purpose) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $visit->address_resolved ?? $visit->latitude . ', ' . $visit->longitude }}</td>
                    <td class="px-6 py-4 text-right font-mono text-sm">₦{{ number_format($visit->amount_collected, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No visits recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">{{ $visits->links() }}</div>
    </div>
</div>
@endsection
