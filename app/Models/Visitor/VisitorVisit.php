<?php

namespace App\Models\Visitor;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisitorVisit extends Model
{
    use HasUuids;

    protected $table = 'visitor_visits';

    protected $fillable = [
        'tenant_id', 'visitor_id', 'host_user_id', 'purpose', 'badge_number',
        'vehicle_plate', 'items_brought', 'items_left', 'branch_id', 'status',
        'notes', 'denial_reason', 'expected_at', 'checked_in_at', 'checked_out_at',
        'checked_in_by', 'checked_out_by',
    ];

    protected $casts = [
        'expected_at'    => 'datetime',
        'checked_in_at'  => 'datetime',
        'checked_out_at' => 'datetime',
    ];

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class, 'visitor_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(VisitorActivity::class, 'visit_id');
    }

    public function duration(): ?string
    {
        if (! $this->checked_in_at) return null;
        $end = $this->checked_out_at ?? now();
        $mins = $this->checked_in_at->diffInMinutes($end);
        return $mins < 60 ? "{$mins}m" : round($mins / 60, 1) . 'h';
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'checked_in'  => 'bg-green-100 text-green-700',
            'checked_out' => 'bg-gray-100 text-gray-600',
            'expected'    => 'bg-blue-100 text-blue-700',
            'denied'      => 'bg-red-100 text-red-700',
            'no_show'     => 'bg-amber-100 text-amber-700',
            default       => 'bg-gray-100 text-gray-600',
        };
    }
}
