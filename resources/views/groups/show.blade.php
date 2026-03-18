<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('groups.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">{{ $group->name }}</h2>
                    <p class="text-sm text-bankos-text-sec mt-1">
                        @if($group->centre)Centre: {{ $group->centre->name }} ·@endif
                        @if($group->loanOfficer)Officer: {{ $group->loanOfficer->name }}@endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('groups.meetings.index', $group) }}" class="btn btn-secondary text-sm">Meetings</a>
                <a href="{{ route('groups.edit', $group) }}" class="btn btn-secondary text-sm">Edit Group</a>
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg">{{ session('error') }}</div>
    @endif

    <!-- KPI Row -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-bankos-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-bankos-text-sec">Members</p>
                <p class="text-xl font-bold text-bankos-text">{{ $group->members->count() }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs text-bankos-text-sec">Active Loans</p>
                <p class="text-xl font-bold text-bankos-text">{{ $group->active_loans_count }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-xs text-bankos-text-sec">PAR</p>
                <p class="text-xl font-bold text-bankos-text">₦{{ number_format($group->portfolio_at_risk, 0) }}</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full {{ $group->solidarity_guarantee ? 'bg-blue-100' : 'bg-gray-100' }} flex items-center justify-center">
                <svg class="w-5 h-5 {{ $group->solidarity_guarantee ? 'text-bankos-primary' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <p class="text-xs text-bankos-text-sec">Solidarity</p>
                <p class="text-sm font-bold {{ $group->solidarity_guarantee ? 'text-bankos-primary' : 'text-bankos-text-sec' }}">
                    {{ $group->solidarity_guarantee ? 'Enabled' : 'Disabled' }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Members Table + Add Member -->
        <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
            <div class="px-6 py-4 border-b border-bankos-border flex justify-between items-center">
                <h3 class="font-semibold text-bankos-text">Members ({{ $group->members->count() }})</h3>
            </div>

            <!-- Add Member Form -->
            @if($availableCustomers->count())
            <div class="px-6 py-4 bg-blue-50/30 dark:bg-blue-900/10 border-b border-bankos-border">
                <form action="{{ route('groups.members.add', $group) }}" method="POST" class="flex flex-wrap gap-2 items-end">
                    @csrf
                    <div class="flex-1 min-w-[160px]">
                        <label class="form-label text-xs">Customer</label>
                        <select name="customer_id" class="form-input text-sm" required>
                            <option value="">Select customer...</option>
                            @foreach($availableCustomers as $c)
                                <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-32">
                        <label class="form-label text-xs">Role</label>
                        <select name="role" class="form-input text-sm">
                            <option value="member">Member</option>
                            <option value="leader">Leader</option>
                            <option value="treasurer">Treasurer</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary text-sm">Add</button>
                </form>
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-6 py-3 font-semibold">Customer</th>
                            <th class="px-6 py-3 font-semibold">Role</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border">
                        @forelse($group->members as $member)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-3">
                                @if($member->customer)
                                <a href="{{ route('customers.show', $member->customer) }}" class="font-medium text-bankos-primary hover:underline">
                                    {{ $member->customer->first_name }} {{ $member->customer->last_name }}
                                </a>
                                @else
                                <span class="text-bankos-muted text-xs italic">Unknown</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <span class="capitalize text-bankos-text-sec">{{ $member->role }}</span>
                            </td>
                            <td class="px-6 py-3">
                                @if($member->status === 'active')
                                    <span class="badge badge-active text-[10px] uppercase">Active</span>
                                @else
                                    <span class="badge bg-gray-100 text-gray-600 text-[10px] uppercase">Exited</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                @if($member->status === 'active')
                                <form action="{{ route('groups.members.remove', [$group, $member]) }}" method="POST" onsubmit="return confirm('Mark this member as exited?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">Exit</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-6 text-center text-bankos-text-sec text-sm">No members yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Meetings -->
        <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
            <div class="px-6 py-4 border-b border-bankos-border flex justify-between items-center">
                <h3 class="font-semibold text-bankos-text">Recent Meetings</h3>
                <a href="{{ route('groups.meetings.create', $group) }}" class="btn btn-primary text-xs">+ Schedule Meeting</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-6 py-3 font-semibold">Date</th>
                            <th class="px-6 py-3 font-semibold">Collected</th>
                            <th class="px-6 py-3 font-semibold">Status</th>
                            <th class="px-6 py-3 font-semibold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border">
                        @forelse($group->meetings as $meeting)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-3 text-bankos-text">{{ $meeting->meeting_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 font-medium text-bankos-text">₦{{ number_format($meeting->total_collected, 0) }}</td>
                            <td class="px-6 py-3">
                                @if($meeting->status === 'completed')
                                    <span class="badge badge-active text-[10px] uppercase">Done</span>
                                @elseif($meeting->status === 'cancelled')
                                    <span class="badge bg-red-100 text-red-700 text-[10px] uppercase">Cancelled</span>
                                @else
                                    <span class="badge bg-amber-100 text-amber-700 text-[10px] uppercase">Scheduled</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('groups.meetings.show', [$group, $meeting]) }}" class="text-bankos-primary hover:text-blue-700 text-sm">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-6 text-center text-bankos-text-sec text-sm">No meetings recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($group->meetings->count())
            <div class="px-6 py-3 border-t border-bankos-border bg-gray-50/30">
                <a href="{{ route('groups.meetings.index', $group) }}" class="text-bankos-primary text-sm hover:underline">View all meetings →</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Group Loans -->
    @if($group->loans->count())
    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border mt-6">
        <div class="px-6 py-4 border-b border-bankos-border">
            <h3 class="font-semibold text-bankos-text">Group Loans</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Loan #</th>
                        <th class="px-6 py-3 font-semibold">Customer</th>
                        <th class="px-6 py-3 font-semibold">Principal</th>
                        <th class="px-6 py-3 font-semibold">Outstanding</th>
                        <th class="px-6 py-3 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border">
                    @foreach($group->loans as $loan)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-3"><a href="{{ route('loans.show', $loan) }}" class="font-mono text-bankos-primary hover:underline">{{ $loan->loan_number }}</a></td>
                        <td class="px-6 py-3 text-bankos-text">{{ $loan->customer?->first_name }} {{ $loan->customer?->last_name }}</td>
                        <td class="px-6 py-3 text-bankos-text">₦{{ number_format($loan->principal_amount, 0) }}</td>
                        <td class="px-6 py-3 text-bankos-text">₦{{ number_format($loan->outstanding_balance, 0) }}</td>
                        <td class="px-6 py-3">
                            <span class="badge text-[10px] uppercase tracking-wider
                                @if($loan->status === 'active') badge-active
                                @elseif($loan->status === 'overdue') bg-red-100 text-red-700
                                @elseif($loan->status === 'closed') bg-gray-100 text-gray-600
                                @else bg-amber-100 text-amber-700 @endif">
                                {{ $loan->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</x-app-layout>
