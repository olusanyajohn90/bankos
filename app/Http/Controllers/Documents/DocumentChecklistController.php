<?php

namespace App\Http\Controllers\Documents;

use App\Http\Controllers\Controller;
use App\Models\CbnDocumentChecklist;
use Illuminate\Http\Request;

class DocumentChecklistController extends Controller
{
    public function index(Request $request)
    {
        $checklists = CbnDocumentChecklist::orderBy('entity_type')->orderBy('sort_order')->get();

        $entityTypes = ['customer', 'loan', 'account', 'staff_profile', 'branch'];
        $grouped = $checklists->groupBy('entity_type');

        // Ensure all entity type keys exist so blade views can safely use array access
        foreach ($entityTypes as $et) {
            if (! $grouped->has($et)) {
                $grouped[$et] = collect();
            }
        }

        return view('documents.checklist.index', compact('checklists', 'grouped'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'entity_type'    => 'required|in:customer,loan,account,staff_profile,branch',
            'document_type'  => 'required|string|max:80',
            'document_label' => 'required|string|max:255',
            'is_required'    => 'boolean',
            'applies_to'     => 'nullable|string|max:80',
            'sort_order'     => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
        ]);

        CbnDocumentChecklist::create([
            'entity_type'    => $request->entity_type,
            'document_type'  => $request->document_type,
            'document_label' => $request->document_label,
            'is_required'    => $request->boolean('is_required'),
            'applies_to'     => $request->applies_to,
            'sort_order'     => $request->input('sort_order', 0),
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Checklist item added successfully.');
    }

    public function update(Request $request, CbnDocumentChecklist $checklist)
    {
        $request->validate([
            'entity_type'    => 'required|in:customer,loan,account,staff_profile,branch',
            'document_type'  => 'required|string|max:80',
            'document_label' => 'required|string|max:255',
            'is_required'    => 'boolean',
            'applies_to'     => 'nullable|string|max:80',
            'sort_order'     => 'nullable|integer|min:0',
            'is_active'      => 'boolean',
        ]);

        $checklist->update([
            'entity_type'    => $request->entity_type,
            'document_type'  => $request->document_type,
            'document_label' => $request->document_label,
            'is_required'    => $request->boolean('is_required'),
            'applies_to'     => $request->applies_to,
            'sort_order'     => $request->input('sort_order', 0),
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Checklist item updated successfully.');
    }

    public function destroy(CbnDocumentChecklist $checklist)
    {
        $checklist->delete();

        return back()->with('success', 'Checklist item deleted.');
    }
}
