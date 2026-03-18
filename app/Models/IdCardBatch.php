<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdCardBatch extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'name', 'total_count', 'generated_count', 'status', 'created_by', 'notes',
    ];

    protected $casts = [
        'total_count' => 'integer',
        'generated_count' => 'integer',
    ];

    public function cards(): HasMany     { return $this->hasMany(StaffIdCard::class, 'batch_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
