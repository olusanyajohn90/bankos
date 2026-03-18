<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'loan_number'             => $this->loan_number,
            'product_name'            => $this->product?->name,
            'principal_amount'        => (float) $this->principal_amount,
            'outstanding_balance'     => (float) $this->outstanding_balance,
            'status'                  => $this->status,
            'interest_rate'           => (float) $this->interest_rate,
            'tenure_months'           => (int) $this->tenure_days,
            'disbursed_at'            => $this->disbursed_at?->toIso8601String(),
            'expected_maturity_date'  => $this->getRawOriginal('expected_maturity_date')
                                            ? \Carbon\Carbon::parse($this->getRawOriginal('expected_maturity_date'))->toDateString()
                                            : $this->getExpectedMaturityDateAttribute()?->toDateString(),
        ];
    }
}
