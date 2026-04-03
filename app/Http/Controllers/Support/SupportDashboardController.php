<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\Support\SupportTicket;
use App\Models\Support\SupportTeam;
use Illuminate\Support\Facades\DB;

class SupportDashboardController extends Controller
{
    public function index()
    {
        $tenantId = session('tenant_id');
        $userId   = auth()->id();

        $stats = [
            'open'        => SupportTicket::where('tenant_id', $tenantId)->where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
            'pending'     => SupportTicket::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'resolved_today' => SupportTicket::where('tenant_id', $tenantId)->where('status', 'resolved')
                ->whereDate('resolved_at', today())->count(),
            'breached'    => SupportTicket::where('tenant_id', $tenantId)->where('sla_breached', true)
                ->whereNotIn('status', ['resolved','closed','cancelled'])->count(),
            'my_open'     => SupportTicket::where('tenant_id', $tenantId)->where('assigned_to', $userId)
                ->whereNotIn('status', ['resolved','closed','cancelled'])->count(),
        ];

        $byPriority = SupportTicket::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['resolved','closed','cancelled'])
            ->select('priority', DB::raw('count(*) as total'))
            ->groupBy('priority')->pluck('total', 'priority');

        $byTeam = SupportTeam::where('tenant_id', $tenantId)->where('is_active', true)
            ->withCount(['openTickets', 'members'])->get();

        $recentTickets = SupportTicket::where('tenant_id', $tenantId)
            ->with('assignedTo','team','category')
            ->orderByDesc('created_at')->limit(10)->get();

        $myTickets = SupportTicket::where('tenant_id', $tenantId)
            ->where('assigned_to', $userId)
            ->whereNotIn('status', ['resolved','closed','cancelled'])
            ->with('category','team')->orderBy('sla_resolution_due_at')->limit(10)->get();

        // ── Enhanced: SLA Compliance Rate ──
        try {
            $totalResolved = SupportTicket::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', '>=', now()->subDays(30))
                ->count();

            $slaBreachedResolved = SupportTicket::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->where('sla_breached', true)
                ->whereDate('resolved_at', '>=', now()->subDays(30))
                ->count();

            $slaComplianceRate = $totalResolved > 0
                ? round((($totalResolved - $slaBreachedResolved) / $totalResolved) * 100, 1)
                : 100;

            // ── Enhanced: Resolution Time ──
            $avgResolutionHours = SupportTicket::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->whereDate('resolved_at', '>=', now()->subDays(30))
                ->selectRaw("AVG(EXTRACT(EPOCH FROM (resolved_at - created_at)) / 3600) as avg_hours")
                ->value('avg_hours');
            $avgResolutionHours = $avgResolutionHours ? round($avgResolutionHours, 1) : 0;

            $medianResolutionHours = 0; // PostgreSQL median approximation
            $resolutionTimes = SupportTicket::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->whereDate('resolved_at', '>=', now()->subDays(30))
                ->selectRaw("EXTRACT(EPOCH FROM (resolved_at - created_at)) / 3600 as hours")
                ->orderBy('hours')
                ->pluck('hours');
            if ($resolutionTimes->count() > 0) {
                $mid = (int) floor($resolutionTimes->count() / 2);
                $medianResolutionHours = round($resolutionTimes[$mid], 1);
            }

            // ── Enhanced: Tickets by Category ──
            $byCategory = SupportTicket::where('tenant_id', $tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->whereNotNull('category_id')
                ->join('support_categories', 'support_tickets.category_id', '=', 'support_categories.id')
                ->select('support_categories.name', DB::raw('count(*) as total'))
                ->groupBy('support_categories.name')
                ->orderByDesc('total')
                ->limit(10)
                ->pluck('total', 'name');

            // ── Enhanced: Agent Performance ──
            $agentPerformance = SupportTicket::where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereNotNull('assigned_to')
                ->whereDate('resolved_at', '>=', now()->subDays(30))
                ->join('users', 'support_tickets.assigned_to', '=', 'users.id')
                ->select('users.name', DB::raw('count(*) as resolved_count'))
                ->groupBy('users.name')
                ->orderByDesc('resolved_count')
                ->limit(10)
                ->get();

            // ── Enhanced: Ticket Trend (last 30 days) ──
            $ticketTrend = SupportTicket::where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', now()->subDays(30))
                ->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

            // ── Enhanced: Customer Satisfaction (from ticket ratings if available) ──
            $avgSatisfaction = DB::table('support_tickets')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('satisfaction_rating')
                ->avg('satisfaction_rating');
            $avgSatisfaction = $avgSatisfaction ? round($avgSatisfaction, 1) : 0;

            $satisfactionCount = DB::table('support_tickets')
                ->where('tenant_id', $tenantId)
                ->whereNotNull('satisfaction_rating')
                ->count();

        } catch (\Exception $e) {
            $slaComplianceRate = 100;
            $avgResolutionHours = $medianResolutionHours = 0;
            $byCategory = collect();
            $agentPerformance = collect();
            $ticketTrend = collect();
            $avgSatisfaction = 0;
            $satisfactionCount = 0;
        }

        return view('support.dashboard', compact(
            'stats', 'byPriority', 'byTeam', 'recentTickets', 'myTickets',
            'slaComplianceRate', 'avgResolutionHours', 'medianResolutionHours',
            'byCategory', 'agentPerformance', 'ticketTrend',
            'avgSatisfaction', 'satisfactionCount'
        ));
    }
}
