<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcurementRequest extends Model
{
    use HasUuids;
    protected $fillable = [
        'tenant_id', 'category_id', 'item_name', 'justification', 'quantity',
        'unit_price', 'total_amount', 'vendor_name', 'vendor_quote_ref', 'urgency',
        'status', 'approval_request_id', 'asset_id', 'required_by_date', 'notes', 'requested_by',
    ];
    protected $casts = [
        'required_by_date' => 'date',
        'unit_price'       => 'decimal:2',
        'total_amount'     => 'decimal:2',
    ];
    public function category(): BelongsTo        { return $this->belongsTo(AssetCategory::class, 'category_id'); }
    public function requestedBy(): BelongsTo     { return $this->belongsTo(User::class, 'requested_by'); }
    public function approvalRequest(): BelongsTo { return $this->belongsTo(ApprovalRequest::class, 'approval_request_id'); }
    public function asset(): BelongsTo           { return $this->belongsTo(Asset::class); }
}
