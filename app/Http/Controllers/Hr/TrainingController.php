<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\StaffCertification;
use App\Models\StaffDocument;
use App\Models\StaffProfile;
use App\Models\TrainingAttendance;
use App\Models\TrainingProgram;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $programs = TrainingProgram::where('tenant_id', $tenantId)
            ->withCount('attendances')
            ->orderBy('title')
            ->get();

        $staff = StaffProfile::where('tenant_id', $tenantId)
            ->with('user')
            ->where('status', 'active')
            ->orderBy('created_at')
            ->get();

        $attendances = TrainingAttendance::whereHas('program', fn ($q) => $q->where('tenant_id', $tenantId))
            ->with(['program', 'staffProfile.user'])
            ->latest('enrolled_at')
            ->paginate(25);

        return view('hr.training.index', compact('programs', 'staff', 'attendances'));
    }

    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|in:technical,compliance,leadership,soft_skills,safety,other',
            'provider'       => 'nullable|string|max:150',
            'duration_hours' => 'required|numeric|min:0.5',
            'is_mandatory'   => 'boolean',
            'description'    => 'nullable|string|max:2000',
        ]);

        TrainingProgram::create([
            'tenant_id'      => $tenantId,
            'title'          => $request->title,
            'category'       => $request->category,
            'provider'       => $request->provider,
            'duration_hours' => $request->duration_hours,
            'is_mandatory'   => $request->boolean('is_mandatory'),
            'description'    => $request->description,
            'status'         => 'active',
        ]);

        return back()->with('success', 'Training program created successfully.');
    }

    public function update(Request $request, TrainingProgram $trainingProgram)
    {
        abort_unless($trainingProgram->tenant_id === auth()->user()->tenant_id, 403);

        $request->validate([
            'title'          => 'required|string|max:200',
            'category'       => 'required|in:technical,compliance,leadership,soft_skills,safety,other',
            'provider'       => 'nullable|string|max:150',
            'duration_hours' => 'required|numeric|min:0.5',
            'is_mandatory'   => 'boolean',
            'description'    => 'nullable|string|max:2000',
            'status'         => 'required|in:active,inactive,archived',
        ]);

        $trainingProgram->update([
            'title'          => $request->title,
            'category'       => $request->category,
            'provider'       => $request->provider,
            'duration_hours' => $request->duration_hours,
            'is_mandatory'   => $request->boolean('is_mandatory'),
            'description'    => $request->description,
            'status'         => $request->status,
        ]);

        return back()->with('success', 'Training program updated successfully.');
    }

    public function enroll(Request $request, TrainingProgram $trainingProgram)
    {
        abort_unless($trainingProgram->tenant_id === auth()->user()->tenant_id, 403);

        $request->validate([
            'staff_profile_ids'   => 'required|array|min:1',
            'staff_profile_ids.*' => 'exists:staff_profiles,id',
        ]);

        $enrolled = 0;
        foreach ($request->staff_profile_ids as $profileId) {
            $exists = TrainingAttendance::where('program_id', $trainingProgram->id)
                ->where('staff_profile_id', $profileId)
                ->exists();

            if (!$exists) {
                TrainingAttendance::create([
                    'tenant_id'        => auth()->user()->tenant_id,
                    'program_id'       => $trainingProgram->id,
                    'staff_profile_id' => $profileId,
                    'enrolled_at'      => now(),
                    'status'           => 'enrolled',
                ]);
                $enrolled++;
            }
        }

        return back()->with('success', "{$enrolled} staff enrolled in the training program.");
    }

    public function updateAttendance(Request $request, TrainingAttendance $trainingAttendance)
    {
        $request->validate([
            'status'              => 'required|in:enrolled,attending,completed,absent,withdrawn',
            'score'               => 'nullable|numeric|min:0|max:100',
            'completed_at'        => 'nullable|date',
            'certificate_issued'  => 'boolean',
        ]);

        $data = [
            'status'             => $request->status,
            'score'              => $request->score,
            'completed_at'       => $request->completed_at,
            'certificate_issued' => $request->boolean('certificate_issued'),
        ];

        if ($request->status === 'completed' && !$trainingAttendance->completed_at) {
            $data['completed_at'] = $request->completed_at ?? now();
        }

        $trainingAttendance->update($data);

        return back()->with('success', 'Attendance record updated.');
    }

    public function myCertifications()
    {
        $tenantId = auth()->user()->tenant_id;

        $profile = StaffProfile::where('user_id', auth()->id())
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $certifications = StaffCertification::where('staff_profile_id', $profile->id)
            ->orderBy('issue_date', 'desc')
            ->get();

        $documents = StaffDocument::where('staff_profile_id', $profile->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hr.training.my-certs', compact('profile', 'certifications', 'documents'));
    }

    public function storeCertification(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'staff_profile_id' => 'nullable|exists:staff_profiles,id',
            'name'             => 'required|string|max:200',
            'issuing_body'     => 'required|string|max:200',
            'cert_number'      => 'nullable|string|max:100',
            'issue_date'       => 'required|date',
            'expiry_date'      => 'nullable|date|after:issue_date',
        ]);

        $profileId = $request->staff_profile_id;
        if (!$profileId) {
            $profile   = StaffProfile::where('user_id', auth()->id())->where('tenant_id', $tenantId)->firstOrFail();
            $profileId = $profile->id;
        }

        StaffCertification::create([
            'staff_profile_id' => $profileId,
            'name'             => $request->name,
            'issuing_body'     => $request->issuing_body,
            'cert_number'      => $request->cert_number,
            'issue_date'       => $request->issue_date,
            'expiry_date'      => $request->expiry_date,
            'is_verified'      => false,
        ]);

        return back()->with('success', 'Certification added successfully.');
    }

    public function storeDocument(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $request->validate([
            'staff_profile_id' => 'nullable|exists:staff_profiles,id',
            'document_type'    => 'required|in:id_card,passport,drivers_license,academic_certificate,professional_certificate,offer_letter,appointment_letter,other',
            'document_number'  => 'nullable|string|max:100',
            'file_url'         => 'nullable|url|max:500',
        ]);

        $profileId = $request->staff_profile_id;
        if (!$profileId) {
            $profile   = StaffProfile::where('user_id', auth()->id())->where('tenant_id', $tenantId)->firstOrFail();
            $profileId = $profile->id;
        }

        StaffDocument::create([
            'staff_profile_id' => $profileId,
            'document_type'    => $request->document_type,
            'document_number'  => $request->document_number,
            'file_url'         => $request->file_url,
            'is_verified'      => false,
        ]);

        return back()->with('success', 'Document added successfully.');
    }

    public function verifyDocument(StaffDocument $staffDocument)
    {
        $staffDocument->update([
            'is_verified' => true,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Document verified successfully.');
    }
}
