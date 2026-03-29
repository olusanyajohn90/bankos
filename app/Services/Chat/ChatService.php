<?php

namespace App\Services\Chat;

use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ChatService
{
    public function findOrCreateDirect(User $userA, User $userB, string $tenantId): ChatConversation
    {
        // Find existing direct conversation between the two users
        $existing = ChatConversation::where('tenant_id', $tenantId)
            ->where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userA->id)->whereNull('left_at'))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userB->id)->whereNull('left_at'))
            ->first();

        if ($existing) return $existing;

        return DB::transaction(function () use ($userA, $userB, $tenantId) {
            $conv = ChatConversation::create([
                'tenant_id'  => $tenantId,
                'type'       => 'direct',
                'created_by' => $userA->id,
            ]);
            foreach ([$userA->id, $userB->id] as $uid) {
                ChatParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id'         => $uid,
                    'role'            => 'member',
                    'joined_at'       => now(),
                ]);
            }
            return $conv;
        });
    }

    public function createGroup(string $name, array $userIds, User $creator, string $tenantId): ChatConversation
    {
        return DB::transaction(function () use ($name, $userIds, $creator, $tenantId) {
            $conv = ChatConversation::create([
                'tenant_id'  => $tenantId,
                'type'       => 'group',
                'name'       => $name,
                'created_by' => $creator->id,
            ]);
            // Creator is admin
            ChatParticipant::create([
                'conversation_id' => $conv->id,
                'user_id'         => $creator->id,
                'role'            => 'admin',
                'joined_at'       => now(),
            ]);
            foreach (array_unique($userIds) as $uid) {
                if ($uid == $creator->id) continue;
                ChatParticipant::create([
                    'conversation_id' => $conv->id,
                    'user_id'         => $uid,
                    'role'            => 'member',
                    'joined_at'       => now(),
                ]);
            }
            return $conv;
        });
    }

    public function sendMessage(
        ChatConversation $conv,
        User $sender,
        ?string $body,
        ?UploadedFile $file,
        ?string $replyToId
    ): ChatMessage {
        return DB::transaction(function () use ($conv, $sender, $body, $file, $replyToId) {
            $type = $file
                ? (str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file')
                : 'text';

            $message = ChatMessage::create([
                'tenant_id'       => $conv->tenant_id,
                'conversation_id' => $conv->id,
                'sender_id'       => $sender->id,
                'body'            => $body,
                'type'            => $type,
                'reply_to_id'     => $replyToId,
            ]);

            if ($file) {
                $path = $file->store("chat/{$conv->tenant_id}/{$conv->id}", 'local');
                ChatAttachment::create([
                    'tenant_id'    => $conv->tenant_id,
                    'message_id'   => $message->id,
                    'file_name'    => $file->getClientOriginalName(),
                    'file_path'    => $path,
                    'mime_type'    => $file->getMimeType(),
                    'file_size_kb' => (int) ceil($file->getSize() / 1024),
                    'uploaded_by'  => $sender->id,
                ]);
            }

            $preview = $body
                ? substr($body, 0, 100)
                : ($file ? "📎 {$file->getClientOriginalName()}" : '');

            $conv->update([
                'last_message_at'      => now(),
                'last_message_preview' => $preview,
            ]);

            return $message;
        });
    }

    public function markRead(ChatConversation $conv, User $user): void
    {
        $this->markMessagesRead($conv, $user);
    }

    public function markMessagesRead(ChatConversation $conv, User $user): void
    {
        ChatParticipant::where('conversation_id', $conv->id)
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);

        // Create read receipts for unread messages
        $unreadMessages = ChatMessage::where('conversation_id', $conv->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_deleted', false)
            ->whereDoesntHave('readReceipts', fn($q) => $q->where('user_id', $user->id))
            ->pluck('id');

        foreach ($unreadMessages as $msgId) {
            \App\Models\ChatReadReceipt::firstOrCreate(
                ['message_id' => $msgId, 'user_id' => $user->id],
                ['read_at' => now()]
            );
        }

        // Update delivery_status to 'read' for those messages
        ChatMessage::where('conversation_id', $conv->id)
            ->where('sender_id', '!=', $user->id)
            ->where('delivery_status', '!=', 'read')
            ->update(['delivery_status' => 'read']);
    }

    public function updatePresence(User $user, ?string $typingInConversationId = null): void
    {
        \App\Models\ChatPresence::updateOrCreate(
            ['user_id' => $user->id],
            [
                'last_seen_at' => now(),
                'typing_in' => $typingInConversationId,
                'typing_at' => $typingInConversationId ? now() : null,
            ]
        );
    }

    public function unreadCount(User $user, string $tenantId): int
    {
        return ChatConversation::where('tenant_id', $tenantId)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id)->whereNull('left_at'))
            ->get()
            ->sum(fn ($conv) => $conv->unreadCountFor($user));
    }
}
