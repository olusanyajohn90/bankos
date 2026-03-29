<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BelongsToTenant;

class ChatCanvas extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'chat_canvas';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'title',
        'content',
        'created_by',
        'last_edited_by',
        'is_shared',
    ];

    protected $casts = [
        'content'   => 'json',
        'is_shared' => 'boolean',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }
}
