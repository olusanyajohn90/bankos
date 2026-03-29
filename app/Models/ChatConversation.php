<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\BelongsToTenant;

class ChatConversation extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'chat_conversations';

    protected $fillable = [
        'tenant_id',
        'type',
        'name',
        'description',
        'created_by',
        'last_message_at',
        'last_message_preview',
        'is_archived',
        'invite_code',
        'disappear_minutes',
    ];

    protected $casts = [
        'last_message_at'  => 'datetime',
        'is_archived'      => 'boolean',
        'disappear_minutes' => 'integer',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChatParticipant::class, 'conversation_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_participants', 'conversation_id', 'user_id')
            ->withPivot('role', 'joined_at', 'last_read_at', 'left_at');
    }

    public function getDisplayName(User $forUser): string
    {
        if ($this->type === 'group') return $this->name ?? 'Group';
        $other = $this->users->firstWhere('id', '!=', $forUser->id);
        return $other?->name ?? 'Unknown';
    }

    public function pinnedMessages(): HasMany
    {
        return $this->hasMany(ChatPinnedMessage::class, 'conversation_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ChatTask::class, 'conversation_id');
    }

    public function polls(): HasMany
    {
        return $this->hasMany(ChatPoll::class, 'conversation_id');
    }

    public function unreadCountFor(User $user): int
    {
        $participant = $this->participants->firstWhere('user_id', $user->id);
        if (!$participant) return 0;
        return $this->messages()
            ->where('is_deleted', false)
            ->where('sender_id', '!=', $user->id)
            ->where('created_at', '>', $participant->last_read_at ?? '2000-01-01')
            ->count();
    }
}
