<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'method', 'endpoint', 'status_code',
        'response_time_ms', 'ip_address',
    ];

    public function client()
    {
        return $this->belongsTo(ApiClient::class, 'client_id');
    }
}
