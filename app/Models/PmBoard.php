<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmBoard extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pm_boards';

    protected $fillable = ['project_id', 'name', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(PmProject::class, 'project_id');
    }

    public function columns()
    {
        return $this->hasMany(PmColumn::class, 'board_id')->orderBy('position');
    }
}
