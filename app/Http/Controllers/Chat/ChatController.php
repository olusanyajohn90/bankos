<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatAttachment;
use App\Models\ChatBookmark;
use App\Models\ChatConversation;
use App\Models\ChatCustomEmoji;
use App\Models\ChatMention;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\ChatPinnedMessage;
use App\Models\ChatPoll;
use App\Models\ChatPollOption;
use App\Models\ChatPollVote;
use App\Models\ChatPresence;
use App\Models\ChatReaction;
use App\Models\ChatReadReceipt;
use App\Models\ChatReminder;
use App\Models\ChatStarredMessage;
use App\Models\ChatTask;
use App\Models\ChatCall;
use App\Models\ChatCallParticipant;
use App\Models\ChatCanvas;
use App\Models\ChatUserGroup;
use App\Models\ChatUserGroupMember;
use App\Models\ChatWorkflow;
use App\Models\ChatWorkflowRun;
use App\Models\User;
use App\Services\Chat\ChatService;
use App\Services\Chat\LiveKitService;
use Carbon\Carbon;
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
                'is_group'             => in_array($conv->type, ['group', 'channel']),
                'is_archived'          => (bool) $conv->is_archived,
                'topic'                => $conv->topic,
                'is_private'           => (bool) $conv->is_private,
                'notify_level'         => $conv->participants->firstWhere('user_id', $user->id)?->notify_level ?? 'all',
                'participant_count'    => $conv->participants->whereNull('left_at')->count(),
                'participants'         => $conv->participants->whereNull('left_at')->map(fn($p) => [
                    'id'   => $p->user_id,
                    'name' => $p->user?->name ?? 'Unknown',
                    'role' => $p->role,
                ])->values()->all(),
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

        // ── Slash commands ────────────────────────────────────────────────
        $body = $request->body ?? '';
        if (str_starts_with($body, '/')) {
            $result = $this->handleSlashCommand($body, $conversation, $user);
            if ($result) {
                return $result;
            }
            // If not a recognized command, send as normal message
        }

        $message = $this->chat->sendMessage(
            $conversation,
            $user,
            $request->body,
            $request->file('file'),
            $request->reply_to_id
        );

        $message->load(['sender', 'replyTo.sender', 'attachments']);

        // Process @mentions
        $this->processMentions($message, $conversation);

        // Check workflow triggers
        $this->checkWorkflows($message, $conversation);

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
            'is_disappearing'      => (bool) $msg->is_disappearing,
            'poll'                 => $pollData,
            'task'                 => $taskData,
            'thread_reply_count'   => (int) $msg->thread_reply_count,
            'thread_last_reply_at' => $msg->thread_last_reply_at?->toISOString(),
            'is_scheduled'         => (bool) $msg->is_scheduled,
            'scheduled_at'         => $msg->scheduled_at?->toISOString(),
            'mentions'             => ChatMention::where('message_id', $msg->id)
                ->with('mentionedUser:id,name')
                ->get()
                ->map(fn($m) => $m->mentionedUser?->name ?? $m->mention_type)
                ->values()
                ->all(),
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

    // ─── Channels ─────────────────────────────────────────────────────────

    public function createChannel(Request $request): JsonResponse
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $request->validate([
            'name'       => 'required|string|max:100',
            'topic'      => 'nullable|string|max:255',
            'is_private' => 'boolean',
            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $conv = ChatConversation::create([
            'tenant_id'  => $tenantId,
            'type'       => 'channel',
            'name'       => $request->name,
            'topic'      => $request->topic,
            'is_private' => $request->boolean('is_private', false),
            'created_by' => $user->id,
        ]);

        // Add creator as admin
        ChatParticipant::create([
            'conversation_id' => $conv->id,
            'user_id'         => $user->id,
            'role'            => 'admin',
            'joined_at'       => now(),
        ]);

        // Add other users
        foreach ($request->input('user_ids', []) as $userId) {
            if ($userId == $user->id) continue;
            ChatParticipant::create([
                'conversation_id' => $conv->id,
                'user_id'         => $userId,
                'role'            => 'member',
                'joined_at'       => now(),
            ]);
        }

        return response()->json([
            'conversation_id' => $conv->id,
            'type'            => 'channel',
            'name'            => $conv->name,
            'topic'           => $conv->topic,
        ]);
    }

    public function browseChannels(Request $request): JsonResponse
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $joinedIds = ChatParticipant::where('user_id', $user->id)
            ->whereNull('left_at')
            ->pluck('conversation_id');

        $channels = ChatConversation::where('tenant_id', $tenantId)
            ->where('type', 'channel')
            ->where('is_private', false)
            ->whereNotIn('id', $joinedIds)
            ->withCount(['participants as member_count' => fn($q) => $q->whereNull('left_at')])
            ->orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id'           => $c->id,
                'name'         => $c->name,
                'topic'        => $c->topic,
                'member_count' => $c->member_count,
                'created_at'   => $c->created_at?->toISOString(),
            ]);

        return response()->json(['channels' => $channels]);
    }

    public function joinChannel(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();

        if ($conversation->type !== 'channel') {
            return response()->json(['error' => 'Not a channel.'], 422);
        }

        if ($conversation->is_private) {
            return response()->json(['error' => 'Cannot join a private channel without invitation.'], 403);
        }

        $existing = ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            if ($existing->left_at !== null) {
                $existing->update(['left_at' => null, 'joined_at' => now()]);
            }
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
            'name'            => $conversation->name,
        ]);
    }

    // ─── Threads ──────────────────────────────────────────────────────────

    public function threadReplies(Request $request, ChatMessage $message): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($message->conversation, $user);

        $replies = ChatMessage::where('thread_id', $message->id)
            ->with(['sender', 'replyTo.sender', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->paginate(30);

        $data = collect($replies->items())->map(fn($msg) => $this->formatMessage($msg));

        return response()->json([
            'replies'  => $data,
            'has_more' => $replies->hasMorePages(),
            'total'    => $replies->total(),
        ]);
    }

    public function replyToThread(Request $request, ChatMessage $message): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($message->conversation, $user);

        $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $conversation = $message->conversation;

        $reply = ChatMessage::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $request->body,
            'type'            => 'text',
            'delivery_status' => 'sent',
            'thread_id'       => $message->id,
        ]);

        // Update parent thread counters
        $message->update([
            'thread_reply_count'   => ($message->thread_reply_count ?? 0) + 1,
            'thread_last_reply_at' => now(),
        ]);

        $reply->load(['sender', 'replyTo.sender', 'attachments']);

        // Process @mentions
        $this->processMentions($reply, $conversation);

        return response()->json(['message' => $this->formatMessage($reply)]);
    }

    // ─── Mentions ─────────────────────────────────────────────────────────

    private function processMentions(ChatMessage $message, ChatConversation $conversation): void
    {
        $body = $message->getRawOriginal('body') ?? '';
        if (empty($body)) return;

        $tenantId = $conversation->tenant_id;

        // Handle @here and @channel — mention all participants
        if (preg_match('/@(here|channel)\b/', $body, $specialMatch)) {
            $participantUserIds = ChatParticipant::where('conversation_id', $conversation->id)
                ->whereNull('left_at')
                ->where('user_id', '!=', $message->sender_id)
                ->pluck('user_id');

            foreach ($participantUserIds as $userId) {
                ChatMention::create([
                    'message_id'       => $message->id,
                    'conversation_id'  => $conversation->id,
                    'mentioned_user_id' => $userId,
                    'mention_type'     => $specialMatch[1],
                    'is_read'          => false,
                    'created_at'       => now(),
                ]);
            }
            return;
        }

        // Handle @username patterns
        preg_match_all('/@([\w\s]+?)(?=\s@|$|\s[^@])/', $body, $matches);
        if (empty($matches[1])) return;

        foreach ($matches[1] as $nameCandidate) {
            $nameCandidate = trim($nameCandidate);
            if (empty($nameCandidate)) continue;

            $mentionedUser = User::where('tenant_id', $tenantId)
                ->where('name', 'ilike', $nameCandidate)
                ->first();

            if ($mentionedUser && $mentionedUser->id !== $message->sender_id) {
                ChatMention::create([
                    'message_id'        => $message->id,
                    'conversation_id'   => $conversation->id,
                    'mentioned_user_id' => $mentionedUser->id,
                    'mention_type'      => 'user',
                    'is_read'           => false,
                    'created_at'        => now(),
                ]);
            }
        }
    }

    public function myMentions(Request $request): JsonResponse
    {
        $user = auth()->user();

        $mentions = ChatMention::where('mentioned_user_id', $user->id)
            ->where('is_read', false)
            ->with(['message.sender', 'conversation'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn($m) => [
                'id'                => $m->id,
                'message_id'        => $m->message_id,
                'conversation_id'   => $m->conversation_id,
                'conversation_name' => $m->conversation?->getDisplayName($user) ?? 'Unknown',
                'sender'            => $m->message?->sender?->name ?? 'Unknown',
                'body'              => $m->message?->getRawOriginal('body') ?? '',
                'mention_type'      => $m->mention_type,
                'created_at'        => $m->created_at?->toISOString(),
            ]);

        return response()->json(['mentions' => $mentions]);
    }

    // ─── Scheduled Messages ───────────────────────────────────────────────

    public function scheduleMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'body'         => 'required|string|max:5000',
            'scheduled_at' => 'required|date|after:now',
        ]);

        $message = ChatMessage::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $request->body,
            'type'            => 'text',
            'delivery_status' => 'pending',
            'is_scheduled'    => true,
            'scheduled_at'    => $request->scheduled_at,
        ]);

        $message->load(['sender', 'replyTo.sender', 'attachments']);

        return response()->json(['message' => $this->formatMessage($message)]);
    }

    public function scheduledMessages(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $messages = ChatMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', $user->id)
            ->where('is_scheduled', true)
            ->with(['sender', 'attachments'])
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn($msg) => $this->formatMessage($msg));

        return response()->json(['scheduled_messages' => $messages]);
    }

    public function cancelScheduled(ChatMessage $message): JsonResponse
    {
        $user = auth()->user();

        if ($message->sender_id !== $user->id) {
            abort(403, 'You can only cancel your own scheduled messages.');
        }

        if (! $message->is_scheduled) {
            return response()->json(['error' => 'This message is not scheduled.'], 422);
        }

        $message->delete();

        return response()->json(['success' => true]);
    }

    // ─── Reminders ────────────────────────────────────────────────────────

    public function createReminder(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'note'            => 'required|string|max:500',
            'remind_at'       => 'required|date|after:now',
            'conversation_id' => 'nullable|exists:chat_conversations,id',
            'message_id'      => 'nullable|exists:chat_messages,id',
        ]);

        $reminder = ChatReminder::create([
            'tenant_id'       => $user->tenant_id,
            'user_id'         => $user->id,
            'conversation_id' => $request->conversation_id,
            'message_id'      => $request->message_id,
            'note'            => $request->note,
            'remind_at'       => $request->remind_at,
            'is_fired'        => false,
        ]);

        return response()->json(['reminder' => [
            'id'        => $reminder->id,
            'note'      => $reminder->note,
            'remind_at' => $reminder->remind_at->toISOString(),
        ]]);
    }

    public function myReminders(Request $request): JsonResponse
    {
        $user = auth()->user();

        $reminders = ChatReminder::where('user_id', $user->id)
            ->where('is_fired', false)
            ->orderBy('remind_at')
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'note'            => $r->note,
                'remind_at'       => $r->remind_at->toISOString(),
                'conversation_id' => $r->conversation_id,
                'message_id'      => $r->message_id,
            ]);

        return response()->json(['reminders' => $reminders]);
    }

    public function dismissReminder(string $reminderId): JsonResponse
    {
        $user     = auth()->user();
        $reminder = ChatReminder::where('id', $reminderId)->where('user_id', $user->id)->firstOrFail();
        $reminder->update(['is_fired' => true]);

        return response()->json(['success' => true]);
    }

    // ─── User Groups ──────────────────────────────────────────────────────

    public function userGroups(): JsonResponse
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $groups = ChatUserGroup::where('tenant_id', $tenantId)
            ->withCount('members')
            ->with('creator:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn($g) => [
                'id'           => $g->id,
                'name'         => $g->name,
                'handle'       => $g->handle,
                'description'  => $g->description,
                'member_count' => $g->members_count,
                'created_by'   => $g->creator?->name ?? 'Unknown',
            ]);

        return response()->json(['groups' => $groups]);
    }

    public function createUserGroup(Request $request): JsonResponse
    {
        $user     = auth()->user();
        $tenantId = $user->tenant_id;

        $request->validate([
            'name'        => 'required|string|max:100',
            'handle'      => 'required|string|max:50|alpha_dash',
            'description' => 'nullable|string|max:500',
            'user_ids'    => 'nullable|array',
            'user_ids.*'  => 'exists:users,id',
        ]);

        $group = ChatUserGroup::create([
            'tenant_id'  => $tenantId,
            'name'       => $request->name,
            'handle'     => $request->handle,
            'description' => $request->description,
            'created_by' => $user->id,
        ]);

        // Add members
        foreach ($request->input('user_ids', []) as $userId) {
            ChatUserGroupMember::create([
                'group_id' => $group->id,
                'user_id'  => $userId,
                'added_at' => now(),
            ]);
        }

        return response()->json([
            'id'     => $group->id,
            'name'   => $group->name,
            'handle' => $group->handle,
        ]);
    }

    public function deleteUserGroup(string $groupId): JsonResponse
    {
        $user  = auth()->user();
        $group = ChatUserGroup::where('id', $groupId)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        ChatUserGroupMember::where('group_id', $group->id)->delete();
        $group->delete();

        return response()->json(['success' => true]);
    }

    // ─── Bookmarks ────────────────────────────────────────────────────────

    public function addBookmark(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'title'      => 'required|string|max:255',
            'url'        => 'nullable|url|max:2000',
            'message_id' => 'nullable|exists:chat_messages,id',
        ]);

        $maxSort = ChatBookmark::where('conversation_id', $conversation->id)->max('sort_order') ?? 0;

        $bookmark = ChatBookmark::create([
            'conversation_id' => $conversation->id,
            'created_by'      => $user->id,
            'title'           => $request->title,
            'url'             => $request->url,
            'message_id'      => $request->message_id,
            'sort_order'      => $maxSort + 1,
        ]);

        return response()->json(['bookmark' => [
            'id'    => $bookmark->id,
            'title' => $bookmark->title,
            'url'   => $bookmark->url,
        ]]);
    }

    public function removeBookmark(string $bookmarkId): JsonResponse
    {
        $user     = auth()->user();
        $bookmark = ChatBookmark::findOrFail($bookmarkId);

        // Verify user is participant of the conversation
        $this->abortIfNotParticipant($bookmark->conversation, $user);

        $bookmark->delete();

        return response()->json(['success' => true]);
    }

    public function conversationBookmarks(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $bookmarks = ChatBookmark::where('conversation_id', $conversation->id)
            ->with('creator:id,name')
            ->orderBy('sort_order')
            ->get()
            ->map(fn($b) => [
                'id'         => $b->id,
                'title'      => $b->title,
                'url'        => $b->url,
                'message_id' => $b->message_id,
                'created_by' => $b->creator?->name ?? 'Unknown',
                'created_at' => $b->created_at?->toISOString(),
            ]);

        return response()->json(['bookmarks' => $bookmarks]);
    }

    // ─── Custom Emoji ─────────────────────────────────────────────────────

    public function uploadEmoji(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'shortcode' => 'required|string|max:50|alpha_dash',
            'image'     => 'required|image|max:256',
        ]);

        $path = $request->file('image')->store('chat/emoji/' . $user->tenant_id, 'public');

        $emoji = ChatCustomEmoji::create([
            'tenant_id'  => $user->tenant_id,
            'shortcode'  => $request->shortcode,
            'image_path' => $path,
            'created_by' => $user->id,
        ]);

        return response()->json(['emoji' => [
            'id'        => $emoji->id,
            'shortcode' => $emoji->shortcode,
            'url'       => Storage::disk('public')->url($emoji->image_path),
        ]]);
    }

    public function customEmojis(): JsonResponse
    {
        $user = auth()->user();

        $emojis = ChatCustomEmoji::where('tenant_id', $user->tenant_id)
            ->orderBy('shortcode')
            ->get()
            ->map(fn($e) => [
                'id'        => $e->id,
                'shortcode' => $e->shortcode,
                'url'       => Storage::disk('public')->url($e->image_path),
            ]);

        return response()->json(['emojis' => $emojis]);
    }

    public function deleteEmoji(string $emojiId): JsonResponse
    {
        $user  = auth()->user();
        $emoji = ChatCustomEmoji::where('id', $emojiId)
            ->where('tenant_id', $user->tenant_id)
            ->firstOrFail();

        Storage::disk('public')->delete($emoji->image_path);
        $emoji->delete();

        return response()->json(['success' => true]);
    }

    // ─── User Status ──────────────────────────────────────────────────────

    public function setStatus(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'chat_status_emoji' => 'nullable|string|max:10',
            'chat_status_text'  => 'nullable|string|max:100',
            'chat_status_until' => 'nullable|date|after:now',
        ]);

        $user->update([
            'chat_status_emoji' => $request->chat_status_emoji,
            'chat_status_text'  => $request->chat_status_text,
            'chat_status_until' => $request->chat_status_until,
        ]);

        return response()->json(['success' => true, 'status' => [
            'emoji' => $user->chat_status_emoji,
            'text'  => $user->chat_status_text,
            'until' => $user->chat_status_until?->toISOString(),
        ]]);
    }

    public function clearStatus(): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'chat_status_emoji' => null,
            'chat_status_text'  => null,
            'chat_status_until' => null,
        ]);

        return response()->json(['success' => true]);
    }

    public function setDnd(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'chat_dnd_until' => 'required|date|after:now',
        ]);

        $user->update([
            'chat_dnd_until' => $request->chat_dnd_until,
        ]);

        return response()->json(['success' => true, 'dnd_until' => $user->chat_dnd_until->toISOString()]);
    }

    // ─── Notification Preferences ─────────────────────────────────────────

    public function setNotifyLevel(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'notify_level' => 'required|string|in:all,mentions,none',
        ]);

        ChatParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->update(['notify_level' => $request->notify_level]);

        return response()->json(['success' => true, 'notify_level' => $request->notify_level]);
    }

    // ─── Link Unfurling ───────────────────────────────────────────────────

    public function unfurlLink(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|url|max:2000',
        ]);

        $url = $request->url;

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout'       => 5,
                    'max_redirects' => 3,
                    'user_agent'    => 'BankOS-LinkPreview/1.0',
                ],
                'ssl' => [
                    'verify_peer' => false,
                ],
            ]);

            $html = @file_get_contents($url, false, $context, 0, 100000);

            if ($html === false) {
                return response()->json(['unfurl' => ['url' => $url, 'title' => null, 'description' => null, 'image' => null]]);
            }

            $title       = null;
            $description = null;
            $image       = null;

            // og:title
            if (preg_match('/<meta[^>]+property=["\']og:title["\'][^>]+content=["\']([^"\']*)["\']/', $html, $m)) {
                $title = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            } elseif (preg_match('/<title[^>]*>([^<]+)<\/title>/', $html, $m)) {
                $title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
            }

            // og:description
            if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']*)["\']/', $html, $m)) {
                $description = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            } elseif (preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']/', $html, $m)) {
                $description = html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
            }

            // og:image
            if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']*)["\']/', $html, $m)) {
                $image = $m[1];
            }

            return response()->json(['unfurl' => [
                'url'         => $url,
                'title'       => $title,
                'description' => $description,
                'image'       => $image,
            ]]);
        } catch (\Throwable $e) {
            return response()->json(['unfurl' => ['url' => $url, 'title' => null, 'description' => null, 'image' => null]]);
        }
    }

    // ─── Slash Command Handler ────────────────────────────────────────────

    private function handleSlashCommand(string $body, ChatConversation $conversation, $user): ?JsonResponse
    {
        $parts   = preg_split('/\s+/', trim($body), 2);
        $command = strtolower($parts[0]);
        $args    = $parts[1] ?? '';

        switch ($command) {
            case '/remind':
                // /remind 30m Review the report
                if (preg_match('/^(\d+)(m|h|d)\s+(.+)$/i', $args, $m)) {
                    $amount = (int) $m[1];
                    $unit   = strtolower($m[2]);
                    $note   = $m[3];

                    $remindAt = match ($unit) {
                        'm' => now()->addMinutes($amount),
                        'h' => now()->addHours($amount),
                        'd' => now()->addDays($amount),
                    };

                    ChatReminder::create([
                        'tenant_id'       => $user->tenant_id,
                        'user_id'         => $user->id,
                        'conversation_id' => $conversation->id,
                        'note'            => $note,
                        'remind_at'       => $remindAt,
                        'is_fired'        => false,
                    ]);

                    return $this->systemMessageResponse("Reminder set for {$remindAt->diffForHumans()}: {$note}", $conversation, $user);
                }
                return null;

            case '/status':
                // /status :smile: Working from home
                if (preg_match('/^(:[\w+-]+:)\s*(.*)$/', $args, $m)) {
                    $user->update([
                        'chat_status_emoji' => $m[1],
                        'chat_status_text'  => $m[2] ?: null,
                    ]);
                    return $this->systemMessageResponse("Status set to {$m[1]} {$m[2]}", $conversation, $user);
                } elseif (! empty($args)) {
                    $user->update(['chat_status_text' => $args]);
                    return $this->systemMessageResponse("Status set to: {$args}", $conversation, $user);
                }
                return null;

            case '/dnd':
                // /dnd 2h
                if (preg_match('/^(\d+)(m|h|d)$/i', $args, $m)) {
                    $amount = (int) $m[1];
                    $unit   = strtolower($m[2]);

                    $dndUntil = match ($unit) {
                        'm' => now()->addMinutes($amount),
                        'h' => now()->addHours($amount),
                        'd' => now()->addDays($amount),
                    };

                    $user->update(['chat_dnd_until' => $dndUntil]);
                    return $this->systemMessageResponse("Do Not Disturb enabled until {$dndUntil->diffForHumans()}", $conversation, $user);
                }
                return null;

            case '/topic':
                if (! empty($args) && in_array($conversation->type, ['group', 'channel'])) {
                    $conversation->update(['topic' => $args]);
                    return $this->systemMessageResponse("Topic changed to: {$args}", $conversation, $user);
                }
                return null;

            case '/poll':
                // /poll What for lunch? | Pizza | Sushi | Tacos
                $segments = array_map('trim', explode('|', $args));
                if (count($segments) >= 3) {
                    $question = $segments[0];
                    $options  = array_slice($segments, 1);

                    return DB::transaction(function () use ($question, $options, $conversation, $user) {
                        $message = ChatMessage::create([
                            'tenant_id'       => $conversation->tenant_id,
                            'conversation_id' => $conversation->id,
                            'sender_id'       => $user->id,
                            'body'            => $question,
                            'type'            => 'poll',
                            'delivery_status' => 'sent',
                        ]);

                        $poll = ChatPoll::create([
                            'message_id'      => $message->id,
                            'conversation_id' => $conversation->id,
                            'question'        => $question,
                            'allow_multiple'  => false,
                            'is_anonymous'    => false,
                            'is_closed'       => false,
                        ]);

                        foreach ($options as $i => $text) {
                            ChatPollOption::create([
                                'poll_id'    => $poll->id,
                                'text'       => $text,
                                'sort_order' => $i,
                            ]);
                        }

                        $conversation->update([
                            'last_message_at'      => now(),
                            'last_message_preview' => "Poll: {$question}",
                        ]);

                        $message->load(['sender', 'replyTo.sender', 'attachments', 'poll.options.votes']);

                        return response()->json(['message' => $this->formatMessage($message)]);
                    });
                }
                return null;

            default:
                return null;
        }
    }

    private function systemMessageResponse(string $text, ChatConversation $conversation, $user): JsonResponse
    {
        $message = ChatMessage::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => $text,
            'type'            => 'system',
            'delivery_status' => 'sent',
        ]);

        $message->load(['sender', 'replyTo.sender', 'attachments']);

        return response()->json(['message' => $this->formatMessage($message)]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CALLS (LiveKit)
    // ═══════════════════════════════════════════════════════════════════════════

    public function initiateCall(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'type' => 'required|in:audio,video',
        ]);

        // Prevent duplicate active calls in the same conversation
        $existing = ChatCall::where('conversation_id', $conversation->id)
            ->whereIn('status', ['ringing', 'active'])
            ->first();

        if ($existing) {
            return response()->json(['error' => 'There is already an active call in this conversation.'], 422);
        }

        $roomName = 'bankos-' . $conversation->id . '-' . time();

        $call = ChatCall::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'initiated_by'    => $user->id,
            'livekit_room_name' => $roomName,
            'type'            => $request->type,
            'status'          => 'ringing',
        ]);

        // Add all conversation participants to call participants
        $participants = ChatParticipant::where('conversation_id', $conversation->id)
            ->whereNull('left_at')
            ->get();

        foreach ($participants as $participant) {
            ChatCallParticipant::create([
                'call_id' => $call->id,
                'user_id' => $participant->user_id,
                'joined_at' => $participant->user_id === $user->id ? now() : null,
            ]);
        }

        // Generate LiveKit token for the caller
        $livekit = app(LiveKitService::class);
        $token = $livekit->generateToken($roomName, (string) $user->id, $user->name);

        // System message
        $typeLabel = $request->type === 'video' ? 'video' : 'voice';
        ChatMessage::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => "\xF0\x9F\x93\x9E {$user->name} started a {$typeLabel} call",
            'type'            => 'system',
            'delivery_status' => 'sent',
        ]);

        return response()->json([
            'call_id'   => $call->id,
            'token'     => $token,
            'ws_url'    => $livekit->getWsUrl(),
            'room_name' => $roomName,
        ]);
    }

    public function joinCall(string $callId): JsonResponse
    {
        $user = auth()->user();

        $call = ChatCall::findOrFail($callId);

        // Verify user is participant of the conversation
        $isParticipant = ChatParticipant::where('conversation_id', $call->conversation_id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->exists();

        if (! $isParticipant) {
            abort(403, 'You are not a participant of this conversation.');
        }

        if (in_array($call->status, ['ended', 'missed', 'declined'])) {
            return response()->json(['error' => 'This call has already ended.'], 422);
        }

        // Update or create call participant
        $callParticipant = ChatCallParticipant::where('call_id', $call->id)
            ->where('user_id', $user->id)
            ->first();

        if ($callParticipant) {
            $callParticipant->update(['joined_at' => now(), 'left_at' => null]);
        } else {
            ChatCallParticipant::create([
                'call_id'   => $call->id,
                'user_id'   => $user->id,
                'joined_at' => now(),
            ]);
        }

        // If call is still ringing, transition to active
        if ($call->status === 'ringing') {
            $call->update([
                'status'     => 'active',
                'started_at' => now(),
            ]);
        }

        $livekit = app(LiveKitService::class);
        $token = $livekit->generateToken($call->livekit_room_name, (string) $user->id, $user->name);

        return response()->json([
            'token'     => $token,
            'ws_url'    => $livekit->getWsUrl(),
            'room_name' => $call->livekit_room_name,
        ]);
    }

    public function leaveCall(string $callId): JsonResponse
    {
        $user = auth()->user();

        $call = ChatCall::findOrFail($callId);

        $callParticipant = ChatCallParticipant::where('call_id', $call->id)
            ->where('user_id', $user->id)
            ->first();

        if ($callParticipant) {
            $callParticipant->update(['left_at' => now()]);
        }

        // If no active participants remain, end the call
        $activeCount = ChatCallParticipant::where('call_id', $call->id)
            ->whereNotNull('joined_at')
            ->whereNull('left_at')
            ->count();

        if ($activeCount === 0 && in_array($call->status, ['active', 'ringing'])) {
            $duration = $call->started_at
                ? (int) now()->diffInSeconds($call->started_at)
                : 0;

            $call->update([
                'status'           => 'ended',
                'ended_at'         => now(),
                'duration_seconds' => $duration,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function declineCall(string $callId): JsonResponse
    {
        $user = auth()->user();

        $call = ChatCall::findOrFail($callId);

        if ($call->status !== 'ringing') {
            return response()->json(['error' => 'This call is no longer ringing.'], 422);
        }

        // Mark this participant's record
        ChatCallParticipant::where('call_id', $call->id)
            ->where('user_id', $user->id)
            ->update(['left_at' => now()]);

        // Check if all non-initiator participants have declined
        $pendingCount = ChatCallParticipant::where('call_id', $call->id)
            ->where('user_id', '!=', $call->initiated_by)
            ->whereNull('left_at')
            ->whereNull('joined_at')
            ->count();

        if ($pendingCount === 0) {
            $call->update([
                'status'   => 'declined',
                'ended_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function endCall(string $callId): JsonResponse
    {
        $user = auth()->user();

        $call = ChatCall::findOrFail($callId);

        if (in_array($call->status, ['ended', 'missed', 'declined'])) {
            return response()->json(['error' => 'This call has already ended.'], 422);
        }

        $duration = $call->started_at
            ? (int) now()->diffInSeconds($call->started_at)
            : 0;

        $call->update([
            'status'           => 'ended',
            'ended_at'         => now(),
            'duration_seconds' => $duration,
        ]);

        // Mark all active participants as left
        ChatCallParticipant::where('call_id', $call->id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function activeCall(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $call = ChatCall::where('conversation_id', $conversation->id)
            ->whereIn('status', ['ringing', 'active'])
            ->with(['initiatedBy', 'participants.user'])
            ->first();

        if (! $call) {
            return response()->json(['call' => null]);
        }

        return response()->json([
            'call' => [
                'id'           => $call->id,
                'type'         => $call->type,
                'status'       => $call->status,
                'room_name'    => $call->livekit_room_name,
                'initiated_by' => [
                    'id'   => $call->initiatedBy->id,
                    'name' => $call->initiatedBy->name,
                ],
                'participants' => $call->participants->map(fn($p) => [
                    'user_id'   => $p->user_id,
                    'name'      => $p->user->name,
                    'joined_at' => $p->joined_at,
                    'is_muted'  => $p->is_muted,
                ]),
                'started_at' => $call->started_at,
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CANVAS / DOCS
    // ═══════════════════════════════════════════════════════════════════════════

    public function createCanvas(Request $request, ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'nullable|array',
        ]);

        $canvas = ChatCanvas::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'title'           => $request->title,
            'content'         => $request->content,
            'created_by'      => $user->id,
            'last_edited_by'  => $user->id,
        ]);

        // System message
        ChatMessage::create([
            'tenant_id'       => $conversation->tenant_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id,
            'body'            => "\xF0\x9F\x93\x84 {$user->name} created a document: {$request->title}",
            'type'            => 'system',
            'delivery_status' => 'sent',
        ]);

        $canvas->load(['createdBy', 'lastEditedBy']);

        return response()->json(['canvas' => $canvas], 201);
    }

    public function listCanvas(ChatConversation $conversation): JsonResponse
    {
        $user = auth()->user();
        $this->abortIfNotParticipant($conversation, $user);

        $canvases = ChatCanvas::where('conversation_id', $conversation->id)
            ->with(['createdBy', 'lastEditedBy'])
            ->orderByDesc('updated_at')
            ->get()
            ->map(fn(ChatCanvas $c) => [
                'id'             => $c->id,
                'title'          => $c->title,
                'is_shared'      => $c->is_shared,
                'created_by'     => $c->createdBy ? ['id' => $c->createdBy->id, 'name' => $c->createdBy->name] : null,
                'last_edited_by' => $c->lastEditedBy ? ['id' => $c->lastEditedBy->id, 'name' => $c->lastEditedBy->name] : null,
                'updated_at'     => $c->updated_at->diffForHumans(),
            ]);

        return response()->json(['canvases' => $canvases]);
    }

    public function getCanvas(string $canvasId): JsonResponse
    {
        $canvas = ChatCanvas::with(['createdBy', 'lastEditedBy'])->findOrFail($canvasId);

        return response()->json(['canvas' => $canvas]);
    }

    public function updateCanvas(Request $request, string $canvasId): JsonResponse
    {
        $user = auth()->user();

        $canvas = ChatCanvas::findOrFail($canvasId);

        $request->validate([
            'title'   => 'sometimes|string|max:255',
            'content' => 'sometimes|nullable|array',
        ]);

        $canvas->update(array_merge(
            $request->only(['title', 'content']),
            ['last_edited_by' => $user->id]
        ));

        $canvas->load(['createdBy', 'lastEditedBy']);

        return response()->json(['canvas' => $canvas]);
    }

    public function deleteCanvas(string $canvasId): JsonResponse
    {
        $canvas = ChatCanvas::findOrFail($canvasId);
        $canvas->delete();

        return response()->json(['success' => true]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // WORKFLOWS
    // ═══════════════════════════════════════════════════════════════════════════

    public function listWorkflows(Request $request): JsonResponse
    {
        $user = auth()->user();

        $workflows = ChatWorkflow::where('tenant_id', $user->tenant_id)
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn(ChatWorkflow $w) => [
                'id'              => $w->id,
                'name'            => $w->name,
                'description'     => $w->description,
                'is_active'       => $w->is_active,
                'trigger'         => $w->trigger,
                'steps'           => $w->steps,
                'conversation_id' => $w->conversation_id,
                'run_count'       => $w->run_count,
                'last_run_at'     => $w->last_run_at?->diffForHumans(),
                'created_by'      => $w->createdBy ? ['id' => $w->createdBy->id, 'name' => $w->createdBy->name] : null,
                'created_at'      => $w->created_at->diffForHumans(),
            ]);

        return response()->json(['workflows' => $workflows]);
    }

    public function createWorkflow(Request $request): JsonResponse
    {
        $user = auth()->user();

        $request->validate([
            'name'            => 'required|string|max:150',
            'description'     => 'nullable|string|max:1000',
            'trigger'         => 'required|array',
            'trigger.type'    => 'required|string|in:message_contains,message_from,new_member,keyword,scheduled',
            'trigger.config'  => 'nullable',
            'steps'           => 'required|array|min:1',
            'steps.*.action'  => 'required|string|in:send_message,create_task,add_reaction,notify_user,webhook',
            'steps.*.config'  => 'nullable',
            'conversation_id' => 'nullable',
        ]);

        $workflow = ChatWorkflow::create([
            'tenant_id'       => $user->tenant_id,
            'name'            => $request->name,
            'description'     => $request->description,
            'created_by'      => $user->id,
            'trigger'         => $request->trigger,
            'steps'           => $request->steps,
            'conversation_id' => $request->conversation_id,
        ]);

        $workflow->load('createdBy');

        return response()->json(['workflow' => $workflow], 201);
    }

    public function updateWorkflow(Request $request, string $workflowId): JsonResponse
    {
        $workflow = ChatWorkflow::findOrFail($workflowId);

        $request->validate([
            'name'            => 'sometimes|string|max:150',
            'description'     => 'nullable|string|max:1000',
            'trigger'         => 'sometimes|array',
            'trigger.type'    => 'required_with:trigger|string|in:message_contains,message_from,new_member,keyword,scheduled',
            'trigger.config'  => 'nullable',
            'steps'           => 'sometimes|array|min:1',
            'steps.*.action'  => 'required_with:steps|string|in:send_message,create_task,add_reaction,notify_user,webhook',
            'steps.*.config'  => 'nullable',
            'conversation_id' => 'nullable',
        ]);

        $workflow->update($request->only([
            'name', 'description', 'trigger', 'steps', 'conversation_id',
        ]));

        $workflow->load('createdBy');

        return response()->json(['workflow' => $workflow]);
    }

    public function deleteWorkflow(string $workflowId): JsonResponse
    {
        $workflow = ChatWorkflow::findOrFail($workflowId);
        $workflow->delete();

        return response()->json(['success' => true]);
    }

    public function toggleWorkflow(string $workflowId): JsonResponse
    {
        $workflow = ChatWorkflow::findOrFail($workflowId);
        $workflow->update(['is_active' => ! $workflow->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $workflow->fresh()->is_active,
        ]);
    }

    public function runWorkflow(string $workflowId): JsonResponse
    {
        $user = auth()->user();

        $workflow = ChatWorkflow::findOrFail($workflowId);

        $run = ChatWorkflowRun::create([
            'workflow_id'         => $workflow->id,
            'triggered_by_user_id' => $user->id,
            'status'              => 'running',
        ]);

        $stepResults = [];
        $error = null;

        try {
            foreach ($workflow->steps ?? [] as $index => $step) {
                $result = $this->executeWorkflowStep($step, $workflow, $user);
                $stepResults[] = [
                    'step'   => $index,
                    'action' => $step['action'],
                    'status' => 'completed',
                    'result' => $result,
                ];
            }

            $run->update([
                'status'       => 'completed',
                'step_results' => $stepResults,
            ]);

            $workflow->update([
                'run_count'   => $workflow->run_count + 1,
                'last_run_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $run->update([
                'status'       => 'failed',
                'step_results' => $stepResults,
                'error'        => $error,
            ]);
        }

        return response()->json([
            'run' => $run->fresh(),
        ]);
    }

    public function workflowRuns(string $workflowId): JsonResponse
    {
        $workflow = ChatWorkflow::findOrFail($workflowId);

        $runs = ChatWorkflowRun::where('workflow_id', $workflow->id)
            ->with('triggeredByUser')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn(ChatWorkflowRun $r) => [
                'id'           => $r->id,
                'status'       => $r->status,
                'step_results' => $r->step_results,
                'error'        => $r->error,
                'triggered_by' => $r->triggeredByUser ? ['id' => $r->triggeredByUser->id, 'name' => $r->triggeredByUser->name] : null,
                'created_at'   => $r->created_at->diffForHumans(),
            ]);

        return response()->json(['runs' => $runs]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // WORKFLOW ENGINE (private)
    // ═══════════════════════════════════════════════════════════════════════════

    private function checkWorkflows(ChatMessage $message, ChatConversation $conversation): void
    {
        if ($message->type === 'system') {
            return;
        }

        $workflows = ChatWorkflow::where('tenant_id', $conversation->tenant_id)
            ->where('is_active', true)
            ->where(function ($q) use ($conversation) {
                $q->whereNull('conversation_id')
                  ->orWhere('conversation_id', $conversation->id);
            })
            ->get();

        foreach ($workflows as $workflow) {
            if ($this->workflowTriggerMatches($workflow, $message, $conversation)) {
                $this->executeWorkflow($workflow, $message);
            }
        }
    }

    private function workflowTriggerMatches(ChatWorkflow $workflow, ChatMessage $message, ChatConversation $conversation): bool
    {
        $trigger = $workflow->trigger;
        if (! $trigger || ! isset($trigger['type'])) {
            return false;
        }

        $body = $message->body ?? '';
        $triggerValue = $trigger['config'] ?? $trigger['value'] ?? '';

        return match ($trigger['type']) {
            'message_contains' => ! empty($triggerValue) && stripos($body, $triggerValue) !== false,
            'keyword'          => ! empty($triggerValue) && preg_match('/\b' . preg_quote($triggerValue, '/') . '\b/i', $body),
            'message_from'     => ! empty($triggerValue) && (string) $message->sender_id === (string) $triggerValue,
            'new_member'       => false, // Handled elsewhere (addParticipants)
            'scheduled'        => false, // Handled by scheduler
            default            => false,
        };
    }

    private function executeWorkflow(ChatWorkflow $workflow, ChatMessage $message): void
    {
        $run = ChatWorkflowRun::create([
            'workflow_id'            => $workflow->id,
            'triggered_by_message_id' => $message->id,
            'triggered_by_user_id'   => $message->sender_id,
            'status'                 => 'running',
        ]);

        $stepResults = [];

        try {
            $sender = $message->sender ?? User::find($message->sender_id);

            foreach ($workflow->steps ?? [] as $index => $step) {
                $result = $this->executeWorkflowStep($step, $workflow, $sender);
                $stepResults[] = [
                    'step'   => $index,
                    'action' => $step['action'],
                    'status' => 'completed',
                    'result' => $result,
                ];
            }

            $run->update([
                'status'       => 'completed',
                'step_results' => $stepResults,
            ]);

            $workflow->update([
                'run_count'   => $workflow->run_count + 1,
                'last_run_at' => now(),
            ]);
        } catch (\Throwable $e) {
            $run->update([
                'status'       => 'failed',
                'step_results' => $stepResults,
                'error'        => $e->getMessage(),
            ]);
        }
    }

    private function executeWorkflowStep(array $step, ChatWorkflow $workflow, $user): ?string
    {
        $action = $step['action'] ?? null;
        $config = $step['config'] ?? [];

        return match ($action) {
            'send_message' => $this->workflowSendMessage($workflow, $config),
            'create_task'  => $this->workflowCreateTask($workflow, $config, $user),
            'add_reaction' => $this->workflowAddReaction($config),
            'notify_user'  => $this->workflowNotifyUser($config),
            'webhook'      => $this->workflowWebhook($config),
            default        => 'unknown_action',
        };
    }

    private function workflowSendMessage(ChatWorkflow $workflow, array $config): string
    {
        $conversationId = $config['conversation_id'] ?? $workflow->conversation_id;
        if (! $conversationId) {
            return 'no_conversation';
        }

        $body = $config['message'] ?? $config['body'] ?? 'Automated message';

        ChatMessage::create([
            'tenant_id'       => $workflow->tenant_id,
            'conversation_id' => $conversationId,
            'sender_id'       => $workflow->created_by,
            'body'            => $body,
            'type'            => 'system',
            'delivery_status' => 'sent',
        ]);

        return 'message_sent';
    }

    private function workflowCreateTask(ChatWorkflow $workflow, array $config, $user): string
    {
        $conversationId = $config['conversation_id'] ?? $workflow->conversation_id;
        if (! $conversationId) {
            return 'no_conversation';
        }

        ChatTask::create([
            'tenant_id'       => $workflow->tenant_id,
            'conversation_id' => $conversationId,
            'title'           => $config['title'] ?? 'Auto-created task',
            'assigned_to'     => $config['assigned_to'] ?? $user->id,
            'created_by'      => $workflow->created_by,
            'status'          => 'open',
            'due_at'          => isset($config['due_hours']) ? now()->addHours((int) $config['due_hours']) : null,
        ]);

        return 'task_created';
    }

    private function workflowAddReaction(array $config): string
    {
        // Reaction adding is contextual to the triggering message
        // This is a placeholder; full implementation would need message context
        return 'reaction_noted';
    }

    private function workflowNotifyUser(array $config): string
    {
        // Placeholder for notification integration
        $userId = $config['user_id'] ?? null;
        $message = $config['message'] ?? 'Workflow notification';

        if ($userId) {
            // Could integrate with the notification system here
            return 'user_notified';
        }

        return 'no_user_specified';
    }

    private function workflowWebhook(array $config): string
    {
        $url = $config['url'] ?? null;
        if (! $url) {
            return 'no_url';
        }

        try {
            $payload = $config['payload'] ?? [];
            \Illuminate\Support\Facades\Http::timeout(10)->post($url, $payload);
            return 'webhook_sent';
        } catch (\Throwable $e) {
            return 'webhook_failed: ' . $e->getMessage();
        }
    }
}
