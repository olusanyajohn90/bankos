<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffIdCard extends Model
{
    use HasUuids;

    protected $fillable = [
        'tenant_id', 'staff_profile_id', 'template_id', 'batch_id', 'card_number',
        'issued_date', 'expiry_date', 'status', 'photo_path', 'qr_payload',
        'pdf_path', 'replaced_by', 'loss_report_date', 'notes', 'issued_by',
    ];

    protected $casts = [
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'loss_report_date' => 'date',
    ];

    public function staffProfile(): BelongsTo { return $this->belongsTo(StaffProfile::class, 'staff_profile_id'); }
    public function template(): BelongsTo     { return $this->belongsTo(CardTemplate::class, 'template_id'); }
    public function batch(): BelongsTo        { return $this->belongsTo(IdCardBatch::class, 'batch_id'); }
    public function issuedBy(): BelongsTo     { return $this->belongsTo(User::class, 'issued_by'); }
    public function replacedByCard(): BelongsTo { return $this->belongsTo(StaffIdCard::class, 'replaced_by'); }

    public function isExpired(): bool  { return $this->expiry_date && $this->expiry_date->isPast(); }
    public function isActive(): bool   { return $this->status === 'active' && !$this->isExpired(); }
}
