<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('posting-files.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text font-mono">{{ $postingFile->reference }}</h2>
                    <p class="text-sm text-bankos-text-sec mt-1">{{ $postingFile->file_name }} · Uploaded {{ $postingFile->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @if($postingFile->status === 'validated' && $postingFile->valid_records > 0)
            <form action="{{ route('posting-files.post', $postingFile) }}" method="POST" onsubmit="return confirm('Post {{ $postingFile->valid_records }} valid records? This cannot be undone.');">
                @csrf
                <button type="submit" class="btn btn-primary shadow-md">
                    Post {{ $postingFile->valid_records }} Valid Records
                </button>
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-bankos-text">{{ $postingFile->total_records }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Total Records</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $postingFile->valid_records }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Valid</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $postingFile->invalid_records }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Invalid / Error</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-bankos-primary">{{ $postingFile->posted_records }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Posted</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-bankos-text">₦{{ number_format($postingFile->total_amount, 0) }}</p>
            <p class="text-xs text-bankos-text-sec mt-1">Total Amount</p>
        </div>
    </div>

    <!-- Records Table -->
    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="px-6 py-4 border-b border-bankos-border flex justify-between items-center">
            <h3 class="font-semibold text-bankos-text">Records</h3>
            @php
                $badgeClass = match($postingFile->status) {
                    'posted'    => 'badge-active',
                    'validated' => 'bg-blue-100 text-blue-700',
                    'failed'    => 'bg-red-100 text-red-700',
                    'posting'   => 'bg-amber-100 text-amber-700',
                    default     => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <span class="badge {{ $badgeClass }} uppercase tracking-wider text-[10px]">{{ $postingFile->status }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Row</th>
                        <th class="px-6 py-3 font-semibold">Identifier</th>
                        <th class="px-6 py-3 font-semibold">Amount</th>
                        <th class="px-6 py-3 font-semibold">Date</th>
                        <th class="px-6 py-3 font-semibold">Channel</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                        <th class="px-6 py-3 font-semibold">Error</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border">
                    @forelse($records as $rec)
                    <tr class="hover:bg-blue-50/30 transition-colors {{ in_array($rec->status, ['invalid','failed','duplicate']) ? 'bg-red-50/30 dark:bg-red-900/5' : '' }}">
                        <td class="px-6 py-3 text-bankos-text-sec">{{ $rec->row_number }}</td>
                        <td class="px-6 py-3">
                            <p class="font-mono text-xs text-bankos-primary">{{ $rec->identifier_type }}</p>
                            <p class="font-mono text-xs text-bankos-text">{{ $rec->identifier_value }}</p>
                        </td>
                        <td class="px-6 py-3 font-medium text-bankos-text">₦{{ number_format($rec->amount, 2) }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec text-xs">{{ $rec->transaction_date->format('d M Y') }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec text-xs">{{ $rec->payment_channel ?? '—' }}</td>
                        <td class="px-6 py-3">
                            @php
                                $rBadge = match($rec->status) {
                                    'posted'    => 'badge-active',
                                    'valid'     => 'bg-blue-100 text-blue-700',
                                    'failed',
                                    'invalid'   => 'bg-red-100 text-red-700',
                                    'duplicate' => 'bg-orange-100 text-orange-700',
                                    default     => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="badge {{ $rBadge }} text-[10px] uppercase tracking-wider">{{ $rec->status }}</span>
                        </td>
                        <td class="px-6 py-3 text-red-600 text-xs">{{ $rec->error_message ?? '' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-6 py-8 text-center text-bankos-text-sec">No records found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($records->hasPages())
        <div class="p-4 border-t border-bankos-border bg-gray-50/30">{{ $records->links() }}</div>
        @endif
    </div>
</x-app-layout>
