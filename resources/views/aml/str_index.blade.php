<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('aml.index') }}" class="text-bankos-muted hover:text-bankos-text transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </a>
            Suspicious Transaction Reports (STR)
        </div>
    </x-slot>

    <div class="space-y-6 max-w-4xl">

        @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- NFIU Reference --}}
        <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-700 rounded-xl p-4 text-sm text-blue-800 dark:text-blue-300">
            <strong>NFIU Reporting Obligation:</strong> Under the Money Laundering (Prevention and Prohibition) Act 2022, financial institutions must file STRs with the Nigerian Financial Intelligence Unit (NFIU) within 24 hours of becoming aware of suspicious activity. STRs filed here must also be submitted through the NFIU goAML portal.
        </div>

        {{-- STR Table --}}
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border">
            <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border">
                <h3 class="font-semibold text-bankos-text dark:text-bankos-dark-text">STR Register</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg text-left">
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Report No.</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Summary</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Created</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Submitted</th>
                            <th class="px-4 py-3 text-xs font-semibold text-bankos-muted uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                        @forelse($strs as $str)
                        @php $customer = $customers[$str->customer_id] ?? null; @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono font-semibold text-bankos-primary text-sm">{{ $str->report_number }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($customer)
                                <span class="font-medium">{{ $customer->first_name }} {{ $customer->last_name }}</span>
                                @else
                                <span class="text-bankos-muted text-xs font-mono">{{ Str::limit($str->customer_id, 10) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-bankos-muted text-xs line-clamp-2 max-w-xs">{{ Str::limit($str->summary, 80) }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $strStatColors = ['draft' => 'bg-amber-100 text-amber-700', 'submitted' => 'bg-green-100 text-green-700', 'acknowledged' => 'bg-blue-100 text-blue-700'];
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $strStatColors[$str->status] ?? 'bg-gray-200 hover:bg-gray-300 text-gray-800' }}">
                                    {{ ucfirst($str->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">
                                {{ $str->created_at->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 text-xs text-bankos-muted">
                                @if($str->submitted_at)
                                {{ $str->submitted_at->format('d M Y') }}
                                @else
                                <span class="text-amber-600 font-medium">Pending</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($str->status === 'draft')
                                <form method="POST" action="{{ route('aml.str.submit', $str->id) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Mark STR {{ $str->report_number }} as submitted to NFIU?')"
                                            class="text-xs text-green-600 hover:underline font-medium">
                                        Submit
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-bankos-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-bankos-muted">
                                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                <p class="text-sm font-medium">No STRs filed yet</p>
                                <p class="text-xs mt-1">Create STRs from individual AML alert pages.</p>
                                <a href="{{ route('aml.index') }}" class="inline-block mt-3 text-sm text-bankos-primary hover:underline">Go to AML Alerts</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($strs->hasPages())
            <div class="px-4 py-3 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $strs->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
