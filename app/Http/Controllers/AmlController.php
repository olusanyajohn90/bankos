<?php

namespace App\Http\Controllers;

use App\Models\AmlAlert;
use App\Models\AmlRule;
use App\Models\TransactionLimit;
use App\Models\SuspiciousTransactionReport;
use App\Services\AmlScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AmlController extends Controller
{
    private function tenantId(): string
    {
        return Auth::user()->tenant_id;
    }

    // ── ALERTS INDEX ──────────────────────────────────────────────────────────

    public function index(Request $r)
    {
        $tenantId = $this->tenantId();

        $query = AmlAlert::where('tenant_id', $tenantId)
            ->orderByDesc('created_at');

        if ($r->filled('severity')) {
            $query->where('severity', $r->severity);
        }
        if ($r->filled('status')) {
            $query->where('status', $r->status);
        }
        if ($r->filled('from')) {
            $query->whereDate('created_at', '>=', $r->from);
        }
        if ($r->filled('to')) {
            $query->whereDate('created_at', '<=', $r->to);
        }
        if ($r->filled('tab') && $r->tab !== 'all') {
            if ($r->tab === 'critical') {
                $query->where('severity', 'critical');
            } else {
                $query->where('status', $r->tab);
            }
        }

        $alerts = $query->paginate(25)->withQueryString();

        // Stats
        $openCount     = AmlAlert::where('tenant_id', $tenantId)->where('status', 'open')->count();
        $critHighCount = AmlAlert::where('tenant_id', $tenantId)->whereIn('severity', ['critical', 'high'])->whereIn('status', ['open', 'under_review'])->count();
        $pendingStrs   = SuspiciousTransactionReport::where('tenant_id', $tenantId)->where('status', 'draft')->count();
        $sanctionsToday = AmlAlert::where('tenant_id', $tenantId)
            ->where('alert_type', 'sanctions_match')
            ->whereDate('created_at', today())
            ->count();

        // Customer lookup
        $customerIds = $alerts->pluck('customer_id')->filter()->unique()->values()->toArray();
        $customers   = DB::table('customers')
            ->whereIn('id', $customerIds)
            ->get(['id', 'first_name', 'last_name', 'customer_number'])
            ->keyBy('id');

        return view('aml.index', compact(
            'alerts', 'openCount', 'critHighCount', 'pendingStrs',
            'sanctionsToday', 'customers'
        ));
    }

    // ── ALERT DETAIL ──────────────────────────────────────────────────────────

    public function show(string $id)
    {
        $tenantId = $this->tenantId();
        $alert    = AmlAlert::where('tenant_id', $tenantId)->findOrFail($id);

        $customer = $alert->customer_id
            ? (DB::table('customers')->where('id', $alert->customer_id)->first() ?: null)
            : null;

        $account = $alert->account_id
            ? (DB::table('accounts')->where('id', $alert->account_id)->first() ?: null)
            : null;

        $transaction = $alert->transaction_id
            ? (DB::table('transactions')->where('id', $alert->transaction_id)->first() ?: null)
            : null;

        $reviewer = $alert->reviewed_by
            ? (DB::table('users')->where('id', $alert->reviewed_by)->first() ?: null)
            : null;

        // Recent transactions for context
        $recentTxns = collect();
        if ($alert->account_id) {
            $recentTxns = DB::table('transactions')
                ->where('account_id', $alert->account_id)
                ->orderByDesc('created_at')
                ->limit(15)
                ->get();
        }

        // Related STRs
        $relatedStrs = SuspiciousTransactionReport::where('tenant_id', $tenantId)
            ->whereJsonContains('alert_ids', $id)
            ->get();

        return view('aml.show', compact('alert', 'customer', 'account', 'transaction', 'reviewer', 'recentTxns', 'relatedStrs'));
    }

    // ── REVIEW ALERT ──────────────────────────────────────────────────────────

    public function review(Request $r, string $id)
    {
        $r->validate([
            'status' => 'required|in:under_review,escalated,dismissed,reported',
            'notes'  => 'nullable|string|max:2000',
        ]);

        $tenantId = $this->tenantId();
        $alert    = AmlAlert::where('tenant_id', $tenantId)->findOrFail($id);

        $alert->update([
            'status'      => $r->status,
            'notes'       => $r->notes,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::id(),
        ]);

        return redirect()->route('aml.show', $id)
            ->with('success', 'Alert updated to ' . ucfirst(str_replace('_', ' ', $r->status)) . '.');
    }

    // ── SANCTIONS SCREEN (AJAX) ───────────────────────────────────────────────

    public function screenName(Request $r)
    {
        $r->validate([
            'name' => 'required|string|min:2|max:200',
            'dob'  => 'nullable|date',
        ]);

        $result = AmlScoringService::screenSanctions($r->name, $r->dob);

        return response()->json($result);
    }

    // ── TRANSACTION LIMITS ────────────────────────────────────────────────────

    public function limits(Request $r)
    {
        $tenantId = $this->tenantId();
        $limits   = TransactionLimit::where('tenant_id', $tenantId)
            ->orderBy('kyc_tier')
            ->orderBy('channel')
            ->orderBy('transaction_type')
            ->get();

        $tiers    = ['level_1', 'level_2', 'level_3'];
        $channels = ['portal', 'api', 'ussd', 'agent', 'teller', 'all'];
        $types    = ['transfer', 'withdrawal', 'bill_payment', 'airtime', 'all'];

        // Index by tier/channel/type for easy lookup in view
        $limitsMap = $limits->keyBy(fn($l) => "{$l->kyc_tier}_{$l->channel}_{$l->transaction_type}");

        return view('aml.limits', compact('limits', 'limitsMap', 'tiers', 'channels', 'types'));
    }

    public function updateLimit(Request $r)
    {
        $r->validate([
            'id'               => 'nullable|uuid',
            'kyc_tier'         => 'required|in:level_1,level_2,level_3',
            'channel'          => 'required|in:portal,api,ussd,agent,teller,all',
            'transaction_type' => 'required|in:transfer,withdrawal,bill_payment,airtime,all',
            'single_limit'     => 'required|numeric|min:1',
            'daily_limit'      => 'required|numeric|min:1',
            'monthly_limit'    => 'nullable|numeric|min:1',
        ]);

        $tenantId = $this->tenantId();

        TransactionLimit::updateOrCreate(
            [
                'tenant_id'        => $tenantId,
                'kyc_tier'         => $r->kyc_tier,
                'channel'          => $r->channel,
                'transaction_type' => $r->transaction_type,
            ],
            [
                'id'            => $r->id ?? Str::uuid(),
                'single_limit'  => $r->single_limit,
                'daily_limit'   => $r->daily_limit,
                'monthly_limit' => $r->monthly_limit,
            ]
        );

        return back()->with('success', 'Transaction limit updated.');
    }

    // ── AML RULES ─────────────────────────────────────────────────────────────

    public function rules(Request $r)
    {
        $tenantId = $this->tenantId();
        $rules    = AmlRule::where('tenant_id', $tenantId)
            ->orderBy('rule_type')
            ->get();

        return view('aml.rules', compact('rules'));
    }

    public function updateRule(Request $r, string $id)
    {
        $r->validate([
            'is_active'         => 'required|boolean',
            'threshold_amount'  => 'nullable|numeric|min:0',
            'threshold_count'   => 'nullable|integer|min:1',
            'time_window_hours' => 'nullable|integer|min:1',
            'severity'          => 'nullable|in:low,medium,high,critical',
            'auto_block'        => 'nullable|boolean',
        ]);

        $tenantId = $this->tenantId();
        $rule     = AmlRule::where('tenant_id', $tenantId)->findOrFail($id);

        $rule->update($r->only([
            'is_active', 'threshold_amount', 'threshold_count',
            'time_window_hours', 'severity', 'auto_block',
        ]));

        return back()->with('success', 'Rule "' . $rule->rule_name . '" updated.');
    }

    // ── STR REPORTS ───────────────────────────────────────────────────────────

    public function strIndex(Request $r)
    {
        $tenantId = $this->tenantId();
        $strs     = SuspiciousTransactionReport::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate(20);

        // Customer lookup
        $customerIds = $strs->pluck('customer_id')->filter()->unique()->values()->toArray();
        $customers   = DB::table('customers')
            ->whereIn('id', $customerIds)
            ->get(['id', 'first_name', 'last_name'])
            ->keyBy('id');

        return view('aml.str_index', compact('strs', 'customers'));
    }

    public function strCreate(Request $r)
    {
        $r->validate([
            'alert_id'       => 'required|uuid',
            'summary'        => 'required|string|min:20|max:5000',
            'transaction_ids'=> 'nullable|string',
        ]);

        $tenantId = $this->tenantId();
        $alert    = AmlAlert::where('tenant_id', $tenantId)->findOrFail($r->alert_id);

        $txnIds = $r->transaction_ids
            ? array_filter(array_map('trim', explode(',', $r->transaction_ids)))
            : ($alert->transaction_id ? [$alert->transaction_id] : []);

        $reportNumber = 'STR-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $str = SuspiciousTransactionReport::create([
            'id'               => Str::uuid(),
            'tenant_id'        => $tenantId,
            'report_number'    => $reportNumber,
            'reporting_officer'=> Auth::id(),
            'customer_id'      => $alert->customer_id,
            'transaction_ids'  => $txnIds,
            'alert_ids'        => [$alert->id],
            'summary'          => $r->summary,
            'status'           => 'draft',
        ]);

        // Update alert status
        $alert->update(['status' => 'reported', 'reviewed_by' => Auth::id(), 'reviewed_at' => now()]);

        return redirect()->route('aml.str.index')
            ->with('success', "STR {$reportNumber} created successfully.");
    }

    public function strSubmit(Request $r, string $id)
    {
        $tenantId = $this->tenantId();
        $str      = SuspiciousTransactionReport::where('tenant_id', $tenantId)->findOrFail($id);

        $str->update([
            'status'       => 'submitted',
            'submitted_at' => now(),
        ]);

        return redirect()->route('aml.str.index')
            ->with('success', "STR {$str->report_number} marked as submitted to NFIU.");
    }

    // ── ALIASES / ADDITIONAL METHODS ──────────────────────────────────────────

    public function sanctionsScreen(Request $r)
    {
        return $this->screenName($r);
    }

    public function strList(Request $r)
    {
        return $this->strIndex($r);
    }

    public function storeRule(Request $r)
    {
        $r->validate([
            'name'                => 'required|string|max:100',
            'rule_type'           => 'required|in:velocity,amount_threshold,structuring,round_amount,dormancy_reactivation',
            'threshold_amount'    => 'nullable|numeric|min:0',
            'threshold_count'     => 'nullable|integer|min:1',
            'time_window_minutes' => 'nullable|integer|min:1',
            'severity'            => 'nullable|in:low,medium,high,critical',
            'auto_block'          => 'nullable|boolean',
        ]);

        $tenantId = $this->tenantId();
        AmlRule::create([
            'id'                  => \Illuminate\Support\Str::uuid(),
            'tenant_id'           => $tenantId,
            'rule_code'           => 'RULE-' . strtoupper(Str::random(6)),
            'rule_name'           => $r->name,
            'rule_type'           => $r->rule_type,
            'threshold_amount'    => $r->threshold_amount,
            'threshold_count'     => $r->threshold_count,
            'time_window_hours'   => $r->time_window_minutes ? intval($r->time_window_minutes / 60) : null,
            'severity'            => $r->severity ?? 'medium',
            'auto_block'          => $r->boolean('auto_block'),
            'is_active'           => true,
        ]);

        return back()->with('success', 'AML rule created.');
    }

    public function toggleRule(string $id)
    {
        $tenantId = $this->tenantId();
        $rule = AmlRule::where('tenant_id', $tenantId)->findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active]);
        return back()->with('success', 'Rule ' . ($rule->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function destroyRule(string $id)
    {
        $tenantId = $this->tenantId();
        AmlRule::where('tenant_id', $tenantId)->findOrFail($id)->delete();
        return back()->with('success', 'AML rule deleted.');
    }
}
