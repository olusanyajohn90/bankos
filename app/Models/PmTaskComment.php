<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmTaskComment extends Model
{
    use HasFactory;

    protected $table = 'pm_task_comments';

    protected $fillable = ['task_id', 'user_id', 'body'];

    public function task()
    {
        return $this->belongsTo(PmTask::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
