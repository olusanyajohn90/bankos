<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryResponse extends Model
{
    use HasUuids;

    protected $fillable = ['case_id', 'staff_response', 'responded_at', 'outcome', 'decided_by', 'decided_at'];

    protected $casts = [
        'responded_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public function disciplinaryCase(): BelongsTo { return $this->belongsTo(DisciplinaryCase::class, 'case_id'); }
    public function decidedBy(): BelongsTo { return $this->belongsTo(User::class, 'decided_by'); }
}
