<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class EntityRelationship extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'entity_a_id', 'entity_a_type',
        'entity_b_id', 'entity_b_type', 'relationship_type',
        'strength', 'transaction_count', 'total_volume',
        'is_suspicious', 'notes',
    ];

    protected $casts = [
        'strength'     => 'decimal:2',
        'total_volume' => 'decimal:2',
        'is_suspicious' => 'boolean',
    ];
}
