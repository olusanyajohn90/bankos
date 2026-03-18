<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\KpiNote;
use Illuminate\Http\Request;

class KpiNoteController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $notes = KpiNote::where('tenant_id', $tenantId)
            ->when($request->subject_type && $request->subject_id, fn($q) =>
                $q->where('subject_type', $request->subject_type)
                  ->where('subject_id', $request->subject_id)
            )
            ->when($request->period, fn($q) => $q->where('period_value', $request->period))
            ->where(function ($q) {
                $q->where('is_private', false)
                  ->orWhere('author_id', auth()->id());
            })
            ->with(['author', 'kpiDefinition'])
            ->latest()
            ->paginate(20);

        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject_type' => 'required|in:user,team,branch',
            'subject_id'   => 'required|uuid',
            'kpi_id'       => 'nullable|exists:kpi_definitions,id',
            'period_value' => 'nullable|string|max:10',
            'body'         => 'required|string|max:2000',
            'is_private'   => 'boolean',
        ]);

        $note = KpiNote::create([
            'tenant_id'    => auth()->user()->tenant_id,
            'author_id'    => auth()->id(),
            'subject_type' => $data['subject_type'],
            'subject_id'   => $data['subject_id'],
            'kpi_id'       => $data['kpi_id'] ?? null,
            'period_value' => $data['period_value'] ?? null,
            'body'         => $data['body'],
            'is_private'   => $data['is_private'] ?? false,
            'is_alert'     => false,
        ]);

        $note->load('author');

        if ($request->wantsJson()) {
            return response()->json($note, 201);
        }

        return back()->with('success', 'Note added.');
    }

    public function destroy(KpiNote $kpiNote)
    {
        if ($kpiNote->author_id !== auth()->id()) {
            abort(403);
        }

        $kpiNote->delete();

        if (request()->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('success', 'Note deleted.');
    }
}
