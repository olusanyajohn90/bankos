<?php

namespace App\Http\Controllers\Cooperative;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ShareController extends Controller
{
    /**
     * Share capital dashboard — KPI cards, products list, recent transactions.
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // KPI stats
        $totalShareCapital = DB::table('member_shares')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->sum('total_value');

        $membersWithShares = DB::table('member_shares')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->distinct('customer_id')
            ->count('customer_id');

        $totalProducts = DB::table('share_products')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->count();

        $totalActiveShares = DB::table('member_shares')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->sum('quantity');

        $avgSharesPerMember = $membersWithShares > 0
            ? round($totalActiveShares / $membersWithShares, 1)
            : 0;

        // Share products with stats
        $products = DB::table('share_products')
            ->where('share_products.tenant_id', $tenantId)
            ->leftJoin('member_shares', function ($join) {
                $join->on('share_products.id', '=', 'member_shares.share_product_id')
                     ->where('member_shares.status', '=', 'active');
            })
            ->select(
                'share_products.*',
                DB::raw('COALESCE(SUM(member_shares.quantity), 0) as total_shares_issued'),
                DB::raw('COALESCE(SUM(member_shares.total_value), 0) as total_value_issued'),
                DB::raw('COUNT(DISTINCT member_shares.customer_id) as member_count')
            )
            ->groupBy('share_products.id', 'share_products.tenant_id', 'share_products.name',
                'share_products.description', 'share_products.par_value', 'share_products.min_shares',
                'share_products.max_shares', 'share_products.dividend_rate', 'share_products.transferable',
                'share_products.redeemable', 'share_products.status', 'share_products.created_at',
                'share_products.updated_at')
            ->orderBy('share_products.created_at', 'desc')
            ->get();

        // Recent transactions
        $recentTransactions = DB::table('share_transactions')
            ->where('share_transactions.tenant_id', $tenantId)
            ->join('customers', 'customers.id', '=', 'share_transactions.customer_id')
            ->join('share_products', 'share_products.id', '=', 'share_transactions.share_product_id')
            ->select(
                'share_transactions.*',
                'customers.first_name',
                'customers.last_name',
                'customers.customer_number',
                'share_products.name as product_name'
            )
            ->orderBy('share_transactions.created_at', 'desc')
            ->limit(10)
            ->get();

        return view('cooperative.shares.index', compact(
            'totalShareCapital', 'membersWithShares', 'totalProducts',
            'avgSharesPerMember', 'products', 'recentTransactions'
        ));
    }

    /**
     * Show form to create a share product.
     */
    public function createProduct()
    {
        return view('cooperative.shares.product-create');
    }

    /**
     * Store a new share product.
     */
    public function storeProduct(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string|max:1000',
            'par_value'     => 'required|numeric|min:0.01',
            'min_shares'    => 'required|integer|min:1',
            'max_shares'    => 'nullable|integer|min:1',
            'dividend_rate' => 'nullable|numeric|min:0|max:100',
            'transferable'  => 'nullable|boolean',
            'redeemable'    => 'nullable|boolean',
        ]);

        DB::table('share_products')->insert([
            'id'            => Str::uuid()->toString(),
            'tenant_id'     => auth()->user()->tenant_id,
            'name'          => $validated['name'],
            'description'   => $validated['description'] ?? null,
            'par_value'     => $validated['par_value'],
            'min_shares'    => $validated['min_shares'],
            'max_shares'    => $validated['max_shares'] ?? null,
            'dividend_rate' => $validated['dividend_rate'] ?? null,
            'transferable'  => !empty($validated['transferable']),
            'redeemable'    => $validated['redeemable'] ?? true,
            'status'        => 'active',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return redirect()->route('cooperative.shares.index')
            ->with('success', 'Share product "' . $validated['name'] . '" created successfully.');
    }

    /**
     * Show a share product's details with member holdings.
     */
    public function showProduct($id)
    {
        $tenantId = auth()->user()->tenant_id;

        $product = DB::table('share_products')
            ->where('id', $id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$product) {
            abort(404);
        }

        // Members holding this product
        $holdings = DB::table('member_shares')
            ->where('member_shares.share_product_id', $id)
            ->where('member_shares.tenant_id', $tenantId)
            ->join('customers', 'customers.id', '=', 'member_shares.customer_id')
            ->select(
                'member_shares.*',
                'customers.first_name',
                'customers.last_name',
                'customers.customer_number'
            )
            ->orderBy('member_shares.created_at', 'desc')
            ->paginate(20);

        // Product summary
        $summary = DB::table('member_shares')
            ->where('share_product_id', $id)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->selectRaw('COALESCE(SUM(quantity), 0) as total_shares, COALESCE(SUM(total_value), 0) as total_value, COUNT(DISTINCT customer_id) as member_count')
            ->first();

        return view('cooperative.shares.product-show', compact('product', 'holdings', 'summary'));
    }

    /**
     * List all members with share holdings.
     */
    public function members(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $search = $request->get('search');

        $query = DB::table('member_shares')
            ->where('member_shares.tenant_id', $tenantId)
            ->where('member_shares.status', 'active')
            ->join('customers', 'customers.id', '=', 'member_shares.customer_id')
            ->select(
                'customers.id as customer_id',
                'customers.first_name',
                'customers.last_name',
                'customers.customer_number',
                DB::raw('SUM(member_shares.quantity) as total_shares'),
                DB::raw('SUM(member_shares.total_value) as total_value'),
                DB::raw('COUNT(member_shares.id) as holdings_count')
            )
            ->groupBy('customers.id', 'customers.first_name', 'customers.last_name', 'customers.customer_number');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('customers.first_name', 'ilike', '%' . $search . '%')
                  ->orWhere('customers.last_name', 'ilike', '%' . $search . '%')
                  ->orWhere('customers.customer_number', 'ilike', '%' . $search . '%');
            });
        }

        $members = $query->orderBy('total_value', 'desc')->paginate(20)->withQueryString();

        return view('cooperative.shares.members', compact('members', 'search'));
    }

    /**
     * Show a specific member's share portfolio.
     */
    public function showMember($customerId)
    {
        $tenantId = auth()->user()->tenant_id;

        $customer = DB::table('customers')
            ->where('id', $customerId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$customer) {
            abort(404);
        }

        // Member's share holdings
        $holdings = DB::table('member_shares')
            ->where('member_shares.customer_id', $customerId)
            ->where('member_shares.tenant_id', $tenantId)
            ->join('share_products', 'share_products.id', '=', 'member_shares.share_product_id')
            ->select('member_shares.*', 'share_products.name as product_name', 'share_products.par_value')
            ->orderBy('member_shares.created_at', 'desc')
            ->get();

        // Transaction history
        $transactions = DB::table('share_transactions')
            ->where('share_transactions.customer_id', $customerId)
            ->where('share_transactions.tenant_id', $tenantId)
            ->join('share_products', 'share_products.id', '=', 'share_transactions.share_product_id')
            ->select('share_transactions.*', 'share_products.name as product_name')
            ->orderBy('share_transactions.created_at', 'desc')
            ->paginate(20);

        // Summary
        $summary = DB::table('member_shares')
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->selectRaw('COALESCE(SUM(quantity), 0) as total_shares, COALESCE(SUM(total_value), 0) as total_value')
            ->first();

        return view('cooperative.shares.member-show', compact('customer', 'holdings', 'transactions', 'summary'));
    }

    /**
     * Show purchase form.
     */
    public function purchaseForm()
    {
        $tenantId = auth()->user()->tenant_id;

        $products = DB::table('share_products')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $customers = DB::table('customers')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('cooperative.shares.purchase', compact('products', 'customers'));
    }

    /**
     * Process share purchase — debit customer account, create member_share + transaction.
     */
    public function purchase(Request $request)
    {
        $validated = $request->validate([
            'customer_id'      => 'required|uuid',
            'share_product_id' => 'required|uuid',
            'quantity'         => 'required|integer|min:1',
            'account_id'       => 'required|uuid',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $product = DB::table('share_products')
            ->where('id', $validated['share_product_id'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$product) {
            return back()->withErrors(['share_product_id' => 'Share product not found or inactive.'])->withInput();
        }

        // Validate quantity against min/max
        if ($validated['quantity'] < $product->min_shares) {
            return back()->withErrors(['quantity' => 'Minimum shares required: ' . $product->min_shares])->withInput();
        }

        if ($product->max_shares && $validated['quantity'] > $product->max_shares) {
            return back()->withErrors(['quantity' => 'Maximum shares allowed: ' . $product->max_shares])->withInput();
        }

        $totalAmount = $validated['quantity'] * $product->par_value;

        // Check customer account balance
        $account = DB::table('accounts')
            ->where('id', $validated['account_id'])
            ->where('customer_id', $validated['customer_id'])
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$account) {
            return back()->withErrors(['account_id' => 'Account not found.'])->withInput();
        }

        if ($account->available_balance < $totalAmount) {
            return back()->withErrors(['account_id' => 'Insufficient balance. Required: ' . number_format($totalAmount, 2) . ', Available: ' . number_format($account->available_balance, 2)])->withInput();
        }

        DB::beginTransaction();
        try {
            // Debit customer account
            DB::table('accounts')
                ->where('id', $account->id)
                ->update([
                    'available_balance' => DB::raw('available_balance - ' . $totalAmount),
                    'ledger_balance'    => DB::raw('ledger_balance - ' . $totalAmount),
                    'updated_at'        => now(),
                ]);

            // Generate certificate number
            $certNumber = 'SC-' . strtoupper(Str::random(8));

            $memberShareId = Str::uuid()->toString();

            // Create member_share record
            DB::table('member_shares')->insert([
                'id'               => $memberShareId,
                'tenant_id'        => $tenantId,
                'customer_id'      => $validated['customer_id'],
                'share_product_id' => $validated['share_product_id'],
                'quantity'         => $validated['quantity'],
                'total_value'      => $totalAmount,
                'certificate_number' => $certNumber,
                'purchase_date'    => now()->toDateString(),
                'status'           => 'active',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Create share_transaction record
            DB::table('share_transactions')->insert([
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $tenantId,
                'customer_id'      => $validated['customer_id'],
                'share_product_id' => $validated['share_product_id'],
                'member_share_id'  => $memberShareId,
                'type'             => 'purchase',
                'quantity'         => $validated['quantity'],
                'amount'           => $totalAmount,
                'unit_price'       => $product->par_value,
                'reference'        => 'TXN-' . strtoupper(Str::random(10)),
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'completed',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();

            return redirect()->route('cooperative.shares.members.show', $validated['customer_id'])
                ->with('success', 'Successfully purchased ' . $validated['quantity'] . ' shares for ' . number_format($totalAmount, 2) . '. Certificate: ' . $certNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'An error occurred processing the purchase: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show redemption form.
     */
    public function redeemForm()
    {
        $tenantId = auth()->user()->tenant_id;

        // Get active member shares that are redeemable
        $holdings = DB::table('member_shares')
            ->where('member_shares.tenant_id', $tenantId)
            ->where('member_shares.status', 'active')
            ->join('share_products', function ($join) {
                $join->on('share_products.id', '=', 'member_shares.share_product_id')
                     ->where('share_products.redeemable', '=', true);
            })
            ->join('customers', 'customers.id', '=', 'member_shares.customer_id')
            ->select(
                'member_shares.*',
                'customers.first_name',
                'customers.last_name',
                'customers.customer_number',
                'share_products.name as product_name',
                'share_products.par_value'
            )
            ->orderBy('customers.first_name')
            ->get();

        return view('cooperative.shares.redeem', compact('holdings'));
    }

    /**
     * Process share redemption — credit customer account, mark shares redeemed.
     */
    public function redeem(Request $request)
    {
        $validated = $request->validate([
            'member_share_id' => 'required|uuid',
            'account_id'      => 'required|uuid',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $tenantId = auth()->user()->tenant_id;

        $memberShare = DB::table('member_shares')
            ->where('id', $validated['member_share_id'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();

        if (!$memberShare) {
            return back()->withErrors(['member_share_id' => 'Share holding not found or already redeemed.'])->withInput();
        }

        $product = DB::table('share_products')
            ->where('id', $memberShare->share_product_id)
            ->first();

        if (!$product || !$product->redeemable) {
            return back()->withErrors(['member_share_id' => 'This share product is not redeemable.'])->withInput();
        }

        $account = DB::table('accounts')
            ->where('id', $validated['account_id'])
            ->where('customer_id', $memberShare->customer_id)
            ->where('tenant_id', $tenantId)
            ->first();

        if (!$account) {
            return back()->withErrors(['account_id' => 'Account not found.'])->withInput();
        }

        $redemptionAmount = $memberShare->total_value;

        DB::beginTransaction();
        try {
            // Credit customer account
            DB::table('accounts')
                ->where('id', $account->id)
                ->update([
                    'available_balance' => DB::raw('available_balance + ' . $redemptionAmount),
                    'ledger_balance'    => DB::raw('ledger_balance + ' . $redemptionAmount),
                    'updated_at'        => now(),
                ]);

            // Mark shares as redeemed
            DB::table('member_shares')
                ->where('id', $memberShare->id)
                ->update([
                    'status'     => 'redeemed',
                    'updated_at' => now(),
                ]);

            // Create share_transaction record
            DB::table('share_transactions')->insert([
                'id'               => Str::uuid()->toString(),
                'tenant_id'        => $tenantId,
                'customer_id'      => $memberShare->customer_id,
                'share_product_id' => $memberShare->share_product_id,
                'member_share_id'  => $memberShare->id,
                'type'             => 'redemption',
                'quantity'         => $memberShare->quantity,
                'amount'           => $redemptionAmount,
                'unit_price'       => $product->par_value,
                'reference'        => 'RDM-' . strtoupper(Str::random(10)),
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'completed',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            DB::commit();

            return redirect()->route('cooperative.shares.members.show', $memberShare->customer_id)
                ->with('success', 'Successfully redeemed ' . $memberShare->quantity . ' shares. ' . number_format($redemptionAmount, 2) . ' credited to account.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['general' => 'An error occurred processing the redemption: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Generate share certificate (PDF download).
     */
    public function certificate($memberShareId)
    {
        $tenantId = auth()->user()->tenant_id;

        $share = DB::table('member_shares')
            ->where('member_shares.id', $memberShareId)
            ->where('member_shares.tenant_id', $tenantId)
            ->join('customers', 'customers.id', '=', 'member_shares.customer_id')
            ->join('share_products', 'share_products.id', '=', 'member_shares.share_product_id')
            ->join('tenants', 'tenants.id', '=', 'member_shares.tenant_id')
            ->select(
                'member_shares.*',
                'customers.first_name',
                'customers.last_name',
                'customers.customer_number',
                'share_products.name as product_name',
                'share_products.par_value',
                'tenants.name as institution_name'
            )
            ->first();

        if (!$share) {
            abort(404);
        }

        // Simple HTML-to-PDF style certificate (returned as HTML for now)
        return response()->view('cooperative.shares.certificate', compact('share'))
            ->header('Content-Type', 'text/html');
    }
}
