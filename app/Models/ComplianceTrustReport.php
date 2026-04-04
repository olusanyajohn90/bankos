<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ComplianceTrustReport extends Model
{
    use HasUuids;

    protected $table = 'compliance_trust_reports';

    protected $fillable = [
        'id', 'tenant_id', 'public_url_token', 'is_published',
        'visible_frameworks', 'custom_sections', 'logo_path', 'intro_text',
    ];

    protected $casts = [
        'is_published'       => 'boolean',
        'visible_frameworks' => 'array',
        'custom_sections'    => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
