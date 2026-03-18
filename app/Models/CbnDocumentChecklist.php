<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class CbnDocumentChecklist extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'cbn_document_checklists';

    protected $fillable = [
        'tenant_id',
        'entity_type',
        'document_type',
        'document_label',
        'is_required',
        'applies_to',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'   => 'boolean',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeForEntity($q, string $entityType)
    {
        return $q->where('entity_type', $entityType);
    }
}
