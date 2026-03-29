<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\BelongsToTenant;

class ChatCall extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'chat_calls';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'initiated_by',
        'livekit_room_name',
        'type',
        'status',
        'started_at',
        'ended_at',
        'duration_seconds',
    ];

    protected $casts = [
        'started_at'       => 'datetime',
        'ended_at'         => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChatCallParticipant::class, 'call_id');
    }

    public function participantUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_call_participants', 'call_id', 'user_id')
            ->withPivot('joined_at', 'left_at', 'is_muted', 'is_video_on', 'is_screen_sharing');
    }
}
