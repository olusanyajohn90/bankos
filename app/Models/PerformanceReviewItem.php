<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReviewItem extends Model
{
    use HasUuids;

    protected $fillable = ['review_id', 'criterion', 'weight', 'self_score', 'manager_score', 'max_score', 'target_description', 'achievement_notes'];

    protected $casts = [
        'weight' => 'float',
        'self_score' => 'float',
        'manager_score' => 'float',
        'max_score' => 'float',
    ];

    public function review(): BelongsTo { return $this->belongsTo(PerformanceReview::class, 'review_id'); }
}
