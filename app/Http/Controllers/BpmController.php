<?php

namespace App\Http\Controllers;

use App\Models\BpmProcess;
use App\Models\BpmInstance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BpmController extends Controller
{
    public function dashboard()
    {
        try {
            $totalProcesses = BpmProcess::count();
            $activeProcesses = BpmProcess::where('is_active', true)->count();
            $totalInstances = BpmInstance::count();
            $activeInstances = BpmInstance::where('status', 'active')->count();
            $completedInstances = BpmInstance::where('status', 'completed')->count();
            $onHoldInstances = BpmInstance::where('status', 'on_hold')->count();

            $avgCompletionHours = BpmProcess::whereNotNull('avg_completion_hours')->avg('avg_completion_hours') ?? 0;

            // By category
            $byCategory = BpmProcess::select('category', DB::raw('COUNT(*) as count'))
                ->groupBy('category')
                ->get();

            // Instance status distribution
            $instancesByStatus = BpmInstance::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();

            // Top processes by instances
            $topProcesses = BpmProcess::select('bpm_processes.name', 'bpm_processes.total_instances', 'bpm_processes.avg_completion_hours')
                ->orderByDesc('total_instances')
                ->limit(5)
                ->get();

            // Recently started
            $recentInstances = BpmInstance::with('process', 'initiator')
                ->latest()
                ->limit(5)
                ->get();

            // Bottleneck detection (instances stuck for > 48h on same step)
            $bottlenecks = BpmInstance::where('status', 'active')
                ->where('updated_at', '<', now()->subHours(48))
                ->count();

            // Monthly instances trend
            $monthlyTrend = BpmInstance::where('created_at', '>=', now()->subMonths(6))
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                ->orderBy('month')
                ->get();

        } catch (\Exception $e) {
            return view('bpm.dashboard', [
                'error' => $e->getMessage(),
                'totalProcesses' => 0, 'activeProcesses' => 0, 'totalInstances' => 0,
                'activeInstances' => 0, 'completedInstances' => 0, 'onHoldInstances' => 0,
                'avgCompletionHours' => 0, 'byCategory' => collect(), 'instancesByStatus' => collect(),
                'topProcesses' => collect(), 'recentInstances' => collect(),
                'bottlenecks' => 0, 'monthlyTrend' => collect(),
            ]);
        }

        return view('bpm.dashboard', compact(
            'totalProcesses', 'activeProcesses', 'totalInstances', 'activeInstances',
            'completedInstances', 'onHoldInstances', 'avgCompletionHours', 'byCategory',
            'instancesByStatus', 'topProcesses', 'recentInstances', 'bottlenecks', 'monthlyTrend'
        ));
    }

    public function processes(Request $request)
    {
        try {
            $query = BpmProcess::with('creator')->latest();
            if ($request->filled('category')) $query->where('category', $request->category);
            if ($request->has('active')) $query->where('is_active', $request->boolean('active'));
            $processes = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $processes = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('bpm.processes.index', compact('processes'));
    }

    public function createProcess()
    {
        return view('bpm.processes.create');
    }

    public function storeProcess(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string',
            'category'    => 'required|in:account_opening,loan_processing,kyc_verification,dispute_resolution,document_approval,custom',
            'steps'       => 'required|json',
        ]);

        try {
            BpmProcess::create([
                'name'        => $request->name,
                'description' => $request->description,
                'category'    => $request->category,
                'steps'       => json_decode($request->steps, true),
                'created_by'  => auth()->id(),
            ]);

            return redirect()->route('bpm.processes')->with('success', 'Process created.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function showProcess($id)
    {
        try {
            $process = BpmProcess::with('creator', 'instances')->findOrFail($id);
            $instanceStats = [
                'active'    => $process->instances->where('status', 'active')->count(),
                'completed' => $process->instances->where('status', 'completed')->count(),
                'cancelled' => $process->instances->where('status', 'cancelled')->count(),
            ];
        } catch (\Exception $e) {
            return redirect()->route('bpm.processes')->with('error', 'Process not found.');
        }

        return view('bpm.processes.show', compact('process', 'instanceStats'));
    }

    public function instances(Request $request, $processId)
    {
        try {
            $process = BpmProcess::findOrFail($processId);
            $query = BpmInstance::with('initiator')->where('process_id', $processId)->latest();
            if ($request->filled('status')) $query->where('status', $request->status);
            $instances = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            return redirect()->route('bpm.processes')->with('error', 'Process not found.');
        }

        return view('bpm.instances.index', compact('process', 'instances'));
    }

    public function createInstance($processId)
    {
        try {
            $process = BpmProcess::findOrFail($processId);
        } catch (\Exception $e) {
            return redirect()->route('bpm.processes')->with('error', 'Process not found.');
        }
        return view('bpm.instances.create', compact('process'));
    }

    public function storeInstance(Request $request, $processId)
    {
        $request->validate([
            'subject_type' => 'nullable|string|max:50',
            'subject_id'   => 'nullable|string|max:50',
        ]);

        try {
            $process = BpmProcess::findOrFail($processId);

            BpmInstance::create([
                'process_id'   => $process->id,
                'tenant_id'    => auth()->user()->tenant_id,
                'subject_type' => $request->subject_type,
                'subject_id'   => $request->subject_id,
                'current_step' => 0,
                'step_history' => [[
                    'step'      => 0,
                    'action'    => 'initiated',
                    'user_id'   => auth()->id(),
                    'timestamp' => now()->toIso8601String(),
                    'notes'     => 'Process instance started',
                ]],
                'initiated_by' => auth()->id(),
            ]);

            $process->increment('total_instances');

            return redirect()->route('bpm.instances', $process->id)->with('success', 'Instance started.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function advanceInstance(Request $request, $instanceId)
    {
        try {
            $instance = BpmInstance::with('process')->findOrFail($instanceId);
            $steps = $instance->process->steps ?? [];
            $nextStep = $instance->current_step + 1;

            $history = $instance->step_history ?? [];
            $history[] = [
                'step'      => $nextStep,
                'action'    => 'advanced',
                'user_id'   => auth()->id(),
                'timestamp' => now()->toIso8601String(),
                'notes'     => $request->notes ?? 'Step completed',
            ];

            if ($nextStep >= count($steps)) {
                $instance->update([
                    'current_step' => $nextStep,
                    'status'       => 'completed',
                    'step_history' => $history,
                    'completed_at' => now(),
                ]);
            } else {
                $instance->update([
                    'current_step' => $nextStep,
                    'step_history' => $history,
                ]);
            }

            return back()->with('success', 'Instance advanced.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function instanceDetail($instanceId)
    {
        try {
            $instance = BpmInstance::with('process', 'initiator')->findOrFail($instanceId);
        } catch (\Exception $e) {
            return redirect()->route('bpm.dashboard')->with('error', 'Instance not found.');
        }

        return view('bpm.instances.show', compact('instance'));
    }
}
