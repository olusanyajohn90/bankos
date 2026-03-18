<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full">
            <div class="flex items-center gap-4">
                <a href="{{ route('groups.meetings.index', $group) }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text">
                        Meeting — {{ $meeting->meeting_date->format('d M Y') }}
                    </h2>
                    <p class="text-sm text-bankos-text-sec mt-1">
                        {{ $group->name }}
                        @if($meeting->location) · {{ $meeting->location }}@endif
                        @if($meeting->conductedBy) · {{ $meeting->conductedBy->name }}@endif
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($meeting->status === 'completed')
                    <span class="badge badge-active uppercase tracking-wider text-[10px]">Completed</span>
                @elseif($meeting->status === 'cancelled')
                    <span class="badge bg-red-100 text-red-700 uppercase tracking-wider text-[10px]">Cancelled</span>
                @else
                    <span class="badge bg-amber-100 text-amber-700 uppercase tracking-wider text-[10px]">Scheduled</span>
                @endif
            </div>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-bankos-primary">{{ $meeting->attendances->where('present', true)->count() }} / {{ $meeting->attendances->count() }}</p>
            <p class="text-sm text-bankos-text-sec mt-1">Present</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-green-600">₦{{ number_format($meeting->total_collected, 0) }}</p>
            <p class="text-sm text-bankos-text-sec mt-1">Total Collected</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-bankos-text">{{ $meeting->attendances->where('present', false)->count() }}</p>
            <p class="text-sm text-bankos-text-sec mt-1">Absent</p>
        </div>
    </div>

    <!-- Attendance Register -->
    <div class="card p-0 overflow-hidden shadow-sm border border-bankos-border">
        <div class="px-6 py-4 border-b border-bankos-border">
            <h3 class="font-semibold text-bankos-text">Attendance Register & Collections</h3>
        </div>

        @if($meeting->status !== 'completed')
        <form action="{{ route('groups.meetings.attendance', [$group, $meeting]) }}" method="POST">
            @csrf @method('PATCH')
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                            <th class="px-6 py-3 font-semibold">Member</th>
                            <th class="px-6 py-3 font-semibold text-center">Present</th>
                            <th class="px-6 py-3 font-semibold">Amount Paid (₦)</th>
                            <th class="px-6 py-3 font-semibold">Notes</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-bankos-border">
                        @forelse($meeting->attendances as $i => $att)
                        <tr class="hover:bg-blue-50/30 transition-colors">
                            <td class="px-6 py-3">
                                <input type="hidden" name="attendance[{{ $i }}][customer_id]" value="{{ $att->customer_id }}">
                                <p class="font-medium text-bankos-text">{{ $att->customer?->first_name }} {{ $att->customer?->last_name }}</p>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <input type="checkbox" name="attendance[{{ $i }}][present]" value="1"
                                    class="w-4 h-4 text-bankos-primary border-gray-300 rounded focus:ring-bankos-primary"
                                    {{ $att->present ? 'checked' : '' }}>
                            </td>
                            <td class="px-6 py-3">
                                <input type="number" name="attendance[{{ $i }}][amount_paid]" value="{{ $att->amount_paid }}"
                                    step="0.01" min="0" class="form-input text-sm w-36" placeholder="0.00">
                            </td>
                            <td class="px-6 py-3">
                                <input type="text" name="attendance[{{ $i }}][notes]" value="{{ $att->notes }}"
                                    class="form-input text-sm" placeholder="Optional note">
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-6 text-center text-bankos-text-sec">No attendance records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-bankos-border bg-gray-50/30 flex justify-end">
                <button type="submit" class="btn btn-primary shadow-md hover:-translate-y-0.5 transition-transform">Save Attendance & Close Meeting</button>
            </div>
        </form>
        @else
        <!-- Read-only attendance view -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50/50 border-b border-bankos-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Member</th>
                        <th class="px-6 py-3 font-semibold text-center">Present</th>
                        <th class="px-6 py-3 font-semibold">Amount Paid</th>
                        <th class="px-6 py-3 font-semibold">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border">
                    @forelse($meeting->attendances as $att)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-3 font-medium text-bankos-text">{{ $att->customer?->first_name }} {{ $att->customer?->last_name }}</td>
                        <td class="px-6 py-3 text-center">
                            @if($att->present)
                                <svg class="w-5 h-5 text-green-500 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                <svg class="w-5 h-5 text-red-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            @endif
                        </td>
                        <td class="px-6 py-3 font-medium text-bankos-text">₦{{ number_format($att->amount_paid, 2) }}</td>
                        <td class="px-6 py-3 text-bankos-text-sec">{{ $att->notes ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-6 text-center text-bankos-text-sec">No attendance records.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @endif
    </div>
</x-app-layout>
