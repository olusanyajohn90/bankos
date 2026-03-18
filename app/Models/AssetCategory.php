<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetCategory extends Model
{
    use HasUuids;
    protected $fillable = ['tenant_id', 'name', 'code', 'description', 'depreciation_years', 'depreciation_method', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];
    public function assets(): HasMany { return $this->hasMany(Asset::class, 'category_id'); }
}
