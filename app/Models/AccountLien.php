<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AccountLien extends Model {
    use HasUuids, BelongsToTenant;
    protected $fillable = ['tenant_id','account_id','amount','reason','lien_type','reference','expires_at','is_active','placed_by','lifted_by','lifted_at'];
    protected $casts = ['amount'=>'decimal:2','expires_at'=>'date','lifted_at'=>'datetime','is_active'=>'boolean'];
    public function account() { return $this->belongsTo(Account::class); }
    public function placedBy() { return $this->belongsTo(User::class,'placed_by'); }
    public function liftedBy() { return $this->belongsTo(User::class,'lifted_by'); }
    public function scopeActive($q) { return $q->where('is_active',true); }
}
