<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PublicHoliday extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id','name','date','type','is_recurring','is_active','notes',
    ];

    protected $casts = [
        'date'         => 'date',
        'is_recurring' => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }
}
