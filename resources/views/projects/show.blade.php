@extends('layouts.app')
@section('title', $project->name)
@section('content')
<div x-data="kanbanBoard()" class="space-y-4">

    {{-- Project Header --}}
    <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <a href="{{ route('projects.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                </a>
                <div class="w-3 h-10 rounded-full flex-shrink-0" style="background-color: {{ $project->color }}"></div>
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white truncate">{{ $project->name }}</h1>
                    <div class="flex items-center gap-3 mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-mono text-xs">{{ $project->code }}</span>
                        @if($project->description)
                        <span class="hidden md:inline truncate max-w-xs">{{ Str::limit($project->description, 60) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-shrink-0">
                {{-- Progress --}}
                <div class="text-center">
                    <div class="text-xs text-gray-500 dark:text-gray-400">Progress</div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <div class="w-20 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $project->progress }}%; background-color: {{ $project->color }}"></div>
                        </div>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $project->progress }}%</span>
                    </div>
                </div>

                {{-- Members --}}
                <div class="flex -space-x-2 ml-2">
                    @foreach($project->members->take(5) as $member)
                    <div class="w-8 h-8 rounded-full bg-bankos-primary text-white text-xs font-semibold flex items-center justify-center ring-2 ring-white dark:ring-bankos-dark-surface" title="{{ $member->user->name ?? '' }}">
                        {{ strtoupper(substr($member->user->name ?? '?', 0, 1)) }}
                    </div>
                    @endforeach
                </div>

                {{-- Sprint badge --}}
                @if($activeSprint)
                <div class="hidden md:flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg text-xs font-medium">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    {{ $activeSprint->name }}
                </div>
                @endif

                {{-- Settings --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-bankos-dark-bg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-48 bg-white dark:bg-bankos-dark-surface rounded-lg shadow-lg border border-bankos-border dark:border-bankos-dark-border py-1 z-50">
                        <button @click="showAddColumn = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">Add Column</button>
                        <button @click="showAddMember = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">Add Member</button>
                        <button @click="showSprints = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">Sprints</button>
                        <button @click="showLabels = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">Labels</button>
                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                        <button @click="showSettings = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">Project Settings</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 rounded-lg text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/30 dark:border-red-800 dark:text-red-300 rounded-lg text-sm">{{ session('error') }}</div>
    @endif

    {{-- ── KANBAN BOARD ─────────────────────────────────────────────── --}}
    <div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 70vh;">
        @foreach($columns as $column)
        <div class="flex-shrink-0 w-72 bg-gray-50 dark:bg-bankos-dark-bg rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col max-h-[calc(100vh-220px)]"
             @dragover.prevent
             @dragenter.prevent="dragOverColumn = '{{ $column->id }}'"
             @drop="dropTask($event, '{{ $column->id }}')"
             :class="{ 'ring-2 ring-bankos-primary ring-opacity-50': dragOverColumn === '{{ $column->id }}' }">

            {{-- Column Header --}}
            <div class="flex items-center justify-between px-3 py-3 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $column->color ?? '#94A3B8' }}"></div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $column->name }}</h3>
                    <span class="text-xs text-gray-400 bg-gray-200 dark:bg-gray-700 px-1.5 py-0.5 rounded-full font-medium">{{ $column->tasks->count() }}</span>
                    @if($column->wip_limit && $column->tasks->count() >= $column->wip_limit)
                    <span class="text-xs text-red-500 font-medium">WIP!</span>
                    @endif
                </div>
                <button @click="quickAddColumn = '{{ $column->id }}'; $nextTick(() => $refs['quickadd_{{ $column->id }}']?.focus())"
                    class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </button>
            </div>

            {{-- Task Cards --}}
            <div class="flex-1 overflow-y-auto p-2 space-y-2">
                @foreach($column->tasks as $task)
                <div draggable="true"
                     @dragstart="dragStart($event, '{{ $task->id }}')"
                     @dragend="dragEnd()"
                     @click="openTaskModal('{{ $task->id }}')"
                     class="bg-white dark:bg-bankos-dark-surface rounded-lg border border-gray-200 dark:border-gray-700 p-3 cursor-pointer hover:shadow-md transition-shadow group"
                     :class="{ 'opacity-50': draggingTask === '{{ $task->id }}' }">

                    {{-- Labels --}}
                    @if($task->labels && count($task->labels))
                    <div class="flex flex-wrap gap-1 mb-2">
                        @foreach($task->labels as $labelId)
                            @php $label = $project->labels->firstWhere('id', $labelId); @endphp
                            @if($label)
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full text-white" style="background-color: {{ $label->color }}">{{ $label->name }}</span>
                            @endif
                        @endforeach
                    </div>
                    @endif

                    {{-- Title --}}
                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200 leading-snug">
                        <span class="text-gray-400 dark:text-gray-500 font-mono text-xs mr-1">{{ $project->code }}-{{ $task->task_number }}</span>
                        {{ $task->title }}
                    </p>

                    {{-- Meta row --}}
                    <div class="flex items-center justify-between mt-2.5">
                        <div class="flex items-center gap-2">
                            {{-- Priority --}}
                            @php
                                $prioColors = [
                                    'critical' => 'text-red-500',
                                    'high'     => 'text-orange-500',
                                    'medium'   => 'text-amber-500',
                                    'low'      => 'text-gray-400',
                                ];
                            @endphp
                            <span class="{{ $prioColors[$task->priority] ?? 'text-gray-400' }}" title="{{ ucfirst($task->priority) }}">
                                @if($task->priority === 'critical')
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 22h20L12 2zm0 4l7.53 14H4.47L12 6z"/><rect x="11" y="10" width="2" height="5"/><rect x="11" y="16" width="2" height="2"/></svg>
                                @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                                @endif
                            </span>

                            {{-- Due date --}}
                            @if($task->due_date)
                            @php $overdue = $task->due_date->isPast() && !$task->completed_at; @endphp
                            <span class="text-[11px] {{ $overdue ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline -mt-0.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                {{ $task->due_date->format('M j') }}
                            </span>
                            @endif

                            {{-- Story points --}}
                            @if($task->story_points)
                            <span class="text-[10px] font-bold bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-1.5 py-0.5 rounded">{{ $task->story_points }}SP</span>
                            @endif

                            {{-- Subtasks --}}
                            @if($task->subtasks->count())
                            <span class="text-[11px] text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline -mt-0.5"><polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path></svg>
                                {{ $task->subtasks->whereNotNull('completed_at')->count() }}/{{ $task->subtasks->count() }}
                            </span>
                            @endif
                        </div>

                        {{-- Assignee --}}
                        @if($task->assignee)
                        <div class="w-6 h-6 rounded-full bg-bankos-primary text-white text-[10px] font-semibold flex items-center justify-center" title="{{ $task->assignee->name }}">
                            {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach

                {{-- Quick Add --}}
                <div x-show="quickAddColumn === '{{ $column->id }}'" x-transition class="mt-1">
                    <form @submit.prevent="quickCreateTask('{{ $column->id }}')" class="space-y-2">
                        <input x-ref="quickadd_{{ $column->id }}" type="text" x-model="quickAddTitle"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-surface text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent"
                            placeholder="Task title..." @keydown.escape="quickAddColumn = null; quickAddTitle = ''">
                        <div class="flex gap-2">
                            <button type="submit" class="flex-1 bg-bankos-primary hover:bg-blue-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">Add</button>
                            <button type="button" @click="quickAddColumn = null; quickAddTitle = ''" class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Column footer: add task --}}
            <div class="p-2 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                <button @click="quickAddColumn = '{{ $column->id }}'; $nextTick(() => $refs['quickadd_{{ $column->id }}']?.focus())"
                    x-show="quickAddColumn !== '{{ $column->id }}'"
                    class="w-full flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Add task
                </button>
            </div>
        </div>
    </div>

    {{-- ── TASK DETAIL MODAL ────────────────────────────────────────── --}}
    <div x-show="taskModalOpen" x-transition.opacity class="fixed inset-0 z-50 flex items-start justify-center pt-8 px-4 overflow-y-auto" style="display:none;">
        <div class="fixed inset-0 bg-black/40" @click="taskModalOpen = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border shadow-2xl w-full max-w-3xl mb-8 z-10" @click.away="taskModalOpen = false">

            <template x-if="currentTask">
                <div>
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between p-5 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-mono text-gray-400" x-text="'{{ $project->code }}-' + currentTask.task_number"></span>
                            <h2 class="text-lg font-bold text-gray-900 dark:text-white mt-0.5" x-text="currentTask.title"></h2>
                        </div>
                        <div class="flex items-center gap-2 ml-4">
                            <a :href="'/projects/tasks/' + currentTask.id" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-bankos-dark-bg transition-colors" title="Open full page">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                            <button @click="taskModalOpen = false" class="p-2 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-bankos-dark-bg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
                        {{-- Left: Description, Comments, Activity --}}
                        <div class="md:col-span-2 p-5 space-y-5 border-r border-gray-100 dark:border-gray-700">

                            {{-- Description --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Description</label>
                                <div x-show="!editingDesc" @click="editingDesc = true" class="mt-1 text-sm text-gray-700 dark:text-gray-300 min-h-[60px] p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg cursor-pointer">
                                    <span x-text="currentTask.description || 'Click to add description...'" :class="!currentTask.description ? 'text-gray-400 italic' : ''"></span>
                                </div>
                                <div x-show="editingDesc" class="mt-1">
                                    <textarea x-model="editDesc" rows="4" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent"></textarea>
                                    <div class="flex gap-2 mt-1">
                                        <button @click="saveTaskField('description', editDesc); editingDesc = false" class="bg-bankos-primary text-white text-xs px-3 py-1.5 rounded-lg">Save</button>
                                        <button @click="editingDesc = false" class="text-xs text-gray-500 px-3 py-1.5">Cancel</button>
                                    </div>
                                </div>
                            </div>

                            {{-- Subtasks --}}
                            <div x-show="currentTask.subtasks && currentTask.subtasks.length">
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Subtasks</label>
                                <div class="mt-2 space-y-1">
                                    <template x-for="sub in (currentTask.subtasks || [])" :key="sub.id">
                                        <div class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg text-sm">
                                            <div class="w-4 h-4 rounded border border-gray-300 dark:border-gray-600 flex items-center justify-center" :class="sub.completed_at ? 'bg-green-500 border-green-500' : ''">
                                                <svg x-show="sub.completed_at" xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                            </div>
                                            <span x-text="sub.title" :class="sub.completed_at ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Comments --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Comments</label>
                                <div class="mt-2">
                                    <form @submit.prevent="addComment()" class="flex gap-2">
                                        <input type="text" x-model="newComment" placeholder="Write a comment..."
                                            class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent">
                                        <button type="submit" class="bg-bankos-primary text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Post</button>
                                    </form>
                                    <div class="mt-3 space-y-3 max-h-60 overflow-y-auto">
                                        <template x-for="comment in (taskComments || [])" :key="comment.id">
                                            <div class="flex gap-3">
                                                <div class="w-7 h-7 rounded-full bg-gray-300 dark:bg-gray-600 text-gray-600 dark:text-gray-300 text-xs font-semibold flex items-center justify-center flex-shrink-0" x-text="(comment.user?.name || '?').charAt(0).toUpperCase()"></div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="comment.user?.name || 'Unknown'"></span>
                                                        <span class="text-[10px] text-gray-400" x-text="new Date(comment.created_at).toLocaleDateString()"></span>
                                                    </div>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-0.5" x-text="comment.body"></p>
                                                </div>
                                            </div>
                                        </template>
                                        <p x-show="!taskComments || !taskComments.length" class="text-xs text-gray-400 italic">No comments yet</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Activity --}}
                            <div>
                                <label class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Activity</label>
                                <div class="mt-2 space-y-2 max-h-48 overflow-y-auto">
                                    <template x-for="act in (taskActivities || [])" :key="act.id">
                                        <div class="flex items-start gap-2 text-xs text-gray-500 dark:text-gray-400">
                                            <span class="font-medium text-gray-700 dark:text-gray-300" x-text="act.user?.name || 'System'"></span>
                                            <span x-text="act.action.replace(/_/g, ' ')"></span>
                                            <span x-show="act.old_value" class="line-through" x-text="act.old_value"></span>
                                            <span x-show="act.new_value" class="font-medium text-gray-700 dark:text-gray-300" x-text="act.new_value"></span>
                                            <span class="text-gray-400 ml-auto flex-shrink-0" x-text="new Date(act.created_at).toLocaleDateString()"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Fields --}}
                        <div class="p-5 space-y-4">
                            {{-- Status / Column --}}
                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</label>
                                <select @change="saveTaskField('column_id', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                                    @foreach($columns as $col)
                                    <option value="{{ $col->id }}" :selected="currentTask.column_id === '{{ $col->id }}'">{{ $col->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Priority --}}
                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Priority</label>
                                <select @change="saveTaskField('priority', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                                    <option value="low" :selected="currentTask.priority === 'low'">Low</option>
                                    <option value="medium" :selected="currentTask.priority === 'medium'">Medium</option>
                                    <option value="high" :selected="currentTask.priority === 'high'">High</option>
                                    <option value="critical" :selected="currentTask.priority === 'critical'">Critical</option>
                                </select>
                            </div>

                            {{-- Assignee --}}
                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Assignee</label>
                                <select @change="saveTaskField('assignee_id', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" :selected="currentTask.assignee_id === '{{ $user->id }}'">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Due Date --}}
                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Due Date</label>
                                <input type="date" :value="currentTask.due_date ? currentTask.due_date.substring(0,10) : ''" @change="saveTaskField('due_date', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                            </div>

                            {{-- Story Points --}}
                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Story Points</label>
                                <input type="number" min="0" max="100" :value="currentTask.story_points || ''" @change="saveTaskField('story_points', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm" placeholder="0">
                            </div>

                            {{-- Time Tracking --}}
                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Time Tracking</label>
                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400 mb-2">
                                    <span class="font-medium" x-text="(currentTask.logged_hours || 0) + 'h logged'"></span>
                                    <span x-show="currentTask.estimated_hours"> / <span x-text="currentTask.estimated_hours + 'h estimated'"></span></span>
                                </div>
                                <form @submit.prevent="logTimeEntry()" class="space-y-1">
                                    <input type="number" x-model="logHours" step="0.25" min="0.25" max="24" placeholder="Hours"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                                    <input type="text" x-model="logNote" placeholder="Note (optional)"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                                    <button type="submit" class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">Log Time</button>
                                </form>
                            </div>

                            {{-- Attachments --}}
                            <div>
                                <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Attachments</label>
                                <div class="mt-1 space-y-1">
                                    <template x-for="att in (taskAttachments || [])" :key="att.id">
                                        <a :href="'/storage/' + att.file_path" target="_blank" class="flex items-center gap-2 text-xs text-blue-600 dark:text-blue-400 hover:underline p-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                            <span x-text="att.file_name" class="truncate"></span>
                                        </a>
                                    </template>
                                </div>
                                <form method="POST" :action="'/projects/tasks/' + currentTask.id + '/attachments'" enctype="multipart/form-data" class="mt-2">
                                    @csrf
                                    <input type="file" name="file" @change="$event.target.form.submit()" class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:font-medium file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-300">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- ── ADD COLUMN MODAL ─────────────────────────────────────────── --}}
    <div x-show="showAddColumn" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="fixed inset-0 bg-black/40" @click="showAddColumn = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-sm p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Column</h3>
            <form method="POST" action="{{ route('projects.columns.add', $project) }}">
                @csrf
                <div class="space-y-3">
                    <input type="text" name="name" required placeholder="Column name"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                    <input type="color" name="color" value="#94A3B8" class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                </div>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="showAddColumn = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-bankos-primary rounded-lg hover:bg-blue-700">Add</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── ADD MEMBER MODAL ─────────────────────────────────────────── --}}
    <div x-show="showAddMember" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="fixed inset-0 bg-black/40" @click="showAddMember = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-sm p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Add Member</h3>
            <form method="POST" action="{{ route('projects.members.add', $project) }}">
                @csrf
                <select name="user_id" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                    <option value="">Select a user...</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="showAddMember = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-bankos-primary rounded-lg hover:bg-blue-700">Add</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── SPRINTS MODAL ────────────────────────────────────────────── --}}
    <div x-show="showSprints" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="fixed inset-0 bg-black/40" @click="showSprints = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sprints</h3>
            <div class="space-y-2 mb-4 max-h-60 overflow-y-auto">
                @forelse($project->sprints as $sprint)
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-bankos-dark-bg rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sprint->name }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($sprint->status) }} {{ $sprint->start_date ? '| ' . $sprint->start_date->format('M j') . ' - ' . ($sprint->end_date ? $sprint->end_date->format('M j') : '?') : '' }}</p>
                    </div>
                    <div class="flex gap-1">
                        @if($sprint->status === 'planned')
                        <form method="POST" action="{{ route('projects.sprints.start', $sprint) }}">@csrf @method('PATCH')
                            <button class="text-xs bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400 px-2 py-1 rounded font-medium">Start</button>
                        </form>
                        @elseif($sprint->status === 'active')
                        <form method="POST" action="{{ route('projects.sprints.complete', $sprint) }}">@csrf @method('PATCH')
                            <button class="text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400 px-2 py-1 rounded font-medium">Complete</button>
                        </form>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-400 italic">No sprints yet</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('projects.sprints.create', $project) }}" class="border-t border-gray-100 dark:border-gray-700 pt-4 space-y-2">
                @csrf
                <input type="text" name="name" required placeholder="Sprint name" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                <div class="grid grid-cols-2 gap-2">
                    <input type="date" name="start_date" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                    <input type="date" name="end_date" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                </div>
                <button type="submit" class="w-full bg-bankos-primary text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700">Create Sprint</button>
            </form>
            <button @click="showSprints = false" class="mt-3 w-full text-center text-sm text-gray-500">Close</button>
        </div>
    </div>

    {{-- ── LABELS MODAL ─────────────────────────────────────────────── --}}
    <div x-show="showLabels" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="fixed inset-0 bg-black/40" @click="showLabels = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-sm p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Labels</h3>
            <div class="space-y-2 mb-4">
                @foreach($project->labels as $label)
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full" style="background-color: {{ $label->color }}"></div>
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label->name }}</span>
                </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('projects.labels.create', $project) }}" class="border-t border-gray-100 dark:border-gray-700 pt-4 flex gap-2">
                @csrf
                <input type="text" name="name" required placeholder="Label name" class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                <input type="color" name="color" value="#3B82F6" class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                <button type="submit" class="bg-bankos-primary text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700">Add</button>
            </form>
            <button @click="showLabels = false" class="mt-3 w-full text-center text-sm text-gray-500">Close</button>
        </div>
    </div>

    {{-- ── SETTINGS MODAL ───────────────────────────────────────────── --}}
    <div x-show="showSettings" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center px-4" style="display:none;">
        <div class="fixed inset-0 bg-black/40" @click="showSettings = false"></div>
        <div class="relative bg-white dark:bg-bankos-dark-surface rounded-xl shadow-xl w-full max-w-md p-6 z-10">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Project Settings</h3>
            <form method="POST" action="{{ route('projects.update', $project) }}" class="space-y-3">
                @csrf @method('PATCH')
                <input type="text" name="name" value="{{ $project->name }}" required class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                <textarea name="description" rows="2" class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">{{ $project->description }}</textarea>
                <div class="grid grid-cols-2 gap-2">
                    <select name="status" class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm">
                        @foreach(['active','on_hold','completed','archived'] as $s)
                        <option value="{{ $s }}" {{ $project->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <input type="color" name="color" value="{{ $project->color }}" class="w-full h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showSettings = false" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-bankos-primary rounded-lg hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function kanbanBoard() {
    return {
        draggingTask: null,
        dragOverColumn: null,
        taskModalOpen: false,
        currentTask: null,
        taskComments: [],
        taskActivities: [],
        taskAttachments: [],
        quickAddColumn: null,
        quickAddTitle: '',
        newComment: '',
        editingDesc: false,
        editDesc: '',
        logHours: '',
        logNote: '',
        showAddColumn: false,
        showAddMember: false,
        showSprints: false,
        showLabels: false,
        showSettings: false,

        dragStart(event, taskId) {
            this.draggingTask = taskId;
            event.dataTransfer.setData('text/plain', taskId);
            event.dataTransfer.effectAllowed = 'move';
        },

        dragEnd() {
            this.draggingTask = null;
            this.dragOverColumn = null;
        },

        dropTask(event, columnId) {
            event.preventDefault();
            const taskId = event.dataTransfer.getData('text/plain');
            this.dragOverColumn = null;
            this.draggingTask = null;

            if (!taskId) return;

            fetch('{{ route("projects.tasks.move") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ task_id: taskId, column_id: columnId, position: 0 }),
            }).then(() => location.reload());
        },

        quickCreateTask(columnId) {
            if (!this.quickAddTitle.trim()) return;

            fetch('{{ route("projects.tasks.create", $project) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ title: this.quickAddTitle, column_id: columnId }),
            }).then(() => {
                this.quickAddTitle = '';
                this.quickAddColumn = null;
                location.reload();
            });
        },

        openTaskModal(taskId) {
            fetch('/projects/tasks/' + taskId, {
                headers: { 'Accept': 'application/json' }
            })
            .then(r => {
                // If it returns HTML (full page), just navigate
                const ct = r.headers.get('content-type');
                if (ct && ct.includes('application/json')) return r.json();
                // Fallback: load task data from page data
                return null;
            })
            .then(data => {
                // Use inline data from the page instead
                this.loadTaskFromDom(taskId);
            })
            .catch(() => this.loadTaskFromDom(taskId));
        },

        loadTaskFromDom(taskId) {
            // Load from embedded data
            const tasks = @json($columns->pluck('tasks')->flatten());
            const task = tasks.find(t => t.id === taskId);
            if (task) {
                this.currentTask = task;
                this.editDesc = task.description || '';
                this.taskComments = [];
                this.taskActivities = [];
                this.taskAttachments = [];
                this.taskModalOpen = true;

                // Fetch full details asynchronously
                this.fetchTaskDetails(taskId);
            }
        },

        fetchTaskDetails(taskId) {
            // Load comments, activities, attachments via the task show page
            fetch('/projects/tasks/' + taskId)
                .then(r => r.text())
                .then(html => {
                    // Parse the JSON data embedded in the page if available
                    // For now, we rely on the task data we have
                })
                .catch(() => {});
        },

        saveTaskField(field, value) {
            if (!this.currentTask) return;

            const data = {};
            data[field] = value;

            fetch('/projects/tasks/' + this.currentTask.id, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            }).then(r => r.json()).then(res => {
                if (res.ok && res.task) {
                    this.currentTask = {...this.currentTask, ...res.task};
                }
                // Reload for column moves
                if (field === 'column_id') location.reload();
            });
        },

        addComment() {
            if (!this.newComment.trim() || !this.currentTask) return;

            fetch('/projects/tasks/' + this.currentTask.id + '/comments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ body: this.newComment }),
            }).then(r => r.json()).then(res => {
                if (res.ok && res.comment) {
                    this.taskComments.unshift(res.comment);
                }
                this.newComment = '';
            });
        },

        logTimeEntry() {
            if (!this.logHours || !this.currentTask) return;

            fetch('/projects/tasks/' + this.currentTask.id + '/time', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ hours: this.logHours, note: this.logNote }),
            }).then(r => r.json()).then(res => {
                if (res.ok) {
                    this.currentTask.logged_hours = parseFloat(this.currentTask.logged_hours || 0) + parseFloat(this.logHours);
                    this.logHours = '';
                    this.logNote = '';
                }
            });
        },
    };
}
</script>
@endsection
