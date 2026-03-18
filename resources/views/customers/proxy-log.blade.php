<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Proxy Action Log
                </h2>
                <div class="flex items-center gap-2 mt-1 text-sm text-bankos-text-sec">
                    <a href="{{ route('customers.index') }}" class="hover:text-bankos-primary">Customers</a>
                    <span>/</span>
                    <a href="{{ route('customers.show', $customer) }}" class="hover:text-bankos-primary">{{ $customer->first_name }} {{ $customer->last_name }}</a>
                    <span>/</span>
                    <span class="text-bankos-text dark:text-white font-medium">Proxy Log</span>
                </div>
            </div>
            <a href="{{ route('customers.show', $customer) }}?tab=portal360" class="btn btn-secondary flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Back to Portal 360
            </a>
        </div>
    </x-slot>

    {{-- Identity banner --}}
    <div class="mb-6 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 px-5 py-4 flex items-start gap-3">
        <div class="mt-0.5 shrink-0 text-amber-600 dark:text-amber-400">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
        <div>
            <p class="text-sm font-bold text-amber-800 dark:text-amber-300">
                Complete proxy action history for: {{ $customer->first_name }} {{ $customer->last_name }} &mdash; {{ $customer->customer_number }}
            </p>
            <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">
                All actions below were performed by staff members on behalf of this customer and are immutable audit records.
            </p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="p-4 border-b border-bankos-border dark:border-bankos-dark-border flex items-center justify-between">
            <h4 class="font-bold text-sm flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-bankos-muted"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                All Proxy Actions
                <span class="ml-1 bg-gray-100 dark:bg-gray-800 text-xs py-0.5 px-2 rounded-full text-bankos-muted">{{ $proxyActions->total() }} total</span>
            </h4>
        </div>

        @if($proxyActions->count())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-gray-800/40">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider whitespace-nowrap">Date / Time</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Action</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Reason</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider whitespace-nowrap">Performed By</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider whitespace-nowrap">IP Address</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @foreach($proxyActions as $pa)
                    @php
                        $actionColors = [
                            'transfer'         => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                            'open_account'     => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                            'close_account'    => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            'freeze_account'   => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
                            'unfreeze_account' => 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-400',
                            'reset_pin'        => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                            'loan_repayment'   => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                            'waive_fee'        => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                        ];
                        $actionLabels = [
                            'transfer'         => 'Transfer',
                            'open_account'     => 'Open Account',
                            'close_account'    => 'Close Account',
                            'freeze_account'   => 'Freeze Account',
                            'unfreeze_account' => 'Unfreeze Account',
                            'reset_pin'        => 'Reset PIN',
                            'loan_repayment'   => 'Loan Repayment',
                            'waive_fee'        => 'Waive Fee',
                        ];
                        $payload = json_decode($pa->payload ?? '{}', true) ?? [];
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors align-top">
                        <td class="px-4 py-3 text-xs text-bankos-muted font-mono whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($pa->created_at)->format('d M Y') }}<br>
                            <span class="text-bankos-muted/60">{{ \Carbon\Carbon::parse($pa->created_at)->format('H:i:s') }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $actionColors[$pa->action] ?? 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}">
                                {{ $actionLabels[$pa->action] ?? ucfirst(str_replace('_', ' ', $pa->action)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-bankos-text dark:text-gray-300 max-w-xs">{{ $pa->reason }}</td>
                        <td class="px-4 py-3 text-xs font-medium text-bankos-text dark:text-gray-300 whitespace-nowrap">{{ $pa->actor_name }}</td>
                        <td class="px-4 py-3 text-xs font-mono text-bankos-muted">{{ $pa->ip_address ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-xs text-bankos-muted">
                            @if(!empty($payload))
                                @foreach($payload as $key => $val)
                                    @if($key !== 'note')
                                    <span class="inline-block mr-1 mb-1 bg-gray-100 dark:bg-gray-800 rounded px-1.5 py-0.5 font-mono text-[11px]">
                                        {{ $key }}: {{ is_bool($val) ? ($val ? 'yes' : 'no') : $val }}
                                    </span>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-bankos-muted/50">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($proxyActions->hasPages())
        <div class="px-4 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
            {{ $proxyActions->links() }}
        </div>
        @endif

        @else
        <div class="p-16 text-center text-bankos-muted text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-4 text-gray-300 dark:text-gray-600"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <p class="font-medium text-bankos-text dark:text-white text-base">No proxy actions yet</p>
            <p class="mt-1">No staff member has performed proxy actions for this customer.</p>
        </div>
        @endif
    </div>

</x-app-layout>
