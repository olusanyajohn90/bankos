<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmPipelineStage extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'name', 'color', 'position',
        'is_closed_won', 'is_closed_lost', 'requires_approval',
    ];

    protected $casts = [
        'is_closed_won'     => 'boolean',
        'is_closed_lost'    => 'boolean',
        'requires_approval' => 'boolean',
    ];

    public function leads(): HasMany { return $this->hasMany(CrmLead::class, 'stage_id'); }
}
