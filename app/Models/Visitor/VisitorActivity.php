<?php

namespace App\Models\Visitor;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorActivity extends Model
{
    use HasUuids;

    protected $table = 'visitor_activities';

    protected $fillable = ['visit_id', 'logged_by', 'activity_type', 'description', 'area_accessed', 'occurred_at'];

    protected $casts = ['occurred_at' => 'datetime'];

    public function visit(): BelongsTo { return $this->belongsTo(VisitorVisit::class, 'visit_id'); }
    public function loggedBy(): BelongsTo { return $this->belongsTo(User::class, 'logged_by'); }
}
