<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class OverdraftFacility extends Model {
    use HasUuids, BelongsToTenant;
    protected $table = 'overdraft_facilities';
    protected $fillable = ['tenant_id','account_id','limit_amount','used_amount','interest_rate','accrued_interest','approved_date','expiry_date','status','approved_by','notes'];
    protected $casts = ['limit_amount'=>'decimal:2','used_amount'=>'decimal:2','interest_rate'=>'decimal:3','accrued_interest'=>'decimal:2','approved_date'=>'date','expiry_date'=>'date'];
    public function account() { return $this->belongsTo(Account::class); }
    public function approvedBy() { return $this->belongsTo(User::class,'approved_by'); }
    public function getAvailableAttribute(): float { return max(0, (float)$this->limit_amount - (float)$this->used_amount); }
    public function getIsExpiredAttribute(): bool { return $this->expiry_date && $this->expiry_date->isPast(); }
    public function scopeActive($q) { return $q->where('status','active'); }
}
