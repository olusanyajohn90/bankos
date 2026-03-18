<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'account_number'    => $this->account_number,
            'account_name'      => $this->account_name,
            'type'              => $this->type,
            'currency'          => $this->currency ?? 'NGN',
            'available_balance' => (float) $this->available_balance,
            'ledger_balance'    => (float) $this->ledger_balance,
            'status'            => $this->status,
            'created_at'        => $this->created_at?->toIso8601String(),
        ];
    }
}
