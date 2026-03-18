<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItemLine extends Model
{
    use HasUuids;

    protected $fillable = ['payroll_item_id', 'pay_component_id', 'component_name', 'component_type', 'is_statutory', 'amount'];

    protected $casts = [
        'is_statutory' => 'boolean',
        'amount' => 'float',
    ];

    public function payrollItem(): BelongsTo { return $this->belongsTo(PayrollItem::class); }
    public function payComponent(): BelongsTo { return $this->belongsTo(PayComponent::class)->withDefault(); }
}
