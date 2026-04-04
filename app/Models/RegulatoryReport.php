<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegulatoryReport extends Model
{
    use HasFactory, HasUuids, BelongsToTenant;

    protected $table = 'regulatory_reports';

    protected $fillable = [
        'tenant_id', 'report_type', 'report_name', 'period', 'due_date',
        'submitted_date', 'status', 'report_data', 'file_path', 'notes',
        'prepared_by', 'approved_by',
    ];

    protected $casts = [
        'due_date'       => 'date',
        'submitted_date' => 'date',
        'report_data'    => 'array',
    ];

    public function preparer()
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
