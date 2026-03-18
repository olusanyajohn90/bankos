<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SupportCategory extends Model
{
    use HasUuids;

    protected $table = 'support_categories';

    protected $fillable = ['tenant_id','team_id','name','icon','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function team() { return $this->belongsTo(SupportTeam::class, 'team_id'); }
    public function tickets() { return $this->hasMany(SupportTicket::class, 'category_id'); }
}
