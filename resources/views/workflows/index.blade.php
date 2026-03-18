<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="font-bold text-xl text-bankos-text dark:text-bankos-dark-text leading-tight">Workflows</h2>
                <p class="text-sm text-bankos-text-sec mt-1">Approval queues, SLA tracking, and audit trail</p>
            </div>
        </div>
    </x-slot>

    {{-- ── Stats Cards ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">My Pending</p>
            <p class="text-3xl font-extrabold text-bankos-primary mt-1">{{ $myPendingCount }}</p>
            <p class="text-xs text-bankos-muted mt-1">Tasks in my queue</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Total Pending</p>
            <p class="text-3xl font-extrabold text-bankos-text mt-1">{{ $pendingCount }}</p>
            <p class="text-xs text-bankos-muted mt-1">Across all queues</p>
        </div>
        <div class="card p-5 {{ $overdueCount > 0 ? 'border-red-300 dark:border-red-800' : '' }}">
            <p class="text-xs {{ $overdueCount > 0 ? 'text-red-600' : 'text-bankos-text-sec' }} uppercase tracking-wider font-semibold">Overdue</p>
            <p class="text-3xl font-extrabold {{ $overdueCount > 0 ? 'text-red-600' : 'text-bankos-text' }} mt-1">{{ $overdueCount }}</p>
            <p class="text-xs text-bankos-muted mt-1">SLA breached</p>
        </div>
        <div class="card p-5">
            <p class="text-xs text-bankos-text-sec uppercase tracking-wider font-semibold">Completed Today</p>
            <p class="text-3xl font-extrabold text-green-600 mt-1">{{ $completedToday }}</p>
            <p class="text-xs text-bankos-muted mt-1">Approved or rejected</p>
        </div>
    </div>

    <div class="card p-0" x-data="{ tab: 'my_tasks', selected: [] }">

        {{-- ── Tabs ── --}}
        <div class="border-b border-bankos-border dark:border-bankos-dark-border px-6 pt-4 flex gap-6">
            <button @click="tab = 'my_tasks'"
                :class="{'text-bankos-primary border-b-2 border-bankos-primary font-semibold': tab === 'my_tasks', 'text-bankos-text-sec hover:text-bankos-text': tab !== 'my_tasks'}"
                class="pb-3 px-1 transition-colors flex items-center gap-2">
                My Tasks
                @if($myPendingCount > 0)
                    <span class="bg-bankos-warning text-white text-xs px-2 py-0.5 rounded-full font-bold">{{ $myPendingCount }}</span>
                @endif
            </button>
            <button @click="tab = 'all_instances'"
                :class="{'text-bankos-primary border-b-2 border-bankos-primary font-semibold': tab === 'all_instances', 'text-bankos-text-sec hover:text-bankos-text': tab !== 'all_instances'}"
                class="pb-3 px-1 transition-colors">
                All Instances
            </button>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- TAB 1: MY TASKS --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'my_tasks'">
            @if($myTasks->isNotEmpty())
            {{-- Bulk action toolbar --}}
            <div x-show="selected.length > 0"
                 x-transition
                 class="bg-bankos-primary/5 border-b border-bankos-primary/20 px-6 py-3 flex items-center gap-3">
                <span class="text-sm text-bankos-primary font-semibold" x-text="selected.length + ' task(s) selected'"></span>
                <form method="POST" action="{{ route('workflows.bulk-action') }}" id="bulkForm">
                    @csrf
                    <input type="hidden" name="action" id="bulkActionVal">
                    <template x-for="id in selected">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <div class="flex gap-2">
                        <button type="button"
                            @click="document.getElementById('bulkActionVal').value='approve'; document.getElementById('bulkForm').submit()"
                            class="btn btn-primary text-xs">
                            Approve All Selected
                        </button>
                        <button type="button"
                            @click="document.getElementById('bulkActionVal').value='reject'; document.getElementById('bulkForm').submit()"
                            class="btn btn-danger text-xs">
                            Reject All Selected
                        </button>
                        <button type="button" @click="selected = []" class="btn btn-secondary text-xs">Clear</button>
                    </div>
                </form>
            </div>
            @endif

            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-4 py-4 w-8">
                            <input type="checkbox" class="rounded border-gray-300"
                                @change="selected = $event.target.checked ? {{ $myTasks->pluck('id')->toJson() }} : []">
                        </th>
                        <th class="px-4 py-4 font-semibold">Process / Step</th>
                        <th class="px-4 py-4 font-semibold">Subject</th>
                        <th class="px-4 py-4 font-semibold">SLA</th>
                        <th class="px-4 py-4 font-semibold">Submitted</th>
                        <th class="px-4 py-4 font-semibold text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @forelse($myTasks as $task)
                    @php $sla = $task->slaStatus(); @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors {{ $sla === 'overdue' ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                        <td class="px-4 py-4">
                            <input type="checkbox" class="rounded border-gray-300" :value="'{{ $task->id }}'"
                                x-model="selected">
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-bankos-text">{{ $task->process_name }}</p>
                            @if($task->total_steps > 1)
                            <div class="flex items-center gap-1 mt-1.5">
                                @for($s = 1; $s <= $task->total_steps; $s++)
                                    <div class="h-1.5 rounded-full flex-1 {{ $s <= $task->step ? 'bg-bankos-primary' : 'bg-gray-200 dark:bg-gray-700' }}"></div>
                                @endfor
                            </div>
                            <p class="text-xs text-bankos-muted mt-1">{{ $task->currentStepLabel() }}</p>
                            @else
                            <p class="text-xs text-bankos-muted mt-1">{{ $task->currentStepLabel() }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-bankos-primary text-sm">{{ $task->subjectDescription() }}</p>
                            <p class="text-xs text-bankos-muted mt-0.5">Role: <span class="font-medium text-bankos-text">{{ $task->assigned_role }}</span></p>
                        </td>
                        <td class="px-4 py-4">
                            @if($task->due_at)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border {{ $task->slaBadgeClasses() }}">
                                @if($sla === 'overdue')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                @endif
                                {{ $task->slaLabel() }}
                            </span>
                            <p class="text-xs text-bankos-muted mt-1">Due {{ $task->due_at->format('d M, H:i') }}</p>
                            @else
                                <span class="text-xs text-bankos-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-xs text-bankos-text-sec">
                            {{ $task->started_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-4 text-right">
                            <a href="{{ route('workflows.show', $task->id) }}" class="btn btn-primary text-xs">
                                Review →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-bankos-muted">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto mb-3 w-8 h-8 text-bankos-border" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="font-medium">Your queue is clear</p>
                            <p class="text-xs mt-1">No pending tasks assigned to your role.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ═══════════════════════════════════════════════════════════════ --}}
        {{-- TAB 2: ALL INSTANCES --}}
        {{-- ═══════════════════════════════════════════════════════════════ --}}
        <div x-show="tab === 'all_instances'" style="display:none">

            {{-- Filters --}}
            <form method="GET" class="flex gap-3 px-6 py-4 border-b border-bankos-border dark:border-bankos-dark-border flex-wrap">
                <select name="process" class="input text-sm py-1.5 pr-8 min-w-[180px]">
                    <option value="">All Processes</option>
                    @foreach($processes as $p)
                        <option value="{{ $p }}" {{ request('process') === $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
                <select name="status" class="input text-sm py-1.5 pr-8">
                    <option value="all">All Statuses</option>
                    <option value="pending"   {{ request('status') === 'pending'   ? 'selected' : '' }}>Pending</option>
                    <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
                    <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Rejected</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="btn btn-secondary text-sm">Filter</button>
                <a href="{{ route('workflows.index') }}" class="btn btn-secondary text-sm">Clear</a>
            </form>

            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-bankos-dark-bg/50 border-b border-bankos-border dark:border-bankos-dark-border text-xs uppercase tracking-wider text-bankos-text-sec">
                        <th class="px-6 py-4 font-semibold">Process / Step</th>
                        <th class="px-6 py-4 font-semibold">Subject</th>
                        <th class="px-6 py-4 font-semibold">Assigned Role</th>
                        <th class="px-6 py-4 font-semibold">Status / SLA</th>
                        <th class="px-6 py-4 font-semibold">Timeline</th>
                        <th class="px-6 py-4 font-semibold">Actioned By</th>
                        <th class="px-6 py-4 font-semibold text-right">Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-bankos-border dark:divide-bankos-dark-border">
                    @foreach($allInstances as $instance)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-semibold text-bankos-text">{{ $instance->process_name }}</p>
                            @if($instance->total_steps > 1)
                            <p class="text-xs text-bankos-muted mt-0.5">Step {{ $instance->step }}/{{ $instance->total_steps }} — {{ $instance->currentStepLabel() }}</p>
                            @else
                            <p class="text-xs text-bankos-muted mt-0.5">{{ $instance->currentStepLabel() }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-bankos-primary font-medium">{{ $instance->subjectDescription() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-mono bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $instance->assigned_role }}</span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="badge {{ $instance->statusBadgeClasses() }} text-xs px-2 py-0.5 rounded-full font-semibold">
                                {{ ucfirst($instance->status) }}
                            </span>
                            @if($instance->status === 'pending' && $instance->due_at)
                            <p class="mt-1">
                                <span class="text-xs px-1.5 py-0.5 rounded border {{ $instance->slaBadgeClasses() }}">
                                    {{ $instance->slaLabel() }}
                                </span>
                            </p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-bankos-text-sec">
                            <p>Started: {{ $instance->started_at->format('d M Y, H:i') }}</p>
                            @if($instance->ended_at)
                            <p class="mt-0.5 text-green-600">Ended: {{ $instance->ended_at->format('d M Y, H:i') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-bankos-text-sec">
                            {{ $instance->actionedBy?->name ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('workflows.show', $instance->id) }}" class="text-bankos-primary text-xs hover:underline font-medium">View →</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($allInstances->hasPages())
            <div class="px-6 py-4 border-t border-bankos-border dark:border-bankos-dark-border">
                {{ $allInstances->links() }}
            </div>
            @endif
        </div>

    </div>
</x-app-layout>
