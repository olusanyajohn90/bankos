<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenanceLog extends Model
{
    use HasUuids;
    protected $fillable = [
        'tenant_id', 'asset_id', 'maintenance_type', 'status',
        'scheduled_date', 'completed_date', 'cost', 'vendor',
        'description', 'findings', 'performed_by', 'logged_by',
    ];
    protected $casts = ['scheduled_date' => 'date', 'completed_date' => 'date', 'cost' => 'decimal:2'];
    public function asset(): BelongsTo       { return $this->belongsTo(Asset::class); }
    public function performedBy(): BelongsTo { return $this->belongsTo(User::class, 'performed_by'); }
    public function loggedBy(): BelongsTo    { return $this->belongsTo(User::class, 'logged_by'); }
}
