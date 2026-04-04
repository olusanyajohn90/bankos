<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BpmProcess extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'category', 'steps',
        'is_active', 'avg_completion_hours', 'total_instances', 'created_by',
    ];

    protected $casts = [
        'steps'     => 'array',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instances()
    {
        return $this->hasMany(BpmInstance::class, 'process_id');
    }
}
