<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmColumn extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pm_columns';

    protected $fillable = ['board_id', 'name', 'color', 'position', 'wip_limit', 'is_done_column'];

    protected $casts = [
        'position'       => 'integer',
        'wip_limit'      => 'integer',
        'is_done_column' => 'boolean',
    ];

    public function board()
    {
        return $this->belongsTo(PmBoard::class, 'board_id');
    }

    public function tasks()
    {
        return $this->hasMany(PmTask::class, 'column_id')->orderBy('position');
    }
}
