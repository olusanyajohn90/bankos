<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AmlRule extends Model
{
    use HasUuids;

    protected $table = 'aml_rules';

    protected $fillable = [
        'id', 'tenant_id', 'rule_code', 'rule_name', 'rule_type',
        'is_active', 'threshold_amount', 'threshold_count',
        'time_window_hours', 'severity', 'auto_block',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'auto_block'       => 'boolean',
        'threshold_amount' => 'float',
        'threshold_count'  => 'integer',
        'time_window_hours'=> 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
