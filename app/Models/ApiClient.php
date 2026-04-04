<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'client_id', 'client_secret',
        'webhook_url', 'allowed_scopes', 'ip_whitelist', 'is_active',
        'rate_limit_per_minute', 'total_requests', 'last_request_at', 'created_by',
    ];

    protected $casts = [
        'allowed_scopes'  => 'array',
        'ip_whitelist'    => 'array',
        'is_active'       => 'boolean',
        'last_request_at' => 'datetime',
    ];

    protected $hidden = ['client_secret'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requestLogs()
    {
        return $this->hasMany(ApiRequestLog::class, 'client_id');
    }
}
