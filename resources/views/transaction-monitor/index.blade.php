<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Transaction Monitor</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Real-time transaction feed and volume analytics</p>
            </div>
            <button onclick="window.location.reload()" class="btn btn-secondary text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.05"/></svg>
                Refresh
            </button>
        </div>
    </x-slot>

    {{-- KPI tiles --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Today's Volume</p>
            <p class="text-2xl font-black text-gray-900">₦{{ number_format($todayVolume / 1000000, 2) }}M</p>
        </div>
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Transactions Today</p>
            <p class="text-2xl font-black text-blue-600">{{ number_format($todayCount) }}</p>
        </div>
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Pending</p>
            <p class="text-2xl font-black {{ $pendingCount > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ $pendingCount }}</p>
        </div>
        <div class="card text-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">High-Value (≥₦1M)</p>
            <p class="text-2xl font-black text-purple-600">{{ $highValue }}</p>
        </div>
    </div>

    {{-- Hourly volume chart --}}
    <div class="card mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Hourly Volume — Last 24 Hours</h3>
            <p class="text-xs text-gray-400">NGN millions</p>
        </div>
        @php $maxVol = max(collect($hourly)->pluck('vol')->max(), 1); @endphp
        <div class="flex items-end gap-1 h-24">
            @foreach($hourly as $h)
            @php $ht = max(3, ($h['vol'] / $maxVol) * 88); @endphp
            <div class="flex-1 flex flex-col items-center gap-1 group relative">
                <div class="w-full rounded-t" style="height:{{ $ht }}px;background:{{ $h['vol'] > 0 ? '#2563eb' : '#e5e7eb' }}" title="{{ $h['hour'] }}: ₦{{ number_format($h['vol'],0) }}"></div>
                @if(str_ends_with($h['hour'], ':00') && in_array(substr($h['hour'],0,2), ['00','06','12','18']))
                <span class="text-xs text-gray-400" style="font-size:9px">{{ $h['hour'] }}</span>
                @else
                <span style="height:16px;display:block"></span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Type breakdown + filters --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
        <div class="card">
            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-4">Today by Type</h3>
            @if($typeBreakdown->isEmpty())
            <p class="text-sm text-gray-400 text-center py-4">No transactions today.</p>
            @else
            <div class="space-y-3">
                @foreach($typeBreakdown as $tb)
                <div class="flex justify-between items-center">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 capitalize">{{ str_replace('_',' ',$tb->type) }}</p>
                        <p class="text-xs text-gray-400">{{ $tb->cnt }} txns</p>
                    </div>
                    <p class="text-sm font-bold text-gray-700">₦{{ number_format($tb->vol/1000,0) }}K</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="lg:col-span-3">
            {{-- Filter bar --}}
            <form action="{{ route('transaction-monitor.index') }}" method="GET" class="flex flex-wrap gap-3 mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Reference / description..." class="form-input text-sm w-48">
                <select name="type" onchange="this.form.submit()" class="form-select text-sm py-2">
                    <option value="">All Types</option>
                    @foreach(['credit','debit','transfer','reversal','fee','interest'] as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
                <select name="status" onchange="this.form.submit()" class="form-select text-sm py-2">
                    <option value="">All Statuses</option>
                    @foreach(['completed','pending','failed','reversed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <input type="number" name="min_amount" value="{{ request('min_amount') }}" placeholder="Min amount" class="form-input text-sm w-32">
                <button type="submit" class="btn btn-secondary text-sm">Filter</button>
                @if(request()->hasAny(['search','type','status','min_amount']))
                <a href="{{ route('transaction-monitor.index') }}" class="text-sm text-bankos-primary hover:underline self-center">Clear</a>
                @endif
            </form>

            {{-- Transaction table --}}
            <div class="card p-0 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-bankos-border">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Reference</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Account</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase text-right">Amount</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Status</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Time</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($transactions as $txn)
                            @php
                            $tsc = ['completed'=>'bg-green-100 text-green-700','pending'=>'bg-amber-100 text-amber-700','failed'=>'bg-red-100 text-red-700','reversed'=>'bg-gray-100 text-gray-500'][$txn->status]??'bg-gray-100 text-gray-500';
                            $amtColor = $txn->type === 'credit' ? 'text-green-600' : 'text-red-600';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-xs font-mono text-gray-600">{{ $txn->reference }}</td>
                                <td class="px-4 py-3">
                                    <p class="text-xs font-semibold text-gray-900">{{ $txn->account?->account_number ?? '—' }}</p>
                                    <p class="text-xs text-gray-400">{{ $txn->account?->customer?->first_name }} {{ $txn->account?->customer?->last_name }}</p>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600 capitalize">{{ $txn->type }}</td>
                                <td class="px-4 py-3 text-sm font-bold {{ $amtColor }} text-right">
                                    {{ $txn->type === 'credit' ? '+' : '−' }}₦{{ number_format($txn->amount, 0) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $tsc }}">{{ strtoupper($txn->status) }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-400">{{ $txn->created_at->format('H:i:s') }}</td>
                                <td class="px-4 py-3">
                                    @if($txn->status === 'completed')
                                    <button onclick="openReversal('{{ $txn->id }}', '{{ $txn->reference }}')"
                                            class="text-xs text-red-500 hover:text-red-700 font-semibold">Reverse</button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 text-sm">No transactions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-bankos-border">{{ $transactions->links() }}</div>
            </div>
        </div>
    </div>

    {{-- Reversal modal --}}
    <div id="reversal-modal" class="fixed inset-0 z-50 hidden" style="background:rgba(0,0,0,0.5)">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Reverse Transaction</h3>
                <p id="reversal-ref" class="text-sm text-gray-400 mb-4 font-mono"></p>
                <form id="reversal-form" method="POST">
                    @csrf
                    <label class="block text-sm text-gray-600 mb-1">Reason for reversal</label>
                    <textarea name="reason" rows="3" placeholder="Duplicate charge, incorrect entry..." required
                              class="form-input w-full text-sm mb-4"></textarea>
                    <div class="flex gap-3">
                        <button type="submit" class="btn bg-red-600 hover:bg-red-700 text-white flex-1">Confirm Reversal</button>
                        <button type="button" onclick="closeReversal()" class="btn btn-secondary flex-1">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function openReversal(id, ref) {
        document.getElementById('reversal-ref').textContent = ref;
        document.getElementById('reversal-form').action = '/transaction-monitor/' + id + '/reverse';
        document.getElementById('reversal-modal').classList.remove('hidden');
    }
    function closeReversal() {
        document.getElementById('reversal-modal').classList.add('hidden');
    }
    </script>
</x-app-layout>
