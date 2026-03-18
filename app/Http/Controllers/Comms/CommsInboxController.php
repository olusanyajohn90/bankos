<?php

namespace App\Http\Controllers\Comms;

use App\Http\Controllers\Controller;
use App\Models\CommsMessage;
use App\Models\CommsRecipient;
use App\Services\Comms\CommsService;
use Illuminate\Http\Request;

class CommsInboxController extends Controller
{
    public function __construct(protected CommsService $commsService) {}

    public function index(Request $request)
    {
        $userId   = auth()->id();
        $tenantId = auth()->user()->tenant_id;

        // Fetch all recipient records for this user with published messages for this tenant
        $allRecipients = CommsRecipient::where('user_id', $userId)
            ->whereHas('message', fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'published'))
            ->with(['message.sender'])
            ->get();

        $priorityOrder = ['critical' => 0, 'urgent' => 1, 'normal' => 2];

        $unread = $allRecipients->filter(fn ($r) => is_null($r->read_at))
            ->sortBy([
                fn ($a, $b) => ($priorityOrder[$a->message->priority] ?? 2) <=> ($priorityOrder[$b->message->priority] ?? 2),
                fn ($a, $b) => $b->message->published_at <=> $a->message->published_at,
            ])
            ->values();

        $read = $allRecipients->filter(fn ($r) => ! is_null($r->read_at))
            ->sortByDesc(fn ($r) => $r->message->published_at)
            ->values();

        return view('comms.inbox', compact('unread', 'read'));
    }

    public function show(Request $request, CommsMessage $message)
    {
        $userId = auth()->id();

        // Ensure user is a recipient
        $recipient = CommsRecipient::where('message_id', $message->id)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Mark as read
        $this->commsService->markRead($message, auth()->user());

        $message->load(['attachments', 'sender']);

        // Reload recipient to reflect updated read_at
        $recipient->refresh();

        return view('comms.inbox-show', compact('message', 'recipient'));
    }

    public function acknowledge(Request $request, CommsMessage $message)
    {
        $request->validate([
            'ack_note' => 'nullable|string|max:500',
        ]);

        $this->commsService->acknowledge($message, auth()->user(), $request->ack_note);

        return back()->with('success', 'Message acknowledged successfully.');
    }
}
