<?php

namespace App\Models\Visitor;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorWatchlist extends Model
{
    use HasUuids;

    protected $table = 'visitor_watchlist';

    protected $fillable = ['tenant_id', 'visitor_id', 'status', 'reason', 'added_by', 'expires_at'];

    protected $casts = ['expires_at' => 'datetime'];

    public function visitor(): BelongsTo { return $this->belongsTo(Visitor::class, 'visitor_id'); }
    public function addedBy(): BelongsTo { return $this->belongsTo(User::class, 'added_by'); }
}
