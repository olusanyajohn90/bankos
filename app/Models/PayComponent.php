<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class PayComponent extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'type', 'is_statutory', 'is_taxable', 'computation_type', 'value', 'formula_key', 'is_active'];

    protected $casts = [
        'is_statutory' => 'boolean',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
        'value' => 'float',
    ];

    public function scopeActive($q) { return $q->where('is_active', true); }
    public function scopeStatutory($q) { return $q->where('is_statutory', true); }
    public function scopeEarnings($q) { return $q->where('type', 'earning'); }
    public function scopeDeductions($q) { return $q->where('type', 'deduction'); }
}
