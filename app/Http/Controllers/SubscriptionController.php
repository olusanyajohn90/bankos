<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use App\Models\TenantUsage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * List all tenant subscriptions.
     */
    public function index(): View
    {
        $subscriptions = TenantSubscription::with(['tenant', 'plan'])
            ->latest()
            ->paginate(25);

        $plans = SubscriptionPlan::where('is_active', true)->get();

        // Attach current-period usage for each subscription
        $period = now()->format('Y-m');
        $usages = TenantUsage::where('period', $period)
            ->whereIn('tenant_id', $subscriptions->pluck('tenant_id'))
            ->get()
            ->keyBy('tenant_id');

        return view('subscriptions.index', compact('subscriptions', 'plans', 'usages'));
    }

    /**
     * Show a single tenant's subscription detail.
     */
    public function show(string $tenantId): View
    {
        $tenant       = Tenant::findOrFail($tenantId);
        $subscription = TenantSubscription::with('plan')
            ->where('tenant_id', $tenantId)
            ->latest()
            ->firstOrFail();

        $invoices = TenantInvoice::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->get();

        $usageHistory = TenantUsage::where('tenant_id', $tenantId)
            ->orderBy('period', 'desc')
            ->limit(12)
            ->get();

        $currentUsage = TenantUsage::where('tenant_id', $tenantId)
            ->where('period', now()->format('Y-m'))
            ->first();

        $plans = SubscriptionPlan::where('is_active', true)->get();

        return view('subscriptions.show', compact(
            'tenant',
            'subscription',
            'invoices',
            'usageHistory',
            'currentUsage',
            'plans'
        ));
    }

    /**
     * Change a tenant's subscription plan.
     */
    public function changePlan(Request $request, string $tenantId): RedirectResponse
    {
        $request->validate([
            'plan_id'       => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $subscription = TenantSubscription::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial', 'past_due'])
            ->latest()
            ->firstOrFail();

        $plan = SubscriptionPlan::findOrFail($request->plan_id);

        $subscription->update([
            'plan_id'               => $request->plan_id,
            'billing_cycle'         => $request->billing_cycle,
            'status'                => 'active',
            'current_period_start'  => today(),
            'current_period_end'    => $request->billing_cycle === 'yearly'
                ? today()->addYear()
                : today()->addMonth(),
        ]);

        // Update tenant's subscription_plan shorthand
        Tenant::where('id', $tenantId)->update(['subscription_plan' => $plan->slug]);

        return back()->with('success', "Plan changed to {$plan->name} successfully.");
    }

    /**
     * Suspend a tenant.
     */
    public function suspend(Request $request, string $tenantId): RedirectResponse
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update([
            'status'            => 'suspended',
            'suspended_at'      => now(),
            'suspension_reason' => $request->reason,
        ]);

        TenantSubscription::where('tenant_id', $tenantId)
            ->whereIn('status', ['active', 'trial'])
            ->update(['status' => 'suspended']);

        return back()->with('success', "Tenant {$tenant->name} has been suspended.");
    }

    /**
     * Unsuspend a tenant.
     */
    public function unsuspend(string $tenantId): RedirectResponse
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update([
            'status'            => 'active',
            'suspended_at'      => null,
            'suspension_reason' => null,
        ]);

        TenantSubscription::where('tenant_id', $tenantId)
            ->where('status', 'suspended')
            ->update([
                'status'               => 'active',
                'current_period_end'   => today()->addMonth(),
            ]);

        return back()->with('success', "Tenant {$tenant->name} has been reactivated.");
    }

    /**
     * Manually record a payment for a tenant.
     */
    public function recordPayment(Request $request, string $tenantId): RedirectResponse
    {
        $request->validate([
            'amount'         => 'required|numeric|min:1',
            'reference'      => 'nullable|string|max:100',
            'billing_period' => 'required|in:monthly,yearly',
        ]);

        $tenant       = Tenant::findOrFail($tenantId);
        $subscription = TenantSubscription::where('tenant_id', $tenantId)->latest()->firstOrFail();

        // Create invoice
        $invoice = TenantInvoice::create([
            'id'                 => Str::uuid(),
            'tenant_id'          => $tenantId,
            'subscription_id'    => $subscription->id,
            'invoice_number'     => TenantInvoice::generateInvoiceNumber(),
            'amount'             => $request->amount,
            'status'             => 'paid',
            'due_date'           => today(),
            'paid_at'            => now(),
            'paystack_reference' => $request->reference,
            'line_items'         => [
                [
                    'description' => $subscription->plan->name . ' Plan — ' . ucfirst($request->billing_period) . ' Billing',
                    'amount'      => $request->amount,
                ],
            ],
        ]);

        // Update subscription
        $periodEnd = $request->billing_period === 'yearly'
            ? today()->addYear()
            : today()->addMonth();

        $subscription->update([
            'status'               => 'active',
            'amount_paid'          => $subscription->amount_paid + $request->amount,
            'current_period_start' => today(),
            'current_period_end'   => $periodEnd,
            'billing_cycle'        => $request->billing_period,
        ]);

        return back()->with('success', "Payment of ₦" . number_format($request->amount, 2) . " recorded. Invoice {$invoice->invoice_number} created.");
    }

    /**
     * List all subscription plans.
     */
    public function plans(): View
    {
        $plans = SubscriptionPlan::withCount('subscriptions')->orderBy('monthly_price')->get();
        return view('subscriptions.plans', compact('plans'));
    }

    /**
     * Cancel a tenant subscription (alias for suspend).
     */
    public function cancel(Request $request, string $tenantId): RedirectResponse
    {
        return $this->suspend($request, $tenantId);
    }
}
