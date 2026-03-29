<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatUserGroupMember extends Model
{
    public $timestamps = false;

    protected $table = 'chat_user_group_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(ChatUserGroup::class, 'group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
