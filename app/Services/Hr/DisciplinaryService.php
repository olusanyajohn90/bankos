<?php
namespace App\Services\Hr;

use App\Models\DisciplinaryCase;
use App\Models\DisciplinaryResponse;
use App\Models\User;

class DisciplinaryService
{
    public function openCase(array $data): DisciplinaryCase
    {
        $year = now()->year;
        $tenantId = $data['tenant_id'];
        $sequence = DisciplinaryCase::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->count() + 1;
        $caseNumber = 'DISC-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return DisciplinaryCase::create([
            'tenant_id'        => $tenantId,
            'staff_profile_id' => $data['staff_profile_id'],
            'case_number'      => $caseNumber,
            'type'             => $data['type'],
            'description'      => $data['description'],
            'incident_date'    => $data['incident_date'],
            'raised_by'        => $data['raised_by'],
            'status'           => 'open',
        ]);
    }

    public function respond(DisciplinaryCase $case, array $data): DisciplinaryResponse
    {
        $response = DisciplinaryResponse::create([
            'case_id'        => $case->id,
            'staff_response' => $data['staff_response'],
            'responded_at'   => now(),
        ]);
        $case->update(['status' => 'responded']);
        return $response;
    }

    public function closeCase(DisciplinaryCase $case, array $data, User $decider): void
    {
        $response = $case->responses()->latest()->first();
        if ($response) {
            $response->update([
                'outcome'    => $data['outcome'],
                'decided_by' => $decider->id,
                'decided_at' => now(),
            ]);
        }
        $case->update(['status' => 'closed']);
    }

    public function appealCase(DisciplinaryCase $case): void
    {
        $case->update(['status' => 'appealed']);
    }
}
