<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'customer_number' => $this->customer_number,
            'first_name'      => $this->first_name,
            'last_name'       => $this->last_name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'kyc_tier'        => $this->kyc_tier,
            'kyc_status'      => $this->kyc_status,
            'portal_active'   => (bool) $this->portal_active,
            'created_at'      => $this->created_at?->toIso8601String(),
        ];
    }
}
