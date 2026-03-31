<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Loan;
use App\Services\AiReviewService;
use App\Services\CortexService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CortexController extends Controller
{
    private CortexService $cortex;
    private AiReviewService $aiReview;

    public function __construct(CortexService $cortex, AiReviewService $aiReview)
    {
        $this->cortex = $cortex;
        $this->aiReview = $aiReview;
    }

    /**
     * AI Command Center — dashboard overview.
     */
    public function dashboard()
    {
        $tenantId = auth()->user()->tenant_id;

        $portfolioSummary = $this->cortex->portfolioRiskSummary($tenantId);

        // Recent AI review count (from cache hits — approximate)
        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();
        $totalLoans = Loan::where('tenant_id', $tenantId)->whereIn('status', ['active', 'overdue'])->count();
        $overdueLoans = Loan::where('tenant_id', $tenantId)->where('status', 'overdue')->count();
        $totalOutstanding = Loan::where('tenant_id', $tenantId)->whereIn('status', ['active', 'overdue'])->sum('outstanding_balance');

        // Get top fraud alerts (sample up to 20 customers)
        $sampleCustomers = Customer::where('tenant_id', $tenantId)
            ->with(['accounts', 'loans'])
            ->inRandomOrder()
            ->limit(20)
            ->get();

        $fraudAlerts = [];
        foreach ($sampleCustomers as $c) {
            try {
                $fraud = $this->cortex->detectFraud($c);
                if ($fraud['risk_level'] !== 'low') {
                    $fraudAlerts[] = array_merge($fraud, [
                        'customer_id' => $c->id,
                        'customer_name' => $c->first_name . ' ' . $c->last_name,
                    ]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Churn watchlist (sample)
        $churnWatchlist = [];
        foreach ($sampleCustomers as $c) {
            try {
                $churn = $this->cortex->predictChurn($c);
                if ($churn['churn_probability'] >= 0.3) {
                    $churnWatchlist[] = array_merge($churn, [
                        'customer_id' => $c->id,
                        'customer_name' => $c->first_name . ' ' . $c->last_name,
                    ]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sort churn by probability desc
        usort($churnWatchlist, fn($a, $b) => $b['churn_probability'] <=> $a['churn_probability']);

        return view('cortex.dashboard', compact(
            'portfolioSummary',
            'totalCustomers',
            'totalLoans',
            'overdueLoans',
            'totalOutstanding',
            'fraudAlerts',
            'churnWatchlist',
        ));
    }

    /**
     * Score a specific loan application.
     */
    public function scoreLoan(Request $request, Loan $loan)
    {
        $tenantId = auth()->user()->tenant_id;
        if ($loan->tenant_id !== $tenantId) {
            abort(403);
        }

        $score = $this->cortex->scoreLoan($loan);

        return response()->json($score);
    }

    /**
     * Fraud alerts listing.
     */
    public function fraudAlerts(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $customers = Customer::where('tenant_id', $tenantId)
            ->with(['accounts', 'loans'])
            ->paginate(50);

        $alerts = [];
        foreach ($customers as $customer) {
            try {
                $fraud = $this->cortex->detectFraud($customer);
                if ($fraud['risk_level'] !== 'low' || $request->get('show_all')) {
                    $alerts[] = array_merge($fraud, [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                    ]);
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sort by risk level
        $riskOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
        usort($alerts, fn($a, $b) => ($riskOrder[$a['risk_level']] ?? 4) <=> ($riskOrder[$b['risk_level']] ?? 4));

        $filterLevel = $request->get('level', 'all');

        return view('cortex.fraud-alerts', compact('alerts', 'filterLevel'));
    }

    /**
     * Churn risk listing.
     */
    public function churnRisk(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $customers = Customer::where('tenant_id', $tenantId)
            ->with(['accounts', 'loans'])
            ->get();

        $churnData = [];
        foreach ($customers as $customer) {
            try {
                $churn = $this->cortex->predictChurn($customer);
                $churnData[] = array_merge($churn, [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                ]);
            } catch (\Exception $e) {
                continue;
            }
        }

        // Sort by churn probability desc
        usort($churnData, fn($a, $b) => $b['churn_probability'] <=> $a['churn_probability']);

        return view('cortex.churn-risk', compact('churnData'));
    }

    /**
     * Individual customer deep-dive insight.
     */
    public function customerInsight(Customer $customer)
    {
        $tenantId = auth()->user()->tenant_id;
        if ($customer->tenant_id !== $tenantId) {
            abort(403);
        }

        $customer->load(['accounts', 'loans.loanProduct', 'insurancePolicies']);

        $review = $this->aiReview->generateReview($customer);
        $clv = $this->cortex->calculateCLV($customer);
        $churn = $this->cortex->predictChurn($customer);
        $recommendations = $this->cortex->recommendProducts($customer);
        $fraud = $this->cortex->detectFraud($customer);

        // Transaction pattern data for chart (last 6 months)
        $transactionPattern = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();

            $monthTxns = \App\Models\Transaction::whereIn('account_id', $customer->accounts->pluck('id'))
                ->where('status', 'success')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get();

            $transactionPattern[] = [
                'month' => $monthStart->format('M Y'),
                'inflow' => round($monthTxns->where('amount', '>', 0)->sum('amount'), 2),
                'outflow' => round(abs($monthTxns->where('amount', '<', 0)->sum('amount')), 2),
                'count' => $monthTxns->count(),
            ];
        }

        return view('cortex.customer', compact(
            'customer',
            'review',
            'clv',
            'churn',
            'recommendations',
            'fraud',
            'transactionPattern',
        ));
    }

    /**
     * Batch analysis on a segment.
     */
    public function batchAnalysis(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $type = $request->input('type', 'churn');
        $limit = min($request->input('limit', 50), 200);

        $customers = Customer::where('tenant_id', $tenantId)
            ->with(['accounts', 'loans'])
            ->limit($limit)
            ->get();

        $results = [];

        foreach ($customers as $customer) {
            try {
                $result = match ($type) {
                    'churn' => $this->cortex->predictChurn($customer),
                    'fraud' => $this->cortex->detectFraud($customer),
                    'clv' => $this->cortex->calculateCLV($customer),
                    'recommendations' => ['recommendations' => $this->cortex->recommendProducts($customer)],
                    default => ['error' => 'Unknown analysis type'],
                };

                $results[] = array_merge($result, [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->first_name . ' ' . $customer->last_name,
                ]);
            } catch (\Exception $e) {
                continue;
            }
        }

        return response()->json([
            'type' => $type,
            'count' => count($results),
            'results' => $results,
        ]);
    }
}
