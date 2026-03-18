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

        return view('support.dashboard', compact('stats','byPriority','byTeam','recentTickets','myTickets'));
    }
}
