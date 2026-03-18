<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AmlAlertCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $alert;
    public int   $tenantId;

    public function __construct(int $tenantId, array $alert)
    {
        $this->tenantId = $tenantId;
        $this->alert    = $alert;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("compliance.{$this->tenantId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'aml.alert.created';
    }

    public function broadcastWith(): array
    {
        return $this->alert;
    }
}
