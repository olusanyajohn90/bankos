<?php

namespace App\Models\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    use HasUuids;

    protected $table = 'document_signatures';

    protected $fillable = [
        'document_id', 'signer_id', 'signature_data', 'signature_type',
        'ip_address', 'user_agent', 'is_valid', 'revoked_at', 'signed_at',
    ];

    protected $casts = [
        'is_valid'   => 'boolean',
        'signed_at'  => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }
}
