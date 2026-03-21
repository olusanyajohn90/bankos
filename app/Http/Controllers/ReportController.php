<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function downloadAccountStatement(Request $request)
    {
        $accountNumber = $request->input('account_number');
        $startDate     = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate       = $request->input('end_date', now()->format('Y-m-d'));

        $account = \App\Models\Account::with('customer')
            ->where('account_number', $accountNumber)
            ->first();

        if (!$account) {
            abort(404, 'Account not found.');
        }

        // Build transactions + running balance (same logic as accountStatement())
        $openingBalance = \App\Models\Transaction::where('account_id', $account->id)
            ->where('status', 'success')
            ->whereDate('created_at', '<', $startDate)
            ->sum('amount');

        $transactions = \App\Models\Transaction::where('account_id', $account->id)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'asc')
            ->get();

        $currentBal = $openingBalance;
        foreach ($transactions as $txn) {
            $currentBal += $txn->amount;
            $txn->running_balance = $currentBal;
        }
        $closingBalance = $currentBal;

        $tenantName = auth()->user()->tenant->name ?? 'bankOS';

        // Render to HTML
        $html = view('reports.pdf.account_statement', compact(
            'account', 'transactions', 'openingBalance', 'closingBalance',
            'startDate', 'endDate', 'tenantName'
        ))->render();

        // Generate password-protected PDF via mPDF
        $mpdf = new \Mpdf\Mpdf([
            'orientation' => 'L',      // Landscape for the wide table
            'margin_left'  => 10,
            'margin_right' => 10,
            'margin_top'   => 12,
            'margin_bottom'=> 12,
        ]);

        // Password = account number (user-facing security)
        $mpdf->SetProtection(['print', 'copy'], $accountNumber, null, 128);

        $mpdf->WriteHTML($html);

        $filename = 'SOA-' . $accountNumber
            . '-' . str_replace('-', '', $startDate)
            . '-to-' . str_replace('-', '', $endDate)
            . '.pdf';

        return response($mpdf->Output($filename, 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    public function accountStatement(Request $request)
    {
        $accountNumber = $request->input('account_number');
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $account = null;
        $transactions = collect();
        $openingBalance = 0;
        $closingBalance = 0;

        if ($accountNumber) {
            $account = \App\Models\Account::with('customer')
                ->where('account_number', $accountNumber)
                ->first();

            if ($account) {
                // Opening balance = sum of all signed amounts before the period
                // Amounts are already signed: positive = credit (deposit/in), negative = debit (withdrawal/out)
                $openingBalance = \App\Models\Transaction::where('account_id', $account->id)
                    ->where('status', 'success')
                    ->whereDate('created_at', '<', $startDate)
                    ->sum('amount');

                $transactions = \App\Models\Transaction::where('account_id', $account->id)
                    ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Running balance — amounts are signed, so just accumulate
                $currentBal = $openingBalance;
                foreach ($transactions as $txn) {
                    $currentBal += $txn->amount;
                    $txn->running_balance = $currentBal;
                }
                
                $closingBalance = $currentBal;
            }
        }

        return view('reports.account_statement', compact(
            'account', 'transactions', 'accountNumber', 'startDate', 'endDate', 'openingBalance', 'closingBalance'
        ));
    }

    public function trialBalance()
    {
        $glAccounts = \App\Models\GlAccount::orderBy('account_number')->get();
        
        $totalDebits = 0;
        $totalCredits = 0;

        // Determine balance for each based on category and current 'balance' field
        foreach ($glAccounts as $account) {
            // Assets and Expenses decrease with credit, increase with debit. So positive balance is a Debit.
            // Liabilities, Equity, and Income decrease with debit, increase with credit. So positive balance is a Credit.
            $isDebitNormal = in_array($account->category, ['asset', 'expense']);
            
            // Assuming the `balance` field stores the net position 
            // If it's a debit-normal account and balance is positive, it's a debit balance
            // For MVP simplicity without a full double-entry system yet, we'll assign the raw balance to the normal side:
            $account->debit_balance = $isDebitNormal ? abs($account->balance) : 0;
            $account->credit_balance = !$isDebitNormal ? abs($account->balance) : 0;

            $totalDebits += $account->debit_balance;
            $totalCredits += $account->credit_balance;
        }

        $isBalanced = abs($totalDebits - $totalCredits) < 0.01;

        return view('reports.trial_balance', compact('glAccounts', 'totalDebits', 'totalCredits', 'isBalanced'));
    }

    public function loanPortfolio()
    {
        $loans = \App\Models\Loan::with(['customer', 'loanProduct'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate aggregate statistics
        $totalLoansCount = $loans->count();
        $activeLoansCount = $loans->whereIn('status', ['active', 'overdue'])->count();
        $totalPrincipal = $loans->sum('principal_amount');
        $totalOutstanding = $loans->sum('outstanding_balance');

        $statusDistribution = [
            'pending' => $loans->where('status', 'pending')->count(),
            'active' => $loans->where('status', 'active')->count(),
            'overdue' => $loans->where('status', 'overdue')->count(),
            'closed' => $loans->where('status', 'closed')->count(),
            'restructured' => $loans->where('status', 'restructured')->count(),
        ];

        // Format for Chart.js
        $chartData = [
            'labels' => array_map('ucfirst', array_keys($statusDistribution)),
            'data' => array_values($statusDistribution),
            'colors' => [
                '#fcd34d', // pending (amber-300)
                '#4ade80', // active (green-400)
                '#f87171', // overdue (red-400)
                '#9ca3af', // closed (gray-400)
                '#60a5fa', // restructured (blue-400)
            ],
        ];

        return view('reports.loan_portfolio', compact(
            'loans', 'totalLoansCount', 'activeLoansCount', 'totalPrincipal', 'totalOutstanding', 'chartData'
        ));
    }

    public function interestAccrual()
    {
        // 1. Expected Interest from Active Loans
        $activeLoans = \App\Models\Loan::whereIn('status', ['active', 'overdue'])->get();
        // Since we don't have a daily snapshot yet, we estimate expected interest dynamically based on the outstanding principal
        // Expected Interest = sum of (total_payable - principal_amount) for all active/overdue loans
        $expectedInterest = $activeLoans->sum(function ($loan) {
            return $loan->total_payable - $loan->principal_amount;
        });
        
        // 2. Interest actually collected — derived from loan repayment records
        // (no separate interest_payment transaction type exists; payments cover principal + interest)
        $allLoansForInterest = \App\Models\Loan::whereIn('status', ['active', 'overdue', 'closed'])->get();
        $postedInterest = $allLoansForInterest->sum(function ($loan) {
            $totalPayable = (float) $loan->total_payable;
            if ($totalPayable <= 0) return 0;
            $totalInterest = (float) $loan->total_interest;
            // Interest collected = amount paid × (interest / total_payable) — flat-rate proportional split
            return (float) $loan->amount_paid * ($totalInterest / $totalPayable);
        });

        // 3. Outstanding Accrued
        $accruedUnpaid = $expectedInterest - $postedInterest;

        // Ensure we don't show negative unpaid interest if they somehow overpaid
        // (Though in the system, overpayments should go to principal or wallet)
        $accruedUnpaid = max(0, $accruedUnpaid);

        // Calculate progress percentage
        $collectionRate = $expectedInterest > 0 ? ($postedInterest / $expectedInterest) * 100 : 0;

        return view('reports.interest_accrual', compact('expectedInterest', 'postedInterest', 'accruedUnpaid', 'collectionRate'));
    }

    public function parAging()
    {
        $overdueLoans = \App\Models\Loan::where('status', 'overdue')
            ->orderBy('expected_maturity_date', 'asc') // This is a simplification; ideally we track individual overdue installments
            ->get();

        $buckets = [
            '1_30' => ['count' => 0, 'principal' => 0, 'outstanding' => 0, 'loans' => collect()],
            '31_60' => ['count' => 0, 'principal' => 0, 'outstanding' => 0, 'loans' => collect()],
            '61_90' => ['count' => 0, 'principal' => 0, 'outstanding' => 0, 'loans' => collect()],
            '90_plus' => ['count' => 0, 'principal' => 0, 'outstanding' => 0, 'loans' => collect()],
        ];

        $now = now();

        foreach ($overdueLoans as $loan) {
            // MVP Approxiation: Calculate days since the loan was expected to be fully paid.
            // In a full core banking system, this is calculated based on the *oldest unpaid installment schedule date*.
            $daysOverdue = max(1, $now->diffInDays($loan->expected_maturity_date ?? $now->subDays(1)));

            if ($daysOverdue <= 30) {
                $bucket = '1_30';
            } elseif ($daysOverdue <= 60) {
                $bucket = '31_60';
            } elseif ($daysOverdue <= 90) {
                $bucket = '61_90';
            } else {
                $bucket = '90_plus';
            }

            $buckets[$bucket]['count']++;
            $buckets[$bucket]['principal'] += $loan->principal_amount;
            $buckets[$bucket]['outstanding'] += $loan->outstanding_balance;
            $buckets[$bucket]['loans']->push($loan);
        }

        $totalOverdueOutstanding = array_sum(array_column($buckets, 'outstanding'));
        
        // PAR = Portfolio At Risk. Total outstanding balance of all loans with at least one late payment
        $totalActiveOutstanding = \App\Models\Loan::whereIn('status', ['active', 'overdue'])->sum('outstanding_balance');
        $parRatio = $totalActiveOutstanding > 0 ? ($totalOverdueOutstanding / $totalActiveOutstanding) * 100 : 0;

        return view('reports.par_aging', compact('buckets', 'totalOverdueOutstanding', 'totalActiveOutstanding', 'parRatio'));
    }

    public function ifrs9()
    {
        // MVP IFRS 9 Staging logic:
        // Stage 1: Performing (0-30 days past due) -> 1% ECL Provision
        // Stage 2: Underperforming (31-90 days past due) -> 10% ECL Provision
        // Stage 3: Non-performing (90+ days past due) -> 50% ECL Provision

        $now = now();
        $allActiveLoans = \App\Models\Loan::whereIn('status', ['active', 'overdue'])->get();

        $stages = [
            'stage_1' => ['count' => 0, 'exposure' => 0, 'provision_rate' => 0.01, 'ecl' => 0],
            'stage_2' => ['count' => 0, 'exposure' => 0, 'provision_rate' => 0.10, 'ecl' => 0],
            'stage_3' => ['count' => 0, 'exposure' => 0, 'provision_rate' => 0.50, 'ecl' => 0],
        ];

        foreach ($allActiveLoans as $loan) {
            $exposure = $loan->outstanding_balance;
            
            if ($loan->status === 'active') {
                $stage = 'stage_1';
            } else {
                $daysOverdue = max(1, $now->diffInDays($loan->expected_maturity_date ?? $now->subDays(1)));
                
                if ($daysOverdue <= 30) {
                    $stage = 'stage_1';
                } elseif ($daysOverdue <= 90) {
                    $stage = 'stage_2';
                } else {
                    $stage = 'stage_3';
                }
            }

            $stages[$stage]['count']++;
            $stages[$stage]['exposure'] += $exposure;
            $stages[$stage]['ecl'] += ($exposure * $stages[$stage]['provision_rate']);
        }

        $totalExposure = array_sum(array_column($stages, 'exposure'));
        $totalEcl = array_sum(array_column($stages, 'ecl'));
        $overallCoverageRatio = $totalExposure > 0 ? ($totalEcl / $totalExposure) * 100 : 0;

        return view('reports.ifrs9', compact('stages', 'totalExposure', 'totalEcl', 'overallCoverageRatio'));
    }

    public function savingsInterest(Request $request)
    {
        $asOfDate = $request->input('as_of_date', now()->format('Y-m-d'));

        // Compute accrued savings interest from account balances × product rates
        // (interest_credit transactions are not posted in real-time; accrual is calculated from balances)
        $accounts = \App\Models\Account::with(['savingsProduct', 'customer'])
            ->where('status', 'active')
            ->whereHas('savingsProduct', fn($q) => $q->where('interest_rate', '>', 0))
            ->get();

        $totalBalance   = $accounts->sum('available_balance');
        $monthlyAccrual = $accounts->sum(function ($acc) {
            $rate = (float) ($acc->savingsProduct->interest_rate ?? 0);
            return max(0, (float) $acc->available_balance) * ($rate / 100 / 12);
        });
        $annualAccrual = $monthlyAccrual * 12;

        // Group by savings product
        $byProduct = $accounts->groupBy('savings_product_id')->map(function ($accs) {
            $product  = $accs->first()->savingsProduct;
            $balance  = (float) $accs->sum('available_balance');
            $rate     = (float) ($product->interest_rate ?? 0);
            $monthly  = max(0, $balance) * ($rate / 100 / 12);
            return [
                'product'         => $product,
                'account_count'   => $accs->count(),
                'total_balance'   => $balance,
                'interest_rate'   => $rate,
                'monthly_accrual' => $monthly,
                'annual_accrual'  => $monthly * 12,
            ];
        })->sortByDesc('monthly_accrual');

        // Top 50 accounts by monthly accrual for the detail table
        $topAccounts = $accounts->sortByDesc(function ($acc) {
            $rate = (float) ($acc->savingsProduct->interest_rate ?? 0);
            return max(0, (float) $acc->available_balance) * ($rate / 100 / 12);
        })->take(50)->values();

        return view('reports.savings_interest', compact(
            'asOfDate', 'totalBalance', 'monthlyAccrual', 'annualAccrual',
            'byProduct', 'topAccounts'
        ));
    }

    public function transactionJournal(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));

        $transactions = \App\Models\Transaction::with(['account.customer'])
            ->whereDate('created_at', $date)
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_volume' => $transactions->where('status', 'success')->sum('amount'),
            'total_count' => $transactions->count(),
            'successful_count' => $transactions->where('status', 'success')->count(),
            'failed_count' => $transactions->where('status', 'failed')->count(),
        ];

        return view('reports.transaction_journal', compact('transactions', 'date', 'summary'));
    }

    public function glMovements(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $accountId = $request->input('gl_account_id');

        $query = \App\Models\GlPosting::with(['glAccount', 'transaction'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);

        if ($accountId) {
            $query->where('gl_account_id', $accountId);
        }

        $postings = $query->orderBy('created_at', 'desc')->get();
        $glAccounts = \App\Models\GlAccount::orderBy('account_number')->get();

        $totalDebits = $postings->sum('debit_amount');
        $totalCredits = $postings->sum('credit_amount');

        return view('reports.gl_movements', compact(
            'postings', 'glAccounts', 'startDate', 'endDate', 'accountId', 'totalDebits', 'totalCredits'
        ));
    }

    public function overdrawnAccounts()
    {
        $overdrawnAccounts = \App\Models\Account::with(['customer', 'savingsProduct'])
            ->where('available_balance', '<', 0)
            ->where('status', 'active')
            ->orderBy('available_balance', 'asc')
            ->get();

        $totalOverdrawn = abs($overdrawnAccounts->sum('available_balance'));

        return view('reports.overdrawn_accounts', compact('overdrawnAccounts', 'totalOverdrawn'));
    }

    public function dormantAccounts(Request $request)
    {
        $months = $request->input('months', 6);
        $thresholdDate = now()->subMonths($months);

        // Find accounts whose last transaction was before the threshold date, or who have no transactions and were created before the threshold date.
        // We use a subquery to find the latest transaction date for each account.
        $dormantAccounts = \App\Models\Account::with(['customer', 'savingsProduct'])
            ->where('status', 'active')
            ->where(function($query) use ($thresholdDate) {
                // Scenario 1: Has transactions, but last one is older than threshold
                $query->whereHas('transactions', function($q) use ($thresholdDate) {
                    $q->select('account_id')->groupBy('account_id')->havingRaw('MAX(created_at) < ?', [$thresholdDate]);
                })
                // Scenario 2: Never had any transactions, and account itself is older than threshold
                ->orWhereDoesntHave('transactions')
                ->where('created_at', '<', $thresholdDate);
            })
            ->get();

        // Let's dynamically attach the 'last_activity_date' to each account to display in the view
        foreach ($dormantAccounts as $account) {
            $latestTxn = \App\Models\Transaction::where('account_id', $account->id)->latest('created_at')->first();
            $account->last_activity_date = $latestTxn ? $latestTxn->created_at : $account->created_at;
            $account->days_dormant = now()->diffInDays($account->last_activity_date);
        }

        // Sort by longest dormancy
        $dormantAccounts = $dormantAccounts->sortByDesc('days_dormant')->values();

        $totalDormantBalance = $dormantAccounts->sum('available_balance');

        return view('reports.dormant_accounts', compact('dormantAccounts', 'months', 'totalDormantBalance'));
    }

    public function suspiciousActivity(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $threshold = $request->input('threshold', 500000); // Default flag at 500k

        // Find single transactions over threshold
        $largeTransactions = \App\Models\Transaction::with(['account.customer'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('amount', '>=', $threshold)
            ->where('status', 'success')
            ->orderBy('amount', 'desc')
            ->get();

        // Find accounts with high volume of smaller transactions (Structuring/Smurfing risk)
        // MVP: Accounts with more than 10 transactions in a single day within the period
        $highFrequencyAccounts = \App\Models\Transaction::with(['account.customer'])
            ->selectRaw('account_id, DATE(created_at) as txn_date, COUNT(*) as txn_count, SUM(amount) as total_volume')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'success')
            ->groupBy('account_id', 'txn_date')
            ->having('txn_count', '>=', 10)
            ->get();

        return view('reports.suspicious_activity', compact(
            'startDate', 'endDate', 'threshold', 'largeTransactions', 'highFrequencyAccounts'
        ));
    }

    public function loanDisbursementsRepayments(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        // Money Out: Disbursements
        $disbursements = \App\Models\Transaction::with(['account.customer'])
            ->where('type', 'disbursement')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Money In: Repayments (Principal + Interest typically combined in 'repayment' type)
        $repayments = \App\Models\Transaction::with(['account.customer'])
            ->where('type', 'repayment')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalDisbursed = $disbursements->sum('amount');
        $totalRepaid = $repayments->sum('amount');
        $netCashflow = $totalRepaid - $totalDisbursed; // Positive means more money came in than went out

        return view('reports.loan_disbursements_repayments', compact(
            'startDate', 'endDate', 'disbursements', 'repayments', 'totalDisbursed', 'totalRepaid', 'netCashflow'
        ));
    }

    // ══════════════════════════════════════════════════════════════════════
    //  NEW STANDARD REPORTS
    // ══════════════════════════════════════════════════════════════════════

    public function loanRepaymentSchedule(Request $request)
    {
        $loanNumber = $request->input('loan_number');
        $loan       = null;

        if ($loanNumber) {
            $loan = \App\Models\Loan::with(['customer', 'loanProduct'])
                ->where('loan_number', $loanNumber)
                ->first();
        }

        $activeLoans = \App\Models\Loan::with('customer')
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('created_at', 'desc')
            ->limit(200)
            ->get();

        return view('reports.loan_repayment_schedule', compact('loan', 'activeLoans', 'loanNumber'));
    }

    public function collectionsReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        $actualCollections = \App\Models\Transaction::with(['account.customer'])
            ->where('type', 'repayment')
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Amounts are signed — repayments are negative (debit from customer's savings account)
        $totalCollected = abs($actualCollections->sum('amount'));

        $activeLoans = \App\Models\Loan::with(['customer', 'loanProduct'])
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('disbursed_at')
            ->get();

        // Expected monthly collection = sum of per-loan installments
        $totalExpected = $activeLoans->sum(function ($loan) {
            return $loan->tenure_days > 0 ? $loan->total_payable / $loan->tenure_days : 0;
        });

        $collectionEfficiency = $totalExpected > 0 ? ($totalCollected / $totalExpected) * 100 : 0;

        $loanCollections = $activeLoans->map(function ($loan) {
            return [
                'loan'        => $loan,
                'installment' => $loan->tenure_days > 0 ? round($loan->total_payable / $loan->tenure_days, 2) : 0,
                'amount_paid' => (float) $loan->amount_paid,
                'outstanding' => (float) $loan->outstanding_balance,
                'is_overdue'  => $loan->status === 'overdue',
            ];
        })->sortByDesc('is_overdue');

        return view('reports.collections_report', compact(
            'actualCollections', 'loanCollections', 'totalCollected', 'totalExpected',
            'collectionEfficiency', 'startDate', 'endDate'
        ));
    }

    public function productPerformance()
    {
        $products = \App\Models\LoanProduct::with('loans')->get();

        $productStats = $products->map(function ($product) {
            $loans        = $product->loans;
            $activeLoans  = $loans->whereIn('status', ['active', 'overdue']);
            $overdueLoans = $loans->where('status', 'overdue');
            $totalOutstanding = (float) $activeLoans->sum('outstanding_balance');
            $totalDisbursed   = (float) $loans->whereNotIn('status', ['pending'])->sum('principal_amount');
            $parRatio = $totalOutstanding > 0
                ? ($overdueLoans->sum('outstanding_balance') / $totalOutstanding) * 100
                : 0;

            return [
                'product'          => $product,
                'total_loans'      => $loans->count(),
                'active_loans'     => $activeLoans->count(),
                'total_disbursed'  => $totalDisbursed,
                'total_outstanding'=> $totalOutstanding,
                'par_ratio'        => $parRatio,
                'avg_loan_size'    => $loans->count() > 0 ? (float) $loans->avg('principal_amount') : 0,
            ];
        })->sortByDesc('total_outstanding')->values();

        return view('reports.product_performance', compact('productStats'));
    }

    public function kycSummary()
    {
        $customers = \App\Models\Customer::all();

        $tierDist = [
            'level_1' => $customers->where('kyc_tier', 'level_1')->count(),
            'level_2' => $customers->where('kyc_tier', 'level_2')->count(),
            'level_3' => $customers->where('kyc_tier', 'level_3')->count(),
        ];

        $statusDist = [
            'verified' => $customers->where('kyc_status', 'verified')->count(),
            'pending'  => $customers->where('kyc_status', 'pending')->count(),
            'rejected' => $customers->where('kyc_status', 'rejected')->count(),
        ];

        $bvnCoverage = $customers->count() > 0
            ? ($customers->filter(fn($c) => !empty($c->bvn))->count() / $customers->count()) * 100
            : 0;
        $bvnVerified = $customers->where('bvn_verified', true)->count();
        $ninVerified = $customers->where('nin_verified', true)->count();

        $pendingKyc = \App\Models\Customer::where('kyc_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('reports.kyc_summary', compact(
            'customers', 'tierDist', 'statusDist', 'bvnCoverage', 'bvnVerified', 'ninVerified', 'pendingKyc'
        ));
    }

    public function maturityProfile()
    {
        $now = now();

        $buckets = [
            '0_30'    => ['label' => '0–30 days',    'loans' => collect(), 'deposits' => collect()],
            '31_60'   => ['label' => '31–60 days',   'loans' => collect(), 'deposits' => collect()],
            '61_90'   => ['label' => '61–90 days',   'loans' => collect(), 'deposits' => collect()],
            '91_180'  => ['label' => '91–180 days',  'loans' => collect(), 'deposits' => collect()],
            '180_365' => ['label' => '180–365 days', 'loans' => collect(), 'deposits' => collect()],
            '365_plus'=> ['label' => '365+ days',    'loans' => collect(), 'deposits' => collect()],
        ];

        $activeLoans = \App\Models\Loan::with('customer')
            ->whereIn('status', ['active', 'overdue'])
            ->whereNotNull('disbursed_at')
            ->get();

        foreach ($activeLoans as $loan) {
            $maturity = $loan->expected_maturity_date;
            if (!$maturity) continue;
            $days = (int) $now->diffInDays($maturity, false);
            $key  = $days <= 0 ? '0_30'
                  : ($days <= 30  ? '0_30'
                  : ($days <= 60  ? '31_60'
                  : ($days <= 90  ? '61_90'
                  : ($days <= 180 ? '91_180'
                  : ($days <= 365 ? '180_365' : '365_plus')))));
            $buckets[$key]['loans']->push($loan);
        }

        $termDeposits = \App\Models\Account::with(['customer', 'savingsProduct'])
            ->where('status', 'active')
            ->whereHas('savingsProduct', fn($q) => $q->whereNotNull('maturity_date')->where('product_type', 'fixed'))
            ->get();

        foreach ($termDeposits as $account) {
            $maturity = $account->savingsProduct->maturity_date;
            if (!$maturity) continue;
            $days = (int) $now->diffInDays($maturity, false);
            $key  = $days <= 0 ? '0_30'
                  : ($days <= 30  ? '0_30'
                  : ($days <= 60  ? '31_60'
                  : ($days <= 90  ? '61_90'
                  : ($days <= 180 ? '91_180'
                  : ($days <= 365 ? '180_365' : '365_plus')))));
            $buckets[$key]['deposits']->push($account);
        }

        return view('reports.maturity_profile', compact('buckets'));
    }

    public function feeChargesRegister(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        $feeTypes = ['processing_fee', 'maintenance_fee', 'penalty', 'transfer_fee', 'insurance_fee', 'charge'];

        $feeTransactions = \App\Models\Transaction::with(['account.customer'])
            ->whereIn('type', $feeTypes)
            ->where('status', 'success')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalFees = abs($feeTransactions->sum('amount'));

        $byType = $feeTransactions->groupBy('type')->map(fn($txns) => [
            'count'  => $txns->count(),
            'amount' => abs($txns->sum('amount')),
        ]);

        // Processing fees from loan disbursements in the period
        $disbursedLoans = \App\Models\Loan::with(['customer', 'loanProduct'])
            ->whereNotNull('disbursed_at')
            ->whereBetween('disbursed_at', [$startDate, $endDate])
            ->get();

        $loanProcessingFees = $disbursedLoans->sum(fn($loan) => (float) ($loan->loanProduct->processing_fee ?? 0));

        return view('reports.fee_charges_register', compact(
            'feeTransactions', 'totalFees', 'byType', 'startDate', 'endDate',
            'disbursedLoans', 'loanProcessingFees'
        ));
    }

    public function staffActivityAudit(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(7)->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->format('Y-m-d'));

        $workflowActivity = \App\Models\WorkflowInstance::with('actionedBy')
            ->whereNotNull('actioned_by')
            ->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->whereIn('status', ['approved', 'rejected'])
            ->get()
            ->groupBy('actioned_by')
            ->map(function ($instances) {
                $user = $instances->first()->actionedBy;
                return [
                    'user'      => $user,
                    'approved'  => $instances->where('status', 'approved')->count(),
                    'rejected'  => $instances->where('status', 'rejected')->count(),
                    'total'     => $instances->count(),
                    'processes' => $instances->pluck('process_name')->unique()->values(),
                ];
            })->sortByDesc('total')->values();

        $auditLogs = \App\Models\AuditLog::with('user')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get();

        $auditByUser = $auditLogs->groupBy('user_id')->map(function ($logs) {
            return [
                'user'    => $logs->first()->user,
                'count'   => $logs->count(),
                'actions' => $logs->groupBy('action')->map->count(),
            ];
        })->sortByDesc('count')->values();

        return view('reports.staff_activity_audit', compact(
            'workflowActivity', 'auditByUser', 'startDate', 'endDate'
        ));
    }

    public function singleObligorLimit()
    {
        // CBN guideline: single borrower exposure must not exceed 20% of shareholders' equity
        $capitalBase = (float) config('bankos.capital_base', 50_000_000);
        $limitPct    = 0.20;
        $limit       = $capitalBase * $limitPct;

        $loans = \App\Models\Loan::with('customer')
            ->whereIn('status', ['active', 'overdue'])
            ->get();

        $exposureByCustomer = $loans->groupBy('customer_id')->map(function ($customerLoans) {
            $customer      = $customerLoans->first()->customer;
            $totalExposure = (float) $customerLoans->sum('outstanding_balance');
            return [
                'customer'       => $customer,
                'loan_count'     => $customerLoans->count(),
                'total_exposure' => $totalExposure,
                'loans'          => $customerLoans,
            ];
        })->sortByDesc('total_exposure')->values();

        $breaches = $exposureByCustomer->filter(fn($e) => $e['total_exposure'] > $limit)->values();

        return view('reports.single_obligor_limit', compact(
            'exposureByCustomer', 'breaches', 'capitalBase', 'limit', 'limitPct'
        ));
    }

    public function branchPerformance(Request $request)
    {
        $branches = \App\Models\Branch::all();
        $date = $request->input('date', now()->format('Y-m')); // Report by month
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        $branchStats = [];

        foreach ($branches as $branch) {
            // Get all customers for this branch
            $customerIds = \App\Models\Customer::where('branch_id', $branch->id)->pluck('id');

            // 1. Total Deposit Balances for Branch
            $totalDeposits = \App\Models\Account::whereIn('customer_id', $customerIds)->sum('available_balance');

            // 2. Total active loans for Branch
            $loans = \App\Models\Loan::whereIn('customer_id', $customerIds)->whereIn('status', ['active', 'overdue'])->get();
            $totalLoanPortfolio = $loans->sum('outstanding_balance');

            // 3. PAR for Branch
            $overdueLoans = $loans->where('status', 'overdue');
            $overdueAmount = $overdueLoans->sum('outstanding_balance');
            $parRatio = $totalLoanPortfolio > 0 ? ($overdueAmount / $totalLoanPortfolio) * 100 : 0;

            // 4. New Accounts this month
            $newAccounts = \App\Models\Account::whereIn('customer_id', $customerIds)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $branchStats[] = [
                'name' => $branch->name,
                'code' => $branch->code,
                'city' => $branch->city,
                'total_deposits' => $totalDeposits,
                'total_loans' => $totalLoanPortfolio,
                'par_ratio' => $parRatio,
                'new_accounts' => $newAccounts
            ];
        }

        // Sort by deposit volume descending for display
        usort($branchStats, function($a, $b) {
            return $b['total_deposits'] <=> $a['total_deposits'];
        });

        return view('reports.branch_performance', compact('branchStats', 'date'));
    }
}
