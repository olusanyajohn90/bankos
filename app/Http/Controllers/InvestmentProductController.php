<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvestmentProductController extends Controller
{
    public function index()
    {
        $products = DB::table('portal_investment_products')
            ->where('tenant_id', Auth::user()->tenant_id)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();

        return view('investment-products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:80',
            'description'   => 'nullable|string|max:500',
            'duration_days' => 'required|integer|min:1|max:3650',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_amount'    => 'required|numeric|min:0',
            'max_amount'    => 'nullable|numeric|min:0',
            'sort_order'    => 'nullable|integer|min:0',
        ]);

        DB::table('portal_investment_products')->insert([
            ...$data,
            'id'        => \Illuminate\Support\Str::uuid(),
            'tenant_id' => Auth::user()->tenant_id,
            'is_active' => true,
            'sort_order'=> $data['sort_order'] ?? 0,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        return back()->with('success', 'Investment product created.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:80',
            'description'   => 'nullable|string|max:500',
            'duration_days' => 'required|integer|min:1|max:3650',
            'interest_rate' => 'required|numeric|min:0|max:100',
            'min_amount'    => 'required|numeric|min:0',
            'max_amount'    => 'nullable|numeric|min:0',
            'sort_order'    => 'nullable|integer|min:0',
            'is_active'     => 'boolean',
        ]);

        DB::table('portal_investment_products')
            ->where('id', $id)
            ->where('tenant_id', Auth::user()->tenant_id)
            ->update(array_merge($data, ['updated_at' => now()]));

        return back()->with('success', 'Product updated.');
    }

    public function toggleActive($id)
    {
        $product = DB::table('portal_investment_products')
            ->where('id', $id)
            ->where('tenant_id', Auth::user()->tenant_id)
            ->firstOrFail();

        DB::table('portal_investment_products')
            ->where('id', $id)
            ->update(['is_active' => !$product->is_active, 'updated_at' => now()]);

        return back()->with('success', 'Product status toggled.');
    }

    public function destroy($id)
    {
        DB::table('portal_investment_products')
            ->where('id', $id)
            ->where('tenant_id', Auth::user()->tenant_id)
            ->delete();

        return back()->with('success', 'Product deleted.');
    }
}
