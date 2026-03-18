<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(protected ChatService $chat) {}

    // ─── Page ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user          = auth()->user();
        $preselectedId = $request->query('conversation_id');
        $tenantUsers   = \App\Models\User::where('tenant_id', $user->tenant_id)
            ->where('id', '!=', $user->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('chat.index', compact('user', 'preselectedId', 'tenantUsers'));
    }

    // ─── Conversations ───────────────────────────────────────────────────────

    public function conversations(Request $request): JsonResponse
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $conversations = ChatConversation::where('tenant_id', $tenantId)
            ->whereHas('participants', function ($q) use ($user) {
                $q->where('user_id', $user->id)->whereNull('left_at');
            })
            ->with([
                'participants.user',
                'messages' => fn($q) => $q->latest()->limit(1),
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->get();

        $data = $conversations->map(function (ChatConversation $conv) use ($user) {
            $displayName = $conv->getDisplayName($user);
            $unread      = $conv->unreadCountFor($user);

            return [
                'id'                   => $conv->id,
                'type'                 => $conv->type,
                'display_name'         => $displayName,
                'last_message_preview' => $conv->last_message_preview,
                'last_message_at'      => $conv->last_message_at
                    ? $conv->last_message_at->diffForHumans()
                    : null,
                'unread_count'         => $unread,
                'avatar_initials'      => mb_strtoupper(
                    collect(explode(' ', $displayName))
                        ->map(fn($w) => mb_substr($w, 0, 1))
                        ->take(2)
                        ->implode('')
                ),
                'is_group'             => $conv->type === 'group',
                'is_archived'          => (bool) $conv->is_archived,
                'participant_count'    => $conv->participants->whereNull('left_at')->count(),
            ];
        });

        return response()->json(['conversations' => $data]);
    }

    public function storeConversation(Request $request): JsonResponse
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;
        $type     = $request->input('type');

        if ($type === 'direct') {
            $request->validate([
                'target_user_id' => 'required|exists:users,id',
            ]);
            $target = User::findOrFail($request->target_user_id);
            $conv   = $this->chat->findOrCreateDirect($user, $target, $tenantId);
        } elseif ($type === 'group') {
            $request->validate([
                'name'     => 'required|string|max:100',
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'exists:users,id',
            ]);
            $conv = $this->chat->createGroup(
                $request->name,
                $request->user_ids,
                $user,
                $tenantId
            );
        } else {
            return response()->json(['error' => 'Invalid conversation type.'], 422);
        }

        return response()->json([
            'conversation_id' => $conv->id,
            'type'            => $conv->type,
            'name'            => $conv->getDisplayName($user),
        ]);
    }

    // ─── Messages ────────────────────────────────────────────────────────────

    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();

        $this->abortIfNotParticipant($conversation, $user);

        $query = ChatMessage::where('conversation_id', $conversation->id)
            ->with(['sender', 'replyTo.sender', 'attachments']);

        if ($request->filled('after_id')) {
            $pivot = ChatMessage::find($request->after_id);
            if ($pivot) {
                $query->where('created_at', '>', $pivot->created_at)
                      ->where('id', '!=', $pivot->id);
            }
            $messages = $query->orderBy('created_at', 'asc')->get();
            $hasMore  = false;
        } elseif ($request->filled('before_id')) {
            $pivot = ChatMessage::find($request->before_id);
            if ($pivot) {
                $query->where('created_at', '<', $pivot->created_at);
            }
            $total    = $query->count();
            $messages = $query->orderBy('created_at', 'desc')->limit(30)->get()->reverse()->values();
            $hasMore  = $total > 30;
        } else {
            $total    = $query->count();
            $messages = $query->orderBy('created_at', 'desc')->limit(30)->get()->reverse()->values();
            $hasMore  = $total > 30;
        }

        $this->chat->markRead($conversation, $user);

        $data = $messages->map(fn($msg) => $this->formatMessage($msg));

        return response()->json([
            'messages' => $data,
            'has_more' => $hasMore,
        ]);
    }

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();

        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'body'        => 'nullable|string|max:5000',
            'file'        => 'nullable|file|max:20480',
            'reply_to_id' => 'nullable|exists:chat_messages,id',
        ]);

        if (! $request->filled('body') && ! $request->hasFile('file')) {
            abort(422, 'A message body or file is required.');
        }

        $message = $this->chat->sendMessage(
            $conversation,
            $user,
            $request->body,
            $request->file('file'),
            $request->reply_to_id
        );

        $message->load(['sender', 'replyTo.sender', 'attachments']);

        return response()->json(['message' => $this->formatMessage($message)]);
    }

    public function editMessage(Request $request, ChatMessage $message): JsonResponse
    {
        if ($message->sender_id !== auth()->id()) {
            abort(403, 'You can only edit your own messages.');
        }

        if ($message->created_at->diffInMinutes(now()) > 15) {
            return response()->json(
                ['error' => 'Messages can only be edited within 15 minutes of sending.'],
                422
            );
        }

        $request->validate(['body' => 'required|string|max:5000']);

        $message->update([
            'body'      => $request->body,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'body'    => $message->fresh()->getRawOriginal('body'),
        ]);
    }

    public function deleteMessage(ChatMessage $message): JsonResponse
    {
        $user = auth()->user();

        $isAdmin = ChatParticipant::where('conversation_id', $message->conversation_id)
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->exists();

        if ($message->sender_id !== $user->id && ! $isAdmin) {
            abort(403, 'You are not allowed to delete this message.');
        }

        $message->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'body'       => null,
        ]);

        return response()->json(['success' => true]);
    }

    // ─── Participants ────────────────────────────────────────────────────────

    public function addParticipants(Request $request, ChatConversation $conversation): JsonResponse
    {
        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Participants can only be added to group conversations.'], 422);
        }

        $user = auth()->user();
        $this->abortIfNotAdmin($conversation, $user);

        $request->validate([
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $added = 0;
        foreach ($request->user_ids as $userId) {
            $existing = ChatParticipant::where('conversation_id', $conversation->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                if ($existing->left_at !== null) {
                    $existing->update(['left_at' => null, 'joined_at' => now()]);
                    $added++;
                }
            } else {
                ChatParticipant::create([
                    'conversation_id' => $conversation->id,
                    'user_id'         => $userId,
                    'role'            => 'member',
                    'joined_at'       => now(),
                ]);
                $added++;
            }
        }

        return response()->json(['success' => true, 'added' => $added]);
    }

    public function removeParticipant(ChatConversation $conversation, User $user): JsonResponse
    {
        $authUser = auth()->user();

        $isAdmin = ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $authUser->id)
            ->where('role', 'admin')
            ->exists();

        $isSelf = $authUser->id === $user->id;

        if (! $isAdmin && ! $isSelf) {
            abort(403, 'You are not allowed to remove this participant.');
        }

        ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['left_at' => now()]);

        return response()->json(['success' => true]);
    }

    // ─── Attachments ─────────────────────────────────────────────────────────

    public function downloadAttachment(ChatAttachment $attachment): StreamedResponse
    {
        $user = auth()->user();

        // Load conversation via the message relationship and verify participant
        $conversation = $attachment->message->conversation;
        $this->abortIfNotParticipant($conversation, $user);

        return Storage::disk('local')->download($attachment->file_path, $attachment->file_name);
    }

    // ─── Unread Count ────────────────────────────────────────────────────────

    public function unreadCount(): JsonResponse
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $count = $this->chat->unreadCount($user, $tenantId);

        return response()->json(['count' => $count]);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function abortIfNotParticipant(ChatConversation $conversation, User $user): void
    {
        $isParticipant = ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();

        if (! $isParticipant) {
            abort(403, 'You are not a participant in this conversation.');
        }
    }

    private function abortIfNotAdmin(ChatConversation $conversation, User $user): void
    {
        $isAdmin = ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->where('role', 'admin')
            ->whereNull('left_at')
            ->exists();

        if (! $isAdmin) {
            abort(403, 'Only group admins can perform this action.');
        }
    }

    private function formatMessage(ChatMessage $msg): array
    {
        $senderName     = $msg->sender?->name ?? 'Unknown';
        $senderInitials = mb_strtoupper(
            collect(explode(' ', $senderName))
                ->map(fn($w) => mb_substr($w, 0, 1))
                ->take(2)
                ->implode('')
        );

        $replyTo = null;
        if ($msg->replyTo) {
            $replyTo = [
                'id'          => $msg->replyTo->id,
                'sender_name' => $msg->replyTo->sender?->name ?? 'Unknown',
                'body_preview' => mb_substr(
                    $msg->replyTo->is_deleted
                        ? 'Message deleted'
                        : ($msg->replyTo->getRawOriginal('body') ?? ''),
                    0,
                    80
                ),
            ];
        }

        $attachments = $msg->attachments->map(function (ChatAttachment $att) use ($msg) {
            return [
                'id'           => $att->id,
                'file_name'    => $att->file_name,
                'mime_type'    => $att->mime_type,
                'file_size_kb' => $att->file_size_kb,
                'is_image'     => $att->isImage(),
                'url'          => route('chat.attachment.download', ['attachment' => $att->id]),
            ];
        })->values()->all();

        // Date label for grouping (Today / Yesterday / formatted date)
        $createdAt = $msg->created_at;
        if ($createdAt) {
            if ($createdAt->isToday()) $dateLabel = 'Today';
            elseif ($createdAt->isYesterday()) $dateLabel = 'Yesterday';
            else $dateLabel = $createdAt->format('d M Y');
        } else {
            $dateLabel = '';
        }

        return [
            'id'              => $msg->id,
            'sender_id'       => $msg->sender_id,
            'sender_name'     => $senderName,
            'sender_initials' => $senderInitials,
            'body'            => $msg->is_deleted ? '' : ($msg->getRawOriginal('body') ?? ''),
            'type'            => $msg->type,
            'is_edited'       => (bool) $msg->is_edited,
            'is_deleted'      => (bool) $msg->is_deleted,
            'created_at'      => $createdAt?->format('H:i'),
            'created_at_full' => $createdAt?->toISOString(),
            'date_label'      => $dateLabel,
            'reply_to'        => $replyTo,
            'attachments'     => $attachments,
        ];
    }
}
