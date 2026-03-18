<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class ReviewCycle extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'period_type', 'start_date', 'end_date', 'status'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function reviews(): HasMany { return $this->hasMany(PerformanceReview::class, 'review_cycle_id'); }
    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeDraft($q) { return $q->where('status', 'draft'); }
}
