@extends('layouts.app')
@section('title', $project->code . '-' . $task->task_number . ' ' . $task->title)
@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('projects.index') }}" class="hover:text-bankos-primary transition-colors">Projects</a>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        <a href="{{ route('projects.show', $project) }}" class="hover:text-bankos-primary transition-colors">{{ $project->name }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        <span class="font-mono text-gray-700 dark:text-gray-300">{{ $project->code }}-{{ $task->task_number }}</span>
    </div>

    @if(session('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-800 dark:bg-green-900/30 dark:border-green-800 dark:text-green-300 rounded-lg text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-5">
            {{-- Title & Description --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $task->title }}</h1>
                @if($task->description)
                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 leading-relaxed whitespace-pre-wrap">{{ $task->description }}</div>
                @else
                <p class="mt-3 text-sm text-gray-400 italic">No description</p>
                @endif
            </div>

            {{-- Subtasks --}}
            @if($task->subtasks->count())
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Subtasks ({{ $task->subtasks->whereNotNull('completed_at')->count() }}/{{ $task->subtasks->count() }})</h3>
                <div class="space-y-2">
                    @foreach($task->subtasks as $sub)
                    <div class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                        <div class="w-5 h-5 rounded border flex items-center justify-center {{ $sub->completed_at ? 'bg-green-500 border-green-500' : 'border-gray-300 dark:border-gray-600' }}">
                            @if($sub->completed_at)
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            @endif
                        </div>
                        <span class="text-sm {{ $sub->completed_at ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $sub->title }}</span>
                        @if($sub->assignee)
                        <span class="ml-auto text-xs text-gray-400">{{ $sub->assignee->name }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Comments --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Comments ({{ $task->comments->count() }})</h3>

                <form method="POST" action="{{ route('projects.tasks.comments', $task) }}" class="flex gap-2 mb-4">
                    @csrf
                    <input type="text" name="body" required placeholder="Write a comment..."
                        class="flex-1 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-bankos-primary focus:border-transparent">
                    <button type="submit" class="bg-bankos-primary text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Post</button>
                </form>

                <div class="space-y-4">
                    @forelse($task->comments as $comment)
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-bankos-primary text-white text-xs font-semibold flex items-center justify-center flex-shrink-0">
                            {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $comment->user->name ?? 'Unknown' }}</span>
                                <span class="text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $comment->body }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 italic">No comments yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Activity</h3>
                <div class="space-y-3">
                    @forelse($task->activities as $act)
                    <div class="flex items-start gap-3 text-sm">
                        <div class="w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-500 dark:text-gray-400 text-[10px] font-semibold flex items-center justify-center flex-shrink-0 mt-0.5">
                            {{ strtoupper(substr($act->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <p class="text-gray-600 dark:text-gray-400">
                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ $act->user->name ?? 'System' }}</span>
                                {{ str_replace('_', ' ', $act->action) }}
                                @if($act->old_value)<span class="line-through text-gray-400">{{ Str::limit($act->old_value, 30) }}</span>@endif
                                @if($act->new_value)<span class="font-medium">{{ Str::limit($act->new_value, 30) }}</span>@endif
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $act->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400 italic">No activity yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">
            {{-- Fields --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5 space-y-4">
                <form method="POST" action="{{ route('projects.tasks.update', $task) }}" class="space-y-4">
                    @csrf @method('PATCH')

                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Column</label>
                        <select name="column_id" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                            @foreach($columns as $col)
                            <option value="{{ $col->id }}" {{ $task->column_id === $col->id ? 'selected' : '' }}>{{ $col->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Priority</label>
                        <select name="priority" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                            @foreach(['low','medium','high','critical'] as $p)
                            <option value="{{ $p }}" {{ $task->priority === $p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Assignee</label>
                        <select name="assignee_id" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                            <option value="">Unassigned</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $task->assignee_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Due Date</label>
                        <input type="date" name="due_date" value="{{ $task->due_date?->format('Y-m-d') }}"
                            class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Story Points</label>
                        <input type="number" name="story_points" value="{{ $task->story_points }}" min="0"
                            class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Sprint</label>
                        <select name="sprint_id" class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                            <option value="">None</option>
                            @foreach($sprints as $sprint)
                            <option value="{{ $sprint->id }}" {{ $task->sprint_id == $sprint->id ? 'selected' : '' }}>{{ $sprint->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Estimated Hours</label>
                        <input type="number" name="estimated_hours" value="{{ $task->estimated_hours }}" step="0.5" min="0"
                            class="mt-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                    </div>

                    <button type="submit" class="w-full bg-bankos-primary text-white text-sm px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Update Task</button>
                </form>
            </div>

            {{-- Time Tracking --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Time Tracking</h3>
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Logged</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $task->logged_hours ?? 0 }}h</span>
                </div>
                @if($task->estimated_hours)
                <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden mb-3">
                    @php $pct = $task->estimated_hours > 0 ? min(100, ($task->logged_hours / $task->estimated_hours) * 100) : 0; @endphp
                    <div class="h-full rounded-full {{ $pct > 100 ? 'bg-red-500' : 'bg-green-500' }}" style="width: {{ $pct }}%"></div>
                </div>
                @endif

                <form method="POST" action="{{ route('projects.tasks.time', $task) }}" class="space-y-2">
                    @csrf
                    <input type="number" name="hours" step="0.25" min="0.25" max="24" required placeholder="Hours"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                    <input type="text" name="note" placeholder="What did you work on?"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-bankos-dark-bg text-gray-900 dark:text-white px-3 py-1.5 text-sm">
                    <button type="submit" class="w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs font-medium px-3 py-2 rounded-lg transition-colors">Log Time</button>
                </form>

                @if($task->timeEntries->count())
                <div class="mt-3 space-y-2 max-h-40 overflow-y-auto">
                    @foreach($task->timeEntries->take(10) as $entry)
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <div>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $entry->user->name ?? 'Unknown' }}</span>
                            @if($entry->note) - {{ Str::limit($entry->note, 30) }} @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">{{ $entry->hours }}h</span>
                            <span>{{ $entry->logged_date?->format('M j') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Attachments --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Attachments ({{ $task->attachments->count() }})</h3>

                @foreach($task->attachments as $att)
                <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank" class="flex items-center gap-2 text-sm text-blue-600 dark:text-blue-400 hover:underline p-1.5 rounded hover:bg-gray-50 dark:hover:bg-bankos-dark-bg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    <span class="truncate">{{ $att->file_name }}</span>
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $att->file_size_kb }}KB</span>
                </a>
                @endforeach

                <form method="POST" action="{{ route('projects.tasks.attachments', $task) }}" enctype="multipart/form-data" class="mt-3">
                    @csrf
                    <input type="file" name="file" onchange="this.form.submit()"
                        class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-gray-100 dark:file:bg-gray-700 file:text-gray-700 dark:file:text-gray-300">
                </form>
            </div>

            {{-- Reporter / Meta --}}
            <div class="bg-white dark:bg-bankos-dark-surface rounded-xl border border-bankos-border dark:border-bankos-dark-border p-5 text-sm text-gray-500 dark:text-gray-400 space-y-2">
                <div class="flex justify-between"><span>Reporter</span><span class="font-medium text-gray-700 dark:text-gray-300">{{ $task->reporter->name ?? 'Unknown' }}</span></div>
                <div class="flex justify-between"><span>Created</span><span>{{ $task->created_at->format('M j, Y') }}</span></div>
                @if($task->completed_at)
                <div class="flex justify-between"><span>Completed</span><span>{{ $task->completed_at->format('M j, Y') }}</span></div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
