<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommsAttachment extends Model
{
    use HasUuids;

    protected $table = 'comms_attachments';

    protected $fillable = [
        'tenant_id',
        'message_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size_kb',
        'uploaded_by',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(CommsMessage::class, 'message_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
