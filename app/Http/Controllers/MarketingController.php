<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MarketingCampaign;
use App\Models\MarketingCampaignRecipient;
use App\Models\MarketingCrossSell;
use App\Models\MarketingSegment;
use App\Models\MarketingTemplate;
use App\Models\MarketingUnsubscribe;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MarketingController extends Controller
{
    // ─── DASHBOARD ──────────────────────────────────────────────────────────────

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $totalCampaigns   = MarketingCampaign::count();
        $activeCampaigns  = MarketingCampaign::whereIn('status', ['sending', 'scheduled'])->count();
        $totalRecipients  = MarketingCampaign::sum('total_recipients');
        $totalSent        = MarketingCampaign::sum('sent_count');
        $totalDelivered   = MarketingCampaign::sum('delivered_count');
        $deliveryRate     = $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 1) : 0;
        $crossSellCount   = MarketingCrossSell::where('status', 'identified')->count();

        $recentCampaigns  = MarketingCampaign::with('segment', 'createdBy')
            ->latest()
            ->take(5)
            ->get();

        $topSegments = MarketingSegment::orderByDesc('cached_count')->take(5)->get();

        return view('marketing.index', compact(
            'totalCampaigns', 'activeCampaigns', 'totalRecipients',
            'totalSent', 'deliveryRate', 'crossSellCount',
            'recentCampaigns', 'topSegments'
        ));
    }

    // ─── CAMPAIGNS ──────────────────────────────────────────────────────────────

    public function campaigns(Request $request)
    {
        $query = MarketingCampaign::with('segment', 'template', 'createdBy');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $campaigns = $query->latest()->paginate(20)->withQueryString();

        return view('marketing.campaigns.index', compact('campaigns'));
    }

    public function createCampaign()
    {
        $templates = MarketingTemplate::orderBy('name')->get();
        $segments  = MarketingSegment::orderBy('name')->get();

        return view('marketing.campaigns.create', compact('templates', 'segments'));
    }

    public function storeCampaign(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:200',
            'type'           => 'required|in:broadcast,cross_sell,event_triggered',
            'channel'        => 'required|in:sms,email,whatsapp',
            'segment_id'     => 'nullable|exists:marketing_segments,id',
            'template_id'    => 'nullable|exists:marketing_templates,id',
            'custom_message' => 'nullable|string|max:2000',
            'custom_subject' => 'nullable|string|max:255',
            'scheduled_at'   => 'nullable|date|after:now',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $campaign = MarketingCampaign::create([
            'tenant_id'      => $tenantId,
            'name'           => $request->name,
            'description'    => $request->description,
            'type'           => $request->type,
            'channel'        => $request->channel,
            'template_id'    => $request->template_id,
            'segment_id'     => $request->segment_id,
            'custom_message' => $request->custom_message,
            'custom_subject' => $request->custom_subject,
            'status'         => $request->scheduled_at ? 'scheduled' : 'draft',
            'scheduled_at'   => $request->scheduled_at,
            'created_by'     => auth()->id(),
        ]);

        // Compute recipients from segment
        if ($request->segment_id) {
            $segment   = MarketingSegment::findOrFail($request->segment_id);
            $customers = $this->evaluateSegment($segment->rules ?? [], $tenantId)->get();
        } else {
            // All active customers
            $customers = Customer::where('tenant_id', $tenantId)->where('status', 'active')->get();
        }

        // Filter out unsubscribed customers
        $unsubscribed = MarketingUnsubscribe::where('tenant_id', $tenantId)
            ->where('channel', $request->channel)
            ->pluck('customer_id');

        $recipients = [];
        foreach ($customers as $customer) {
            if ($unsubscribed->contains($customer->id)) {
                continue;
            }
            $address = match ($request->channel) {
                'email'    => $customer->email,
                'sms'      => $customer->phone,
                'whatsapp' => $customer->phone,
                default    => $customer->phone,
            };
            if (!$address) {
                continue;
            }
            $recipients[] = [
                'campaign_id'     => $campaign->id,
                'customer_id'     => $customer->id,
                'channel_address' => $address,
                'status'          => 'queued',
            ];
        }

        if (!empty($recipients)) {
            foreach (array_chunk($recipients, 500) as $chunk) {
                MarketingCampaignRecipient::insert($chunk);
            }
        }

        $campaign->update(['total_recipients' => count($recipients)]);

        return redirect()->route('marketing.campaigns.show', $campaign->id)
            ->with('success', 'Campaign created with ' . count($recipients) . ' recipients.');
    }

    public function showCampaign($id)
    {
        $campaign = MarketingCampaign::with('template', 'segment', 'createdBy')
            ->findOrFail($id);

        $recipients = MarketingCampaignRecipient::where('campaign_id', $id)
            ->with('customer')
            ->paginate(25);

        return view('marketing.campaigns.show', compact('campaign', 'recipients'));
    }

    public function sendCampaign($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        if (!in_array($campaign->status, ['draft', 'scheduled', 'paused'])) {
            return back()->with('error', 'Campaign cannot be sent in its current status.');
        }

        $now = now();

        // Simulate sending: update all queued recipients to 'sent'
        MarketingCampaignRecipient::where('campaign_id', $id)
            ->where('status', 'queued')
            ->update([
                'status'  => 'sent',
                'sent_at' => $now,
            ]);

        $sentCount = MarketingCampaignRecipient::where('campaign_id', $id)
            ->where('status', 'sent')
            ->count();

        $campaign->update([
            'status'       => 'sent',
            'sent_at'      => $now,
            'completed_at' => $now,
            'sent_count'   => $sentCount,
            'delivered_count' => $sentCount, // simulated
        ]);

        return back()->with('success', "Campaign sent to {$sentCount} recipients.");
    }

    public function pauseCampaign($id)
    {
        $campaign = MarketingCampaign::findOrFail($id);

        if ($campaign->status !== 'sending') {
            return back()->with('error', 'Only sending campaigns can be paused.');
        }

        $campaign->update(['status' => 'paused']);

        return back()->with('success', 'Campaign paused.');
    }

    public function duplicateCampaign($id)
    {
        $original = MarketingCampaign::findOrFail($id);

        $clone = $original->replicate();
        $clone->id           = Str::uuid()->toString();
        $clone->name         = $original->name . ' (Copy)';
        $clone->status       = 'draft';
        $clone->scheduled_at = null;
        $clone->sent_at      = null;
        $clone->completed_at = null;
        $clone->total_recipients   = 0;
        $clone->sent_count         = 0;
        $clone->delivered_count    = 0;
        $clone->opened_count       = 0;
        $clone->clicked_count      = 0;
        $clone->converted_count    = 0;
        $clone->failed_count       = 0;
        $clone->unsubscribed_count = 0;
        $clone->cost               = 0;
        $clone->created_by         = auth()->id();
        $clone->save();

        return redirect()->route('marketing.campaigns.show', $clone->id)
            ->with('success', 'Campaign duplicated as draft.');
    }

    // ─── SEGMENTS ───────────────────────────────────────────────────────────────

    public function segments()
    {
        $segments = MarketingSegment::withCount('campaigns')->latest()->paginate(20);

        return view('marketing.segments.index', compact('segments'));
    }

    public function createSegment()
    {
        $branches = \App\Models\Branch::orderBy('name')->get();

        return view('marketing.segments.create', compact('branches'));
    }

    public function storeSegment(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:150',
            'rules' => 'required|array|min:1',
            'rules.*.field'    => 'required|string',
            'rules.*.operator' => 'required|string',
            'rules.*.value'    => 'required|string',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $count    = $this->evaluateSegment($request->rules, $tenantId)->count();

        MarketingSegment::create([
            'tenant_id'        => $tenantId,
            'name'             => $request->name,
            'description'      => $request->description,
            'rules'            => $request->rules,
            'cached_count'     => $count,
            'count_computed_at' => now(),
            'created_by'       => auth()->id(),
        ]);

        return redirect()->route('marketing.segments')
            ->with('success', 'Segment created with ' . number_format($count) . ' matching customers.');
    }

    public function previewSegment(Request $request)
    {
        $request->validate([
            'rules'            => 'required|array|min:1',
            'rules.*.field'    => 'required|string',
            'rules.*.operator' => 'required|string',
            'rules.*.value'    => 'required|string',
        ]);

        $tenantId = auth()->user()->tenant_id;
        $query    = $this->evaluateSegment($request->rules, $tenantId);
        $count    = $query->count();
        $sample   = $query->take(5)->get(['id', 'first_name', 'last_name', 'phone', 'email']);

        return response()->json([
            'count'  => $count,
            'sample' => $sample,
        ]);
    }

    public function deleteSegment($id)
    {
        $segment = MarketingSegment::findOrFail($id);

        if ($segment->is_system) {
            return back()->with('error', 'System segments cannot be deleted.');
        }

        $segment->delete();

        return back()->with('success', 'Segment deleted.');
    }

    private function evaluateSegment(array $rules, string $tenantId): \Illuminate\Database\Eloquent\Builder
    {
        $query = Customer::where('customers.tenant_id', $tenantId)->where('customers.status', 'active');

        foreach ($rules as $rule) {
            $field    = $rule['field'] ?? '';
            $operator = $rule['operator'] ?? 'equals';
            $value    = $rule['value'] ?? '';

            switch ($field) {
                case 'gender':
                case 'kyc_tier':
                case 'status':
                    $query->where($field, $value);
                    break;

                case 'account_type':
                    $query->whereHas('accounts', fn($q) => $q->where('type', $value));
                    break;

                case 'available_balance':
                    $query->whereHas('accounts', function ($q) use ($operator, $value) {
                        match ($operator) {
                            'greater_than' => $q->where('available_balance', '>', $value),
                            'less_than'    => $q->where('available_balance', '<', $value),
                            'equals'       => $q->where('available_balance', $value),
                            default        => $q,
                        };
                    });
                    break;

                case 'has_loan':
                    if ($value === 'yes') {
                        $query->whereHas('loans');
                    } else {
                        $query->whereDoesntHave('loans');
                    }
                    break;

                case 'loan_status':
                    $query->whereHas('loans', fn($q) => $q->where('status', $value));
                    break;

                case 'has_insurance':
                    if ($value === 'yes') {
                        $query->whereHas('insurancePolicies');
                    } else {
                        $query->whereDoesntHave('insurancePolicies');
                    }
                    break;

                case 'branch_id':
                    $query->where('branch_id', $value);
                    break;

                case 'age':
                    $age = (int) $value;
                    match ($operator) {
                        'greater_than' => $query->where('date_of_birth', '<', now()->subYears($age)),
                        'less_than'    => $query->where('date_of_birth', '>', now()->subYears($age)),
                        default        => $query,
                    };
                    break;

                case 'days_since_last_transaction':
                    $days = (int) $value;
                    $query->whereHas('accounts', function ($q) use ($operator, $days) {
                        $q->whereHas('transactions', function ($tq) use ($operator, $days) {
                            // empty — we use the inverse
                        });
                        // Simpler: check latest transaction date
                        match ($operator) {
                            'greater_than' => $q->whereDoesntHave('transactions', fn($tq) => $tq->where('created_at', '>=', now()->subDays($days))),
                            'less_than'    => $q->whereHas('transactions', fn($tq) => $tq->where('created_at', '>=', now()->subDays($days))),
                            default        => null,
                        };
                    });
                    break;

                case 'created_at':
                    match ($operator) {
                        'greater_than', 'after'  => $query->where('customers.created_at', '>', $value),
                        'less_than', 'before'    => $query->where('customers.created_at', '<', $value),
                        'equals'                 => $query->whereDate('customers.created_at', $value),
                        default                  => $query,
                    };
                    break;
            }
        }

        return $query;
    }

    // ─── TEMPLATES ──────────────────────────────────────────────────────────────

    public function templates()
    {
        $templates = MarketingTemplate::with('createdBy')->latest()->paginate(20);

        return view('marketing.templates.index', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:150',
            'channel' => 'required|in:sms,email,whatsapp',
            'subject' => 'nullable|required_if:channel,email|string|max:255',
            'body'    => 'required|string',
        ]);

        // Extract placeholders from body
        preg_match_all('/\{(\w+)\}/', $request->body, $matches);
        $placeholders = array_unique($matches[1] ?? []);

        MarketingTemplate::create([
            'tenant_id'    => auth()->user()->tenant_id,
            'name'         => $request->name,
            'channel'      => $request->channel,
            'subject'      => $request->subject,
            'body'         => $request->body,
            'placeholders' => array_values($placeholders),
            'created_by'   => auth()->id(),
        ]);

        return redirect()->route('marketing.templates')
            ->with('success', 'Template created.');
    }

    public function deleteTemplate($id)
    {
        $template = MarketingTemplate::findOrFail($id);
        $template->delete();

        return back()->with('success', 'Template deleted.');
    }

    // ─── CROSS-SELL ─────────────────────────────────────────────────────────────

    public function crossSells(Request $request)
    {
        $query = MarketingCrossSell::with('customer', 'assignedTo');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('opportunity_type', $request->type);
        }

        $crossSells = $query->latest()->paginate(20)->withQueryString();
        $users      = User::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get();

        return view('marketing.cross-sells.index', compact('crossSells', 'users'));
    }

    public function generateCrossSells()
    {
        $tenantId = auth()->user()->tenant_id;
        $created  = 0;

        // 1) Savings customers without a loan → suggest loan
        $savingsNoLoan = Customer::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('accounts', fn($q) => $q->where('type', 'savings')->where('available_balance', '>', 10000))
            ->whereDoesntHave('loans')
            ->whereDoesntHave('crossSells', fn($q) => $q->where('opportunity_type', 'savings_to_loan')->whereIn('status', ['identified', 'contacted', 'interested']))
            ->take(100)
            ->get();

        foreach ($savingsNoLoan as $customer) {
            $balance = $customer->accounts()->where('type', 'savings')->max('available_balance');
            MarketingCrossSell::create([
                'tenant_id'          => $tenantId,
                'customer_id'        => $customer->id,
                'opportunity_type'   => 'savings_to_loan',
                'recommended_product' => 'Personal Loan',
                'reason'             => 'Active savings customer with balance of ' . number_format($balance, 2) . ' — no existing loan.',
                'estimated_value'    => $balance * 2,
                'status'             => 'identified',
            ]);
            $created++;
        }

        // 2) Loan customers without insurance → suggest insurance
        $loanNoInsurance = Customer::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('loans', fn($q) => $q->where('status', 'active'))
            ->whereDoesntHave('insurancePolicies')
            ->whereDoesntHave('crossSells', fn($q) => $q->where('opportunity_type', 'loan_to_insurance')->whereIn('status', ['identified', 'contacted', 'interested']))
            ->take(100)
            ->get();

        foreach ($loanNoInsurance as $customer) {
            $loanAmount = $customer->loans()->where('status', 'active')->sum('principal_amount');
            MarketingCrossSell::create([
                'tenant_id'          => $tenantId,
                'customer_id'        => $customer->id,
                'opportunity_type'   => 'loan_to_insurance',
                'recommended_product' => 'Loan Protection Insurance',
                'reason'             => 'Active loan of ' . number_format($loanAmount, 2) . ' without insurance coverage.',
                'estimated_value'    => $loanAmount * 0.03,
                'status'             => 'identified',
            ]);
            $created++;
        }

        // 3) Loan customers without savings → suggest savings
        $loanNoSavings = Customer::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereHas('loans')
            ->whereDoesntHave('accounts', fn($q) => $q->where('type', 'savings'))
            ->whereDoesntHave('crossSells', fn($q) => $q->where('opportunity_type', 'loan_to_savings')->whereIn('status', ['identified', 'contacted', 'interested']))
            ->take(100)
            ->get();

        foreach ($loanNoSavings as $customer) {
            MarketingCrossSell::create([
                'tenant_id'          => $tenantId,
                'customer_id'        => $customer->id,
                'opportunity_type'   => 'loan_to_savings',
                'recommended_product' => 'Savings Account',
                'reason'             => 'Loan customer with no savings account — opportunity for deposit mobilisation.',
                'estimated_value'    => 50000,
                'status'             => 'identified',
            ]);
            $created++;
        }

        return back()->with('success', "{$created} cross-sell opportunities generated.");
    }

    public function updateCrossSell(Request $request, $id)
    {
        $request->validate([
            'status'      => 'required|in:identified,contacted,interested,converted,declined',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $crossSell = MarketingCrossSell::findOrFail($id);

        $data = ['status' => $request->status];

        if ($request->filled('assigned_to')) {
            $data['assigned_to'] = $request->assigned_to;
        }

        if ($request->status === 'contacted' && !$crossSell->contacted_at) {
            $data['contacted_at'] = now();
        }
        if ($request->status === 'converted') {
            $data['converted_at'] = now();
        }

        $crossSell->update($data);

        return back()->with('success', 'Cross-sell opportunity updated.');
    }

    // ─── ANALYTICS ──────────────────────────────────────────────────────────────

    public function analytics()
    {
        $tenantId = auth()->user()->tenant_id;

        // Channel breakdown
        $channelStats = MarketingCampaign::where('tenant_id', $tenantId)
            ->selectRaw("channel, COUNT(*) as count, SUM(sent_count) as sent, SUM(delivered_count) as delivered, SUM(opened_count) as opened")
            ->groupBy('channel')
            ->get();

        // Monthly performance (last 6 months)
        $monthlyStats = MarketingCampaign::selectRaw("TO_CHAR(created_at, 'YYYY-MM') as month, COUNT(*) as campaigns, SUM(sent_count) as sent, SUM(delivered_count) as delivered, SUM(opened_count) as opened, SUM(converted_count) as converted")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupByRaw("TO_CHAR(created_at, 'YYYY-MM')")
            ->orderBy('month')
            ->get();

        // Top performing campaigns
        $topCampaigns = MarketingCampaign::where('sent_count', '>', 0)
            ->orderByDesc('delivered_count')
            ->take(10)
            ->get();

        // Overall rates
        $totalSent      = MarketingCampaign::sum('sent_count');
        $totalDelivered  = MarketingCampaign::sum('delivered_count');
        $totalOpened     = MarketingCampaign::sum('opened_count');
        $totalConverted  = MarketingCampaign::sum('converted_count');

        $deliveryRate   = $totalSent > 0 ? round(($totalDelivered / $totalSent) * 100, 1) : 0;
        $openRate       = $totalDelivered > 0 ? round(($totalOpened / $totalDelivered) * 100, 1) : 0;
        $conversionRate = $totalDelivered > 0 ? round(($totalConverted / $totalDelivered) * 100, 1) : 0;

        return view('marketing.analytics', compact(
            'channelStats', 'monthlyStats', 'topCampaigns',
            'deliveryRate', 'openRate', 'conversionRate',
            'totalSent', 'totalDelivered', 'totalOpened', 'totalConverted'
        ));
    }
}
