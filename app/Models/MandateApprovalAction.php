<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MandateApprovalAction extends Model
{
    use HasUuids;

    protected $fillable = [
        'approval_id',
        'signatory_id',
        'action',
        'notes',
        'actioned_by',
        'actioned_at',
    ];

    protected $casts = [
        'actioned_at' => 'datetime',
    ];

    // Relationships

    public function approval()
    {
        return $this->belongsTo(MandateApproval::class, 'approval_id');
    }

    public function signatory()
    {
        return $this->belongsTo(MandateSignatory::class, 'signatory_id');
    }

    public function actionedBy()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }
}
