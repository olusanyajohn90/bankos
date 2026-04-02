<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PmProjectMember extends Model
{
    use HasFactory;

    protected $table = 'pm_project_members';

    protected $fillable = ['project_id', 'user_id', 'role', 'joined_at'];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public $timestamps = false;

    public function project()
    {
        return $this->belongsTo(PmProject::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
