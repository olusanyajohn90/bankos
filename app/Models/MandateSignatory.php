<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MandateSignatory extends Model
{
    use HasUuids;

    protected $fillable = [
        'mandate_id',
        'user_id',
        'signatory_name',
        'signatory_class',
        'phone',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships

    public function mandate()
    {
        return $this->belongsTo(AccountMandate::class, 'mandate_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
