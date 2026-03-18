<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalMatrixStep extends Model
{
    use HasUuids;

    protected $fillable = [
        'matrix_id', 'step_number', 'step_name', 'approver_type', 'approver_value',
        'is_mandatory', 'timeout_hours', 'on_timeout',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'step_number' => 'integer',
        'timeout_hours' => 'integer',
    ];

    public function matrix(): BelongsTo { return $this->belongsTo(ApprovalMatrix::class, 'matrix_id'); }
}
