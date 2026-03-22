<?php

namespace App\Http\Controllers\Cooperative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DividendController extends Controller
{
    /* ───────────────────────────── helpers ───────────────────────────── */

    private function tenantId(): string
    {
        return Auth::user()->tenant_id;
    }

    /* ───────────────────────────── index ─────────────────────────────── */

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();

        $declarations = DB::table('dividend_declarations')
            ->where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->paginate(20);

        // Aggregate stats
        $stats = DB::table('dividend_declarations')
            ->where('tenant_id', $tenantId)
            ->selectRaw("
                COUNT(*) as total_declarations,
                COALESCE(SUM(total_distributed), 0) as total_distributed,
                COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as completed_count,
                COALESCE(SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END), 0) as draft_count
            ")
            ->first();

        return view('cooperative.dividends.index', compact('declarations', 'stats'));
    }

    /* ───────────────────────────── create ────────────────────────────── */

    public function create()
    {
        return view('cooperative.dividends.create');
    }

    /* ───────────────────────────── store ─────────────────────────────── */

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'financial_year'   => 'required|string|max:10',
            'total_surplus'    => 'required|numeric|min:0.01',
            'dividend_rate'    => 'required|numeric|min:0.0001|max:100',
            'declaration_date' => 'required|date',
            'notes'            => 'nullable|string',
        ]);

        $tenantId = $this->tenantId();

        // Count eligible members: active member_shares grouped by customer
        $eligibleMembers = DB::table('member_shares')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->distinct('customer_id')
            ->count('customer_id');

        $id = (string) Str::uuid();

        DB::table('dividend_declarations')->insert([
            'id'               => $id,
            'tenant_id'        => $tenantId,
            'title'            => $data['title'],
            'financial_year'   => $data['financial_year'],
            'total_surplus'    => $data['total_surplus'],
            'dividend_rate'    => $data['dividend_rate'],
            'total_distributed'=> 0,
            'eligible_members' => $eligibleMembers,
            'declaration_date' => $data['declaration_date'],
            'payment_date'     => null,
            'status'           => 'draft',
            'notes'            => $data['notes'] ?? null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()
            ->route('cooperative.dividends.show', $id)
            ->with('success', 'Dividend declaration created with ' . $eligibleMembers . ' eligible member(s).');
    }

    /* ───────────────────────────── show ──────────────────────────────── */

    public function show(string $id)
    {
        $tenantId = $this->tenantId();

        $declaration = DB::table('dividend_declarations')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        abort_unless($declaration, 404);

        // Get payouts with customer info
        $payouts = DB::table('dividend_payouts as dp')
            ->join('customers as c', 'c.id', '=', 'dp.customer_id')
            ->where('dp.dividend_declaration_id', $id)
            ->where('dp.tenant_id', $tenantId)
            ->select(
                'dp.*',
                'c.first_name',
                'c.last_name',
                'c.customer_number'
            )
            ->orderBy('c.last_name')
            ->paginate(50);

        // If still draft/approved, preview what would be distributed
        $preview = collect();
        if (in_array($declaration->status, ['draft', 'approved'])) {
            $preview = $this->calculatePayouts($tenantId, $declaration);
        }

        return view('cooperative.dividends.show', compact('declaration', 'payouts', 'preview'));
    }

    /* ───────────────────────────── approve ───────────────────────────── */

    public function approve(string $id)
    {
        $tenantId = $this->tenantId();

        $declaration = DB::table('dividend_declarations')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        abort_unless($declaration, 404);

        if ($declaration->status !== 'draft') {
            return back()->with('error', 'Only draft declarations can be approved.');
        }

        DB::table('dividend_declarations')
            ->where('id', $id)
            ->update(['status' => 'approved', 'updated_at' => now()]);

        return back()->with('success', 'Declaration approved. You may now process payouts.');
    }

    /* ───────────────────────────── process ───────────────────────────── */

    public function process(string $id)
    {
        $tenantId = $this->tenantId();

        $declaration = DB::table('dividend_declarations')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        abort_unless($declaration, 404);

        if ($declaration->status !== 'approved') {
            return back()->with('error', 'Only approved declarations can be processed.');
        }

        // Mark as processing
        DB::table('dividend_declarations')
            ->where('id', $id)
            ->update(['status' => 'processing', 'updated_at' => now()]);

        $memberPayouts = $this->calculatePayouts($tenantId, $declaration);

        $totalDistributed = 0;
        $now = now();

        DB::beginTransaction();
        try {
            foreach ($memberPayouts as $payout) {
                // Find primary savings account (first active savings account for this customer)
                $account = DB::table('accounts')
                    ->where('tenant_id', $tenantId)
                    ->where('customer_id', $payout->customer_id)
                    ->where('status', 'active')
                    ->orderBy('created_at')
                    ->first();

                $payoutStatus = 'failed';
                $accountId = null;
                $paidAt = null;

                if ($account) {
                    // Credit the account
                    DB::table('accounts')
                        ->where('id', $account->id)
                        ->increment('balance', $payout->amount);

                    // Create a share_transaction of type 'dividend'
                    DB::table('share_transactions')->insert([
                        'id'               => (string) Str::uuid(),
                        'tenant_id'        => $tenantId,
                        'customer_id'      => $payout->customer_id,
                        'share_product_id' => $payout->share_product_id,
                        'member_share_id'  => null,
                        'type'             => 'dividend',
                        'quantity'         => $payout->total_shares,
                        'amount'           => $payout->amount,
                        'unit_price'       => $payout->par_value,
                        'reference'        => 'DIV-' . $declaration->financial_year . '-' . strtoupper(Str::random(6)),
                        'notes'            => $declaration->title,
                        'status'           => 'completed',
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ]);

                    $payoutStatus = 'paid';
                    $accountId = $account->id;
                    $paidAt = $now;
                    $totalDistributed += $payout->amount;
                }

                // Create dividend_payout record
                DB::table('dividend_payouts')->insert([
                    'id'                       => (string) Str::uuid(),
                    'tenant_id'                => $tenantId,
                    'dividend_declaration_id'   => $id,
                    'customer_id'              => $payout->customer_id,
                    'shares_held'              => $payout->total_shares,
                    'amount'                   => $payout->amount,
                    'account_id'               => $accountId,
                    'status'                   => $payoutStatus,
                    'paid_at'                  => $paidAt,
                    'created_at'               => $now,
                    'updated_at'               => $now,
                ]);
            }

            // Update declaration
            DB::table('dividend_declarations')
                ->where('id', $id)
                ->update([
                    'total_distributed' => $totalDistributed,
                    'eligible_members'  => $memberPayouts->count(),
                    'payment_date'      => $now->toDateString(),
                    'status'            => 'completed',
                    'updated_at'        => $now,
                ]);

            DB::commit();

            return back()->with('success', 'Dividends processed successfully. Total distributed: ' . number_format($totalDistributed, 2));
        } catch (\Throwable $e) {
            DB::rollBack();

            // Revert to approved status
            DB::table('dividend_declarations')
                ->where('id', $id)
                ->update(['status' => 'approved', 'updated_at' => now()]);

            return back()->with('error', 'Processing failed: ' . $e->getMessage());
        }
    }

    /* ───────────────────────────── cancel ────────────────────────────── */

    public function cancel(string $id)
    {
        $tenantId = $this->tenantId();

        $declaration = DB::table('dividend_declarations')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        abort_unless($declaration, 404);

        if (!in_array($declaration->status, ['draft', 'approved'])) {
            return back()->with('error', 'Only draft or approved declarations can be cancelled.');
        }

        DB::table('dividend_declarations')
            ->where('id', $id)
            ->update(['status' => 'cancelled', 'updated_at' => now()]);

        return redirect()
            ->route('cooperative.dividends.index')
            ->with('success', 'Declaration cancelled.');
    }

    /* ─────────────────────── internal helpers ────────────────────────── */

    /**
     * Calculate per-member payouts based on active share holdings.
     *
     * dividend_amount = total_shares * (dividend_rate / 100) * par_value
     */
    private function calculatePayouts(string $tenantId, object $declaration)
    {
        return DB::table('member_shares as ms')
            ->join('share_products as sp', 'sp.id', '=', 'ms.share_product_id')
            ->where('ms.tenant_id', $tenantId)
            ->where('ms.status', 'active')
            ->groupBy('ms.customer_id', 'sp.id', 'sp.par_value')
            ->select(
                'ms.customer_id',
                'sp.id as share_product_id',
                'sp.par_value',
                DB::raw('CAST(SUM(ms.quantity) AS INTEGER) as total_shares'),
                DB::raw('SUM(ms.quantity) * (' . (float) $declaration->dividend_rate . ' / 100) * sp.par_value as amount')
            )
            ->get();
    }
}
