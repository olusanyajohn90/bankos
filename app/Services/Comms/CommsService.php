<?php

namespace App\Services\Comms;

use App\Models\Branch;
use App\Models\CommsMessage;
use App\Models\CommsRecipient;
use App\Models\Department;
use App\Models\StaffProfile;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommsService
{
    public function publish(CommsMessage $message): void
    {
        DB::transaction(function () use ($message) {
            $userIds = $this->resolveRecipients(
                $message->scope_type,
                $message->scope_id,
                $message->tenant_id
            );

            $rows = array_map(fn ($uid) => [
                'tenant_id'  => $message->tenant_id,
                'message_id' => $message->id,
                'user_id'    => $uid,
                'created_at' => now(),
                'updated_at' => now(),
            ], $userIds);

            // Chunk to avoid hitting MySQL max_allowed_packet
            foreach (array_chunk($rows, 500) as $chunk) {
                CommsRecipient::upsert($chunk, ['message_id', 'user_id'], []);
            }

            $message->update(['status' => 'published', 'published_at' => now()]);
        });
    }

    public function markRead(CommsMessage $message, User $user): void
    {
        CommsRecipient::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function acknowledge(CommsMessage $message, User $user, ?string $note): void
    {
        CommsRecipient::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->update(['ack_at' => now(), 'ack_note' => $note]);
    }

    public function resolveRecipients(string $scopeType, ?string $scopeId, string $tenantId): array
    {
        return match ($scopeType) {
            'all_staff' => User::where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray(),

            'branch' => User::where('tenant_id', $tenantId)
                ->where('branch_id', $scopeId)
                ->pluck('id')
                ->toArray(),

            'department' => StaffProfile::where('tenant_id', $tenantId)
                ->where('department_id', $scopeId)
                ->pluck('user_id')
                ->toArray(),

            'team' => StaffProfile::where('tenant_id', $tenantId)
                ->where('team_id', $scopeId)
                ->pluck('user_id')
                ->toArray(),

            'role' => User::where('tenant_id', $tenantId)
                ->whereHas('roles', fn ($q) => $q->where('name', $scopeId))
                ->pluck('id')
                ->toArray(),

            'individual' => [(int) $scopeId],

            default => [],
        };
    }
}
