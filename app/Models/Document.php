<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use App\Traits\BelongsToTenant;

class Document extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'documentable_type',
        'documentable_id',
        'document_type',
        'document_category',
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size_kb',
        'version',
        'is_current_version',
        'parent_id',
        'status',
        'expiry_date',
        'alert_days_before',
        'is_required',
        'reviewed_by',
        'review_notes',
        'reviewed_at',
        'uploaded_by',
    ];

    protected $casts = [
        'is_current_version' => 'boolean',
        'is_required'        => 'boolean',
        'expiry_date'        => 'date',
        'reviewed_at'        => 'datetime',
        'version'            => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Document::class, 'parent_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isExpiringSoon(): bool
    {
        return $this->expiry_date
            && $this->expiry_date->diffInDays(now()) <= $this->alert_days_before
            && !$this->isExpired();
    }

    public function scopeCurrent($q)
    {
        return $q->where('is_current_version', true);
    }

    public function scopeApproved($q)
    {
        return $q->where('status', 'approved');
    }
}
