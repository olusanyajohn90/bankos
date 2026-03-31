<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingSurveyResponse extends Model
{
    use HasUuids;

    protected $fillable = [
        'survey_id', 'customer_id', 'answers', 'nps_score', 'feedback',
    ];

    protected $casts = [
        'answers' => 'array',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(MarketingSurvey::class, 'survey_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
