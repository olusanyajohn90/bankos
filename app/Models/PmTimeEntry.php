<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmTimeEntry extends Model
{
    use HasFactory;

    protected $table = 'pm_time_entries';

    protected $fillable = ['task_id', 'user_id', 'hours', 'note', 'logged_date'];

    protected $casts = [
        'hours'       => 'decimal:2',
        'logged_date' => 'date',
    ];

    public function task()
    {
        return $this->belongsTo(PmTask::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
