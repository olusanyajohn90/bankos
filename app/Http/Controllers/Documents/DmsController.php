<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Documents\DocumentWorkflow;
use App\Models\Documents\DocumentWorkflowStep;
use App\Models\Documents\DocumentWorkflowInstance;
use App\Models\Documents\DocumentWorkflowAction;
use App\Models\Documents\DocumentNote;
use App\Models\Documents\DocumentSignature;
use App\Models\Documents\DocumentFolder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DmsController extends Controller
{
    // ── Workflow Templates ───────────────────────────────────────────────────

    public function workflowsIndex()
    {
        $tenantId  = session('tenant_id');
        $workflows = DocumentWorkflow::where('tenant_id', $tenantId)
            ->withCount('instances')
            ->with('steps')
            ->get();
        $users = User::where('tenant_id', $tenantId)->orderBy('name')->get();

        return view('documents.workflows.index', compact('workflows', 'users'));
    }

    public function workflowsStore(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:200',
            'steps'   => 'required|array|min:1',
            'steps.*.name'        => 'required|string',
            'steps.*.action_type' => 'required|in:approve,sign,review,acknowledge',
        ]);

        $workflow = DocumentWorkflow::create([
            'id'                       => Str::uuid(),
            'tenant_id'                => session('tenant_id'),
            'name'                     => $request->name,
            'description'              => $request->description,
            'trigger_category'         => $request->trigger_category,
            'is_active'                => true,
            'requires_all_signatures'  => $request->boolean('requires_all_signatures'),
        ]);

        foreach ($request->steps as $order => $step) {
            DocumentWorkflowStep::create([
                'id'               => Str::uuid(),
                'workflow_id'      => $workflow->id,
                'step_order'       => $order + 1,
                'name'             => $step['name'],
                'action_type'      => $step['action_type'],
                'assignee_type'    => $step['assignee_type'] ?? 'user',
                'assignee_user_id' => $step['assignee_user_id'] ?? null,
                'assignee_role'    => $step['assignee_role'] ?? null,
                'deadline_hours'   => $step['deadline_hours'] ?? null,
                'is_optional'      => isset($step['is_optional']),
            ]);
        }

        return back()->with('success', 'Workflow created.');
    }

    public function workflowsToggle(DocumentWorkflow $documentWorkflow)
    {
        $documentWorkflow->update(['is_active' => ! $documentWorkflow->is_active]);
        return back()->with('success', 'Workflow ' . ($documentWorkflow->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function workflowsDestroy(DocumentWorkflow $documentWorkflow)
    {
        if ($documentWorkflow->instances()->where('status', 'in_progress')->exists()) {
            return back()->with('error', 'Cannot delete workflow with active instances.');
        }
        $documentWorkflow->steps()->delete();
        $documentWorkflow->delete();
        return back()->with('success', 'Workflow deleted.');
    }

    // ── Initiate workflow on a document ─────────────────────────────────────

    public function initiate(Request $request, Document $document)
    {
        $request->validate(['workflow_id' => 'required|exists:document_workflows,id']);

        $workflow = DocumentWorkflow::with('steps')->findOrFail($request->workflow_id);

        $instance = DocumentWorkflowInstance::create([
            'id'                  => Str::uuid(),
            'document_id'         => $document->id,
            'workflow_id'         => $workflow->id,
            'initiated_by'        => auth()->id(),
            'status'              => 'in_progress',
            'current_step_order'  => 1,
            'notes'               => $request->notes,
            'started_at'          => now(),
        ]);

        $firstStep = $workflow->steps->first();
        if ($firstStep) {
            $assigneeId = $firstStep->assignee_user_id ?? auth()->id();
            DocumentWorkflowAction::create([
                'id'          => Str::uuid(),
                'instance_id' => $instance->id,
                'step_id'     => $firstStep->id,
                'assignee_id' => $assigneeId,
                'status'      => 'pending',
                'deadline_at' => $firstStep->deadline_hours ? now()->addHours($firstStep->deadline_hours) : null,
            ]);
        }

        return back()->with('success', 'Workflow initiated.');
    }

    // ── Act on a workflow step ───────────────────────────────────────────────

    public function act(Request $request, DocumentWorkflowAction $action)
    {
        $request->validate([
            'decision' => 'required|in:approved,rejected,signed,acknowledged',
            'notes'    => 'nullable|string|max:1000',
        ]);

        $action->update([
            'actor_id' => auth()->id(),
            'status'   => $request->decision,
            'notes'    => $request->notes,
            'acted_at' => now(),
        ]);

        $instance = $action->instance()->with('workflow.steps')->first();

        if ($request->decision === 'rejected') {
            $instance->update(['status' => 'rejected', 'completed_at' => now()]);
            return back()->with('success', 'Document rejected.');
        }

        // Advance to next step
        $nextOrder = $instance->current_step_order + 1;
        $nextStep  = $instance->workflow->steps->where('step_order', $nextOrder)->first();

        if ($nextStep) {
            $instance->update(['current_step_order' => $nextOrder]);
            $assigneeId = $nextStep->assignee_user_id ?? auth()->id();
            DocumentWorkflowAction::create([
                'id'          => Str::uuid(),
                'instance_id' => $instance->id,
                'step_id'     => $nextStep->id,
                'assignee_id' => $assigneeId,
                'status'      => 'pending',
                'deadline_at' => $nextStep->deadline_hours ? now()->addHours($nextStep->deadline_hours) : null,
            ]);
        } else {
            $instance->update(['status' => 'completed', 'completed_at' => now()]);
            // Update document status to approved
            $instance->document()->update(['status' => 'approved', 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        }

        return back()->with('success', 'Action recorded.');
    }

    // ── Notes ────────────────────────────────────────────────────────────────

    public function addNote(Request $request, Document $document)
    {
        $request->validate(['body' => 'required|string|max:5000']);

        DocumentNote::create([
            'id'          => Str::uuid(),
            'document_id' => $document->id,
            'author_id'   => auth()->id(),
            'body'        => $request->body,
            'is_internal' => $request->boolean('is_internal'),
            'parent_id'   => $request->parent_id,
        ]);

        return back()->with('success', 'Note added.');
    }

    public function deleteNote(DocumentNote $documentNote)
    {
        abort_unless($documentNote->author_id === auth()->id(), 403);
        $documentNote->delete();
        return back()->with('success', 'Note deleted.');
    }

    // ── Signatures ───────────────────────────────────────────────────────────

    public function sign(Request $request, Document $document)
    {
        $request->validate([
            'signature_data' => 'required|string',
            'signature_type' => 'required|in:drawn,typed',
        ]);

        DocumentSignature::create([
            'id'             => Str::uuid(),
            'document_id'    => $document->id,
            'signer_id'      => auth()->id(),
            'signature_data' => $request->signature_data,
            'signature_type' => $request->signature_type,
            'ip_address'     => $request->ip(),
            'user_agent'     => $request->userAgent(),
            'is_valid'       => true,
            'signed_at'      => now(),
        ]);

        return back()->with('success', 'Document signed.');
    }

    // ── Folders ──────────────────────────────────────────────────────────────

    public function foldersStore(Request $request)
    {
        $request->validate(['name' => 'required|string|max:200']);
        DocumentFolder::create([
            'id'        => Str::uuid(),
            'tenant_id' => session('tenant_id'),
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
            'icon'      => $request->icon ?? 'folder',
        ]);
        return back()->with('success', 'Folder created.');
    }

    // ── My pending actions ───────────────────────────────────────────────────

    public function myActions()
    {
        $pending = DocumentWorkflowAction::where('assignee_id', auth()->id())
            ->where('status', 'pending')
            ->with(['instance.document', 'instance.workflow', 'step'])
            ->orderBy('deadline_at')
            ->paginate(20);

        return view('documents.my-actions', compact('pending'));
    }
}
