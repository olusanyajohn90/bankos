<?php

namespace App\Services;

use App\Models\BureauReport;
use App\Models\Customer;
use App\Models\Loan;
use Illuminate\Support\Str;

class CreditBureauService
{
    public function query(Customer $customer, string $bureau = 'crc', ?Loan $loan = null): BureauReport
    {
        $report = BureauReport::create([
            'tenant_id'   => $customer->tenant_id,
            'customer_id' => $customer->id,
            'loan_id'     => $loan?->id,
            'bureau'      => $bureau,
            'reference'   => 'BUR-' . strtoupper(Str::random(10)),
            'status'      => 'pending',
        ]);

        try {
            $response = $this->callProvider($bureau, $customer);

            $report->update([
                'status'              => 'retrieved',
                'credit_score'        => $response['credit_score'] ?? null,
                'active_loans_count'  => $response['active_loans_count'] ?? 0,
                'total_outstanding'   => $response['total_outstanding'] ?? 0,
                'delinquency_count'   => $response['delinquency_count'] ?? 0,
                'raw_response'        => $response,
                'retrieved_at'        => now(),
            ]);
        } catch (\Exception $e) {
            $report->update(['status' => 'failed']);
        }

        return $report->fresh();
    }

    private function callProvider(string $bureau, Customer $customer): array
    {
        // Stub — replace with actual API integration per bureau
        return match($bureau) {
            'crc'         => $this->queryCrc($customer),
            'xds'         => $this->queryXds($customer),
            'firstcentral'=> $this->queryFirstCentral($customer),
            default       => throw new \InvalidArgumentException("Unknown bureau: $bureau"),
        };
    }

    private function queryCrc(Customer $customer): array
    {
        // TODO: integrate with CRC Credit Bureau API
        return [
            'credit_score'       => 650,
            'active_loans_count' => 1,
            'total_outstanding'  => 50000,
            'delinquency_count'  => 0,
        ];
    }

    private function queryXds(Customer $customer): array
    {
        // TODO: integrate with XDS Credit Bureau API
        return [
            'credit_score'       => 600,
            'active_loans_count' => 2,
            'total_outstanding'  => 120000,
            'delinquency_count'  => 1,
        ];
    }

    private function queryFirstCentral(Customer $customer): array
    {
        // TODO: integrate with FirstCentral Credit Bureau API
        return [
            'credit_score'       => 700,
            'active_loans_count' => 0,
            'total_outstanding'  => 0,
            'delinquency_count'  => 0,
        ];
    }
}
