<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference'        => $this->reference,
            'type'             => $this->type,
            'amount'           => (float) $this->amount,
            'currency'         => $this->currency ?? 'NGN',
            'description'      => $this->description,
            'status'           => $this->status,
            'balance_after'    => isset($this->balance_after) ? (float) $this->balance_after : null,
            'recipient_name'   => $this->recipient_name ?? null,
            'recipient_account'=> $this->recipient_account ?? null,
            'recipient_bank'   => $this->recipient_bank ?? null,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
