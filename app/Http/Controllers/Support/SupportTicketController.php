<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTicketReply;
use App\Models\Support\SupportTeam;
use App\Models\Support\SupportCategory;
use App\Models\Support\SupportSlaPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('tenant_id');
        $query = SupportTicket::where('tenant_id', $tenantId)
            ->with('assignedTo','team','category')
            ->orderByDesc('created_at');

        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('priority')) $query->where('priority', $request->priority);
        if ($request->filled('team_id'))  $query->where('team_id', $request->team_id);
        if ($request->filled('assigned_to')) {
            $request->assigned_to === 'me'
                ? $query->where('assigned_to', auth()->id())
                : $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('subject', 'like', "%{$s}%")
                ->orWhere('ticket_number', 'like', "%{$s}%")
                ->orWhere('requester_name', 'like', "%{$s}%"));
        }

        $tickets    = $query->paginate(25)->withQueryString();
        $teams      = SupportTeam::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $categories = SupportCategory::where('tenant_id', $tenantId)->where('is_active', true)->get();

        return view('support.tickets.index', compact('tickets','teams','categories'));
    }

    public function create()
    {
        $tenantId   = session('tenant_id');
        $teams      = SupportTeam::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $categories = SupportCategory::where('tenant_id', $tenantId)->where('is_active', true)->get();
        return view('support.tickets.create', compact('teams','categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'        => 'required|string|max:300',
            'description'    => 'required|string',
            'priority'       => 'required|in:low,medium,high,critical',
            'channel'        => 'required|string',
            'requester_name' => 'required|string|max:200',
        ]);

        $tenantId = session('tenant_id');
        $number   = $this->nextTicketNumber($tenantId);

        // Auto-assign SLA based on priority
        $sla = SupportSlaPolicy::where('tenant_id', $tenantId)
            ->where('priority', $request->priority)->first()
            ?? SupportSlaPolicy::where('tenant_id', $tenantId)->where('is_default', true)->first();

        $ticket = SupportTicket::create([
            'id'                    => Str::uuid(),
            'tenant_id'             => $tenantId,
            'ticket_number'         => $number,
            'subject'               => $request->subject,
            'description'           => $request->description,
            'channel'               => $request->channel,
            'priority'              => $request->priority,
            'status'                => 'open',
            'category_id'           => $request->category_id,
            'team_id'               => $request->team_id,
            'assigned_to'           => $request->assigned_to,
            'created_by'            => auth()->id(),
            'requester_type'        => $request->requester_type ?? 'customer',
            'requester_name'        => $request->requester_name,
            'requester_email'       => $request->requester_email,
            'requester_phone'       => $request->requester_phone,
            'account_number'        => $request->account_number,
            'sla_policy_id'         => $sla?->id,
            'sla_response_due_at'   => $sla ? now()->addMinutes($sla->response_minutes) : null,
            'sla_resolution_due_at' => $sla ? now()->addMinutes($sla->resolution_minutes) : null,
        ]);

        return redirect()->route('support.tickets.show', $ticket)->with('success', "Ticket {$number} created.");
    }

    public function show(SupportTicket $ticket)
    {
        abort_unless($ticket->tenant_id === session('tenant_id'), 403);
        $ticket->load('assignedTo','team','category','slaPolicy','replies.author','createdBy');

        $tenantId = session('tenant_id');
        $teams    = SupportTeam::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $agents   = \App\Models\User::where('tenant_id', $tenantId)->get();

        return view('support.tickets.show', compact('ticket','teams','agents'));
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->tenant_id === session('tenant_id'), 403);
        $request->validate(['body' => 'required|string']);

        $isInternal = $request->boolean('is_internal');

        SupportTicketReply::create([
            'id'         => Str::uuid(),
            'ticket_id'  => $ticket->id,
            'author_id'  => auth()->id(),
            'body'       => $request->body,
            'type'       => 'reply',
            'is_internal'=> $isInternal,
        ]);

        // Mark first response time
        if (! $ticket->first_responded_at && ! $isInternal) {
            $ticket->update(['first_responded_at' => now()]);
        }

        // Status transition
        if ($request->filled('status') && $request->status !== $ticket->status) {
            $updates = ['status' => $request->status];
            if ($request->status === 'resolved') $updates['resolved_at'] = now();
            if ($request->status === 'closed')   $updates['closed_at']   = now();
            $ticket->update($updates);
        }

        return back()->with('success', 'Reply posted.');
    }

    public function assign(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->tenant_id === session('tenant_id'), 403);
        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'team_id'     => $request->team_id ?? $ticket->team_id,
            'status'      => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
        ]);

        SupportTicketReply::create([
            'id'          => Str::uuid(),
            'ticket_id'   => $ticket->id,
            'author_id'   => auth()->id(),
            'body'        => 'Ticket assigned to ' . ($ticket->assignedTo?->name ?? 'agent'),
            'type'        => 'assignment',
            'is_internal' => true,
        ]);

        return back()->with('success', 'Ticket assigned.');
    }

    public function escalate(Request $request, SupportTicket $ticket)
    {
        abort_unless($ticket->tenant_id === session('tenant_id'), 403);
        $request->validate(['escalated_to' => 'required|exists:users,id']);

        $ticket->update([
            'escalation_level' => $ticket->escalation_level + 1,
            'escalated_at'     => now(),
            'escalated_to'     => $request->escalated_to,
        ]);

        SupportTicketReply::create([
            'id'          => Str::uuid(),
            'ticket_id'   => $ticket->id,
            'author_id'   => auth()->id(),
            'body'        => 'Ticket escalated. Reason: ' . ($request->reason ?? 'SLA breach'),
            'type'        => 'status_change',
            'is_internal' => true,
        ]);

        return back()->with('success', 'Ticket escalated.');
    }

    public function resolve(SupportTicket $ticket)
    {
        abort_unless($ticket->tenant_id === session('tenant_id'), 403);
        $ticket->update(['status' => 'resolved', 'resolved_at' => now()]);
        return back()->with('success', 'Ticket resolved.');
    }

    public function close(SupportTicket $ticket)
    {
        abort_unless($ticket->tenant_id === session('tenant_id'), 403);
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);
        return back()->with('success', 'Ticket closed.');
    }

    private function nextTicketNumber(string $tenantId): string
    {
        $year  = now()->year;
        $count = SupportTicket::where('tenant_id', $tenantId)
                     ->whereYear('created_at', $year)->count() + 1;
        return 'TKT-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
