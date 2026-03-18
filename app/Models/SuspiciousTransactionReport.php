<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SuspiciousTransactionReport extends Model
{
    use HasUuids;

    protected $table = 'suspicious_transaction_reports';

    protected $fillable = [
        'id', 'tenant_id', 'report_number', 'reporting_officer',
        'customer_id', 'transaction_ids', 'alert_ids', 'summary',
        'status', 'submitted_at', 'nfiu_reference',
    ];

    protected $casts = [
        'transaction_ids' => 'array',
        'alert_ids'       => 'array',
        'submitted_at'    => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reportingOfficer()
    {
        return $this->belongsTo(User::class, 'reporting_officer');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
