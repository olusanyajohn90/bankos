<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WorkspaceDashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        $userId   = Auth::id();
        $today    = now()->toDateString();

        try {
            // ── Chat Stats ──
            $totalConversations = DB::table('chat_conversations')
                ->where('tenant_id', $tenantId)
                ->count();

            $activeChannels = DB::table('chat_conversations')
                ->where('tenant_id', $tenantId)
                ->where('type', 'channel')
                ->count();

            $messagesToday = DB::table('chat_messages')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', $today)
                ->count();

            $myUnreadMessages = DB::table('chat_messages')
                ->where('tenant_id', $tenantId)
                ->where('recipient_id', $userId)
                ->whereNull('read_at')
                ->count();

            // ── Document Stats ──
            $totalDocuments = DB::table('documents')
                ->where('tenant_id', $tenantId)
                ->count();

            $pendingReviewDocs = DB::table('documents')
                ->where('tenant_id', $tenantId)
                ->where('status', 'pending_review')
                ->count();

            $recentlyUploaded = DB::table('documents')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', now()->subDays(7))
                ->count();

            $myDocActions = DB::table('document_workflow_steps')
                ->where('assignee_id', $userId)
                ->where('status', 'pending')
                ->count();

            // ── Support Tickets ──
            $openTickets = DB::table('support_tickets')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();

            $resolvedToday = DB::table('support_tickets')
                ->where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', $today)
                ->count();

            $avgResolutionHours = DB::table('support_tickets')
                ->where('tenant_id', $tenantId)
                ->where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->whereDate('resolved_at', '>=', now()->subDays(30))
                ->selectRaw("AVG(EXTRACT(EPOCH FROM (resolved_at - created_at)) / 3600) as avg_hours")
                ->value('avg_hours');
            $avgResolutionHours = $avgResolutionHours ? round($avgResolutionHours, 1) : 0;

            $myTickets = DB::table('support_tickets')
                ->where('tenant_id', $tenantId)
                ->where('assigned_to', $userId)
                ->whereNotIn('status', ['resolved', 'closed', 'cancelled'])
                ->count();

            // ── Calendar Events ──
            $eventsToday = DB::table('calendar_events')
                ->where('tenant_id', $tenantId)
                ->whereDate('start_at', $today)
                ->count();

            $eventsThisWeek = DB::table('calendar_events')
                ->where('tenant_id', $tenantId)
                ->whereDate('start_at', '>=', now()->startOfWeek())
                ->whereDate('start_at', '<=', now()->endOfWeek())
                ->count();

            $upcomingEvents = DB::table('calendar_events')
                ->where('tenant_id', $tenantId)
                ->whereDate('start_at', '>=', $today)
                ->whereDate('start_at', '<=', now()->addDays(7)->toDateString())
                ->orderBy('start_at')
                ->limit(5)
                ->get();

            // ── Active Tasks from Chat ──
            $activeTasks = DB::table('chat_tasks')
                ->where('tenant_id', $tenantId)
                ->where('assigned_to', $userId)
                ->where('status', 'pending')
                ->orderBy('due_at')
                ->limit(5)
                ->get();

            $totalPendingTasks = DB::table('chat_tasks')
                ->where('tenant_id', $tenantId)
                ->where('assigned_to', $userId)
                ->where('status', 'pending')
                ->count();

            // ── Recent Announcements ──
            $announcements = DB::table('announcements')
                ->where('tenant_id', $tenantId)
                ->where('is_published', true)
                ->orderByDesc('is_pinned')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            // ── Team Online Status ──
            $onlineUsers = DB::table('chat_presence')
                ->where('tenant_id', $tenantId)
                ->where('last_seen_at', '>=', now()->subMinutes(5))
                ->count();

            $totalUsers = DB::table('users')
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->count();

            // ── Charts: Messages per day (last 14 days) ──
            $messageTrend = DB::table('chat_messages')
                ->where('tenant_id', $tenantId)
                ->whereDate('created_at', '>=', now()->subDays(14))
                ->select(DB::raw("DATE(created_at) as date"), DB::raw("count(*) as total"))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->orderBy('date')
                ->get();

            // ── Charts: Tickets by status ──
            $ticketsByStatus = DB::table('support_tickets')
                ->where('tenant_id', $tenantId)
                ->select('status', DB::raw("count(*) as total"))
                ->groupBy('status')
                ->pluck('total', 'status');

            // ── Charts: Documents by status ──
            $docsByStatus = DB::table('documents')
                ->where('tenant_id', $tenantId)
                ->select('status', DB::raw("count(*) as total"))
                ->groupBy('status')
                ->pluck('total', 'status');

        } catch (\Exception $e) {
            $totalConversations = $activeChannels = $messagesToday = $myUnreadMessages = 0;
            $totalDocuments = $pendingReviewDocs = $recentlyUploaded = $myDocActions = 0;
            $openTickets = $resolvedToday = $avgResolutionHours = $myTickets = 0;
            $eventsToday = $eventsThisWeek = 0;
            $upcomingEvents = collect();
            $activeTasks = collect();
            $totalPendingTasks = 0;
            $announcements = collect();
            $onlineUsers = $totalUsers = 0;
            $messageTrend = collect();
            $ticketsByStatus = collect();
            $docsByStatus = collect();
        }

        return view('workspace.dashboard', compact(
            'totalConversations', 'activeChannels', 'messagesToday', 'myUnreadMessages',
            'totalDocuments', 'pendingReviewDocs', 'recentlyUploaded', 'myDocActions',
            'openTickets', 'resolvedToday', 'avgResolutionHours', 'myTickets',
            'eventsToday', 'eventsThisWeek', 'upcomingEvents',
            'activeTasks', 'totalPendingTasks',
            'announcements',
            'onlineUsers', 'totalUsers',
            'messageTrend', 'ticketsByStatus', 'docsByStatus'
        ));
    }
}
