<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    use HasUuids;
    protected $fillable = [
        'tenant_id', 'asset_id', 'staff_profile_id', 'assigned_date',
        'returned_date', 'condition_at_assignment', 'condition_at_return',
        'notes', 'assigned_by', 'received_by',
    ];
    protected $casts = ['assigned_date' => 'date', 'returned_date' => 'date'];
    public function asset(): BelongsTo        { return $this->belongsTo(Asset::class); }
    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function assignedBy(): BelongsTo   { return $this->belongsTo(User::class, 'assigned_by'); }
    public function isActive(): bool          { return is_null($this->returned_date); }
}
