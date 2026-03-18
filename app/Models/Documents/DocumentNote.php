<?php

namespace App\Models\Documents;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentNote extends Model
{
    use HasUuids;

    protected $table = 'document_notes';

    protected $fillable = ['document_id', 'author_id', 'body', 'is_internal', 'parent_id'];

    protected $casts = ['is_internal' => 'boolean'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentNote::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DocumentNote::class, 'parent_id');
    }
}
