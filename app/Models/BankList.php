<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankList extends Model
{
    use HasFactory;

    protected $table = 'bank_list';

    protected $fillable = [
        'cbn_code',
        'bank_name',
        'nibss_code',
        'is_active',
        'is_microfinance',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'is_microfinance' => 'boolean',
    ];

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Static helpers ─────────────────────────────────────────────────────────

    public static function findByCode(string $code): ?self
    {
        return static::where('cbn_code', $code)->first();
    }
}
