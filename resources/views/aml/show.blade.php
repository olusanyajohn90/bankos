<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('aml.index') }}" class="text-bankos-text-sec hover:text-bankos-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">AML Alert</h2>
                <p class="text-sm text-bankos-text-sec mt-0.5">{{ ucwords(str_replace('_',' ',$alert->alert_type)) }} · {{ $alert->created_at->format('d M Y H:i') }}</p>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
    <div class="mb-5 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            {{-- Alert details --}}
            <div class="card">
                <h3 class="font-bold mb-4">Alert Details</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs mb-1">Customer</dt>
                        <dd class="font-semibold">
                            @if($customer)
                                <a href="{{ route('customers.show', $customer->id) }}" class="text-bankos-primary hover:underline">{{ $customer->first_name }} {{ $customer->last_name }}</a>
                            @else
                                N/A
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs mb-1">Account</dt>
                        <dd class="font-mono">{{ $account->account_number ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs mb-1">Transaction Amount</dt>
                        <dd class="font-semibold">
                            @php $amt = $alert->details['amount'] ?? null; @endphp
                            {{ $amt ? '₦'.number_format($amt, 2) : '—' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs mb-1">Risk Score</dt>
                        <dd><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $alert->score >= 80 ? 'bg-red-100 text-red-700' : ($alert->score >= 50 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">{{ $alert->score }}/100</span></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs mb-1">Status</dt>
                        <dd><span class="px-2 py-0.5 rounded-full text-xs font-bold {{ $alert->status === 'open' ? 'bg-red-100 text-red-700' : ($alert->status === 'under_review' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">{{ ucfirst(str_replace('_', ' ', $alert->status)) }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs mb-1">Alert Type</dt>
                        <dd>{{ ucwords(str_replace('_', ' ', $alert->alert_type)) }}</dd>
                    </div>
                </dl>
                @if($alert->notes)
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-semibold text-gray-500 mb-1">Risk Factors / Notes</p>
                    <p class="text-sm text-gray-700">{{ $alert->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Flagged Transaction --}}
            @if($transaction)
            <div class="card">
                <h3 class="font-bold mb-4">Flagged Transaction</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-400 text-xs mb-1">Reference</dt><dd class="font-mono text-xs">{{ $transaction->reference }}</dd></div>
                    <div><dt class="text-gray-400 text-xs mb-1">Type</dt><dd>{{ ucfirst($transaction->type) }}</dd></div>
                    <div><dt class="text-gray-400 text-xs mb-1">Amount</dt><dd class="font-bold text-lg">₦{{ number_format($transaction->amount, 2) }}</dd></div>
                    <div><dt class="text-gray-400 text-xs mb-1">Date</dt><dd>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y H:i:s') }}</dd></div>
                    <div><dt class="text-gray-400 text-xs mb-1">Status</dt><dd>{{ ucfirst($transaction->status) }}</dd></div>
                    <div><dt class="text-gray-400 text-xs mb-1">Narration</dt><dd>{{ $transaction->narration ?? $transaction->description ?? '—' }}</dd></div>
                </dl>
            </div>
            @endif

            {{-- Recent Transactions for context --}}
            @if($recentTxns->isNotEmpty())
            <div class="card p-0 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-bankos-border">
                    <span class="font-bold text-sm">Recent Account Transactions</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Reference</th>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-right">Amount</th>
                                <th class="px-4 py-2 text-left">Date</th>
                                <th class="px-4 py-2 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($recentTxns as $txn)
                            <tr class="{{ $alert->transaction_id === $txn->id ? 'bg-amber-50 dark:bg-amber-900/20' : 'hover:bg-gray-50' }}">
                                <td class="px-4 py-2 font-mono text-xs text-gray-500">{{ $txn->reference }}</td>
                                <td class="px-4 py-2">{{ ucfirst($txn->type) }}</td>
                                <td class="px-4 py-2 text-right font-mono">₦{{ number_format($txn->amount, 2) }}</td>
                                <td class="px-4 py-2 text-gray-400 text-xs">{{ \Carbon\Carbon::parse($txn->created_at)->format('d M Y H:i') }}</td>
                                <td class="px-4 py-2"><span class="text-xs">{{ ucfirst($txn->status) }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="space-y-4">
            @if(in_array($alert->status, ['open', 'under_review']))
            <div class="card">
                <h3 class="font-bold mb-4">Review Actions</h3>
                <form method="POST" action="{{ route('aml.review', $alert->id) }}">
                    @csrf
                    @if($errors->any())
                    <div class="mb-3 p-3 bg-red-50 rounded-lg text-sm text-red-700">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Update Status</label>
                        <select name="status" class="input w-full" required>
                            <option value="under_review" {{ $alert->status === 'open' ? 'selected' : '' }}>Mark Under Review</option>
                            <option value="escalated">Escalate to Compliance</option>
                            <option value="dismissed">Dismiss (False Positive)</option>
                            <option value="reported">File STR Report</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Notes</label>
                        <textarea name="notes" rows="3" class="input w-full" placeholder="Review notes...">{{ old('notes', $alert->notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Submit Review</button>
                </form>
            </div>
            @endif

            @if($alert->status === 'open' || $alert->status === 'under_review')
            <div class="card">
                <h3 class="font-bold mb-3 text-sm">File STR</h3>
                <form method="POST" action="{{ route('aml.str.create') }}">
                    @csrf
                    <input type="hidden" name="alert_id" value="{{ $alert->id }}">
                    <div class="mb-3">
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Summary</label>
                        <textarea name="summary" rows="3" class="input w-full text-sm" placeholder="Describe the suspicious activity..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-secondary w-full text-sm">Create STR Report</button>
                </form>
            </div>
            @endif

            <div class="card">
                <h3 class="font-bold mb-3 text-sm">Timeline</h3>
                <div class="space-y-3">
                    <div class="flex gap-2 text-sm">
                        <div class="w-2 h-2 rounded-full bg-red-500 mt-1.5 flex-shrink-0"></div>
                        <div>
                            <p class="font-medium">Alert Created</p>
                            <p class="text-xs text-gray-400">{{ $alert->created_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @if($alert->reviewed_at)
                    <div class="flex gap-2 text-sm">
                        <div class="w-2 h-2 rounded-full bg-green-500 mt-1.5 flex-shrink-0"></div>
                        <div>
                            <p class="font-medium">Reviewed by {{ $reviewer ? $reviewer->name : 'Unknown' }}</p>
                            <p class="text-xs text-gray-400">{{ $alert->reviewed_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            @if($customer)
            <div class="card">
                <h3 class="font-bold mb-3 text-sm">Customer</h3>
                <p class="text-sm font-medium">{{ $customer->first_name }} {{ $customer->last_name }}</p>
                <p class="text-xs text-gray-400">{{ $customer->customer_number }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $customer->email }}</p>
                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-secondary text-xs w-full mt-3 flex justify-center">View Profile</a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
