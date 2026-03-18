<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlAccount extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'account_number', 'name', 'category',
        'level', 'parent_id', 'balance', 'branch_id',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(GlAccount::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(GlAccount::class, 'parent_id');
    }

    public function glPostings()
    {
        return $this->hasMany(GlPosting::class);
    }
}
