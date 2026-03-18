<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'title'      => $this->event ?? $this->title ?? null,
            'body'       => $this->message ?? $this->body ?? null,
            'type'       => $this->channel ?? $this->type ?? null,
            'read_at'    => isset($this->read_at) ? ($this->read_at instanceof \DateTimeInterface ? $this->read_at->toIso8601String() : $this->read_at) : null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
