<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\CardTemplate;
use App\Models\IdCardBatch;
use App\Models\StaffIdCard;
use App\Models\StaffProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class StaffIdCardController extends Controller
{
    // ── List ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = StaffIdCard::where('tenant_id', $tenantId)
            ->with(['staffProfile.user', 'staffProfile.branch', 'template'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('card_number', 'like', "%{$search}%")
                  ->orWhereHas('staffProfile.user', fn($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        $cards     = $query->paginate(30)->withQueryString();
        $templates = CardTemplate::where('tenant_id', $tenantId)->get();

        // Stats
        $active  = StaffIdCard::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $expired = StaffIdCard::where('tenant_id', $tenantId)->where('status', 'expired')->count();
        $lost    = StaffIdCard::where('tenant_id', $tenantId)->where('status', 'lost')->count();

        // Staff without active cards
        $staffWithCard = StaffIdCard::where('tenant_id', $tenantId)
            ->whereIn('status', ['active'])
            ->pluck('staff_profile_id');

        $staffWithoutCard = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereNotIn('id', $staffWithCard)
            ->with('user')
            ->get();

        return view('hr.id-cards.index', compact(
            'cards', 'templates', 'active', 'expired', 'lost', 'staffWithoutCard'
        ));
    }

    // ── Generate single card ────────────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'staff_profile_id' => 'required|uuid|exists:staff_profiles,id',
            'template_id'      => 'nullable|uuid|exists:card_templates,id',
            'notes'            => 'nullable|string|max:500',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $profile  = StaffProfile::where('id', $request->staff_profile_id)
            ->where('tenant_id', $tenantId)
            ->with(['user', 'branch', 'orgDepartment'])
            ->firstOrFail();

        $template = $request->template_id
            ? CardTemplate::where('id', $request->template_id)->where('tenant_id', $tenantId)->first()
            : CardTemplate::where('tenant_id', $tenantId)->where('is_default', true)->first();

        $card = $this->generateCard($profile, $template, $tenantId, null, auth()->id(), $request->notes);

        return back()->with('success', "ID card {$card->card_number} generated successfully.");
    }

    // ── Bulk generate ───────────────────────────────────────────────────────

    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'template_id' => 'nullable|uuid|exists:card_templates,id',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $template = $request->template_id
            ? CardTemplate::where('id', $request->template_id)->where('tenant_id', $tenantId)->first()
            : CardTemplate::where('tenant_id', $tenantId)->where('is_default', true)->first();

        $existingCards = StaffIdCard::where('tenant_id', $tenantId)
            ->whereIn('status', ['active'])
            ->pluck('staff_profile_id');

        $staffList = StaffProfile::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereNotIn('id', $existingCards)
            ->with(['user', 'branch', 'orgDepartment'])
            ->get();

        if ($staffList->isEmpty()) {
            return back()->with('error', 'All active staff already have cards.');
        }

        $batch = IdCardBatch::create([
            'id'          => \Illuminate\Support\Str::uuid(),
            'tenant_id'   => $tenantId,
            'name'        => 'Bulk Issue — ' . now()->format('d M Y'),
            'total_count' => $staffList->count(),
            'status'      => 'generating',
            'created_by'  => auth()->id(),
        ]);

        $generated = 0;
        foreach ($staffList as $profile) {
            $this->generateCard($profile, $template, $tenantId, $batch->id, auth()->id());
            $generated++;
        }

        $batch->update(['generated_count' => $generated, 'status' => 'ready']);

        return back()->with('success', "{$generated} ID cards generated successfully.");
    }

    // ── Download PDF ────────────────────────────────────────────────────────

    public function download(StaffIdCard $staffIdCard)
    {
        abort_unless($staffIdCard->tenant_id === auth()->user()->tenant_id, 403);

        $staffIdCard->load(['staffProfile.user', 'staffProfile.branch', 'staffProfile.orgDepartment', 'template']);

        // Generate QR as SVG — no imagick/GD extension required
        $qrSvg = QrCode::format('svg')
            ->size(150)
            ->errorCorrection('H')
            ->generate(
                route('hr.id-cards.verify', $staffIdCard->card_number)
            );

        $pdf = Pdf::loadView('hr.id-cards.pdf', [
            'card'     => $staffIdCard,
            'profile'  => $staffIdCard->staffProfile,
            'template' => $staffIdCard->template,
            'qrSvg'    => $qrSvg,
        ]);

        // CR80 card: 85.6mm × 54mm — two pages (front + back)
        $pdf->setPaper([0, 0, 242.36, 153.07], 'landscape')
            ->set_option('isRemoteEnabled', false)
            ->set_option('defaultFont', 'arial');

        return $pdf->download("id-card-{$staffIdCard->card_number}.pdf");
    }

    // ── Report lost ─────────────────────────────────────────────────────────

    public function reportLost(Request $request, StaffIdCard $staffIdCard)
    {
        abort_unless($staffIdCard->tenant_id === auth()->user()->tenant_id, 403);

        if ($staffIdCard->status !== 'active') {
            return back()->with('error', 'Card is not active.');
        }

        $staffIdCard->update([
            'status'           => 'lost',
            'loss_report_date' => now()->toDateString(),
        ]);

        return back()->with('success', "Card {$staffIdCard->card_number} marked as lost.");
    }

    // ── Replace card ────────────────────────────────────────────────────────

    public function replace(Request $request, StaffIdCard $staffIdCard)
    {
        abort_unless($staffIdCard->tenant_id === auth()->user()->tenant_id, 403);

        $tenantId = auth()->user()->tenant_id;
        $profile  = $staffIdCard->staffProfile()->with(['user', 'branch', 'orgDepartment'])->first();
        $template = $staffIdCard->template;

        // Mark old card as replaced
        $staffIdCard->update(['status' => 'replaced']);

        $newCard = $this->generateCard(
            $profile, $template, $tenantId, null, auth()->id(),
            "Replacement for {$staffIdCard->card_number}"
        );

        // Link old → new
        $staffIdCard->update(['replaced_by' => $newCard->id]);

        return back()->with('success', "Replacement card {$newCard->card_number} issued.");
    }

    // ── Public verify (QR scan) ─────────────────────────────────────────────

    public function verify(string $cardNumber)
    {
        $card = StaffIdCard::where('card_number', $cardNumber)
            ->with(['staffProfile.user', 'staffProfile.branch', 'staffProfile.orgDepartment'])
            ->first();

        if (!$card) {
            return view('hr.id-cards.verify', ['card' => null, 'status' => 'not_found']);
        }

        $status = match (true) {
            $card->status === 'lost'      => 'lost',
            $card->status === 'cancelled' => 'cancelled',
            $card->status === 'replaced'  => 'replaced',
            $card->isExpired()            => 'expired',
            $card->isActive()             => 'valid',
            default                       => 'invalid',
        };

        return view('hr.id-cards.verify', compact('card', 'status'));
    }

    // ── Template Management ──────────────────────────────────────────────────

    public function templates()
    {
        $tenantId  = auth()->user()->tenant_id;
        $templates = CardTemplate::where('tenant_id', $tenantId)->orderByDesc('is_default')->get();
        return view('hr.id-cards.templates', compact('templates'));
    }

    public function templateStore(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:200',
            'primary_color'    => 'required|string|max:20',
            'secondary_color'  => 'required|string|max:20',
            'text_color'       => 'required|string|max:20',
            'background_color' => 'required|string|max:20',
            'expiry_years'     => 'required|integer|min:1|max:10',
        ]);

        $tenantId  = auth()->user()->tenant_id;
        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            CardTemplate::where('tenant_id', $tenantId)->update(['is_default' => false]);
        }

        CardTemplate::create([
            'id'                     => \Illuminate\Support\Str::uuid(),
            'tenant_id'              => $tenantId,
            'name'                   => $request->name,
            'primary_color'          => $request->primary_color,
            'secondary_color'        => $request->secondary_color,
            'text_color'             => $request->text_color,
            'background_color'       => $request->background_color,
            'expiry_years'           => (int) $request->expiry_years,
            'is_default'             => $isDefault,
            'show_qr'                => $request->boolean('show_qr', true),
            'show_photo'             => $request->boolean('show_photo', true),
            'show_department'        => $request->boolean('show_department', true),
            'show_grade'             => $request->boolean('show_grade', true),
            'show_blood_group'       => $request->boolean('show_blood_group'),
            'show_emergency_contact' => $request->boolean('show_emergency_contact'),
        ]);

        return back()->with('success', 'Template created.');
    }

    public function templateUpdate(Request $request, CardTemplate $cardTemplate)
    {
        abort_unless($cardTemplate->tenant_id === auth()->user()->tenant_id, 403);

        $tenantId  = auth()->user()->tenant_id;
        if ($request->boolean('is_default')) {
            CardTemplate::where('tenant_id', $tenantId)->where('id', '!=', $cardTemplate->id)->update(['is_default' => false]);
        }

        $cardTemplate->update(array_merge(
            $request->only(['name','primary_color','secondary_color','text_color','background_color','expiry_years']),
            [
                'is_default'             => $request->boolean('is_default'),
                'show_qr'                => $request->boolean('show_qr'),
                'show_photo'             => $request->boolean('show_photo'),
                'show_department'        => $request->boolean('show_department'),
                'show_grade'             => $request->boolean('show_grade'),
                'show_blood_group'       => $request->boolean('show_blood_group'),
                'show_emergency_contact' => $request->boolean('show_emergency_contact'),
            ]
        ));

        return back()->with('success', 'Template updated.');
    }

    public function templateSetDefault(CardTemplate $cardTemplate)
    {
        abort_unless($cardTemplate->tenant_id === auth()->user()->tenant_id, 403);
        CardTemplate::where('tenant_id', $cardTemplate->tenant_id)->update(['is_default' => false]);
        $cardTemplate->update(['is_default' => true]);
        return back()->with('success', "'{$cardTemplate->name}' set as default template.");
    }

    public function templateUploadLogo(Request $request, CardTemplate $cardTemplate)
    {
        abort_unless($cardTemplate->tenant_id === auth()->user()->tenant_id, 403);
        $request->validate(['logo' => 'required|image|max:2048']);
        $path = $request->file('logo')->store("card-logos/{$cardTemplate->tenant_id}", 'public');
        $cardTemplate->update(['logo_path' => $path]);
        return back()->with('success', 'Logo uploaded.');
    }

    public function templateDestroy(CardTemplate $cardTemplate)
    {
        abort_unless($cardTemplate->tenant_id === auth()->user()->tenant_id, 403);
        if ($cardTemplate->idCards()->exists()) {
            return back()->with('error', 'Cannot delete — cards have been issued with this template.');
        }
        $cardTemplate->delete();
        return back()->with('success', 'Template deleted.');
    }

    // ── Internal helper ─────────────────────────────────────────────────────

    private function generateCard(
        StaffProfile $profile,
        ?CardTemplate $template,
        string $tenantId,
        ?string $batchId,
        int $issuedBy,
        ?string $notes = null
    ): StaffIdCard {
        $expiryYears = $template?->expiry_years ?? 2;
        $issuedDate  = now()->toDateString();
        $expiryDate  = now()->addYears($expiryYears)->toDateString();
        $cardNumber  = $this->generateCardNumber($tenantId);

        $verifyUrl = route('hr.id-cards.verify', $cardNumber);

        return StaffIdCard::create([
            'tenant_id'        => $tenantId,
            'staff_profile_id' => $profile->id,
            'template_id'      => $template?->id,
            'batch_id'         => $batchId,
            'card_number'      => $cardNumber,
            'issued_date'      => $issuedDate,
            'expiry_date'      => $expiryDate,
            'status'           => 'active',
            'qr_payload'       => $verifyUrl,
            'issued_by'        => $issuedBy,
            'notes'            => $notes,
        ]);
    }

    private function generateCardNumber(string $tenantId): string
    {
        $year   = now()->format('Y');
        $count  = StaffIdCard::where('tenant_id', $tenantId)->count() + 1;
        return 'ID-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
