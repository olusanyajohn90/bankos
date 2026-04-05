<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class AdverseMediaResult extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'customer_id', 'source', 'headline',
        'summary', 'url', 'published_date', 'category',
        'severity', 'disposition',
    ];

    protected $casts = [
        'published_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
