<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Models\LoanProduct;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LoanApplicationReviewController extends Controller
{
    public function index(Request $request)
    {
        if (! $this->portalTableExists('loan_applications')) {
            return view('loan-applications.index', [
                'applications'  => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20),
                'statusCounts'  => collect(),
                'portalUnavailable' => true,
            ]);
        }

        // LoanApplication lives in the portal DB (same DB, different model namespace)
        $query = DB::table('loan_applications')
            ->join('customers', 'loan_applications.customer_id', '=', 'customers.id')
            ->select(
                'loan_applications.*',
                DB::raw("customers.first_name || ' ' || customers.last_name as customer_name"),
                'customers.phone as customer_phone',
                'customers.id as customer_uuid'
            )
            ->orderByDesc('loan_applications.created_at');

        if ($request->filled('status')) {
            $query->where('loan_applications.status', $request->status);
        }
        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('loan_applications.reference', 'like', $s)
                  ->orWhere('customers.first_name', 'like', $s)
                  ->orWhere('customers.last_name', 'like', $s);
            });
        }

        $applications = $query->paginate(20)->withQueryString();
        $statusCounts = DB::table('loan_applications')
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('loan-applications.index', compact('applications', 'statusCounts'));
    }

    public function show($id)
    {
        $this->requirePortalTable('loan_applications', 'Loan application review');
        $app = DB::table('loan_applications')
            ->join('customers', 'loan_applications.customer_id', '=', 'customers.id')
            ->select(
                'loan_applications.*',
                DB::raw("customers.first_name || ' ' || customers.last_name as customer_name"),
                'customers.phone as customer_phone',
                'customers.email as customer_email',
                'customers.kyc_tier',
                'customers.id as customer_uuid'
            )
            ->where('loan_applications.id', $id)
            ->first();
        abort_if(!$app, 404);

        $customer = Customer::find($app->customer_uuid);
        $activeLoans = Loan::where('customer_id', $app->customer_uuid)
            ->whereIn('status', ['active', 'overdue'])->get();
        $accounts = Account::where('customer_id', $app->customer_uuid)
            ->where('status', 'active')->get();
        $loanProducts = LoanProduct::where('is_active', true)->get();

        return view('loan-applications.show', compact('app', 'customer', 'activeLoans', 'accounts', 'loanProducts'));
    }

    public function approve(Request $request, $id)
    {
        $this->requirePortalTable('loan_applications', 'Loan application review');
        $request->validate(['officer_notes' => 'nullable|string|max:1000']);

        DB::table('loan_applications')
            ->where('id', $id)
            ->update([
                'status'       => 'approved',
                'officer_notes'=> $request->officer_notes,
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
                'updated_at'   => now(),
            ]);

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application approved. Customer will be notified.');
    }

    public function reject(Request $request, $id)
    {
        $this->requirePortalTable('loan_applications', 'Loan application review');
        $request->validate(['officer_notes' => 'required|string|max:1000']);

        DB::table('loan_applications')
            ->where('id', $id)
            ->update([
                'status'       => 'rejected',
                'officer_notes'=> $request->officer_notes,
                'reviewed_by'  => auth()->id(),
                'reviewed_at'  => now(),
                'updated_at'   => now(),
            ]);

        return redirect()->route('loan-applications.index')
            ->with('success', 'Loan application rejected and customer notified.');
    }

    public function convert(Request $request, $id)
    {
        $this->requirePortalTable('loan_applications', 'Loan application review');
        $request->validate([
            'loan_product_id' => 'required|uuid',
            'amount'          => 'required|numeric|min:1000',
            'tenor_months'    => 'required|integer|min:1',
            'account_id'      => 'required|uuid',
        ]);

        $app = DB::table('loan_applications')->where('id', $id)->first();
        abort_if(!$app, 404);

        DB::transaction(function () use ($request, $app, $id) {
            $product = LoanProduct::findOrFail($request->loan_product_id);
            $rate    = $product->interest_rate / 100 / 12;
            $months  = $request->tenor_months;
            $p       = $request->amount;
            $monthly = $rate > 0
                ? round($p * $rate / (1 - pow(1 + $rate, -$months)), 2)
                : round($p / $months, 2);

            $loan = Loan::create([
                'id'               => (string) Str::uuid(),
                'tenant_id'        => $app->tenant_id,
                'customer_id'      => $app->customer_id,
                'account_id'       => $request->account_id,
                'loan_product_id'  => $product->id,
                'loan_reference'   => 'LN-' . strtoupper(Str::random(8)),
                'principal_amount' => $p,
                'outstanding_balance' => $p,
                'interest_rate'    => $product->interest_rate,
                'tenor_months'     => $months,
                'monthly_repayment'=> $monthly,
                'status'           => 'approved',
                'purpose'          => $app->purpose,
                'applied_at'       => now(),
                'approved_at'      => now(),
                'approved_by'      => auth()->id(),
            ]);

            DB::table('loan_applications')->where('id', $id)->update([
                'status'          => 'converted',
                'resulting_loan_id' => $loan->id,
                'reviewed_by'     => auth()->id(),
                'reviewed_at'     => now(),
                'updated_at'      => now(),
            ]);
        });

        return redirect()->route('loan-applications.index')
            ->with('success', 'Application converted to loan successfully.');
    }
}
