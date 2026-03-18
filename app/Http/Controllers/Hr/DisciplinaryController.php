<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\DisciplinaryCase;
use App\Models\StaffProfile;
use App\Services\Hr\DisciplinaryService;
use Illuminate\Http\Request;

class DisciplinaryController extends Controller
{
    public function __construct(protected DisciplinaryService $disciplinaryService) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = DisciplinaryCase::where('tenant_id', $tenantId)
            ->with(['staffProfile.user', 'raisedBy'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $cases = $query->paginate(20)->withQueryString();
        $staff = StaffProfile::where('tenant_id', $tenantId)->with('user')->get();

        return view('hr.disciplinary.index', compact('cases', 'staff'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'staff_profile_id' => 'required|exists:staff_profiles,id',
            'type'             => 'required|in:query,warning,suspension,demotion,termination',
            'description'      => 'required|string|max:2000',
            'incident_date'    => 'required|date',
        ]);

        $this->disciplinaryService->openCase([
            'tenant_id'        => $tenantId,
            'staff_profile_id' => $request->staff_profile_id,
            'type'             => $request->type,
            'description'      => $request->description,
            'incident_date'    => $request->incident_date,
            'raised_by'        => auth()->id(),
        ]);

        return back()->with('success', 'Disciplinary case opened successfully.');
    }

    public function show(DisciplinaryCase $disciplinaryCase)
    {
        abort_unless($disciplinaryCase->tenant_id === auth()->user()->tenant_id, 403);

        $disciplinaryCase->load(['staffProfile.user', 'raisedBy', 'responses.decidedBy']);

        return view('hr.disciplinary.show', compact('disciplinaryCase'));
    }

    public function respond(Request $request, DisciplinaryCase $disciplinaryCase)
    {
        abort_unless($disciplinaryCase->tenant_id === auth()->user()->tenant_id, 403);

        $request->validate([
            'staff_response' => 'required|string|max:3000',
        ]);

        $this->disciplinaryService->respond($disciplinaryCase, $request->only('staff_response'));

        return back()->with('success', 'Response recorded successfully.');
    }

    public function close(Request $request, DisciplinaryCase $disciplinaryCase)
    {
        abort_unless($disciplinaryCase->tenant_id === auth()->user()->tenant_id, 403);

        $request->validate([
            'outcome' => 'required|in:no_action,warning_issued,suspended,dismissed,cleared',
        ]);

        $this->disciplinaryService->closeCase($disciplinaryCase, $request->only('outcome'), auth()->user());

        return back()->with('success', 'Case closed successfully.');
    }

    public function appeal(DisciplinaryCase $disciplinaryCase)
    {
        abort_unless($disciplinaryCase->tenant_id === auth()->user()->tenant_id, 403);

        $this->disciplinaryService->appealCase($disciplinaryCase);

        return back()->with('success', 'Case marked as appealed.');
    }
}
