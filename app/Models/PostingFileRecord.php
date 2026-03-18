<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostingFileRecord extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'posting_file_id', 'row_number',
        'identifier_type', 'identifier_value', 'amount', 'transaction_date',
        'payment_channel', 'narration', 'status', 'error_message', 'transaction_id',
    ];

    protected $casts = [
        'amount'           => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function postingFile()
    {
        return $this->belongsTo(PostingFile::class);
    }
}
