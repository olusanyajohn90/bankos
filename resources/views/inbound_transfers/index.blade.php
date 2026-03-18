<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Inbound Transfers</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Incoming payments from NIBSS and payment switches</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <!-- Filters -->
    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <select name="status" class="form-input w-auto text-sm">
            <option value="">All Statuses</option>
            @foreach(['pending','posted','failed','reversed'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="channel" class="form-input w-auto text-sm">
            <option value="">All Channels</option>
            @foreach(['nibss','branch','mobile','pos','internet_banking','other'] as $ch)
                <option value="{{ $ch }}" {{ request('channel') == $ch ? 'selected' : '' }}>{{ strtoupper($ch) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary text-sm">Filter</button>
        @if(request()->hasAny(['status','channel']))
            <a href="{{ route('inbound-transfers.index') }}" class="btn btn-secondary text-sm">Clear</a>
        @endif
    </form>

    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Session ID</th>
                        <th class="px-6 py-4 font-semibold">Sender</th>
                        <th class="px-6 py-4 font-semibold">Destination Account</th>
                        <th class="px-6 py-4 font-semibold">Amount</th>
                        <th class="px-6 py-4 font-semibold">Channel</th>
                        <th class="px-6 py-4 font-semibold">Posting</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold">Received</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                        <td class="px-6 py-4 font-mono text-xs text-bankos-primary">{{ Str::limit($transfer->session_id, 20) }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-bankos-text">{{ $transfer->sender_name ?? '—' }}</p>
                            @if($transfer->sender_account)
                                <p class="text-xs text-bankos-text-sec font-mono">{{ $transfer->sender_account }}</p>
                            @endif
                            @if($transfer->sender_bank)
                                <p class="text-xs text-bankos-text-sec">{{ $transfer->sender_bank }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-mono font-bold text-bankos-text">{{ $transfer->destination_account }}</p>
                            @if($transfer->account)
                                <p class="text-xs text-bankos-text-sec">{{ $transfer->account->account_name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-bankos-text">{{ $transfer->currency }} {{ number_format($transfer->amount, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="badge bg-gray-200 hover:bg-gray-300 text-gray-800 text-[10px] uppercase tracking-wider">{{ $transfer->channel }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs text-bankos-text-sec capitalize">{{ $transfer->posting_type }}</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($transfer->status === 'posted')
                                <span class="badge badge-active text-[10px] uppercase tracking-wider">Posted</span>
                            @elseif($transfer->status === 'failed')
                                <span class="badge bg-red-100 text-red-700 text-[10px] uppercase tracking-wider">Failed</span>
                            @elseif($transfer->status === 'reversed')
                                <span class="badge bg-orange-100 text-orange-700 text-[10px] uppercase tracking-wider">Reversed</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-700 text-[10px] uppercase tracking-wider">Pending</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-bankos-text-sec text-xs">{{ $transfer->received_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-3">
                                <a href="{{ route('inbound-transfers.show', $transfer) }}" class="text-bankos-primary hover:text-blue-700 font-medium text-sm">View</a>
                                @if($transfer->status === 'pending')
                                <form action="{{ route('inbound-transfers.post', $transfer) }}" method="POST" onsubmit="return confirm('Manually post this transfer?');">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 font-medium text-sm">Post</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center">
                            <p class="font-medium text-bankos-text">No inbound transfers received yet.</p>
                            <p class="text-bankos-text-sec text-sm mt-1">Transfers arrive via the webhook endpoint: <code class="bg-gray-100 dark:bg-gray-800 px-1 rounded text-xs">/api/webhook/{tenant}/inbound</code></p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transfers->hasPages())
        <div class="p-4 border-t border-bankos-border bg-gray-50/30">{{ $transfers->links() }}</div>
        @endif
    </div>
</x-app-layout>
