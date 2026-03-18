<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asset extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'category_id', 'name', 'asset_tag', 'serial_number',
        'model', 'manufacturer', 'vendor', 'purchase_date', 'purchase_price',
        'current_value', 'warranty_expiry', 'condition', 'status',
        'location', 'branch_id', 'notes', 'invoice_number', 'photo_path', 'added_by',
    ];

    protected $casts = [
        'purchase_date'   => 'date',
        'warranty_expiry' => 'date',
        'purchase_price'  => 'decimal:2',
        'current_value'   => 'decimal:2',
    ];

    public function category(): BelongsTo      { return $this->belongsTo(AssetCategory::class, 'category_id'); }
    public function branch(): BelongsTo        { return $this->belongsTo(Branch::class); }
    public function addedBy(): BelongsTo       { return $this->belongsTo(User::class, 'added_by'); }
    public function assignments(): HasMany     { return $this->hasMany(AssetAssignment::class); }
    public function maintenanceLogs(): HasMany { return $this->hasMany(AssetMaintenanceLog::class); }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class)->whereNull('returned_date');
    }

    public function isWarrantyValid(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isFuture();
    }

    public function depreciatedValue(int $years = 0): float
    {
        $age = $years ?: (int) $this->purchase_date?->diffInYears(now());
        $category = $this->category;
        if (!$this->purchase_price || !$category || $category->depreciation_years === 0) return (float) $this->purchase_price;
        $annualDep = $this->purchase_price / $category->depreciation_years;
        return max(0, (float) $this->purchase_price - ($annualDep * $age));
    }
}
