@extends('layouts.app')
@section('title', 'My Tasks')
@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Tasks</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Tasks assigned to you across all projects</p>
        </div>
        <div class="flex items-center gap-3 text-sm">
            <span class="text-gray-500 dark:text-gray-400">{{ $tasks->count() }} tasks total</span>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex gap-2 flex-wrap">
        <form method="GET" class="flex gap-2 flex-wrap">
            <select name="priority" onchange="this.form.submit()" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-surface text-gray-700 dark:text-gray-300 px-3 py-1.5 text-sm">
                <option value="">All Priorities</option>
                @foreach(['critical','high','medium','low'] as $p)
                <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
            <select name="status" onchange="this.form.submit()" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-surface text-gray-700 dark:text-gray-300 px-3 py-1.5 text-sm">
                <option value="">All Statuses</option>
                @foreach(['open','in_progress','review','done'] as $s)
                <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            @if(request('priority') || request('status'))
            <a href="{{ route('projects.my-tasks') }}" class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Clear filters</a>
            @endif
        </form>
    </div>

    @if($tasks->isEmpty())
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto text-gray-300 dark:text-gray-600 mb-4"><path d="M9 11l3 3L22 4"></path><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">No tasks assigned</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">You don't have any tasks assigned to you yet</p>
        </div>
    @else
        {{-- Group by project --}}
        @foreach($tasksByProject as $projectId => $projectTasks)
        @php $proj = $projectTasks->first()->project; @endphp
        <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 bg-gray-50 dark:bg-bankos-dark-bg border-b border-bankos-border dark:border-bankos-dark-border">
                <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $proj->color ?? '#3B82F6' }}"></div>
                <a href="{{ route('projects.show', $proj) }}" class="text-sm font-semibold text-gray-900 dark:text-white hover:text-bankos-primary transition-colors">{{ $proj->name }}</a>
                <span class="text-xs text-gray-400 font-mono">{{ $proj->code }}</span>
                <span class="ml-auto text-xs text-gray-400">{{ $projectTasks->count() }} tasks</span>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($projectTasks as $task)
                <a href="{{ route('projects.tasks.show', $task) }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg transition-colors">
                    {{-- Priority indicator --}}
                    @php
                        $prioColors = [
                            'critical' => 'bg-red-500',
                            'high'     => 'bg-orange-500',
                            'medium'   => 'bg-amber-500',
                            'low'      => 'bg-gray-300 dark:bg-gray-600',
                        ];
                    @endphp
                    <div class="w-1.5 h-8 rounded-full {{ $prioColors[$task->priority] ?? 'bg-gray-300' }} flex-shrink-0"></div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">
                            <span class="text-gray-400 dark:text-gray-500 font-mono text-xs mr-1">{{ $proj->code }}-{{ $task->task_number }}</span>
                            {{ $task->title }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ $task->column->name ?? 'Unknown' }}
                            @if($task->due_date)
                            @php $overdue = $task->due_date->isPast() && !$task->completed_at; @endphp
                            <span class="{{ $overdue ? 'text-red-500 font-medium' : '' }}"> -- Due {{ $task->due_date->format('M j, Y') }}</span>
                            @endif
                        </p>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        {{-- Status badge --}}
                        @php
                            $statusBg = [
                                'open'        => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400',
                                'review'      => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400',
                                'done'        => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400',
                            ];
                        @endphp
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $statusBg[$task->status] ?? $statusBg['open'] }}">{{ ucfirst(str_replace('_',' ',$task->status)) }}</span>

                        {{-- Priority badge --}}
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $task->priority === 'critical' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400' : ($task->priority === 'high' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-400' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400') }}">{{ ucfirst($task->priority) }}</span>
                    </div>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    @endif
</div>
@endsection
