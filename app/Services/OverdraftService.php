<?php
namespace App\Services;
use App\Models\{OverdraftFacility, Account};
use Illuminate\Support\Facades\DB;

class OverdraftService {
    public function create(array $data, string $tenantId): OverdraftFacility {
        return OverdraftFacility::create(array_merge($data, [
            'tenant_id' => $tenantId,
            'approved_by' => auth()->id(),
        ]));
    }

    public function accrueInterest(OverdraftFacility $od): void {
        if ($od->status !== 'active' || $od->used_amount <= 0) return;
        $dailyRate = $od->interest_rate / 100 / 365;
        $daily = round((float)$od->used_amount * $dailyRate, 2);
        $od->increment('accrued_interest', $daily);
    }

    public function checkExpiredFacilities(string $tenantId): int {
        return OverdraftFacility::where('tenant_id', $tenantId)
            ->active()->where('expiry_date','<',now()->toDateString())
            ->update(['status'=>'expired']);
    }
}
