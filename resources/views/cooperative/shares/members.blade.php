<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center w-full gap-4">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">
                    Member Shares
                </h2>
                <p class="text-sm text-bankos-text-sec mt-1">All cooperative members with share holdings</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cooperative.shares.purchase') }}" class="btn btn-primary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Purchase Shares
                </a>
                <a href="{{ route('cooperative.shares.index') }}" class="btn btn-secondary flex items-center gap-2 text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="card p-0 overflow-hidden">
        {{-- Search --}}
        <div class="border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50/50 dark:bg-bankos-dark-bg/20">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 p-4">
                <div class="text-sm text-bankos-muted">
                    {{ $members->total() }} {{ Str::plural('member', $members->total()) }} with shares
                </div>
                <form action="{{ route('cooperative.shares.members') }}" method="GET" class="flex items-center gap-2">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-bankos-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ $search }}"
                               class="form-input pl-9 pr-4 py-2 text-sm w-56"
                               placeholder="Search by name or number...">
                    </div>
                    <button type="submit" class="btn btn-secondary text-sm py-2">Search</button>
                    @if($search)
                        <a href="{{ route('cooperative.shares.members') }}" class="text-sm text-bankos-primary hover:underline">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-5 py-4 font-semibold">Member</th>
                        <th class="px-5 py-4 font-semibold">Customer #</th>
                        <th class="px-5 py-4 font-semibold">Total Shares</th>
                        <th class="px-5 py-4 font-semibold">Total Value</th>
                        <th class="px-5 py-4 font-semibold">Holdings</th>
                        <th class="px-5 py-4 font-semibold"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($members as $member)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-bankos-dark-bg/30 transition-colors">
                            <td class="px-5 py-4">
                                <a href="{{ route('cooperative.shares.members.show', $member->customer_id) }}" class="font-medium text-bankos-primary hover:underline">
                                    {{ $member->first_name }} {{ $member->last_name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 text-sm font-mono text-bankos-muted">{{ $member->customer_number }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text font-semibold">{{ number_format($member->total_shares) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-text dark:text-bankos-dark-text font-mono font-semibold">{{ number_format($member->total_value, 2) }}</td>
                            <td class="px-5 py-4 text-sm text-bankos-muted">{{ $member->holdings_count }} {{ Str::plural('holding', $member->holdings_count) }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ route('cooperative.shares.members.show', $member->customer_id) }}" class="text-bankos-primary hover:underline text-sm font-medium">View Portfolio</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-bankos-muted">
                                @if($search)
                                    No members found matching "{{ $search }}".
                                @else
                                    No members have purchased shares yet.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($members->hasPages())
            <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $members->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
