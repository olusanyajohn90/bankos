<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmProject extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'pm_projects';

    protected $fillable = [
        'tenant_id', 'name', 'code', 'description', 'color', 'owner_id',
        'status', 'visibility', 'start_date', 'end_date', 'progress',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'progress'   => 'integer',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->hasMany(PmProjectMember::class, 'project_id');
    }

    public function memberUsers()
    {
        return $this->belongsToMany(User::class, 'pm_project_members', 'project_id', 'user_id')
            ->withPivot('role', 'joined_at');
    }

    public function boards()
    {
        return $this->hasMany(PmBoard::class, 'project_id');
    }

    public function defaultBoard()
    {
        return $this->hasOne(PmBoard::class, 'project_id')->where('is_default', true);
    }

    public function tasks()
    {
        return $this->hasMany(PmTask::class, 'project_id');
    }

    public function sprints()
    {
        return $this->hasMany(PmSprint::class, 'project_id');
    }

    public function labels()
    {
        return $this->hasMany(PmLabel::class, 'project_id');
    }
}
