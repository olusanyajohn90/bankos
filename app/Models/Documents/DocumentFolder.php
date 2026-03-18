<?php

namespace App\Models\Documents;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentFolder extends Model
{
    use HasUuids;

    protected $table = 'document_folders';

    protected $fillable = ['tenant_id', 'name', 'parent_id', 'icon', 'description', 'is_system', 'sort_order'];

    protected $casts = ['is_system' => 'boolean'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(DocumentFolder::class, 'parent_id')->orderBy('sort_order');
    }
}
