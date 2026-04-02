<?php

namespace App\Http\Controllers;

use App\Models\PmProject;
use App\Models\PmBoard;
use App\Models\PmColumn;
use App\Models\PmTask;
use App\Models\PmTaskComment;
use App\Models\PmTaskAttachment;
use App\Models\PmTaskActivity;
use App\Models\PmTimeEntry;
use App\Models\PmSprint;
use App\Models\PmLabel;
use App\Models\PmProjectMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    // ── Project CRUD ────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = PmProject::with(['owner', 'members.user', 'tasks'])
            ->where('pm_projects.tenant_id', auth()->user()->tenant_id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->orderByDesc('created_at')->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('projects.create', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:10|alpha_num',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:7',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'visibility'  => 'nullable|in:public,private',
            'members'     => 'nullable|array',
        ]);

        $project = DB::transaction(function () use ($request) {
            $project = PmProject::create([
                'tenant_id'   => auth()->user()->tenant_id,
                'name'        => $request->name,
                'code'        => strtoupper($request->code),
                'description' => $request->description,
                'color'       => $request->color ?? '#3B82F6',
                'owner_id'    => auth()->id(),
                'status'      => 'active',
                'visibility'  => $request->visibility ?? 'public',
                'start_date'  => $request->start_date,
                'end_date'    => $request->end_date,
                'progress'    => 0,
            ]);

            // Default board
            $board = PmBoard::create([
                'project_id' => $project->id,
                'name'       => 'Main Board',
                'is_default' => true,
            ]);

            // Default columns
            $columns = [
                ['name' => 'To Do',        'color' => '#94A3B8', 'position' => 0, 'is_done_column' => false],
                ['name' => 'In Progress',   'color' => '#3B82F6', 'position' => 1, 'is_done_column' => false],
                ['name' => 'Review',        'color' => '#F59E0B', 'position' => 2, 'is_done_column' => false],
                ['name' => 'Done',          'color' => '#10B981', 'position' => 3, 'is_done_column' => true],
            ];

            foreach ($columns as $col) {
                PmColumn::create(array_merge($col, ['board_id' => $board->id]));
            }

            // Add owner as member
            PmProjectMember::create([
                'project_id' => $project->id,
                'user_id'    => auth()->id(),
                'role'       => 'owner',
                'joined_at'  => now(),
            ]);

            // Add invited members
            if ($request->members) {
                foreach ($request->members as $userId) {
                    if ($userId != auth()->id()) {
                        PmProjectMember::create([
                            'project_id' => $project->id,
                            'user_id'    => $userId,
                            'role'       => 'member',
                            'joined_at'  => now(),
                        ]);
                    }
                }
            }

            return $project;
        });

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function show($id)
    {
        $project = PmProject::with(['owner', 'members.user', 'labels', 'sprints'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $board = $project->defaultBoard ?? $project->boards()->first();

        $columns = $board
            ? PmColumn::where('board_id', $board->id)
                ->with(['tasks' => function ($q) {
                    $q->with(['assignee', 'subtasks'])->orderBy('position');
                }])
                ->orderBy('position')
                ->get()
            : collect();

        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $activeSprint = $project->sprints()->where('status', 'active')->first();

        return view('projects.show', compact('project', 'columns', 'board', 'users', 'activeSprint'));
    }

    public function update(Request $request, $id)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:7',
            'status'      => 'nullable|in:active,on_hold,completed,archived',
            'start_date'  => 'nullable|date',
            'end_date'    => 'nullable|date',
            'visibility'  => 'nullable|in:public,private',
        ]);

        $project->update($request->only([
            'name', 'description', 'color', 'status', 'start_date', 'end_date', 'visibility',
        ]));

        return back()->with('success', 'Project updated.');
    }

    public function destroy($id)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);
        $project->update(['status' => 'archived']);

        return redirect()->route('projects.index')->with('success', 'Project archived.');
    }

    // ── Board / Kanban ──────────────────────────────────────────────────────────

    public function board($projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);
        $board = $project->defaultBoard ?? $project->boards()->first();

        if (!$board) {
            return response()->json(['columns' => []]);
        }

        $columns = PmColumn::where('board_id', $board->id)
            ->with(['tasks' => function ($q) {
                $q->with(['assignee', 'subtasks'])->orderBy('position');
            }])
            ->orderBy('position')
            ->get();

        return response()->json(['columns' => $columns]);
    }

    public function moveTask(Request $request)
    {
        $request->validate([
            'task_id'   => 'required|string',
            'column_id' => 'required|string',
            'position'  => 'required|integer|min:0',
        ]);

        $task = PmTask::findOrFail($request->task_id);
        $oldColumn = $task->column;
        $newColumn = PmColumn::findOrFail($request->column_id);

        $oldColumnName = $oldColumn->name ?? 'Unknown';
        $newColumnName = $newColumn->name;

        $task->update([
            'column_id' => $request->column_id,
            'position'  => $request->position,
        ]);

        // Mark completed if moved to done column
        if ($newColumn->is_done_column && !$task->completed_at) {
            $task->update(['status' => 'done', 'completed_at' => now()]);
        } elseif (!$newColumn->is_done_column && $task->completed_at) {
            $task->update(['status' => 'in_progress', 'completed_at' => null]);
        }

        // Log activity
        if ($oldColumnName !== $newColumnName) {
            PmTaskActivity::create([
                'task_id'   => $task->id,
                'user_id'   => auth()->id(),
                'action'    => 'moved',
                'old_value' => $oldColumnName,
                'new_value' => $newColumnName,
            ]);
        }

        // Update project progress
        $this->updateProjectProgress($task->project_id);

        return response()->json(['ok' => true]);
    }

    public function addColumn(Request $request, $projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);
        $board = $project->defaultBoard ?? $project->boards()->first();

        $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|max:7',
        ]);

        $maxPos = PmColumn::where('board_id', $board->id)->max('position') ?? -1;

        PmColumn::create([
            'board_id'       => $board->id,
            'name'           => $request->name,
            'color'          => $request->color ?? '#94A3B8',
            'position'       => $maxPos + 1,
            'is_done_column' => false,
        ]);

        return back()->with('success', 'Column added.');
    }

    public function updateColumn(Request $request, $columnId)
    {
        $column = PmColumn::findOrFail($columnId);

        $request->validate([
            'name'           => 'sometimes|string|max:100',
            'color'          => 'nullable|string|max:7',
            'position'       => 'nullable|integer',
            'wip_limit'      => 'nullable|integer|min:0',
            'is_done_column' => 'nullable|boolean',
        ]);

        $column->update($request->only(['name', 'color', 'position', 'wip_limit', 'is_done_column']));

        return back()->with('success', 'Column updated.');
    }

    public function deleteColumn($columnId)
    {
        $column = PmColumn::findOrFail($columnId);

        if ($column->tasks()->count() > 0) {
            return back()->with('error', 'Cannot delete a column that has tasks. Move tasks first.');
        }

        $column->delete();

        return back()->with('success', 'Column deleted.');
    }

    // ── Tasks ───────────────────────────────────────────────────────────────────

    public function createTask(Request $request, $projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);

        $request->validate([
            'title'       => 'required|string|max:500',
            'column_id'   => 'required|string',
            'description' => 'nullable|string',
            'priority'    => 'nullable|in:low,medium,high,critical',
            'assignee_id' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'labels'      => 'nullable|array',
            'sprint_id'   => 'nullable|string',
            'story_points'=> 'nullable|integer|min:0',
            'parent_id'   => 'nullable|string',
        ]);

        // Auto-generate task number
        $lastNum = PmTask::where('project_id', $project->id)->max('task_number') ?? 0;
        $taskNumber = $lastNum + 1;

        $maxPos = PmTask::where('column_id', $request->column_id)->max('position') ?? -1;

        $task = PmTask::create([
            'project_id'   => $project->id,
            'column_id'    => $request->column_id,
            'parent_id'    => $request->parent_id,
            'task_number'  => $taskNumber,
            'title'        => $request->title,
            'description'  => $request->description,
            'priority'     => $request->priority ?? 'medium',
            'status'       => 'open',
            'assignee_id'  => $request->assignee_id,
            'reporter_id'  => auth()->id(),
            'due_date'     => $request->due_date,
            'position'     => $maxPos + 1,
            'labels'       => $request->labels ?? [],
            'sprint_id'    => $request->sprint_id,
            'story_points' => $request->story_points,
        ]);

        PmTaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => auth()->id(),
            'action'    => 'created',
            'old_value' => null,
            'new_value' => $task->title,
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'task' => $task->load('assignee')]);
        }

        return back()->with('success', 'Task created.');
    }

    public function showTask($taskId)
    {
        $task = PmTask::with([
            'project', 'column', 'assignee', 'reporter',
            'comments.user', 'attachments.uploader', 'activities.user',
            'timeEntries.user', 'subtasks.assignee', 'sprint',
        ])->findOrFail($taskId);

        $project = $task->project;
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $columns = PmColumn::whereHas('board', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })->orderBy('position')->get();
        $labels = PmLabel::where('project_id', $project->id)->get();
        $sprints = PmSprint::where('project_id', $project->id)->get();

        return view('projects.task', compact('task', 'project', 'users', 'columns', 'labels', 'sprints'));
    }

    public function updateTask(Request $request, $taskId)
    {
        $task = PmTask::findOrFail($taskId);

        $fillable = [
            'title', 'description', 'priority', 'status', 'assignee_id',
            'due_date', 'start_date', 'estimated_hours', 'labels',
            'sprint_id', 'story_points', 'column_id',
        ];

        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $oldVal = $task->$field;
                $newVal = $request->$field;

                if ($field === 'labels' && is_array($newVal)) {
                    $newVal = $newVal;
                }

                if ($oldVal != $newVal) {
                    PmTaskActivity::create([
                        'task_id'   => $task->id,
                        'user_id'   => auth()->id(),
                        'action'    => "updated_{$field}",
                        'old_value' => is_array($oldVal) ? json_encode($oldVal) : (string) $oldVal,
                        'new_value' => is_array($newVal) ? json_encode($newVal) : (string) $newVal,
                    ]);
                }
            }
        }

        $task->update($request->only($fillable));

        // If moved to done column, mark completed
        if ($request->has('column_id')) {
            $col = PmColumn::find($request->column_id);
            if ($col && $col->is_done_column && !$task->completed_at) {
                $task->update(['status' => 'done', 'completed_at' => now()]);
            }
        }

        $this->updateProjectProgress($task->project_id);

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'task' => $task->fresh()->load('assignee')]);
        }

        return back()->with('success', 'Task updated.');
    }

    public function deleteTask($taskId)
    {
        $task = PmTask::findOrFail($taskId);
        $projectId = $task->project_id;
        $task->delete();

        $this->updateProjectProgress($projectId);

        return back()->with('success', 'Task deleted.');
    }

    public function addComment(Request $request, $taskId)
    {
        $request->validate(['body' => 'required|string']);

        $task = PmTask::findOrFail($taskId);

        $comment = PmTaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'body'    => $request->body,
        ]);

        PmTaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => auth()->id(),
            'action'    => 'commented',
            'old_value' => null,
            'new_value' => Str::limit($request->body, 80),
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => true, 'comment' => $comment->load('user')]);
        }

        return back()->with('success', 'Comment added.');
    }

    public function addAttachment(Request $request, $taskId)
    {
        $request->validate(['file' => 'required|file|max:10240']);

        $task = PmTask::findOrFail($taskId);
        $file = $request->file('file');

        $path = $file->store('pm-attachments/' . $task->project_id, 'public');

        PmTaskAttachment::create([
            'task_id'      => $task->id,
            'uploaded_by'  => auth()->id(),
            'file_name'    => $file->getClientOriginalName(),
            'file_path'    => $path,
            'mime_type'    => $file->getMimeType(),
            'file_size_kb' => (int) ceil($file->getSize() / 1024),
        ]);

        PmTaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => auth()->id(),
            'action'    => 'attached_file',
            'old_value' => null,
            'new_value' => $file->getClientOriginalName(),
        ]);

        return back()->with('success', 'Attachment uploaded.');
    }

    public function logTime(Request $request, $taskId)
    {
        $request->validate([
            'hours'       => 'required|numeric|min:0.25|max:24',
            'note'        => 'nullable|string|max:500',
            'logged_date' => 'nullable|date',
        ]);

        $task = PmTask::findOrFail($taskId);

        PmTimeEntry::create([
            'task_id'     => $task->id,
            'user_id'     => auth()->id(),
            'hours'       => $request->hours,
            'note'        => $request->note,
            'logged_date' => $request->logged_date ?? now()->toDateString(),
        ]);

        // Update logged hours on task
        $totalLogged = PmTimeEntry::where('task_id', $task->id)->sum('hours');
        $task->update(['logged_hours' => $totalLogged]);

        PmTaskActivity::create([
            'task_id'   => $task->id,
            'user_id'   => auth()->id(),
            'action'    => 'logged_time',
            'old_value' => null,
            'new_value' => $request->hours . 'h',
        ]);

        if ($request->ajax()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Time logged.');
    }

    // ── Members ─────────────────────────────────────────────────────────────────

    public function members($projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);
        $members = PmProjectMember::where('project_id', $project->id)->with('user')->get();

        return response()->json(['members' => $members]);
    }

    public function addMember(Request $request, $projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role'    => 'nullable|in:member,admin',
        ]);

        $exists = PmProjectMember::where('project_id', $project->id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return back()->with('error', 'User is already a member.');
        }

        PmProjectMember::create([
            'project_id' => $project->id,
            'user_id'    => $request->user_id,
            'role'       => $request->role ?? 'member',
            'joined_at'  => now(),
        ]);

        return back()->with('success', 'Member added.');
    }

    public function removeMember($projectId, $userId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);

        PmProjectMember::where('project_id', $project->id)
            ->where('user_id', $userId)
            ->where('role', '!=', 'owner')
            ->delete();

        return back()->with('success', 'Member removed.');
    }

    // ── Sprints ─────────────────────────────────────────────────────────────────

    public function sprints($projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);
        $sprints = PmSprint::where('project_id', $project->id)->withCount('tasks')->orderByDesc('created_at')->get();

        return response()->json(['sprints' => $sprints]);
    }

    public function createSprint(Request $request, $projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);

        $request->validate([
            'name'       => 'required|string|max:255',
            'goal'       => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        PmSprint::create([
            'project_id' => $project->id,
            'name'       => $request->name,
            'goal'       => $request->goal,
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'status'     => 'planned',
        ]);

        return back()->with('success', 'Sprint created.');
    }

    public function startSprint($sprintId)
    {
        $sprint = PmSprint::findOrFail($sprintId);
        $sprint->update(['status' => 'active', 'start_date' => $sprint->start_date ?? now()]);

        return back()->with('success', 'Sprint started.');
    }

    public function completeSprint($sprintId)
    {
        $sprint = PmSprint::findOrFail($sprintId);
        $sprint->update(['status' => 'completed', 'end_date' => now()]);

        return back()->with('success', 'Sprint completed.');
    }

    // ── Labels ──────────────────────────────────────────────────────────────────

    public function labels($projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);
        $labels = PmLabel::where('project_id', $project->id)->get();

        return response()->json(['labels' => $labels]);
    }

    public function createLabel(Request $request, $projectId)
    {
        $project = PmProject::where('tenant_id', auth()->user()->tenant_id)->findOrFail($projectId);

        $request->validate([
            'name'  => 'required|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        PmLabel::create([
            'project_id' => $project->id,
            'name'       => $request->name,
            'color'      => $request->color ?? '#3B82F6',
        ]);

        return back()->with('success', 'Label created.');
    }

    // ── My Tasks ────────────────────────────────────────────────────────────────

    public function myTasks(Request $request)
    {
        $query = PmTask::with(['project', 'column', 'assignee'])
            ->where('assignee_id', auth()->id())
            ->whereHas('project', function ($q) {
                $q->where('tenant_id', auth()->user()->tenant_id)
                  ->where('status', '!=', 'archived');
            });

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tasks = $query->orderByDesc('created_at')->get();

        // Group by project
        $tasksByProject = $tasks->groupBy('project_id');

        return view('projects.my-tasks', compact('tasks', 'tasksByProject'));
    }

    // ── Helpers ──────────────────────────────────────────────────────────────────

    private function updateProjectProgress($projectId)
    {
        $total = PmTask::where('project_id', $projectId)->whereNull('parent_id')->count();
        $done  = PmTask::where('project_id', $projectId)->whereNull('parent_id')->whereNotNull('completed_at')->count();

        $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;

        PmProject::where('id', $projectId)->update(['progress' => $progress]);
    }
}
