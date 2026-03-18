<?php
namespace App\Http\Controllers;
use App\Models\{Account, AccountLien};
use Illuminate\Http\Request;

class AccountLienController extends Controller {
    public function store(Request $request, Account $account) {
        $data = $request->validate([
            'amount'     => 'required|numeric|min:1',
            'reason'     => 'required|string|max:500',
            'lien_type'  => 'required|in:loan_collateral,court_order,regulatory,internal',
            'reference'  => 'nullable|string|max:100',
            'expires_at' => 'nullable|date|after:today',
        ]);
        AccountLien::create(array_merge($data, [
            'tenant_id' => auth()->user()->tenant_id,
            'account_id'=> $account->id,
            'placed_by' => auth()->id(),
        ]));
        return back()->with('success','Lien placed on account.');
    }

    public function lift(AccountLien $lien) {
        $lien->update([
            'is_active' => false,
            'lifted_by' => auth()->id(),
            'lifted_at' => now(),
        ]);
        return back()->with('success','Lien lifted from account.');
    }

    public function pnd(Request $request, Account $account) {
        $request->validate(['reason'=>'required|string|max:255']);
        if ($account->pnd_active) {
            $account->update(['pnd_active'=>false,'pnd_reason'=>null]);
            return back()->with('success','Post-No-Debit removed from account.');
        }
        $account->update([
            'pnd_active' => true,
            'pnd_reason' => $request->reason,
            'pnd_placed_by' => auth()->id(),
            'pnd_placed_at' => now(),
        ]);
        return back()->with('success','Post-No-Debit placed on account.');
    }
}
