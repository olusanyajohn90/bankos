<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use App\Models\ApiRequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OpenBankingController extends Controller
{
    public function dashboard()
    {
        try {
            $totalClients = ApiClient::count();
            $activeClients = ApiClient::where('is_active', true)->count();
            $inactiveClients = $totalClients - $activeClients;
            $totalRequests = ApiRequestLog::count();
            $todayRequests = ApiRequestLog::whereDate('created_at', today())->count();
            $avgResponseTime = ApiRequestLog::avg('response_time_ms') ?? 0;

            // Error rate
            $errorCount = ApiRequestLog::where('status_code', '>=', 400)->count();
            $errorRate = $totalRequests > 0 ? ($errorCount / $totalRequests) * 100 : 0;

            // Top endpoints
            $topEndpoints = ApiRequestLog::select('endpoint', DB::raw('COUNT(*) as hits'))
                ->groupBy('endpoint')
                ->orderByDesc('hits')
                ->limit(10)
                ->get();

            // Requests per day (last 14 days)
            $dailyRequests = ApiRequestLog::where('created_at', '>=', now()->subDays(14))
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as date"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
                ->orderBy('date')
                ->get();

            // Status code distribution
            $statusCodeDist = ApiRequestLog::select(
                    DB::raw("CASE WHEN status_code < 300 THEN '2xx' WHEN status_code < 400 THEN '3xx' WHEN status_code < 500 THEN '4xx' ELSE '5xx' END as group"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw("CASE WHEN status_code < 300 THEN '2xx' WHEN status_code < 400 THEN '3xx' WHEN status_code < 500 THEN '4xx' ELSE '5xx' END"))
                ->get();

            // Top clients by usage
            $topClients = ApiClient::select('api_clients.name', 'api_clients.total_requests')
                ->orderByDesc('total_requests')
                ->limit(5)
                ->get();

        } catch (\Exception $e) {
            return view('open-banking.dashboard', [
                'error' => $e->getMessage(),
                'totalClients' => 0, 'activeClients' => 0, 'inactiveClients' => 0,
                'totalRequests' => 0, 'todayRequests' => 0, 'avgResponseTime' => 0,
                'errorRate' => 0, 'topEndpoints' => collect(), 'dailyRequests' => collect(),
                'statusCodeDist' => collect(), 'topClients' => collect(),
            ]);
        }

        return view('open-banking.dashboard', compact(
            'totalClients', 'activeClients', 'inactiveClients', 'totalRequests',
            'todayRequests', 'avgResponseTime', 'errorRate', 'topEndpoints',
            'dailyRequests', 'statusCodeDist', 'topClients'
        ));
    }

    public function clients(Request $request)
    {
        try {
            $query = ApiClient::with('creator')->latest();
            if ($request->filled('search')) {
                $query->where('name', 'ilike', "%{$request->search}%");
            }
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }
            $clients = $query->paginate(20)->withQueryString();
        } catch (\Exception $e) {
            $clients = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        return view('open-banking.clients.index', compact('clients'));
    }

    public function createClient()
    {
        return view('open-banking.clients.create');
    }

    public function storeClient(Request $request)
    {
        $request->validate([
            'name'                 => 'required|string|max:200',
            'description'          => 'nullable|string',
            'webhook_url'          => 'nullable|url|max:500',
            'rate_limit_per_minute'=> 'nullable|integer|min:1|max:10000',
        ]);

        try {
            $clientId = 'bnk_' . Str::random(32);
            $clientSecret = 'sk_' . Str::random(48);

            $client = ApiClient::create([
                'name'                  => $request->name,
                'description'           => $request->description,
                'client_id'             => $clientId,
                'client_secret'         => $clientSecret,
                'webhook_url'           => $request->webhook_url,
                'allowed_scopes'        => $request->scopes ?? ['accounts:read', 'transactions:read'],
                'rate_limit_per_minute' => $request->rate_limit_per_minute ?? 60,
                'created_by'            => auth()->id(),
            ]);

            return redirect()->route('open-banking.clients.show', $client->id)
                ->with('success', 'API client created. Secret shown once: ' . $clientSecret);
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function showClient($id)
    {
        try {
            $client = ApiClient::with('creator')->findOrFail($id);
            $recentLogs = ApiRequestLog::where('client_id', $id)->latest()->limit(50)->get();
            $dailyUsage = ApiRequestLog::where('client_id', $id)
                ->where('created_at', '>=', now()->subDays(7))
                ->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD') as date"),
                    DB::raw('COUNT(*) as count')
                )
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM-DD')"))
                ->orderBy('date')
                ->get();
        } catch (\Exception $e) {
            return redirect()->route('open-banking.clients')->with('error', 'Client not found.');
        }

        return view('open-banking.clients.show', compact('client', 'recentLogs', 'dailyUsage'));
    }

    public function toggleClient($id)
    {
        try {
            $client = ApiClient::findOrFail($id);
            $client->update(['is_active' => !$client->is_active]);
            $status = $client->is_active ? 'activated' : 'deactivated';
            return back()->with('success', "Client {$status}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed: ' . $e->getMessage());
        }
    }

    public function documentation()
    {
        $endpoints = [
            ['method' => 'POST', 'path' => '/api/v1/auth/login', 'description' => 'Authenticate and obtain access token'],
            ['method' => 'GET', 'path' => '/api/v1/accounts', 'description' => 'List customer accounts', 'scope' => 'accounts:read'],
            ['method' => 'GET', 'path' => '/api/v1/accounts/{id}', 'description' => 'Get account details', 'scope' => 'accounts:read'],
            ['method' => 'GET', 'path' => '/api/v1/accounts/{id}/balance', 'description' => 'Get account balance', 'scope' => 'accounts:read'],
            ['method' => 'GET', 'path' => '/api/v1/accounts/{id}/transactions', 'description' => 'List account transactions', 'scope' => 'transactions:read'],
            ['method' => 'POST', 'path' => '/api/v1/transfers', 'description' => 'Initiate fund transfer', 'scope' => 'transfers:write'],
            ['method' => 'GET', 'path' => '/api/v1/transfers/{ref}', 'description' => 'Check transfer status', 'scope' => 'transfers:read'],
            ['method' => 'GET', 'path' => '/api/v1/customers/me', 'description' => 'Get authenticated customer profile', 'scope' => 'customers:read'],
            ['method' => 'GET', 'path' => '/api/v1/loans', 'description' => 'List customer loans', 'scope' => 'loans:read'],
            ['method' => 'POST', 'path' => '/api/v1/bills/pay', 'description' => 'Pay a bill', 'scope' => 'bills:write'],
        ];

        return view('open-banking.documentation', compact('endpoints'));
    }
}
