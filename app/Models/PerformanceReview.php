<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerformanceReview extends Model
{
    use HasUuids;

    protected $fillable = ['tenant_id', 'review_cycle_id', 'staff_profile_id', 'reviewer_id', 'status', 'overall_score', 'rating', 'staff_comments', 'manager_comments', 'submitted_at', 'reviewed_at'];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'overall_score' => 'float',
    ];

    public function reviewCycle(): BelongsTo { return $this->belongsTo(ReviewCycle::class, 'review_cycle_id'); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function items(): HasMany { return $this->hasMany(PerformanceReviewItem::class, 'review_id'); }
}
