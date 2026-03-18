<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('inbound-transfers.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text font-mono">{{ Str::limit($inboundTransfer->session_id, 30) }}</h2>
                    <p class="text-sm text-bankos-text-sec mt-1">Inbound Transfer · {{ $inboundTransfer->received_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
            @if($inboundTransfer->status === 'pending')
            <form action="{{ route('inbound-transfers.post', $inboundTransfer) }}" method="POST" onsubmit="return confirm('Post this transfer to the destination account?');">
                @csrf
                <button type="submit" class="btn btn-primary shadow-md">Post Transfer</button>
            </form>
            @endif
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Transfer Details -->
        <div class="card p-6 shadow-sm border border-bankos-border space-y-4">
            <h3 class="font-semibold text-bankos-text border-b border-bankos-border pb-2">Transfer Details</h3>

            @php
                $rows = [
                    'Session ID'          => $inboundTransfer->session_id,
                    'Amount'              => $inboundTransfer->currency . ' ' . number_format($inboundTransfer->amount, 2),
                    'Destination Account' => $inboundTransfer->destination_account,
                    'Channel'             => strtoupper($inboundTransfer->channel),
                    'Source'              => $inboundTransfer->source ?? '—',
                    'Narration'           => $inboundTransfer->narration ?? '—',
                    'Posting Type'        => ucfirst($inboundTransfer->posting_type),
                    'Status'              => ucfirst($inboundTransfer->status),
                    'Received At'         => $inboundTransfer->received_at->format('d M Y, H:i:s'),
                    'Posted At'           => $inboundTransfer->posted_at ? $inboundTransfer->posted_at->format('d M Y, H:i:s') : '—',
                ];
            @endphp

            @foreach($rows as $label => $value)
            <div class="flex justify-between items-start gap-4 text-sm">
                <span class="text-bankos-text-sec font-medium w-40 flex-shrink-0">{{ $label }}</span>
                <span class="text-bankos-text text-right font-mono">{{ $value }}</span>
            </div>
            @endforeach
        </div>

        <!-- Sender & Account Info -->
        <div class="space-y-4">
            <div class="card p-6 shadow-sm border border-bankos-border space-y-4">
                <h3 class="font-semibold text-bankos-text border-b border-bankos-border pb-2">Sender Information</h3>
                @foreach([
                    'Sender Name'    => $inboundTransfer->sender_name ?? '—',
                    'Sender Account' => $inboundTransfer->sender_account ?? '—',
                    'Sender Bank'    => $inboundTransfer->sender_bank ?? '—',
                ] as $label => $value)
                <div class="flex justify-between items-center text-sm">
                    <span class="text-bankos-text-sec font-medium">{{ $label }}</span>
                    <span class="text-bankos-text font-mono">{{ $value }}</span>
                </div>
                @endforeach
            </div>

            @if($inboundTransfer->account)
            <div class="card p-6 shadow-sm border border-bankos-border space-y-4">
                <h3 class="font-semibold text-bankos-text border-b border-bankos-border pb-2">Matched Account</h3>
                <div class="flex justify-between text-sm"><span class="text-bankos-text-sec">Account Name</span><span class="text-bankos-text font-medium">{{ $inboundTransfer->account->account_name }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-bankos-text-sec">Account Number</span><span class="text-bankos-text font-mono">{{ $inboundTransfer->account->account_number }}</span></div>
                @if($inboundTransfer->account->customer)
                <div class="flex justify-between text-sm"><span class="text-bankos-text-sec">Customer</span>
                    <a href="{{ route('customers.show', $inboundTransfer->account->customer) }}" class="text-bankos-primary hover:underline">
                        {{ $inboundTransfer->account->customer->first_name }} {{ $inboundTransfer->account->customer->last_name }}
                    </a>
                </div>
                @endif
            </div>
            @endif
        </div>
    </div>

    @if($inboundTransfer->raw_payload)
    <div class="card p-6 shadow-sm border border-bankos-border mt-6">
        <h3 class="font-semibold text-bankos-text mb-3">Raw Webhook Payload</h3>
        <pre class="bg-gray-50 dark:bg-bankos-dark-bg/50 p-4 rounded-lg text-xs font-mono text-bankos-text overflow-x-auto">{{ json_encode($inboundTransfer->raw_payload, JSON_PRETTY_PRINT) }}</pre>
    </div>
    @endif
</x-app-layout>
