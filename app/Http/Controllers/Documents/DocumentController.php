<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\Documents\DocumentService;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function __construct(protected DocumentService $documentService) {}

    public function index(Request $request)
    {
        $tenantId         = auth()->user()->tenant_id;
        $documentableType = $request->query('documentable_type');
        $documentableId   = $request->query('documentable_id');

        // ── Standalone DMS hub (no entity context) ──────────────────────────
        if (! $documentableType || ! $documentableId) {
            return $this->hub($request, $tenantId);
        }

        $query = Document::where('tenant_id', $tenantId)
            ->where('documentable_type', $documentableType)
            ->where('documentable_id', $documentableId)
            ->with(['uploadedBy', 'reviewedByUser']);

        if (! $request->boolean('show_all')) {
            $query->where('is_current_version', true);
        }

        if ($request->filled('category')) {
            $query->where('document_category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documents = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Resolve entity model instance for display
        $entityClass = '\\' . ltrim($documentableType, '\\');
        $entity      = null;
        if (class_exists($entityClass)) {
            $entity = $entityClass::find($documentableId);
        }

        // Map entity_type string for checklist lookup
        $entityTypeMap = [
            'App\Models\Customer'     => 'customer',
            'App\Models\Loan'         => 'loan',
            'App\Models\Account'      => 'account',
            'App\Models\StaffProfile' => 'staff_profile',
            'App\Models\Branch'       => 'branch',
        ];
        $entityType    = $entityTypeMap[$documentableType] ?? null;
        $checklistStatus = $entityType
            ? $this->documentService->checklistStatus($entityType, $documentableId, $tenantId)
            : [];

        return view('documents.index', compact(
            'documents',
            'checklistStatus',
            'documentableType',
            'documentableId',
            'entity'
        ));
    }

    private function hub(Request $request, string $tenantId)
    {
        $query = Document::where('tenant_id', $tenantId)
            ->where('is_current_version', true)
            ->with(['uploadedBy', 'reviewedByUser']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($s) => $s->where('title', 'like', "%$q%")->orWhere('document_type', 'like', "%$q%"));
        }
        if ($request->filled('status'))   $query->where('status', $request->status);
        if ($request->filled('category')) $query->where('document_category', $request->category);
        if ($request->filled('type'))     $query->where('document_type', $request->type);

        $documents = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        $stats = [
            'total'          => Document::where('tenant_id', $tenantId)->where('is_current_version', true)->count(),
            'pending_review' => Document::where('tenant_id', $tenantId)->where('status', 'pending_review')->count(),
            'approved'       => Document::where('tenant_id', $tenantId)->where('status', 'approved')->count(),
            'expiring_soon'  => Document::where('tenant_id', $tenantId)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>=', now())
                ->count(),
            'expired'        => Document::where('tenant_id', $tenantId)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', now())
                ->count(),
        ];

        $categories   = Document::where('tenant_id', $tenantId)->distinct()->pluck('document_category')->filter()->sort()->values();
        $types        = Document::where('tenant_id', $tenantId)->distinct()->pluck('document_type')->filter()->sort()->values();
        $workflows    = \App\Models\Documents\DocumentWorkflow::where('tenant_id', $tenantId)->where('is_active', true)->get();
        $pendingMyAction = \App\Models\Documents\DocumentWorkflowAction::where('assignee_id', auth()->id())
            ->where('status', 'pending')
            ->with(['instance.document', 'step'])
            ->latest()
            ->take(10)
            ->get();

        return view('documents.hub', compact('documents', 'stats', 'categories', 'types', 'workflows', 'pendingMyAction'));
    }

    public function create(Request $request)
    {
        $tenantId         = auth()->user()->tenant_id;
        $documentableType = $request->query('documentable_type');
        $documentableId   = $request->query('documentable_id');

        if (! $documentableType || ! $documentableId) {
            return redirect()->back()->with('error', 'Please navigate to a specific customer, loan, or account to upload documents. Both documentable_type and documentable_id are required.');
        }

        $entityTypeMap = [
            'App\Models\Customer'     => 'customer',
            'App\Models\Loan'         => 'loan',
            'App\Models\Account'      => 'account',
            'App\Models\StaffProfile' => 'staff_profile',
            'App\Models\Branch'       => 'branch',
        ];
        $entityType = $entityTypeMap[$documentableType] ?? null;

        $checklistItems = $entityType
            ? \App\Models\CbnDocumentChecklist::active()->forEntity($entityType)->orderBy('sort_order')->get()
            : collect();

        return view('documents.upload', compact(
            'documentableType',
            'documentableId',
            'checklistItems'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'             => 'required|string|max:255',
            'document_type'     => 'required|string|max:80',
            'document_category' => 'required|string|max:80',
            'expiry_date'       => 'nullable|date|after:today',
            'is_required'       => 'boolean',
            'file'              => 'required|file|max:20480',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $data = array_merge(
            $request->only(['title', 'document_type', 'document_category', 'description', 'expiry_date', 'is_required', 'alert_days_before']),
            [
                'documentable_type' => $request->input('documentable_type'),
                'documentable_id'   => $request->input('documentable_id'),
                'uploaded_by'       => auth()->id(),
            ]
        );

        $this->documentService->upload($data, $request->file('file'), $tenantId);

        $docType = $request->input('documentable_type');
        $docId   = $request->input('documentable_id');

        if ($docType && $docId) {
            return redirect()->route('documents.index', [
                'documentable_type' => $docType,
                'documentable_id'   => $docId,
            ])->with('success', 'Document uploaded successfully.');
        }

        return redirect()->route('documents.index')->with('success', 'Document uploaded successfully.');
    }

    public function show(Request $request, Document $document)
    {
        $this->documentService->logAccess($document, auth()->user(), 'viewed', $request);

        // Load all versions in the same chain (parent_id chain or same parent)
        $parentId = $document->parent_id ?? $document->id;
        $versions = Document::where(function ($q) use ($parentId, $document) {
            $q->where('id', $parentId)
              ->orWhere('parent_id', $parentId)
              ->orWhere('id', $document->id);
        })
        ->with('uploadedBy')
        ->orderBy('version')
        ->get();

        $accessLogs = \App\Models\DocumentAccessLog::where('document_id', $document->id)
            ->with('accessedByUser')
            ->orderByDesc('accessed_at')
            ->paginate(15);

        // DMS additions
        $notes = \App\Models\Documents\DocumentNote::where('document_id', $document->id)
            ->with('author')
            ->orderBy('created_at')
            ->get();

        $signatures = \App\Models\Documents\DocumentSignature::where('document_id', $document->id)
            ->where('is_valid', true)
            ->with('signer')
            ->get();

        $workflowInstances = \App\Models\Documents\DocumentWorkflowInstance::where('document_id', $document->id)
            ->with(['workflow', 'initiatedBy', 'actions.step', 'actions.assignee', 'actions.actor'])
            ->orderByDesc('created_at')
            ->get();

        $availableWorkflows = \App\Models\Documents\DocumentWorkflow::where('tenant_id', auth()->user()->tenant_id)
            ->where('is_active', true)->get();

        $myPendingAction = $workflowInstances
            ->where('status', 'in_progress')
            ->flatMap->actions
            ->where('status', 'pending')
            ->where('assignee_id', auth()->id())
            ->first();

        return view('documents.show', compact(
            'document', 'versions', 'accessLogs',
            'notes', 'signatures', 'workflowInstances',
            'availableWorkflows', 'myPendingAction'
        ));
    }

    public function download(Request $request, Document $document)
    {
        $this->documentService->logAccess($document, auth()->user(), 'downloaded', $request);

        return \Storage::disk('local')->download($document->file_path, $document->file_name);
    }

    public function newVersion(Request $request, Document $document)
    {
        $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $this->documentService->newVersion(
            $document,
            ['uploaded_by' => auth()->id()],
            $request->file('file'),
            auth()->user()
        );

        return redirect()->route('documents.show', $document)
            ->with('success', 'New version uploaded successfully.');
    }

    public function review(Request $request, Document $document)
    {
        $request->validate([
            'status'       => 'required|in:approved,rejected',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $this->documentService->review(
            $document,
            $request->input('status'),
            $request->input('review_notes'),
            auth()->user()
        );

        return back()->with('success', 'Document reviewed successfully.');
    }

    public function destroy(Document $document)
    {
        $document->update([
            'status'             => 'archived',
            'is_current_version' => false,
        ]);

        return back()->with('success', 'Document archived successfully.');
    }
}
