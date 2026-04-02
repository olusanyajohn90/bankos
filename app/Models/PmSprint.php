<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmSprint extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pm_sprints';

    protected $fillable = [
        'project_id', 'name', 'goal', 'start_date', 'end_date', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(PmProject::class, 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(PmTask::class, 'sprint_id');
    }
}
