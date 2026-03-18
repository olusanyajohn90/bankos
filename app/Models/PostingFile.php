<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostingFile extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'uploaded_by', 'file_name', 'file_path', 'reference',
        'type', 'status', 'total_records', 'valid_records', 'invalid_records',
        'posted_records', 'total_amount', 'validation_errors',
    ];

    protected $casts = [
        'total_amount'      => 'decimal:2',
        'validation_errors' => 'array',
    ];

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function records()
    {
        return $this->hasMany(PostingFileRecord::class);
    }
}
