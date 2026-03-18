<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('reports.index') }}" class="text-bankos-text-sec hover:text-bankos-primary transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            </a>
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Staff Activity Audit</h2>
                <p class="text-sm text-bankos-text-sec mt-1">{{ \Carbon\Carbon::parse($startDate)->format('d M') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="flex justify-between items-center mb-6">
        <form method="GET" class="flex items-center gap-4 bg-white dark:bg-bankos-dark-bg p-2 rounded-lg shadow-sm border border-bankos-border dark:border-bankos-dark-border">
            <div class="flex items-center gap-2 ml-2">
                <label class="text-xs font-semibold text-bankos-text-sec">From</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-input text-sm border-none shadow-none">
            </div>
            <div class="h-6 w-px bg-bankos-border"></div>
            <div class="flex items-center gap-2">
                <label class="text-xs font-semibold text-bankos-text-sec">To</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-input text-sm border-none shadow-none">
            </div>
            <button type="submit" class="btn btn-primary text-sm py-2 px-4">Filter</button>
        </form>
        <button class="btn btn-secondary text-sm flex items-center gap-2 print:hidden" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            Print
        </button>
    </div>

    {{-- Workflow Approvals per Staff --}}
    <div class="card p-0 overflow-hidden shadow-md mb-8">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">Workflow Actions by Staff</h3>
            <p class="text-xs text-bankos-muted mt-0.5">Approvals and rejections processed in the period</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Staff Member</th>
                        <th class="px-6 py-3 font-semibold text-center">Approved</th>
                        <th class="px-6 py-3 font-semibold text-center">Rejected</th>
                        <th class="px-6 py-3 font-semibold text-center">Total</th>
                        <th class="px-6 py-3 font-semibold">Processes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($workflowActivity as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-bankos-text">{{ $row['user']?->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-bankos-muted">{{ $row['user']?->email ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-3 text-center font-bold text-emerald-600">{{ $row['approved'] }}</td>
                        <td class="px-6 py-3 text-center font-bold text-red-600">{{ $row['rejected'] }}</td>
                        <td class="px-6 py-3 text-center font-bold text-bankos-primary">{{ $row['total'] }}</td>
                        <td class="px-6 py-3 text-xs text-bankos-text-sec">{{ $row['processes']->implode(', ') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-bankos-muted">No workflow activity in the selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- System Audit Log per User --}}
    <div class="card p-0 overflow-hidden shadow-md mb-8">
        <div class="px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border bg-gray-50 dark:bg-bankos-dark-bg/50">
            <h3 class="text-sm font-semibold text-bankos-text">System Actions by Staff</h3>
            <p class="text-xs text-bankos-muted mt-0.5">All audit-logged activities in the period</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-bankos-light dark:bg-bankos-dark-bg/80 border-b border-bankos-border text-xs uppercase text-bankos-text-sec">
                        <th class="px-6 py-3 font-semibold">Staff Member</th>
                        <th class="px-6 py-3 font-semibold text-right">Total Actions</th>
                        <th class="px-6 py-3 font-semibold">Action Breakdown</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($auditByUser as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                        <td class="px-6 py-3">
                            <p class="font-medium text-bankos-text">{{ $row['user']?->name ?? 'System / Unknown' }}</p>
                            <p class="text-xs text-bankos-muted">{{ $row['user']?->email ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-3 text-right font-bold text-bankos-primary">{{ number_format($row['count']) }}</td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1">
                                @foreach($row['actions'] as $action => $cnt)
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-bankos-text-sec px-2 py-0.5 rounded capitalize">{{ $action }}: {{ $cnt }}</span>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-6 py-10 text-center text-bankos-muted">No audit log entries found for the selected period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
