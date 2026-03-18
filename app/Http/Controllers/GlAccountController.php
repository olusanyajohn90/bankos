<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\GlAccount;
use Illuminate\Http\Request;

class GlAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $glAccounts = GlAccount::with('parent')->orderBy('account_number')->paginate();
        return view('gl_accounts.index', compact('glAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parents = GlAccount::orderBy('name')->get();
        $branches = Branch::all();
        return view('gl_accounts.create', compact('parents', 'branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|max:20|unique:gl_accounts,account_number',
            'name' => 'required|string|max:255',
            'category' => 'required|in:Asset,Liability,Equity,Revenue,Expense',
            'level' => 'required|integer|min:1',
            'parent_id' => 'nullable|exists:gl_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
            'balance' => 'numeric',
        ]);

        GlAccount::create($validated);

        return redirect()->route('gl-accounts.index')->with('success', 'GL Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(GlAccount $glAccount)
    {
        $glAccount->load(['parent', 'children', 'glPostings' => function ($query) {
            $query->latest()->limit(50);
        }]);
        
        return view('gl_accounts.show', compact('glAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GlAccount $glAccount)
    {
        $parents = GlAccount::where('id', '!=', $glAccount->id)->orderBy('name')->get();
        $branches = Branch::all();
        return view('gl_accounts.edit', compact('glAccount', 'parents', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GlAccount $glAccount)
    {
        $validated = $request->validate([
            'account_number' => 'required|string|max:20|unique:gl_accounts,account_number,' . $glAccount->id,
            'name' => 'required|string|max:255',
            'category' => 'required|in:Asset,Liability,Equity,Revenue,Expense',
            'level' => 'required|integer|min:1',
            'parent_id' => 'nullable|exists:gl_accounts,id',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $glAccount->update($validated);

        return redirect()->route('gl-accounts.index')->with('success', 'GL Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GlAccount $glAccount)
    {
        if ($glAccount->children()->exists()) {
            return back()->with('error', 'Cannot delete GL account because it has child accounts.');
        }

        if ($glAccount->glPostings()->exists()) {
            return back()->with('error', 'Cannot delete GL account that has existing postings.');
        }

        $glAccount->delete();

        return redirect()->route('gl-accounts.index')->with('success', 'GL Account deleted successfully.');
    }
}
