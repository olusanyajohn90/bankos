<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Referral Rewards</h2>
        <p class="text-sm text-bankos-text-sec mt-1">Manage customer referral reward payouts</p>
    </x-slot>

    {{-- KPI Tiles --}}
    <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach(['pending'=>['bg-amber-50 border-amber-200 dark:bg-amber-900/20','text-amber-700 dark:text-amber-400'],'approved'=>['bg-blue-50 border-blue-200 dark:bg-blue-900/20','text-blue-700 dark:text-blue-400'],'paid'=>['bg-green-50 border-green-200 dark:bg-green-900/20','text-green-700 dark:text-green-400'],'rejected'=>['bg-gray-50 border-gray-200 dark:bg-gray-800','text-gray-600 dark:text-gray-400']] as $st=>[$bg,$tc])
        @php $t = $totals[$st] ?? null; @endphp
        <a href="{{ route('referral-rewards.index', ['status'=>$st]) }}" class="card p-4 {{ $bg }} border hover:shadow-md transition-shadow">
            <p class="text-xs font-semibold uppercase tracking-wider {{ $tc }}">{{ ucfirst($st) }}</p>
            <p class="text-2xl font-bold mt-1 {{ $tc }}">{{ $t?->cnt ?? 0 }}</p>
            <p class="text-xs {{ $tc }} opacity-75 mt-0.5">₦{{ number_format($t?->total ?? 0, 0) }}</p>
        </a>
        @endforeach
    </div>

    <div class="card">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border flex gap-3">
            <form method="GET" class="flex flex-wrap gap-3 w-full">
                <select name="status" class="input w-44 text-sm">
                    <option value="">All statuses</option>
                    @foreach(['pending','approved','paid','rejected'] as $s)
                    <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary text-sm">Filter</button>
                @if(request('status'))
                <a href="{{ route('referral-rewards.index') }}" class="btn btn-secondary text-sm">Clear</a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800 text-xs uppercase tracking-wider text-bankos-text-sec">
                    <tr>
                        <th class="px-4 py-3 text-left">Referrer</th>
                        <th class="px-4 py-3 text-left">New Customer</th>
                        <th class="px-4 py-3 text-left">Reward</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($rewards as $r)
                    @php
                    $sc = match($r->status){
                        'paid'     => 'badge-active',
                        'approved' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                        'pending'  => 'badge-pending',
                        default    => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                    };
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <td class="px-4 py-3">
                            <a href="{{ route('customers.show', $r->referrer_cid) }}" class="font-medium text-bankos-primary hover:underline">{{ $r->ref_fn }} {{ $r->ref_ln }}</a>
                            <p class="text-xs text-bankos-muted">{{ $r->ref_no }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium">{{ $r->new_fn }} {{ $r->new_ln }}</p>
                            <p class="text-xs text-bankos-muted">{{ $r->new_no }}</p>
                        </td>
                        <td class="px-4 py-3 font-bold text-bankos-success">₦{{ number_format($r->reward_amount, 0) }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $sc }} text-xs">{{ strtoupper($r->status) }}</span></td>
                        <td class="px-4 py-3 text-bankos-muted">{{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($r->status === 'pending')
                                <form method="POST" action="{{ route('referral-rewards.approve', $r->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary text-xs py-1 px-3"
                                            onclick="return confirm('Approve this reward?')">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('referral-rewards.reject', $r->id) }}" class="inline" x-data>
                                    @csrf
                                    <input type="hidden" name="payout_notes" id="reject-note-{{ $r->id }}" value="">
                                    <button type="button" class="btn btn-secondary text-xs py-1 px-3 text-red-600 hover:border-red-400"
                                            onclick="const n=prompt('Reason for rejection (required):');if(n){document.getElementById('reject-note-{{ $r->id }}').value=n;this.form.submit();}">
                                        Reject
                                    </button>
                                </form>
                                @elseif($r->status === 'approved')
                                <form method="POST" action="{{ route('referral-rewards.pay', $r->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-primary text-xs py-1 px-3 bg-green-600 border-green-600 hover:bg-green-700"
                                            onclick="return confirm('Mark as paid?')">Mark Paid</button>
                                </form>
                                @else
                                <span class="text-xs text-bankos-muted">{{ $r->payout_notes ? Str::limit($r->payout_notes,30) : '—' }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-bankos-muted">No referral rewards found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rewards->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border">{{ $rewards->links() }}</div>
        @endif
    </div>
</x-app-layout>
