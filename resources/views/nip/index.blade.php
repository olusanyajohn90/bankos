<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">NIP Transfers</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Outward instant payments via NIBSS</p>
            </div>
            <a href="{{ route('nip.create') }}" class="btn btn-primary shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     class="mr-1.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                New Transfer
            </a>
        </div>
    </x-slot>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('nip.index') }}"
          class="flex flex-wrap items-end gap-3 mb-5">

        {{-- Status --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">Status</label>
            <select name="status" class="form-input text-sm w-40">
                <option value="">All Statuses</option>
                @foreach(['pending','name_enquiry','initiated','successful','failed','reversed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Date From --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="form-input text-sm w-auto">
        </div>

        {{-- Date To --}}
        <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-bankos-text-sec uppercase tracking-wider">To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="form-input text-sm w-auto">
        </div>

        <div class="flex gap-2 pb-0.5">
            <button type="submit" class="btn btn-primary text-sm">Filter</button>
            @if(request()->hasAny(['status', 'date_from', 'date_to']))
                <a href="{{ route('nip.index') }}" class="btn btn-secondary text-sm">Clear</a>
            @endif
        </div>
    </form>

    {{-- Transfers Table --}}
    <div class="card p-0 overflow-hidden border border-bankos-border">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-bankos-dark-bg/30 border-b border-bankos-border
                                text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Date</th>
                        <th class="px-6 py-4 font-semibold">Session ID</th>
                        <th class="px-6 py-4 font-semibold">Beneficiary</th>
                        <th class="px-6 py-4 font-semibold">Bank</th>
                        <th class="px-6 py-4 font-semibold text-right">Amount</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors cursor-pointer"
                        onclick="window.location='{{ route('nip.show', $transfer) }}'">

                        {{-- Date --}}
                        <td class="px-6 py-4 text-xs text-bankos-text-sec whitespace-nowrap">
                            {{ $transfer->created_at->format('d M Y') }}<br>
                            <span class="text-bankos-muted">{{ $transfer->created_at->format('H:i') }}</span>
                        </td>

                        {{-- Session ID --}}
                        <td class="px-6 py-4">
                            <span class="font-mono text-xs text-bankos-primary"
                                  title="{{ $transfer->session_id }}">
                                {{ Str::limit($transfer->session_id, 20) }}
                            </span>
                        </td>

                        {{-- Beneficiary --}}
                        <td class="px-6 py-4">
                            <p class="font-medium text-bankos-text">
                                {{ $transfer->beneficiary_account_name ?? '—' }}
                            </p>
                            <p class="text-xs font-mono text-bankos-text-sec mt-0.5">
                                {{ $transfer->beneficiary_account_number }}
                            </p>
                        </td>

                        {{-- Bank --}}
                        <td class="px-6 py-4 text-xs text-bankos-text-sec">
                            {{ $transfer->beneficiary_bank_name ?? '—' }}
                        </td>

                        {{-- Amount --}}
                        <td class="px-6 py-4 text-right font-bold text-bankos-text whitespace-nowrap">
                            ₦{{ number_format($transfer->amount, 2) }}
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-4">
                            @php
                                $status = $transfer->status;
                            @endphp
                            @if($status === 'successful')
                                <span class="badge badge-active text-[10px] uppercase tracking-wider">Successful</span>
                            @elseif($status === 'failed')
                                <span class="badge badge-danger text-[10px] uppercase tracking-wider">Failed</span>
                            @elseif($status === 'reversed')
                                <span class="badge bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 text-[10px] uppercase tracking-wider">Reversed</span>
                            @elseif($status === 'initiated')
                                <span class="badge bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 text-[10px] uppercase tracking-wider">Initiated</span>
                            @else
                                <span class="badge badge-pending text-[10px] uppercase tracking-wider">{{ ucfirst($status) }}</span>
                            @endif
                        </td>

                        {{-- Actions --}}
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('nip.show', $transfer) }}"
                               class="text-bankos-primary hover:text-blue-700 font-medium text-sm"
                               onclick="event.stopPropagation()">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="inline-flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-bankos-border" xmlns="http://www.w3.org/2000/svg"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                </svg>
                                <p class="font-medium text-bankos-text">No NIP transfers found.</p>
                                <p class="text-bankos-text-sec text-sm">
                                    @if(request()->hasAny(['status','date_from','date_to']))
                                        No transfers match your filters.
                                        <a href="{{ route('nip.index') }}" class="text-bankos-primary hover:underline">Clear filters</a>
                                    @else
                                        <a href="{{ route('nip.create') }}" class="text-bankos-primary hover:underline">Initiate a transfer</a> to get started.
                                    @endif
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transfers->hasPages())
        <div class="p-4 border-t border-bankos-border dark:border-bankos-dark-border bg-gray-50/30 dark:bg-bankos-dark-bg/20">
            {{ $transfers->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
