<?php

namespace App\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\User;

class SupportKbArticle extends Model
{
    use HasUuids;

    protected $table = 'support_kb_articles';

    protected $fillable = ['tenant_id','created_by','title','body','category','status','view_count','helpful_count'];

    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public function scopePublished($query) { return $query->where('status', 'published'); }
}
