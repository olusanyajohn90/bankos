<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReferralRewardController extends Controller
{
    public function index(Request $request)
    {
        if (! $this->portalTableExists('portal_referrals')) {
            return view('referral-rewards.index', [
                'rewards' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 25),
                'totals'  => collect(),
                'portalUnavailable' => true,
            ]);
        }

        $query = DB::table('portal_referrals as r')
            ->join('customers as referrer', 'r.referrer_customer_id', '=', 'referrer.id')
            ->leftJoin('customers as referred', 'r.referee_customer_id', '=', 'referred.id')
            ->where('referrer.tenant_id', Auth::user()->tenant_id)
            ->select(
                'r.*',
                'referrer.first_name as ref_fn', 'referrer.last_name as ref_ln',
                'referrer.customer_number as ref_no', 'referrer.id as referrer_cid',
                'referred.first_name as new_fn', 'referred.last_name as new_ln',
                'referred.customer_number as new_no'
            )
            ->orderByRaw("CASE r.status WHEN 'pending' THEN 1 WHEN 'approved' THEN 2 WHEN 'paid' THEN 3 WHEN 'rejected' THEN 4 ELSE 5 END")
            ->orderBy('r.created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('r.status', $request->status);
        }

        $rewards = $query->paginate(25)->withQueryString();

        $totals = DB::table('portal_referrals as r')
            ->join('customers as referrer', 'r.referrer_customer_id', '=', 'referrer.id')
            ->where('referrer.tenant_id', Auth::user()->tenant_id)
            ->selectRaw('r.status, COUNT(*) as cnt, COALESCE(SUM(r.reward_amount),0) as total')
            ->groupBy('r.status')
            ->get()->keyBy('status');

        return view('referral-rewards.index', compact('rewards', 'totals'));
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['payout_notes' => 'nullable|string|max:500']);
        $this->updateReferral($id, 'approved', $request->payout_notes);
        return back()->with('success', 'Reward approved and queued for payout.');
    }

    public function pay(Request $request, $id)
    {
        $request->validate(['payout_notes' => 'nullable|string|max:500']);
        $this->updateReferral($id, 'paid', $request->payout_notes, true);
        return back()->with('success', 'Reward marked as paid.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['payout_notes' => 'required|string|max:500']);
        $this->updateReferral($id, 'rejected', $request->payout_notes);
        return back()->with('success', 'Reward rejected.');
    }

    private function updateReferral($id, $status, $notes = null, $paid = false)
    {
        $this->requirePortalTable('portal_referrals', 'Referral rewards');
        $row = DB::table('portal_referrals as r')
            ->join('customers as referrer', 'r.referrer_customer_id', '=', 'referrer.id')
            ->where('referrer.tenant_id', Auth::user()->tenant_id)
            ->where('r.id', $id)
            ->select('r.id')
            ->firstOrFail();

        $data = [
            'status'              => $status,
            'payout_notes'        => $notes,
            'payout_processed_by' => Auth::id(),
            'payout_processed_at' => $paid ? now() : null,
            'updated_at'          => now(),
        ];
        DB::table('portal_referrals')->where('id', $row->id)->update($data);
    }
}
