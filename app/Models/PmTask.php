<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmTask extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pm_tasks';

    protected $fillable = [
        'project_id', 'column_id', 'parent_id', 'task_number', 'title',
        'description', 'priority', 'status', 'assignee_id', 'reporter_id',
        'due_date', 'start_date', 'estimated_hours', 'logged_hours',
        'position', 'labels', 'sprint_id', 'story_points', 'completed_at',
    ];

    protected $casts = [
        'due_date'        => 'date',
        'start_date'      => 'date',
        'completed_at'    => 'datetime',
        'estimated_hours' => 'decimal:2',
        'logged_hours'    => 'decimal:2',
        'position'        => 'integer',
        'story_points'    => 'integer',
        'labels'          => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(PmProject::class, 'project_id');
    }

    public function column()
    {
        return $this->belongsTo(PmColumn::class, 'column_id');
    }

    public function parent()
    {
        return $this->belongsTo(PmTask::class, 'parent_id');
    }

    public function subtasks()
    {
        return $this->hasMany(PmTask::class, 'parent_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function sprint()
    {
        return $this->belongsTo(PmSprint::class, 'sprint_id');
    }

    public function comments()
    {
        return $this->hasMany(PmTaskComment::class, 'task_id')->orderByDesc('created_at');
    }

    public function attachments()
    {
        return $this->hasMany(PmTaskAttachment::class, 'task_id');
    }

    public function activities()
    {
        return $this->hasMany(PmTaskActivity::class, 'task_id')->orderByDesc('created_at');
    }

    public function timeEntries()
    {
        return $this->hasMany(PmTimeEntry::class, 'task_id');
    }
}
