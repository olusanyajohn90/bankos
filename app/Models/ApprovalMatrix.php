<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApprovalMatrix extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'name', 'action_type', 'description',
        'min_amount', 'max_amount', 'condition_field', 'condition_operator', 'condition_value',
        'total_steps', 'is_active', 'requires_checker', 'escalation_hours', 'created_by',
    ];

    protected $casts = [
        'min_amount' => 'float',
        'max_amount' => 'float',
        'is_active' => 'boolean',
        'requires_checker' => 'boolean',
        'total_steps' => 'integer',
        'escalation_hours' => 'integer',
    ];

    public function steps(): HasMany    { return $this->hasMany(ApprovalMatrixStep::class, 'matrix_id')->orderBy('step_number'); }
    public function requests(): HasMany { return $this->hasMany(ApprovalRequest::class, 'matrix_id'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public static function findForAction(string $tenantId, string $actionType, float $amount = 0): ?self
    {
        return self::where('tenant_id', $tenantId)
            ->where('action_type', $actionType)
            ->where('is_active', true)
            ->where(function ($q) use ($amount) {
                $q->whereNull('min_amount')->orWhere('min_amount', '<=', $amount);
            })
            ->where(function ($q) use ($amount) {
                $q->whereNull('max_amount')->orWhere('max_amount', '>=', $amount);
            })
            ->orderByDesc('min_amount')
            ->first();
    }
}
