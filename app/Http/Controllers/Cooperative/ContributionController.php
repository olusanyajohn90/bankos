<?php

namespace App\Http\Controllers\Cooperative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ContributionController extends Controller
{
    /**
     * Dashboard: list contribution schedules, total collected this month, compliance rate.
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $schedules = DB::table('contribution_schedules')
            ->where('tenant_id', $tenantId)
            ->orderBy('name')
            ->get();

        $currentMonth = Carbon::now()->format('Y-m');

        $totalCollectedThisMonth = DB::table('member_contributions')
            ->where('tenant_id', $tenantId)
            ->where('period', $currentMonth)
            ->where('status', 'paid')
            ->sum('amount');

        $totalMembers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        // Members who have at least one paid contribution this month
        $paidMembers = DB::table('member_contributions')
            ->where('tenant_id', $tenantId)
            ->where('period', $currentMonth)
            ->where('status', 'paid')
            ->distinct('customer_id')
            ->count('customer_id');

        $complianceRate = $totalMembers > 0 ? round(($paidMembers / $totalMembers) * 100, 1) : 0;

        // Per-schedule stats for the current month
        $scheduleStats = DB::table('contribution_schedules as cs')
            ->leftJoin('member_contributions as mc', function ($join) use ($currentMonth) {
                $join->on('mc.contribution_schedule_id', '=', 'cs.id')
                    ->where('mc.period', '=', $currentMonth)
                    ->where('mc.status', '=', 'paid');
            })
            ->where('cs.tenant_id', $tenantId)
            ->groupBy('cs.id', 'cs.name', 'cs.amount', 'cs.frequency', 'cs.mandatory', 'cs.status')
            ->select([
                'cs.id',
                'cs.name',
                'cs.amount',
                'cs.frequency',
                'cs.mandatory',
                'cs.status',
                DB::raw('COALESCE(SUM(mc.amount), 0) as total_collected'),
                DB::raw('COUNT(DISTINCT mc.customer_id) as members_paid'),
            ])
            ->orderBy('cs.name')
            ->get();

        return view('cooperative.contributions.index', compact(
            'schedules', 'totalCollectedThisMonth', 'totalMembers',
            'paidMembers', 'complianceRate', 'scheduleStats', 'currentMonth'
        ));
    }

    /**
     * Form to create a contribution schedule.
     */
    public function createSchedule()
    {
        return view('cooperative.contributions.schedule-create');
    }

    /**
     * Save a contribution schedule.
     */
    public function storeSchedule(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric|min:0.01',
            'frequency'   => 'required|in:monthly,weekly,quarterly,annual,one_time',
            'mandatory'   => 'sometimes|boolean',
        ]);

        DB::table('contribution_schedules')->insert([
            'id'          => Str::uuid()->toString(),
            'tenant_id'   => Auth::user()->tenant_id,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'amount'      => $validated['amount'],
            'frequency'   => $validated['frequency'],
            'mandatory'   => $request->has('mandatory') ? true : false,
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('cooperative.contributions.index')
            ->with('success', 'Contribution schedule created successfully.');
    }

    /**
     * Show schedule with member payment status for current period.
     */
    public function showSchedule($id)
    {
        $tenantId = Auth::user()->tenant_id;

        $schedule = DB::table('contribution_schedules')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$schedule) {
            abort(404);
        }

        $currentPeriod = $this->getCurrentPeriod($schedule->frequency);

        // All active members
        $members = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Payments for this schedule in the current period
        $payments = DB::table('member_contributions')
            ->where('tenant_id', $tenantId)
            ->where('contribution_schedule_id', $id)
            ->where('period', $currentPeriod)
            ->get()
            ->keyBy('customer_id');

        return view('cooperative.contributions.schedule-show', compact(
            'schedule', 'members', 'payments', 'currentPeriod'
        ));
    }

    /**
     * Single contribution collection form.
     */
    public function collect()
    {
        $tenantId = Auth::user()->tenant_id;

        $customers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $schedules = DB::table('contribution_schedules')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('cooperative.contributions.collect', compact('customers', 'schedules'));
    }

    /**
     * Record a single contribution payment.
     */
    public function storeCollect(Request $request)
    {
        $validated = $request->validate([
            'customer_id'              => 'required|uuid|exists:customers,id',
            'contribution_schedule_id' => 'required|uuid|exists:contribution_schedules,id',
            'amount'                   => 'required|numeric|min:0.01',
            'period'                   => 'required|string|max:20',
            'payment_method'           => 'required|in:cash,transfer,deduction',
            'reference'                => 'nullable|string|max:100',
            'notes'                    => 'nullable|string',
        ]);

        DB::table('member_contributions')->insert([
            'id'                       => Str::uuid()->toString(),
            'tenant_id'                => Auth::user()->tenant_id,
            'customer_id'              => $validated['customer_id'],
            'contribution_schedule_id' => $validated['contribution_schedule_id'],
            'amount'                   => $validated['amount'],
            'period'                   => $validated['period'],
            'payment_method'           => $validated['payment_method'],
            'reference'                => $validated['reference'] ?? null,
            'status'                   => 'paid',
            'notes'                    => $validated['notes'] ?? null,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);

        return redirect()->route('cooperative.contributions.index')
            ->with('success', 'Contribution recorded successfully.');
    }

    /**
     * Bulk collection form (meeting-style).
     */
    public function bulkCollect()
    {
        $tenantId = Auth::user()->tenant_id;

        $customers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $schedules = DB::table('contribution_schedules')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('cooperative.contributions.bulk-collect', compact('customers', 'schedules'));
    }

    /**
     * Process bulk collection.
     */
    public function storeBulkCollect(Request $request)
    {
        $validated = $request->validate([
            'contribution_schedule_id' => 'required|uuid|exists:contribution_schedules,id',
            'period'                   => 'required|string|max:20',
            'payment_method'           => 'required|in:cash,transfer,deduction',
            'members'                  => 'required|array|min:1',
            'members.*'                => 'uuid|exists:customers,id',
        ]);

        $tenantId = Auth::user()->tenant_id;

        $schedule = DB::table('contribution_schedules')
            ->where('id', $validated['contribution_schedule_id'])
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$schedule) {
            abort(404);
        }

        $now = now();
        $records = [];

        foreach ($validated['members'] as $customerId) {
            $records[] = [
                'id'                       => Str::uuid()->toString(),
                'tenant_id'                => $tenantId,
                'customer_id'              => $customerId,
                'contribution_schedule_id' => $validated['contribution_schedule_id'],
                'amount'                   => $schedule->amount,
                'period'                   => $validated['period'],
                'payment_method'           => $validated['payment_method'],
                'reference'                => null,
                'status'                   => 'paid',
                'notes'                    => 'Bulk collection',
                'created_at'               => $now,
                'updated_at'               => $now,
            ];
        }

        // Insert in chunks for large cooperatives
        foreach (array_chunk($records, 100) as $chunk) {
            DB::table('member_contributions')->insert($chunk);
        }

        $count = count($validated['members']);

        return redirect()->route('cooperative.contributions.index')
            ->with('success', "Bulk collection recorded for {$count} members.");
    }

    /**
     * Show a member's contribution history.
     */
    public function memberHistory($customerId)
    {
        $tenantId = Auth::user()->tenant_id;

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$customer) {
            abort(404);
        }

        $contributions = DB::table('member_contributions as mc')
            ->join('contribution_schedules as cs', 'cs.id', '=', 'mc.contribution_schedule_id')
            ->where('mc.tenant_id', $tenantId)
            ->where('mc.customer_id', $customerId)
            ->select('mc.*', 'cs.name as schedule_name')
            ->orderByDesc('mc.period')
            ->orderByDesc('mc.created_at')
            ->paginate(30);

        $totalPaid = DB::table('member_contributions')
            ->where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('status', 'paid')
            ->sum('amount');

        return view('cooperative.contributions.member-history', compact(
            'customer', 'contributions', 'totalPaid'
        ));
    }

    /**
     * Contribution compliance report: who has paid, who hasn't, by period.
     */
    public function report(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $scheduleId = $request->query('schedule_id');
        $period = $request->query('period', Carbon::now()->format('Y-m'));

        $schedules = DB::table('contribution_schedules')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $members = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        $report = [];

        if ($scheduleId) {
            $schedule = DB::table('contribution_schedules')
                ->where('id', $scheduleId)
                ->where('tenant_id', $tenantId)
                ->first();

            // Get all payments for this schedule in the period
            $payments = DB::table('member_contributions')
                ->where('tenant_id', $tenantId)
                ->where('contribution_schedule_id', $scheduleId)
                ->where('period', $period)
                ->get()
                ->keyBy('customer_id');

            foreach ($members as $member) {
                $payment = $payments->get($member->id);
                $report[] = (object) [
                    'customer'   => $member,
                    'paid'       => $payment && $payment->status === 'paid',
                    'amount'     => $payment ? $payment->amount : 0,
                    'status'     => $payment ? $payment->status : 'unpaid',
                    'payment_id' => $payment ? $payment->id : null,
                ];
            }
        }

        $selectedSchedule = $scheduleId
            ? $schedules->firstWhere('id', $scheduleId)
            : null;

        return view('cooperative.contributions.report', compact(
            'schedules', 'report', 'period', 'scheduleId', 'selectedSchedule'
        ));
    }

    /**
     * Helper: get current period string based on frequency.
     */
    private function getCurrentPeriod(string $frequency): string
    {
        return match ($frequency) {
            'weekly'    => Carbon::now()->format('Y-\\WW'),
            'monthly'   => Carbon::now()->format('Y-m'),
            'quarterly' => Carbon::now()->format('Y') . '-Q' . ceil(Carbon::now()->month / 3),
            'annual'    => Carbon::now()->format('Y'),
            default     => Carbon::now()->format('Y-m'),
        };
    }
}
