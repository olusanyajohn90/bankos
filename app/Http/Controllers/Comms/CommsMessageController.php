<?php

namespace App\Http\Controllers\Comms;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CommsAttachment;
use App\Models\CommsMessage;
use App\Models\Department;
use App\Models\Team;
use App\Models\User;
use App\Services\Comms\CommsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class CommsMessageController extends Controller
{
    public function __construct(protected CommsService $commsService) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $user     = auth()->user();

        $query = CommsMessage::where('tenant_id', $tenantId)
            ->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere(function ($sq) use ($user) {
                      if ($user->hasAnyRole(['super_admin', 'admin', 'hr_manager', 'branch_manager'])) {
                          $sq->whereNotNull('id'); // all messages for admins
                      }
                  });
            })
            ->withCount(['recipients', 'attachments'])
            ->with('sender');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $messages = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Summary counts
        $draftCount     = CommsMessage::where('tenant_id', $tenantId)->where('status', 'draft')->count();
        $publishedCount = CommsMessage::where('tenant_id', $tenantId)->where('status', 'published')->count();
        $archivedCount  = CommsMessage::where('tenant_id', $tenantId)->where('status', 'archived')->count();

        return view('comms.index', compact(
            'messages',
            'draftCount',
            'publishedCount',
            'archivedCount'
        ));
    }

    public function create()
    {
        $tenantId    = auth()->user()->tenant_id;
        $branches    = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();
        $departments = Department::where('tenant_id', $tenantId)->orderBy('name')->get();
        $teams       = Team::where('tenant_id', $tenantId)->orderBy('name')->get();
        $roles       = Role::where('guard_name', 'web')->orderBy('name')->get();
        $users       = User::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('comms.compose', compact('branches', 'departments', 'teams', 'roles', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
            'type'         => 'required|in:memo,circular,announcement',
            'priority'     => 'required|in:normal,urgent,critical',
            'requires_ack' => 'boolean',
            'ack_deadline' => 'nullable|date',
            'scope_type'   => 'required|in:all,branch,department,team,role,individual',
            'scope_id'     => 'nullable|integer',
            'attachments'  => 'nullable|array',
            'attachments.*'=> 'file|max:10240',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $message = CommsMessage::create([
            'tenant_id'    => $tenantId,
            'subject'      => $request->subject,
            'body'         => $request->body,
            'type'         => $request->type,
            'priority'     => $request->priority,
            'requires_ack' => $request->boolean('requires_ack'),
            'ack_deadline' => $request->ack_deadline,
            'sender_id'    => auth()->id(),
            'scope_type'   => $request->scope_type,
            'scope_id'     => $request->scope_id,
            'status'       => 'draft',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("comms/attachments/{$tenantId}", 'local');

                CommsAttachment::create([
                    'message_id'   => $message->id,
                    'file_name'    => $file->getClientOriginalName(),
                    'file_path'    => $path,
                    'mime_type'    => $file->getMimeType(),
                    'file_size_kb' => (int) round($file->getSize() / 1024),
                ]);
            }
        }

        // If publish flag was passed, publish immediately
        if ($request->boolean('publish')) {
            $this->commsService->publish($message);
            return redirect()->route('comms.messages.index')
                ->with('success', 'Message published successfully.');
        }

        return redirect()->route('comms.messages.edit', $message)
            ->with('success', 'Draft saved. Review and publish when ready.');
    }

    public function edit(CommsMessage $message)
    {
        $tenantId    = auth()->user()->tenant_id;
        $branches    = Branch::where('tenant_id', $tenantId)->orderBy('name')->get();
        $departments = Department::where('tenant_id', $tenantId)->orderBy('name')->get();
        $teams       = Team::where('tenant_id', $tenantId)->orderBy('name')->get();
        $roles       = Role::where('guard_name', 'web')->orderBy('name')->get();
        $users       = User::where('tenant_id', $tenantId)->orderBy('name')->get();

        $message->load('attachments');

        return view('comms.compose', compact('message', 'branches', 'departments', 'teams', 'roles', 'users'));
    }

    public function update(Request $request, CommsMessage $message)
    {
        abort_unless($message->status === 'draft', 403, 'Only draft messages can be edited.');

        $request->validate([
            'subject'      => 'required|string|max:255',
            'body'         => 'required|string',
            'type'         => 'required|in:memo,circular,announcement',
            'priority'     => 'required|in:normal,urgent,critical',
            'requires_ack' => 'boolean',
            'ack_deadline' => 'nullable|date',
            'scope_type'   => 'required|in:all,branch,department,team,role,individual',
            'scope_id'     => 'nullable|integer',
            'attachments'  => 'nullable|array',
            'attachments.*'=> 'file|max:10240',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $message->update([
            'subject'      => $request->subject,
            'body'         => $request->body,
            'type'         => $request->type,
            'priority'     => $request->priority,
            'requires_ack' => $request->boolean('requires_ack'),
            'ack_deadline' => $request->ack_deadline,
            'scope_type'   => $request->scope_type,
            'scope_id'     => $request->scope_id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("comms/attachments/{$tenantId}", 'local');

                CommsAttachment::create([
                    'message_id'   => $message->id,
                    'file_name'    => $file->getClientOriginalName(),
                    'file_path'    => $path,
                    'mime_type'    => $file->getMimeType(),
                    'file_size_kb' => (int) round($file->getSize() / 1024),
                ]);
            }
        }

        if ($request->boolean('publish')) {
            $this->commsService->publish($message);
            return redirect()->route('comms.messages.index')
                ->with('success', 'Message published successfully.');
        }

        return back()->with('success', 'Draft updated successfully.');
    }

    public function publish(Request $request, CommsMessage $message)
    {
        abort_if($message->status === 'published', 409, 'Message is already published.');

        $this->commsService->publish($message);

        return redirect()->route('comms.messages.index')
            ->with('success', 'Message published and delivered to recipients.');
    }

    public function archive(CommsMessage $message)
    {
        $message->update([
            'status'      => 'archived',
            'archived_at' => now(),
        ]);

        return back()->with('success', 'Message archived.');
    }

    public function recipients(Request $request, CommsMessage $message)
    {
        $recipients = $message->recipients()
            ->with('user:id,name,email')
            ->paginate(50);

        $data = $recipients->through(fn ($r) => [
            'id'       => $r->id,
            'name'     => $r->user?->name ?? 'Unknown',
            'email'    => $r->user?->email,
            'read_at'  => $r->read_at?->toIso8601String(),
            'ack_at'   => $r->ack_at?->toIso8601String(),
            'ack_note' => $r->ack_note,
        ]);

        return response()->json($data);
    }
}
