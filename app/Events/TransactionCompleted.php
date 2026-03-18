<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $payload;
    public int   $tenantId;
    public int   $customerId;

    public function __construct(int $tenantId, int $customerId, array $payload)
    {
        $this->tenantId   = $tenantId;
        $this->customerId = $customerId;
        $this->payload    = $payload;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("customer.{$this->tenantId}.{$this->customerId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'transaction.completed';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
