<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\TenantSubscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class TenantOnboardingController extends Controller
{
    // ── STEP 0: Start / Landing ───────────────────────────────────────────────

    public function start(): View
    {
        session()->forget('tenant_onboarding');

        return view('setup.start');
    }

    // ── STEP 1: Institution Details ───────────────────────────────────────────

    public function institutionDetails(): View
    {
        $data = session('tenant_onboarding', []);

        return view('setup.step1', compact('data'));
    }

    public function storeStep1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:100',
            'short_name'         => 'required|string|max:20|alpha_dash',
            'type'               => 'required|in:microfinance,commercial,cooperative,digital',
            'cbn_license_number' => 'nullable|string|max:50',
            'contact_email'      => 'required|email|max:100',
            'contact_phone'      => 'required|string|max:20',
            'address'            => 'required|string|max:255',
        ]);

        $onboarding                      = session('tenant_onboarding', []);
        $onboarding['step1']             = $validated;
        $onboarding['completed_steps'][] = 1;
        session(['tenant_onboarding' => $onboarding]);

        return redirect()->route('setup.step2');
    }

    // ── STEP 2: Branding ──────────────────────────────────────────────────────

    public function branding(): View
    {
        $this->requireStep(1);
        $data = session('tenant_onboarding', []);

        return view('setup.step2', compact('data'));
    }

    public function storeBranding(Request $request): RedirectResponse
    {
        $this->requireStep(1);

        $validated = $request->validate([
            'primary_color'   => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'logo'            => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo') && $request->file('logo')->isValid()) {
            $logoPath = $request->file('logo')->store('tenant-logos', 'public');
        }

        $onboarding                      = session('tenant_onboarding', []);
        $onboarding['step2']             = [
            'primary_color'   => $validated['primary_color'],
            'secondary_color' => $validated['secondary_color'],
            'logo_path'       => $logoPath,
        ];
        $onboarding['completed_steps'][] = 2;
        session(['tenant_onboarding' => $onboarding]);

        return redirect()->route('setup.step3');
    }

    // ── STEP 3: Admin User ────────────────────────────────────────────────────

    public function adminUser(): View
    {
        $this->requireStep(2);
        $data = session('tenant_onboarding', []);

        return view('setup.step3', compact('data'));
    }

    public function storeAdminUser(Request $request): RedirectResponse
    {
        $this->requireStep(2);

        $validated = $request->validate([
            'admin_name'     => 'required|string|max:100',
            'admin_email'    => 'required|email|max:100|unique:users,email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        $onboarding                      = session('tenant_onboarding', []);
        $onboarding['step3']             = [
            'name'     => $validated['admin_name'],
            'email'    => $validated['admin_email'],
            'password' => $validated['admin_password'],
        ];
        $onboarding['completed_steps'][] = 3;
        session(['tenant_onboarding' => $onboarding]);

        return redirect()->route('setup.step4');
    }

    // ── STEP 4: Subscription Plan ─────────────────────────────────────────────

    public function subscription(): View
    {
        $this->requireStep(3);
        $data  = session('tenant_onboarding', []);
        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price_monthly')->get();

        return view('setup.step4', compact('data', 'plans'));
    }

    public function storeSubscription(Request $request): RedirectResponse
    {
        $this->requireStep(3);

        $validated = $request->validate([
            'plan_slug'     => 'required|exists:subscription_plans,slug',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $onboarding                      = session('tenant_onboarding', []);
        $onboarding['step4']             = $validated;
        $onboarding['completed_steps'][] = 4;
        session(['tenant_onboarding' => $onboarding]);

        return redirect()->route('setup.review');
    }

    // ── Review ────────────────────────────────────────────────────────────────

    public function review(): View
    {
        $this->requireStep(4);
        $data = session('tenant_onboarding', []);
        $plan = SubscriptionPlan::where('slug', $data['step4']['plan_slug'])->first();

        return view('setup.review', compact('data', 'plan'));
    }

    // ── Complete / Launch ─────────────────────────────────────────────────────

    public function complete(Request $request): RedirectResponse
    {
        $this->requireStep(4);
        $onboarding = session('tenant_onboarding', []);

        try {
            DB::transaction(function () use ($onboarding) {
                $step1 = $onboarding['step1'];
                $step2 = $onboarding['step2'];
                $step3 = $onboarding['step3'];
                $step4 = $onboarding['step4'];

                // Map institution type
                $typeMap = [
                    'microfinance' => 'bank',
                    'commercial'   => 'bank',
                    'cooperative'  => 'cooperative',
                    'digital'      => 'lender',
                ];
                $tenantType = $typeMap[$step1['type']] ?? 'bank';

                // Create tenant
                $tenant = Tenant::create([
                    'id'                      => Str::uuid(),
                    'name'                    => $step1['name'],
                    'short_name'              => Str::slug($step1['short_name'], '_'),
                    'type'                    => $tenantType,
                    'account_prefix'          => strtoupper(substr($step1['short_name'], 0, 3)),
                    'cbn_license_number'      => $step1['cbn_license_number'] ?? null,
                    'contact_email'           => $step1['contact_email'],
                    'contact_phone'           => $step1['contact_phone'],
                    'address'                 => ['street' => $step1['address']],
                    'status'                  => 'active',
                    'primary_color'           => $step2['primary_color'],
                    'secondary_color'         => $step2['secondary_color'],
                    'logo_path'               => $step2['logo_path'],
                    'subscription_plan'       => $step4['plan_slug'],
                    'onboarding_completed_at' => now(),
                    'onboarding_step'         => 5,
                ]);

                // Create admin user
                $user = User::create([
                    'name'      => $step3['name'],
                    'email'     => $step3['email'],
                    'password'  => Hash::make($step3['password']),
                    'tenant_id' => $tenant->id,
                ]);

                // Assign tenant_admin role
                $role = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
                $user->assignRole($role);

                // Create subscription record
                $plan = SubscriptionPlan::where('slug', $step4['plan_slug'])->firstOrFail();
                TenantSubscription::create([
                    'id'                   => Str::uuid(),
                    'tenant_id'            => $tenant->id,
                    'plan_id'              => $plan->id,
                    'status'               => 'trial',
                    'trial_ends_at'        => now()->addDays(14),
                    'current_period_start' => today(),
                    'current_period_end'   => today()->addMonth(),
                    'billing_cycle'        => $step4['billing_cycle'],
                ]);
            });

            session()->forget('tenant_onboarding');

            return redirect()->route('login')->with('success', 'Your bankOS account is ready! Please log in to get started.');
        } catch (\Throwable $e) {
            Log::error('Tenant onboarding failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Onboarding failed: ' . $e->getMessage());
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function requireStep(int $step): void
    {
        $onboarding = session('tenant_onboarding', []);
        $completed  = $onboarding['completed_steps'] ?? [];

        if (!in_array($step, $completed)) {
            abort(redirect()->route('setup.start'));
        }
    }
}
