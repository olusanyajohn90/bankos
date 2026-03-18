<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class Division extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = ['tenant_id', 'name', 'code', 'description'];

    public function departments(): HasMany { return $this->hasMany(Department::class); }
}
