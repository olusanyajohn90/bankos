<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmLabel extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pm_labels';
    public $timestamps = false;

    protected $fillable = ['project_id', 'name', 'color'];

    public function project()
    {
        return $this->belongsTo(PmProject::class, 'project_id');
    }
}
