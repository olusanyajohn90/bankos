<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCampaign extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'description', 'type', 'channel',
        'template_id', 'segment_id', 'custom_message', 'custom_subject',
        'status', 'scheduled_at', 'sent_at', 'completed_at',
        'total_recipients', 'sent_count', 'delivered_count', 'opened_count',
        'clicked_count', 'converted_count', 'failed_count', 'unsubscribed_count',
        'cost', 'created_by',
    ];

    protected $casts = [
        'scheduled_at'  => 'datetime',
        'sent_at'       => 'datetime',
        'completed_at'  => 'datetime',
        'cost'          => 'decimal:2',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(MarketingTemplate::class, 'template_id');
    }

    public function segment(): BelongsTo
    {
        return $this->belongsTo(MarketingSegment::class, 'segment_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MarketingCampaignRecipient::class, 'campaign_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
