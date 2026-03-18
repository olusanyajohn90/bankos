<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentWorkflow extends Model
{
    use HasUuids;

    protected $table = 'document_workflows';

    protected $fillable = [
        'tenant_id', 'name', 'description', 'trigger_category',
        'is_active', 'requires_all_signatures',
    ];

    protected $casts = [
        'is_active'                  => 'boolean',
        'requires_all_signatures'    => 'boolean',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(DocumentWorkflowStep::class, 'workflow_id')->orderBy('step_order');
    }

    public function instances(): HasMany
    {
        return $this->hasMany(DocumentWorkflowInstance::class, 'workflow_id');
    }
}
