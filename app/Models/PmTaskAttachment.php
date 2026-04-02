<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmTaskAttachment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pm_task_attachments';

    protected $fillable = [
        'task_id', 'uploaded_by', 'file_name', 'file_path', 'mime_type', 'file_size_kb',
    ];

    protected $casts = [
        'file_size_kb' => 'integer',
    ];

    public function task()
    {
        return $this->belongsTo(PmTask::class, 'task_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
