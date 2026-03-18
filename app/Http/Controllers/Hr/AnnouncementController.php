<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = session('tenant_id');

        // Published announcements for all staff
        $announcements = Announcement::where('tenant_id', $tenantId)
            ->published()
            ->with('createdBy')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        // Mark visible ones as read
        foreach ($announcements as $a) {
            $a->markReadBy(auth()->id());
        }

        $unread = Announcement::where('tenant_id', $tenantId)
            ->published()
            ->whereDoesntHave('readers', fn($q) => $q->where('user_id', auth()->id()))
            ->count();

        return view('hr.announcements.index', compact('announcements','unread'));
    }

    public function manage(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','super_admin']), 403);

        $tenantId = session('tenant_id');
        $announcements = Announcement::where('tenant_id', $tenantId)
            ->with('createdBy')
            ->orderByDesc('created_at')
            ->paginate(25)->withQueryString();

        return view('hr.announcements.manage', compact('announcements'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','super_admin']), 403);

        $request->validate([
            'title'      => 'required|string|max:300',
            'body'       => 'required|string',
            'priority'   => 'required|in:low,normal,high,urgent',
            'audience'   => 'required|in:all,branch,department,role',
            'publish_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:publish_at',
        ]);

        $publishNow = ! $request->filled('publish_at');

        Announcement::create([
            'id'           => Str::uuid(),
            'tenant_id'    => session('tenant_id'),
            'created_by'   => auth()->id(),
            'title'        => $request->title,
            'body'         => $request->body,
            'priority'     => $request->priority,
            'audience'     => $request->audience,
            'publish_at'   => $request->publish_at,
            'expires_at'   => $request->expires_at,
            'is_pinned'    => $request->boolean('is_pinned'),
            'is_published' => $publishNow || $request->boolean('publish_now'),
        ]);

        return back()->with('success', 'Announcement ' . ($publishNow ? 'published' : 'saved as draft') . '.');
    }

    public function publish(Announcement $announcement)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','super_admin']), 403);
        $announcement->update(['is_published' => true, 'publish_at' => now()]);
        return back()->with('success', 'Announcement published.');
    }

    public function destroy(Announcement $announcement)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','super_admin']), 403);
        $announcement->delete();
        return back()->with('success', 'Announcement deleted.');
    }

    public function togglePin(Announcement $announcement)
    {
        abort_unless(auth()->user()->hasRole(['admin','hr_manager','super_admin']), 403);
        $announcement->update(['is_pinned' => ! $announcement->is_pinned]);
        return back()->with('success', $announcement->is_pinned ? 'Pinned.' : 'Unpinned.');
    }
}
