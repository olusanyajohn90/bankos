<?php

namespace App\Services\Documents;

use App\Models\CbnDocumentChecklist;
use App\Models\Document;
use App\Models\DocumentAccessLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    public function upload(array $data, UploadedFile $file, string $tenantId): Document
    {
        $path = $file->store("documents/{$tenantId}", 'local');
        return Document::create([
            ...$data,
            'tenant_id'          => $tenantId,
            'file_path'          => $path,
            'file_name'          => $file->getClientOriginalName(),
            'mime_type'          => $file->getMimeType(),
            'file_size_kb'       => (int) ceil($file->getSize() / 1024),
            'version'            => 1,
            'is_current_version' => true,
            'status'             => 'pending',
        ]);
    }

    public function newVersion(Document $parent, array $data, UploadedFile $file, User $uploader): Document
    {
        $tenantId = $parent->tenant_id;
        $path = $file->store("documents/{$tenantId}", 'local');

        // Mark parent (and all prior versions) as not current
        Document::where('id', $parent->id)
            ->orWhere('parent_id', $parent->id)
            ->update(['is_current_version' => false, 'status' => 'archived']);

        return Document::create([
            ...$data,
            'tenant_id'          => $tenantId,
            'documentable_type'  => $parent->documentable_type,
            'documentable_id'    => $parent->documentable_id,
            'document_type'      => $parent->document_type,
            'document_category'  => $parent->document_category,
            'file_path'          => $path,
            'file_name'          => $file->getClientOriginalName(),
            'mime_type'          => $file->getMimeType(),
            'file_size_kb'       => (int) ceil($file->getSize() / 1024),
            'version'            => $parent->version + 1,
            'is_current_version' => true,
            'parent_id'          => $parent->id,
            'uploaded_by'        => $uploader->id,
            'status'             => 'pending',
        ]);
    }

    public function review(Document $document, string $status, ?string $notes, User $reviewer): void
    {
        $document->update([
            'status'       => $status,
            'review_notes' => $notes,
            'reviewed_by'  => $reviewer->id,
            'reviewed_at'  => now(),
        ]);
    }

    public function logAccess(Document $document, User $user, string $action, Request $request): void
    {
        DocumentAccessLog::create([
            'tenant_id'   => $document->tenant_id,
            'document_id' => $document->id,
            'accessed_by' => $user->id,
            'action'      => $action,
            'ip_address'  => $request->ip(),
            'user_agent'  => substr($request->userAgent() ?? '', 0, 500),
            'accessed_at' => now(),
        ]);
    }

    public function checklistStatus(string $entityType, string $entityId, string $tenantId): array
    {
        $checklist = CbnDocumentChecklist::where('tenant_id', $tenantId)
            ->where('entity_type', $entityType)
            ->active()
            ->orderBy('sort_order')
            ->get();

        $existingDocs = Document::where('tenant_id', $tenantId)
            ->where('documentable_type', $this->morphTypeFor($entityType))
            ->where('documentable_id', $entityId)
            ->where('is_current_version', true)
            ->whereIn('status', ['pending', 'approved'])
            ->get()
            ->keyBy('document_type');

        return $checklist->map(function ($item) use ($existingDocs) {
            $doc = $existingDocs[$item->document_type] ?? null;
            return [
                'label'         => $item->document_label,
                'document_type' => $item->document_type,
                'is_required'   => $item->is_required,
                'status'        => $doc ? $doc->status : 'missing',
                'is_expired'    => $doc ? $doc->isExpired() : false,
                'document_id'   => $doc?->id,
            ];
        })->toArray();
    }

    private function morphTypeFor(string $entityType): string
    {
        return match ($entityType) {
            'customer'      => 'App\\Models\\Customer',
            'loan'          => 'App\\Models\\Loan',
            'account'       => 'App\\Models\\Account',
            'staff_profile' => 'App\\Models\\StaffProfile',
            'branch'        => 'App\\Models\\Branch',
            default         => $entityType,
        };
    }
}
