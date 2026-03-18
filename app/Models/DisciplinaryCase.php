<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplinaryCase extends Model
{
    use HasUuids;

    protected $table = 'disciplinary_cases';

    protected $fillable = ['tenant_id', 'staff_profile_id', 'case_number', 'type', 'description', 'incident_date', 'raised_by', 'status'];

    protected $casts = [
        'incident_date' => 'date',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class); }
    public function raisedBy(): BelongsTo { return $this->belongsTo(User::class, 'raised_by'); }
    public function responses(): HasMany { return $this->hasMany(DisciplinaryResponse::class, 'case_id'); }
    public function scopeOpen($q) { return $q->whereIn('status', ['open', 'awaiting_response']); }
}
