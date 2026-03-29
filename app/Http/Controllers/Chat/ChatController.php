<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\ChatPinnedMessage;
use App\Models\ChatPoll;
use App\Models\ChatPollOption;
use App\Models\ChatPollVote;
use App\Models\ChatPresence;
use App\Models\ChatReaction;
use App\Models\ChatReadReceipt;
use App\Models\ChatStarredMessage;
use App\Models\ChatTask;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'file'        => 'nullable|file|max:10240',
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

    // ─── Reactions ─────────────────────────────────────────────────────────

    public function toggleReaction(Request $request, ChatMessage $message): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($message->conversation, $user);

        $request->validate(['emoji' => 'required|string|max:10']);

        $existing = ChatReaction::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->where('emoji', $request->emoji)
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            ChatReaction::create([
                'message_id' => $message->id,
                'user_id'    => $user->id,
                'emoji'      => $request->emoji,
                'created_at' => now(),
            ]);
        }

        return response()->json(['reactions' => $this->getGroupedReactions($message)]);
    }

    // ─── Pinned Messages ──────────────────────────────────────────────────

    public function pinMessage(ChatMessage $message): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($message->conversation, $user);

        ChatPinnedMessage::firstOrCreate(
            ['conversation_id' => $message->conversation_id, 'message_id' => $message->id],
            ['pinned_by' => $user->id, 'pinned_at' => now()]
        );

        return response()->json(['success' => true]);
    }

    public function unpinMessage(ChatMessage $message): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($message->conversation, $user);

        ChatPinnedMessage::where('conversation_id', $message->conversation_id)
            ->where('message_id', $message->id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function pinnedMessages(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $pinned = ChatPinnedMessage::where('conversation_id', $conversation->id)
            ->with(['message.sender', 'pinnedByUser'])
            ->orderByDesc('pinned_at')
            ->get()
            ->map(fn($p) => [
                'id'         => $p->message_id,
                'body'       => $p->message->getRawOriginal('body') ?? '',
                'sender'     => $p->message->sender?->name ?? 'Unknown',
                'pinned_by'  => $p->pinnedByUser?->name ?? 'Unknown',
                'pinned_at'  => $p->pinned_at?->toISOString(),
                'created_at' => $p->message->created_at?->toISOString(),
            ]);

        return response()->json(['pinned_messages' => $pinned]);
    }

    // ─── Starred Messages ─────────────────────────────────────────────────

    public function starMessage(ChatMessage $message): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($message->conversation, $user);

        $existing = ChatStarredMessage::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $starred = false;
        } else {
            ChatStarredMessage::create([
                'message_id' => $message->id,
                'user_id'    => $user->id,
                'created_at' => now(),
            ]);
            $starred = true;
        }

        return response()->json(['starred' => $starred]);
    }

    public function starredMessages(): JsonResponse
    {
        $user = auth()->user();

        $starred = ChatStarredMessage::where('user_id', $user->id)
            ->with(['message.sender', 'message.conversation'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($s) => [
                'message_id'      => $s->message_id,
                'body'            => $s->message->getRawOriginal('body') ?? '',
                'sender'          => $s->message->sender?->name ?? 'Unknown',
                'conversation_id' => $s->message->conversation_id,
                'conversation'    => $s->message->conversation->getDisplayName($user),
                'created_at'      => $s->message->created_at?->toISOString(),
                'starred_at'      => $s->created_at?->toISOString(),
            ]);

        return response()->json(['starred_messages' => $starred]);
    }

    // ─── Mute ─────────────────────────────────────────────────────────────

    public function muteConversation(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $participant = ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($participant->is_muted) {
            $participant->update(['is_muted' => false, 'muted_until' => null]);
            $muted = false;
        } else {
            $request->validate(['muted_until' => 'nullable|date|after:now']);
            $participant->update([
                'is_muted'    => true,
                'muted_until' => $request->input('muted_until'),
            ]);
            $muted = true;
        }

        return response()->json(['muted' => $muted, 'muted_until' => $participant->fresh()->muted_until]);
    }

    // ─── Search ───────────────────────────────────────────────────────────

    public function searchMessages(Request $request): JsonResponse
    {
        $user = auth()->user();
        $request->validate(['q' => 'required|string|min:2|max:200']);

        $conversationIds = ChatParticipant::where('user_id', $user->id)
            ->whereNull('left_at')
            ->pluck('conversation_id');

        $messages = ChatMessage::whereIn('conversation_id', $conversationIds)
            ->where('is_deleted', false)
            ->where('body', 'ilike', '%' . $request->input('q') . '%')
            ->with(['sender', 'conversation'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $grouped = $messages->groupBy('conversation_id')->map(function ($msgs, $convId) use ($user) {
            $conv = $msgs->first()->conversation;
            return [
                'conversation_id'   => $convId,
                'conversation_name' => $conv->getDisplayName($user),
                'messages'          => $msgs->map(fn($m) => [
                    'id'         => $m->id,
                    'body'       => $m->getRawOriginal('body') ?? '',
                    'sender'     => $m->sender?->name ?? 'Unknown',
                    'created_at' => $m->created_at?->toISOString(),
                ])->values(),
            ];
        })->values();

        return response()->json(['results' => $grouped]);
    }

    // ─── Forward ──────────────────────────────────────────────────────────

    public function forwardMessage(Request $request, ChatMessage $message): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($message->conversation, $user);

        $request->validate(['conversation_id' => 'required|exists:chat_conversations,id']);

        $targetConv = ChatConversation::findOrFail($request->conversation_id);
        $this->abortIfNotParticipant($targetConv, $user);

        $originalBody = $message->getRawOriginal('body') ?? '';
        $forwarded = $this->chat->sendMessage(
            $targetConv,
            $user,
            "Forwarded: {$originalBody}",
            null,
            null
        );

        $forwarded->load(['sender', 'replyTo.sender', 'attachments']);

        return response()->json(['message' => $this->formatMessage($forwarded)]);
    }

    // ─── Polls ────────────────────────────────────────────────────────────

    public function createPoll(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'question'       => 'required|string|max:500',
            'options'        => 'required|array|min:2|max:10',
            'options.*'      => 'required|string|max:200',
            'allow_multiple' => 'boolean',
            'is_anonymous'   => 'boolean',
        ]);

        return DB::transaction(function () use ($request, $conversation, $user) {
            $message = ChatMessage::create([
                'tenant_id'       => $conversation->tenant_id,
                'conversation_id' => $conversation->id,
                'sender_id'       => $user->id,
                'body'            => $request->question,
                'type'            => 'poll',
                'delivery_status' => 'sent',
            ]);

            if ($conversation->disappear_minutes) {
                $message->update([
                    'is_disappearing' => true,
                    'disappear_at'    => now()->addMinutes($conversation->disappear_minutes),
                ]);
            }

            $poll = ChatPoll::create([
                'message_id'      => $message->id,
                'conversation_id' => $conversation->id,
                'question'        => $request->question,
                'allow_multiple'  => $request->boolean('allow_multiple', false),
                'is_anonymous'    => $request->boolean('is_anonymous', false),
                'is_closed'       => false,
            ]);

            foreach ($request->options as $i => $text) {
                ChatPollOption::create([
                    'poll_id'    => $poll->id,
                    'text'       => $text,
                    'sort_order' => $i,
                ]);
            }

            $conversation->update([
                'last_message_at'      => now(),
                'last_message_preview' => "Poll: {$request->question}",
            ]);

            $message->load(['sender', 'replyTo.sender', 'attachments', 'poll.options.votes']);

            return response()->json(['message' => $this->formatMessage($message)]);
        });
    }

    public function votePoll(Request $request, string $pollId): JsonResponse
    {
        $user = auth()->user();
        $poll = ChatPoll::findOrFail($pollId);
        $this->abortIfNotParticipant($poll->conversation, $user);

        if ($poll->is_closed) {
            return response()->json(['error' => 'This poll is closed.'], 422);
        }

        $request->validate([
            'option_ids'   => 'required|array|min:1',
            'option_ids.*' => 'exists:chat_poll_options,id',
        ]);

        if (! $poll->allow_multiple && count($request->option_ids) > 1) {
            return response()->json(['error' => 'This poll only allows a single vote.'], 422);
        }

        // Remove previous votes
        ChatPollVote::where('poll_id', $poll->id)->where('user_id', $user->id)->delete();

        foreach ($request->option_ids as $optionId) {
            $option = ChatPollOption::where('id', $optionId)->where('poll_id', $poll->id)->first();
            if ($option) {
                ChatPollVote::create([
                    'poll_id'    => $poll->id,
                    'option_id'  => $optionId,
                    'user_id'    => $user->id,
                    'created_at' => now(),
                ]);
            }
        }

        $poll->load(['options.votes']);

        return response()->json(['poll' => $this->formatPollData($poll, $user)]);
    }

    public function closePoll(string $pollId): JsonResponse
    {
        $user = auth()->user();
        $poll = ChatPoll::findOrFail($pollId);

        // Only creator can close
        if ($poll->message->sender_id !== $user->id) {
            abort(403, 'Only the poll creator can close it.');
        }

        $poll->update(['is_closed' => true]);
        $poll->load(['options.votes']);

        return response()->json(['poll' => $this->formatPollData($poll, $user)]);
    }

    // ─── Tasks ────────────────────────────────────────────────────────────

    public function createTask(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'assigned_to' => 'nullable|exists:users,id',
            'priority'    => 'nullable|string|in:low,medium,high,urgent',
            'due_date'    => 'nullable|date',
        ]);

        return DB::transaction(function () use ($request, $conversation, $user) {
            $message = ChatMessage::create([
                'tenant_id'       => $conversation->tenant_id,
                'conversation_id' => $conversation->id,
                'sender_id'       => $user->id,
                'body'            => "Task: {$request->title}",
                'type'            => 'task',
                'delivery_status' => 'sent',
            ]);

            if ($conversation->disappear_minutes) {
                $message->update([
                    'is_disappearing' => true,
                    'disappear_at'    => now()->addMinutes($conversation->disappear_minutes),
                ]);
            }

            ChatTask::create([
                'message_id'      => $message->id,
                'conversation_id' => $conversation->id,
                'tenant_id'       => $conversation->tenant_id,
                'title'           => $request->title,
                'description'     => $request->description,
                'assigned_to'     => $request->assigned_to,
                'created_by'      => $user->id,
                'priority'        => $request->input('priority', 'medium'),
                'status'          => 'pending',
                'due_date'        => $request->due_date,
            ]);

            $conversation->update([
                'last_message_at'      => now(),
                'last_message_preview' => "Task: {$request->title}",
            ]);

            $message->load(['sender', 'replyTo.sender', 'attachments', 'task.assignedTo']);

            return response()->json(['message' => $this->formatMessage($message)]);
        });
    }

    public function updateTaskStatus(Request $request, string $taskId): JsonResponse
    {
        $user = auth()->user();
        $task = ChatTask::findOrFail($taskId);

        if ($task->assigned_to !== $user->id && $task->created_by !== $user->id) {
            abort(403, 'Only the assigned user or creator can update this task.');
        }

        $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
        ]);

        $task->update([
            'status'       => $request->status,
            'completed_at' => $request->status === 'completed' ? now() : null,
        ]);

        $task->load('assignedTo');

        return response()->json([
            'task' => [
                'id'           => $task->id,
                'title'        => $task->title,
                'status'       => $task->status,
                'assigned_to'  => $task->assignedTo?->name ?? 'Unknown',
                'priority'     => $task->priority,
                'due_date'     => $task->due_date?->toDateString(),
                'completed_at' => $task->completed_at?->toISOString(),
            ],
        ]);
    }

    public function conversationTasks(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $tasks = ChatTask::where('conversation_id', $conversation->id)
            ->with(['assignedTo', 'createdByUser'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($t) => [
                'id'           => $t->id,
                'title'        => $t->title,
                'description'  => $t->description,
                'assigned_to'  => $t->assignedTo?->name ?? 'Unknown',
                'created_by'   => $t->createdByUser?->name ?? 'Unknown',
                'priority'     => $t->priority,
                'status'       => $t->status,
                'due_date'     => $t->due_date?->toDateString(),
                'completed_at' => $t->completed_at?->toISOString(),
                'created_at'   => $t->created_at?->toISOString(),
            ]);

        return response()->json(['tasks' => $tasks]);
    }

    // ─── Presence / Typing ────────────────────────────────────────────────

    public function heartbeat(Request $request): JsonResponse
    {
        $user = auth()->user();
        $request->validate(['typing_in' => 'nullable|uuid']);

        $this->chat->updatePresence($user, $request->input('typing_in'));

        // Get online users (seen in last 2 minutes)
        $onlineUsers = ChatPresence::where('last_seen_at', '>=', now()->subMinutes(2))
            ->with('user:id,name')
            ->get()
            ->map(fn($p) => [
                'user_id'   => $p->user_id,
                'name'      => $p->user?->name ?? 'Unknown',
                'typing_in' => ($p->typing_at && $p->typing_at->diffInSeconds(now()) < 10) ? $p->typing_in : null,
            ]);

        return response()->json(['online_users' => $onlineUsers]);
    }

    public function presence(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $participantIds = ChatParticipant::where('conversation_id', $conversation->id)
            ->whereNull('left_at')
            ->pluck('user_id');

        $presences = ChatPresence::whereIn('user_id', $participantIds)
            ->with('user:id,name')
            ->get()
            ->map(fn($p) => [
                'user_id'    => $p->user_id,
                'name'       => $p->user?->name ?? 'Unknown',
                'is_online'  => $p->last_seen_at && $p->last_seen_at->diffInMinutes(now()) < 2,
                'last_seen'  => $p->last_seen_at?->toISOString(),
                'is_typing'  => $p->typing_in === $conversation->id
                    && $p->typing_at
                    && $p->typing_at->diffInSeconds(now()) < 10,
            ]);

        return response()->json(['presence' => $presences]);
    }

    // ─── Group Enhancements ───────────────────────────────────────────────

    public function updateGroup(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotAdmin($conversation, $user);

        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Only group conversations can be updated.'], 422);
        }

        $request->validate([
            'name'              => 'nullable|string|max:100',
            'description'       => 'nullable|string|max:500',
            'disappear_minutes' => 'nullable|integer|min:1|max:43200',
        ]);

        $data = array_filter($request->only(['name', 'description', 'disappear_minutes']), fn($v) => $v !== null);
        if (! empty($data)) {
            $conversation->update($data);
        }

        return response()->json([
            'success'           => true,
            'name'              => $conversation->name,
            'description'       => $conversation->description,
            'disappear_minutes' => $conversation->disappear_minutes,
        ]);
    }

    public function generateInviteLink(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotAdmin($conversation, $user);

        if ($conversation->type !== 'group') {
            return response()->json(['error' => 'Invite links are only for groups.'], 422);
        }

        $code = $conversation->invite_code ?? Str::random(20);
        $conversation->update(['invite_code' => $code]);

        return response()->json([
            'invite_code' => $code,
            'invite_url'  => url("/chat/join/{$code}"),
        ]);
    }

    public function joinViaInvite(string $inviteCode): JsonResponse
    {
        $user = auth()->user();

        $conversation = ChatConversation::where('invite_code', $inviteCode)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        $existing = ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->left_at !== null) {
                $existing->update(['left_at' => null, 'joined_at' => now()]);
            }
            // Already a participant
        } else {
            ChatParticipant::create([
                'conversation_id' => $conversation->id,
                'user_id'         => $user->id,
                'role'            => 'member',
                'joined_at'       => now(),
            ]);
        }

        return response()->json([
            'success'         => true,
            'conversation_id' => $conversation->id,
            'name'            => $conversation->getDisplayName($user),
        ]);
    }

    // ─── Disappearing Messages ────────────────────────────────────────────

    public function setDisappearing(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotAdmin($conversation, $user);

        $request->validate([
            'disappear_minutes' => 'nullable|integer|min:1|max:43200',
        ]);

        $conversation->update([
            'disappear_minutes' => $request->input('disappear_minutes'),
        ]);

        return response()->json([
            'success'           => true,
            'disappear_minutes' => $conversation->disappear_minutes,
        ]);
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

        // Poll data
        $pollData = null;
        if ($msg->type === 'poll' && $msg->poll) {
            $pollData = $this->formatPollData($msg->poll, auth()->user());
        }

        // Task data
        $taskData = null;
        if ($msg->type === 'task' && $msg->task) {
            $msg->task->load('assignedTo');
            $taskData = [
                'id'           => $msg->task->id,
                'title'        => $msg->task->title,
                'assigned_to'  => $msg->task->assignedTo?->name ?? 'Unknown',
                'status'       => $msg->task->status,
                'priority'     => $msg->task->priority,
                'due_date'     => $msg->task->due_date?->toDateString(),
            ];
        }

        $userId = auth()->id();

        return [
            'id'              => $msg->id,
            'sender_id'       => $msg->sender_id,
            'sender_name'     => $senderName,
            'sender_initials' => $senderInitials,
            'body'            => $msg->is_deleted ? '' : ($msg->getRawOriginal('body') ?? ''),
            'type'            => $msg->type,
            'is_edited'       => (bool) $msg->is_edited,
            'is_deleted'      => (bool) $msg->is_deleted,
            'delivery_status' => $msg->delivery_status,
            'created_at'      => $createdAt?->format('H:i'),
            'created_at_full' => $createdAt?->toISOString(),
            'date_label'      => $dateLabel,
            'reply_to'        => $replyTo,
            'attachments'     => $attachments,
            'reactions'       => $this->getGroupedReactions($msg),
            'is_pinned'       => ChatPinnedMessage::where('message_id', $msg->id)
                ->where('conversation_id', $msg->conversation_id)->exists(),
            'is_starred'      => ChatStarredMessage::where('message_id', $msg->id)
                ->where('user_id', $userId)->exists(),
            'is_disappearing' => (bool) $msg->is_disappearing,
            'poll'            => $pollData,
            'task'            => $taskData,
        ];
    }

    private function getGroupedReactions(ChatMessage $msg): array
    {
        $reactions = ChatReaction::where('message_id', $msg->id)->with('user:id,name')->get();
        $userId = auth()->id();

        return $reactions->groupBy('emoji')->map(function ($group, $emoji) use ($userId) {
            return [
                'count'   => $group->count(),
                'users'   => $group->map(fn($r) => $r->user?->name ?? 'Unknown')->values()->all(),
                'reacted' => $group->contains('user_id', $userId),
            ];
        })->all();
    }

    private function formatPollData(ChatPoll $poll, $user): array
    {
        $poll->loadMissing(['options.votes']);
        $userId = $user->id ?? auth()->id();

        $totalVotes = $poll->votes()->count();
        $userVoteOptionIds = ChatPollVote::where('poll_id', $poll->id)
            ->where('user_id', $userId)
            ->pluck('option_id')
            ->all();

        return [
            'id'             => $poll->id,
            'question'       => $poll->question,
            'allow_multiple' => $poll->allow_multiple,
            'is_anonymous'   => $poll->is_anonymous,
            'is_closed'      => $poll->is_closed,
            'total_votes'    => $totalVotes,
            'user_votes'     => $userVoteOptionIds,
            'options'        => $poll->options->map(fn($opt) => [
                'id'         => $opt->id,
                'text'       => $opt->text,
                'vote_count' => $opt->votes->count(),
            ])->values()->all(),
        ];
    }
}
