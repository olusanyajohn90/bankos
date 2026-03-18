<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('portal-disputes.index') }}" class="text-bankos-text-sec hover:text-bankos-primary">Portal Disputes</a>
            <span class="text-bankos-muted">/</span>
            <span class="font-mono text-sm">{{ $dispute->reference }}</span>
        </div>
    </x-slot>

    @php
    $sc = match($dispute->status){
        'open'          => 'badge-danger',
        'investigating' => 'badge-pending',
        'escalated'     => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300',
        'resolved'      => 'badge-active',
        default         => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    };
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Dispute Details --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="font-bold text-lg">{{ str_replace('_',' ',ucfirst($dispute->type ?? 'Dispute')) }}</h3>
                    <span class="badge {{ $sc }} text-xs">{{ strtoupper(str_replace('_',' ',$dispute->status)) }}</span>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm mb-4">
                    <div>
                        <p class="text-bankos-text-sec text-xs uppercase tracking-wider font-semibold mb-1">Reference</p>
                        <p class="font-mono">{{ $dispute->reference }}</p>
                    </div>
                    <div>
                        <p class="text-bankos-text-sec text-xs uppercase tracking-wider font-semibold mb-1">Type</p>
                        <p class="capitalize">{{ str_replace('_',' ',$dispute->type ?? '—') }}</p>
                    </div>
                    <div>
                        <p class="text-bankos-text-sec text-xs uppercase tracking-wider font-semibold mb-1">Raised</p>
                        <p>{{ \Carbon\Carbon::parse($dispute->created_at)->format('d M Y, H:i') }}</p>
                    </div>
                    @if($account)
                    <div>
                        <p class="text-bankos-text-sec text-xs uppercase tracking-wider font-semibold mb-1">Account</p>
                        <p class="font-mono">{{ $account->account_number }}</p>
                    </div>
                    @endif
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 text-sm leading-relaxed">
                    {{ $dispute->description }}
                </div>

                @if(!empty($dispute->attachment_path ?? ''))
                <div class="mt-4">
                    <a href="{{ $dispute->attachment_path }}" target="_blank" class="text-bankos-primary text-sm flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        View Attachment
                    </a>
                </div>
                @endif
            </div>

            @if($dispute->admin_response)
            <div class="card p-6 border-l-4 border-l-bankos-primary">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Previous Response</p>
                <p class="text-sm leading-relaxed">{{ $dispute->admin_response }}</p>
                <p class="text-xs text-bankos-muted mt-2">Responded {{ $dispute->admin_responded_at ? \Carbon\Carbon::parse($dispute->admin_responded_at)->format('d M Y, H:i') : '—' }}</p>
            </div>
            @endif

            @if(!in_array($dispute->status, ['resolved','rejected','escalated']))
            <div class="card p-6">
                <h4 class="font-bold text-base mb-4">Respond to Dispute</h4>
                <form method="POST" action="{{ route('portal-disputes.respond', $dispute->id) }}">
                    @csrf
                    @if($errors->any())
                    <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg text-sm text-red-700 dark:text-red-400">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Response</label>
                        <textarea name="admin_response" rows="4" required
                                  class="input w-full text-sm resize-y"
                                  placeholder="Type your response to the customer…">{{ old('admin_response') }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-1">Update Status to</label>
                        <select name="new_status" class="input w-full text-sm" required>
                            <option value="investigating" {{ $dispute->status==='open'?'selected':'' }}>Investigating</option>
                            <option value="escalated">Escalated</option>
                            <option value="resolved">Resolved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Send Response</button>
                </form>
            </div>
            @endif
        </div>

        {{-- Customer Sidebar --}}
        <div class="space-y-4">
            <div class="card p-5">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-3">Customer</p>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 text-bankos-primary flex items-center justify-center font-bold text-sm">
                        {{ substr($dispute->first_name,0,1) }}{{ substr($dispute->last_name,0,1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-sm">{{ $dispute->first_name }} {{ $dispute->last_name }}</p>
                        <p class="text-xs text-bankos-muted">{{ $dispute->customer_number }}</p>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-bankos-text-sec">{{ $dispute->email }}</p>
                    <p class="text-bankos-text-sec">{{ $dispute->phone }}</p>
                </div>
                <a href="{{ route('customers.show', $dispute->cust_id) }}" class="btn btn-secondary text-xs w-full mt-4 flex justify-center">
                    View Full Profile
                </a>
            </div>

            @if($dispute->status !== 'open')
            <div class="card p-5 text-sm text-bankos-text-sec">
                <p class="text-xs font-semibold text-bankos-muted uppercase tracking-wider mb-2">Timeline</p>
                <div class="space-y-2">
                    <div class="flex gap-2"><span class="text-green-500">●</span> <span>Raised {{ \Carbon\Carbon::parse($dispute->created_at)->format('d M Y') }}</span></div>
                    @if($dispute->admin_responded_at)
                    <div class="flex gap-2"><span class="text-blue-500">●</span> <span>Responded {{ \Carbon\Carbon::parse($dispute->admin_responded_at)->format('d M Y') }}</span></div>
                    @endif
                    @if(in_array($dispute->status,['resolved','rejected']))
                    <div class="flex gap-2"><span class="{{ $dispute->status==='resolved'?'text-green-500':'text-red-500' }}">●</span> <span>{{ ucfirst($dispute->status) }}</span></div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
