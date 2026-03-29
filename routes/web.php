<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SavingsProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanProductController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\GlAccountController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostingFileController;
use App\Http\Controllers\InboundTransferController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\EclController;
use App\Http\Controllers\BureauController;
use App\Http\Controllers\Kpi\KpiDashboardController;
use App\Http\Controllers\Kpi\KpiAlertController;
use App\Http\Controllers\Kpi\KpiNoteController;
use App\Http\Controllers\Kpi\KpiDefinitionController;
use App\Http\Controllers\Kpi\KpiTargetController;
use App\Http\Controllers\Kpi\StaffProfileController;
use App\Http\Controllers\Kpi\TeamController;
use App\Http\Controllers\Hr\OrgController;
use App\Http\Controllers\Hr\LeaveTypeController;
use App\Http\Controllers\Hr\LeaveRequestController;
use App\Http\Controllers\Hr\DisciplinaryController;
use App\Http\Controllers\Hr\ReviewCycleController;
use App\Http\Controllers\Hr\PerformanceReviewController;
use App\Http\Controllers\Hr\TrainingController;
use App\Http\Controllers\Payroll\PayrollSetupController;
use App\Http\Controllers\Payroll\PayrollRunController;
use App\Http\Controllers\Payroll\PayslipController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\UssdController;
use App\Http\Controllers\Documents\DocumentController;
use App\Http\Controllers\Documents\DocumentChecklistController;
use App\Http\Controllers\Documents\DmsController;
use App\Http\Controllers\Comms\CommsMessageController;
use App\Http\Controllers\Comms\CommsInboxController;
use App\Http\Controllers\Chat\ChatController;
use App\Http\Controllers\FixedDeposit\FixedDepositController;
use App\Http\Controllers\FixedDeposit\FixedDepositProductController;
use App\Http\Controllers\StandingOrderController;
use App\Http\Controllers\OverdraftController;
use App\Http\Controllers\AccountLienController;
use App\Http\Controllers\Teller\TellerController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\FixedAssetController;
use App\Http\Controllers\Compliance\RegulatoryReportController;
use App\Http\Controllers\Credit\CreditPolicyController;
use App\Http\Controllers\Nip\NipController;
use App\Http\Controllers\MandateController;
use App\Http\Controllers\LoanApplicationReviewController;
use App\Http\Controllers\KycUpgradeReviewController;
use App\Http\Controllers\ParDashboardController;
use App\Http\Controllers\FeatureFlagController;
use App\Http\Controllers\FeeRuleController;
use App\Http\Controllers\TransactionMonitorController;
use App\Http\Controllers\AmlController;
use App\Http\Controllers\PortalDisputeAdminController;
use App\Http\Controllers\ReferralRewardController;
use App\Http\Controllers\InvestmentProductController;
use App\Http\Controllers\ProxyActionController;
use App\Http\Controllers\PortalAnalyticsController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\WebhookEndpointController;
use App\Http\Controllers\CustomReportController;
use App\Http\Controllers\BoardPackController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\IpWhitelistController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TenantOnboardingController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\Support\SupportDashboardController;
use App\Http\Controllers\Support\SupportTicketController;
use App\Http\Controllers\Support\SupportTeamController;
use App\Http\Controllers\Visitor\VisitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// SuperAdmin impersonation — no auth required (token-based)
Route::get('/sa-impersonate/{token}', [\App\Http\Controllers\ImpersonateController::class, 'handle'])
    ->name('sa-impersonate');

Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('dashboard');

Route::middleware(['auth', 'tenant'])->group(function () {
    // Internal API for AJAX calls (customer accounts dropdown)
    Route::get('/api/internal/customer-accounts/{customerId}', function ($customerId) {
        $tenantId = auth()->user()->tenant_id;
        $accounts = \DB::table('accounts')
            ->where('customer_id', $customerId)
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select('id', 'account_number', 'available_balance', 'type')
            ->get();
        return response()->json($accounts);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Operations & Administration - Workflows
    Route::get('/workflows', [\App\Http\Controllers\WorkflowController::class, 'index'])->name('workflows.index');
    Route::get('/workflows/{workflow}', [\App\Http\Controllers\WorkflowController::class, 'show'])->name('workflows.show');
    Route::post('/workflows/{workflow}/action', [\App\Http\Controllers\WorkflowController::class, 'action'])->name('workflows.action');
    Route::post('/workflows/bulk-action', [\App\Http\Controllers\WorkflowController::class, 'bulkAction'])->name('workflows.bulk-action');

    // Loan rejection (for workflow-driven rejections from loan page)
    Route::post('loans/{loan}/reject', [\App\Http\Controllers\LoanController::class, 'reject'])->name('loans.reject');

    // Operations & Administration - Reports
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/account-statement', [\App\Http\Controllers\ReportController::class, 'accountStatement'])->name('reports.account-statement');
    Route::get('/reports/account-statement/download', [\App\Http\Controllers\ReportController::class, 'downloadAccountStatement'])->name('reports.account-statement.download');
    Route::get('/reports/savings-interest', [\App\Http\Controllers\ReportController::class, 'savingsInterest'])->name('reports.savings-interest');
    Route::get('/reports/transaction-journal', [\App\Http\Controllers\ReportController::class, 'transactionJournal'])->name('reports.transaction-journal');
    Route::get('/reports/gl-movements', [\App\Http\Controllers\ReportController::class, 'glMovements'])->name('reports.gl-movements');
    Route::get('/reports/overdrawn-accounts', [\App\Http\Controllers\ReportController::class, 'overdrawnAccounts'])->name('reports.overdrawn-accounts');
    Route::get('/reports/dormant-accounts', [\App\Http\Controllers\ReportController::class, 'dormantAccounts'])->name('reports.dormant-accounts');
    Route::get('/reports/suspicious-activity', [\App\Http\Controllers\ReportController::class, 'suspiciousActivity'])->name('reports.suspicious-activity');
    Route::get('/reports/loan-disbursements-repayments', [\App\Http\Controllers\ReportController::class, 'loanDisbursementsRepayments'])->name('reports.loan-disbursements-repayments');
    Route::get('/reports/branch-performance', [\App\Http\Controllers\ReportController::class, 'branchPerformance'])->name('reports.branch-performance');
    Route::get('/reports/trial-balance', [\App\Http\Controllers\ReportController::class, 'trialBalance'])->name('reports.trial-balance');
    Route::get('/reports/loan-portfolio', [\App\Http\Controllers\ReportController::class, 'loanPortfolio'])->name('reports.loan-portfolio');
    Route::get('/reports/interest-accrual', [\App\Http\Controllers\ReportController::class, 'interestAccrual'])->name('reports.interest-accrual');
    Route::get('/reports/par-aging', [\App\Http\Controllers\ReportController::class, 'parAging'])->name('reports.par-aging');
    Route::get('/reports/ifrs9', [\App\Http\Controllers\ReportController::class, 'ifrs9'])->name('reports.ifrs9');
    Route::get('/reports/loan-repayment-schedule', [\App\Http\Controllers\ReportController::class, 'loanRepaymentSchedule'])->name('reports.loan-repayment-schedule');
    Route::get('/reports/collections', [\App\Http\Controllers\ReportController::class, 'collectionsReport'])->name('reports.collections');
    Route::get('/reports/product-performance', [\App\Http\Controllers\ReportController::class, 'productPerformance'])->name('reports.product-performance');
    Route::get('/reports/kyc-summary', [\App\Http\Controllers\ReportController::class, 'kycSummary'])->name('reports.kyc-summary');
    Route::get('/reports/maturity-profile', [\App\Http\Controllers\ReportController::class, 'maturityProfile'])->name('reports.maturity-profile');
    Route::get('/reports/fee-charges-register', [\App\Http\Controllers\ReportController::class, 'feeChargesRegister'])->name('reports.fee-charges-register');
    Route::get('/reports/staff-activity-audit', [\App\Http\Controllers\ReportController::class, 'staffActivityAudit'])->name('reports.staff-activity-audit');
    Route::get('/reports/single-obligor-limit', [\App\Http\Controllers\ReportController::class, 'singleObligorLimit'])->name('reports.single-obligor-limit');
    Route::get('/reports/loan-due-today', [\App\Http\Controllers\ReportController::class, 'loanDueToday'])->name('reports.loan-due-today');
    Route::get('/reports/fixed-assets', [\App\Http\Controllers\ReportController::class, 'fixedAssets'])->name('reports.fixed-assets');
    Route::get('/reports/loan-analytics-demographics', [\App\Http\Controllers\ReportController::class, 'loanAnalyticsDemographics'])->name('reports.loan-analytics-demographics');
    Route::get('/reports/call-over', [\App\Http\Controllers\ReportController::class, 'callOver'])->name('reports.call-over');
    Route::get('/reports/icard', [\App\Http\Controllers\ReportController::class, 'icardReport'])->name('reports.icard');

    // Custom Report Builder
    Route::prefix('custom-reports')->name('custom-reports.')->group(function () {
        Route::get('/', [CustomReportController::class, 'index'])->name('index');
        Route::get('/create', [CustomReportController::class, 'create'])->name('create');
        Route::post('/preview', [CustomReportController::class, 'preview'])->name('preview');
        Route::post('/', [CustomReportController::class, 'store'])->name('store');
        Route::get('/{id}', [CustomReportController::class, 'show'])->name('show');
        Route::get('/{id}/export', [CustomReportController::class, 'export'])->name('export');
        Route::delete('/{id}', [CustomReportController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/schedule', [CustomReportController::class, 'schedule'])->name('schedule');
        Route::delete('/{id}/schedule', [CustomReportController::class, 'unschedule'])->name('unschedule');
    });

    // Board Pack Generator
    Route::get('board-pack', [BoardPackController::class, 'index'])->name('board-pack.generate');
    Route::post('board-pack/generate', [BoardPackController::class, 'generate'])->name('board-pack.download');

    // System Administration
    Route::resource('roles', RoleController::class);
    Route::get('branches/analytics', [BranchController::class, 'analytics'])->name('branches.analytics');
    Route::resource('branches', BranchController::class);
    Route::resource('gl-accounts', GlAccountController::class);
    Route::resource('users', UserController::class);
    Route::resource('tenants', TenantController::class);

    // Group Lending
    Route::resource('centres', CentreController::class);
    Route::resource('groups', GroupController::class);
    Route::post('groups/{group}/members', [GroupController::class, 'addMember'])->name('groups.members.add');
    Route::delete('groups/{group}/members/{member}', [GroupController::class, 'removeMember'])->name('groups.members.remove');
    Route::resource('groups.meetings', MeetingController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('groups/{group}/meetings/{meeting}/attendance', [MeetingController::class, 'updateAttendance'])->name('groups.meetings.attendance');

    // Customer Routes
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/kyc-review', [CustomerController::class, 'reviewKyc'])
        ->name('customers.kyc');
    Route::post('customers/{customer}/documents', [CustomerController::class, 'uploadDocument'])
        ->name('customers.documents.upload');
    Route::get('customers/{customer}/ai-review', [CustomerController::class, 'generateAiReview'])
        ->name('customers.ai.review');
    Route::post('customers/{customer}/portal/activate', [CustomerController::class, 'portalActivate'])
        ->name('customers.portal.activate');
    Route::post('customers/{customer}/portal/deactivate', [CustomerController::class, 'portalDeactivate'])
        ->name('customers.portal.deactivate');
    Route::post('customers/{customer}/portal/reset-password', [CustomerController::class, 'portalResetPassword'])
        ->name('customers.portal.reset-password');

    // Customer Proxy Actions (admin acting on behalf of customer)
    Route::prefix('customers/{customer}/proxy')->name('proxy.')->group(function () {
        Route::post('transfer', [ProxyActionController::class, 'transfer'])->name('transfer');
        Route::post('freeze-account', [ProxyActionController::class, 'freezeAccount'])->name('freeze-account');
        Route::post('unfreeze-account', [ProxyActionController::class, 'unfreezeAccount'])->name('unfreeze-account');
        Route::post('update-pin', [ProxyActionController::class, 'updatePin'])->name('update-pin');
        Route::post('open-account', [ProxyActionController::class, 'openAccount'])->name('open-account');
        Route::post('close-account', [ProxyActionController::class, 'closeAccount'])->name('close-account');
        Route::post('waive-fee', [ProxyActionController::class, 'waiveFee'])->name('waive-fee');
        Route::post('loan-repayment', [ProxyActionController::class, 'proxyLoanRepayment'])->name('loan-repayment');
        Route::get('log', [ProxyActionController::class, 'actionLog'])->name('log');
    });

    // Account & Savings Routes
    Route::resource('savings-products', SavingsProductController::class);
    Route::resource('accounts', AccountController::class);
    Route::patch('accounts/{account}/status', [AccountController::class, 'updateStatus'])->name('accounts.status');

    // Transaction Routes
    Route::resource('transactions', TransactionController::class)->only(['index', 'create', 'store']);
    Route::post('transactions/transfer', [TransactionController::class, 'transfer'])->name('transactions.transfer');

    // Loan Routes
    Route::resource('loan-products', LoanProductController::class);
    Route::resource('loans', LoanController::class);
    Route::post('loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::post('loans/{loan}/disburse', [LoanController::class, 'disburse'])->name('loans.disburse');
    Route::post('loans/{loan}/repay', [LoanController::class, 'repay'])->name('loans.repay');
    // Liquidation
    Route::post('loans/{loan}/liquidate', [\App\Http\Controllers\LoanLiquidationController::class, 'store'])->name('loans.liquidate');
    // Restructuring
    Route::get('loans/{loan}/restructures', [\App\Http\Controllers\LoanRestructureController::class, 'index'])->name('loans.restructures.index');
    Route::post('loans/{loan}/restructures', [\App\Http\Controllers\LoanRestructureController::class, 'store'])->name('loans.restructures.store');
    Route::post('loan-restructures/{restructure}/approve', [\App\Http\Controllers\LoanRestructureController::class, 'approve'])->name('loan.restructures.approve');
    Route::post('loan-restructures/{restructure}/reject', [\App\Http\Controllers\LoanRestructureController::class, 'reject'])->name('loan.restructures.reject');

    // Top-ups
    Route::get('loans/{loan}/topups', [\App\Http\Controllers\LoanTopupController::class, 'index'])->name('loans.topups.index');
    Route::post('loans/{loan}/topups', [\App\Http\Controllers\LoanTopupController::class, 'store'])->name('loans.topups.store');
    Route::post('loan-topups/{topup}/approve', [\App\Http\Controllers\LoanTopupController::class, 'approve'])->name('loan.topups.approve');
    Route::post('loan-topups/{topup}/reject', [\App\Http\Controllers\LoanTopupController::class, 'reject'])->name('loan.topups.reject');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/templates', [NotificationController::class, 'templates'])->name('notifications.templates');
    Route::post('notifications/templates', [NotificationController::class, 'storeTemplate'])->name('notifications.templates.store');
    Route::delete('notifications/templates/{template}', [NotificationController::class, 'destroyTemplate'])->name('notifications.templates.destroy');

    // Posting Files
    Route::resource('posting-files', PostingFileController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('posting-files/{postingFile}/post', [PostingFileController::class, 'post'])->name('posting-files.post');
    Route::get('posting-files-template', [PostingFileController::class, 'downloadTemplate'])->name('posting-files.template');

    // Inbound Transfers
    Route::resource('inbound-transfers', InboundTransferController::class)->only(['index', 'show']);
    Route::post('inbound-transfers/{inboundTransfer}/post', [InboundTransferController::class, 'post'])->name('inbound-transfers.post');

    // Agent Banking
    Route::resource('agents', AgentController::class);
    Route::post('agents/{agent}/fund-float', [AgentController::class, 'fundFloat'])->name('agents.fund-float');
    Route::post('agents/{agent}/visits', [AgentController::class, 'logVisit'])->name('agents.visits.log');

    // IFRS 9 ECL
    Route::get('ecl', [EclController::class, 'index'])->name('ecl.index');
    Route::post('ecl/run', [EclController::class, 'run'])->name('ecl.run');

    // Credit Bureau — customer-centric
    Route::get('bureau', [BureauController::class, 'index'])->name('bureau.index');
    Route::get('bureau/upload', [BureauController::class, 'uploadForm'])->name('bureau.upload');
    Route::post('bureau/upload', [BureauController::class, 'uploadProcess'])->name('bureau.upload.process');
    Route::post('bureau/query', [BureauController::class, 'query'])->name('bureau.query');
    // Customer-scoped routes (before wildcard)
    Route::get('bureau/customer/{customer}', [BureauController::class, 'customerReports'])->name('bureau.customer.reports');
    Route::get('bureau/customer/{customer}/internal', [BureauController::class, 'internalReport'])->name('bureau.customer.internal');
    // Report-level routes
    Route::get('bureau/{bureauReport}/analytics', [BureauController::class, 'analytics'])->name('bureau.analytics');
    Route::get('bureau/{bureauReport}', [BureauController::class, 'show'])->name('bureau.show');

    // Smart Collections
    Route::get('collections', [CollectionController::class, 'index'])->name('collections.index');
    Route::get('collections/{loan}', [CollectionController::class, 'show'])->name('collections.show');
    Route::post('collections/{loan}/log', [CollectionController::class, 'logAction'])->name('collections.log');

    // Insurance
    Route::resource('insurance', InsuranceController::class)->only(['index', 'create', 'store', 'show']);
    Route::patch('insurance/{insurancePolicy}', [InsuranceController::class, 'update'])->name('insurance.update');

    // ── KPI / Performance Management ─────────────────────────────────────
    Route::prefix('kpi')->name('kpi.')->group(function () {

        // Personal dashboard (all staff)
        Route::get('me', [KpiDashboardController::class, 'myPerformance'])->name('me');

        // Individual staff KPI dashboard (HR / manager view)
        Route::get('staff/{staffProfile}', [KpiDashboardController::class, 'staffDashboard'])->name('staff');

        // Team dashboard (team lead and above)
        Route::get('team/{team}', [KpiDashboardController::class, 'teamDashboard'])->name('team');

        // Branch dashboard (branch manager and above)
        Route::get('branch/{branch}', [KpiDashboardController::class, 'branchDashboard'])->name('branch');

        // HQ overview (senior management / HQ admin)
        Route::get('hq', [KpiDashboardController::class, 'hqOverview'])->name('hq');

        // Alerts
        Route::get('alerts', [KpiAlertController::class, 'index'])->name('alerts.index');
        Route::patch('alerts/mark-all-read', [KpiAlertController::class, 'markAllRead'])->name('alerts.mark-all-read');
        Route::patch('alerts/{kpiAlert}/read', [KpiAlertController::class, 'markRead'])->name('alerts.read');
        Route::patch('alerts/{kpiAlert}/dismiss', [KpiAlertController::class, 'dismiss'])->name('alerts.dismiss');

        // Notes (AJAX-friendly)
        Route::get('notes', [KpiNoteController::class, 'index'])->name('notes.index');
        Route::post('notes', [KpiNoteController::class, 'store'])->name('notes.store');
        Route::delete('notes/{kpiNote}', [KpiNoteController::class, 'destroy'])->name('notes.destroy');

        // Setup — KPI definitions
        Route::get('setup/definitions', [KpiDefinitionController::class, 'index'])->name('definitions.index');
        Route::post('setup/definitions', [KpiDefinitionController::class, 'store'])->name('definitions.store');
        Route::patch('setup/definitions/{kpiDefinition}', [KpiDefinitionController::class, 'update'])->name('definitions.update');
        Route::delete('setup/definitions/{kpiDefinition}', [KpiDefinitionController::class, 'destroy'])->name('definitions.destroy');

        // Setup — Targets
        Route::get('setup/targets', [KpiTargetController::class, 'index'])->name('targets.index');
        Route::post('setup/targets', [KpiTargetController::class, 'store'])->name('targets.store');
        Route::post('setup/targets/bulk', [KpiTargetController::class, 'bulkStore'])->name('targets.bulk');
        Route::delete('setup/targets/{kpiTarget}', [KpiTargetController::class, 'destroy'])->name('targets.destroy');

        // Setup — Staff profiles
        Route::get('setup/staff', [StaffProfileController::class, 'index'])->name('staff.index');
        Route::post('setup/staff', [StaffProfileController::class, 'store'])->name('staff.store');
        Route::patch('setup/staff/{staffProfile}', [StaffProfileController::class, 'update'])->name('staff.update');
        Route::post('setup/staff/{staffProfile}/regen-code', [StaffProfileController::class, 'regenerateCode'])->name('staff.regen-code');

        // Setup — Teams
        Route::get('setup/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('setup/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::patch('setup/teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('setup/teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
        Route::post('setup/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.add');
        Route::delete('setup/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.remove');

        // Manual compute trigger (HQ admin)
        Route::post('compute', [KpiDashboardController::class, 'triggerCompute'])->name('compute');
    });

    // ── HR MODULE ─────────────────────────────────────────────────────────────
    Route::prefix('hr')->name('hr.')->group(function () {

        // HR Dashboard
        Route::get('/', [\App\Http\Controllers\Hr\HrDashboardController::class, 'index'])->name('dashboard');

        // Org structure (regions, divisions, departments)
        Route::get('org', [OrgController::class, 'index'])->name('org.index');
        Route::post('org/regions', [OrgController::class, 'storeRegion'])->name('org.regions.store');
        Route::patch('org/regions/{region}', [OrgController::class, 'updateRegion'])->name('org.regions.update');
        Route::delete('org/regions/{region}', [OrgController::class, 'destroyRegion'])->name('org.regions.destroy');
        Route::post('org/divisions', [OrgController::class, 'storeDivision'])->name('org.divisions.store');
        Route::patch('org/divisions/{division}', [OrgController::class, 'updateDivision'])->name('org.divisions.update');
        Route::delete('org/divisions/{division}', [OrgController::class, 'destroyDivision'])->name('org.divisions.destroy');
        Route::post('org/departments', [OrgController::class, 'storeDepartment'])->name('org.departments.store');
        Route::patch('org/departments/{department}', [OrgController::class, 'updateDepartment'])->name('org.departments.update');
        Route::delete('org/departments/{department}', [OrgController::class, 'destroyDepartment'])->name('org.departments.destroy');

        // Leave types
        Route::get('leave/types', [LeaveTypeController::class, 'index'])->name('leave.types.index');
        Route::post('leave/types', [LeaveTypeController::class, 'store'])->name('leave.types.store');
        Route::patch('leave/types/{leaveType}', [LeaveTypeController::class, 'update'])->name('leave.types.update');
        Route::delete('leave/types/{leaveType}', [LeaveTypeController::class, 'destroy'])->name('leave.types.destroy');
        Route::post('leave/types/init-balances', [LeaveTypeController::class, 'initBalances'])->name('leave.types.init-balances');

        // Leave requests
        Route::get('leave/requests', [LeaveRequestController::class, 'index'])->name('leave.requests.index');
        Route::get('leave/my-requests', [LeaveRequestController::class, 'myRequests'])->name('leave.my-requests');
        Route::post('leave/requests', [LeaveRequestController::class, 'store'])->name('leave.requests.store');
        Route::post('leave/requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave.requests.approve');
        Route::post('leave/requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave.requests.reject');
        Route::post('leave/requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel'])->name('leave.requests.cancel');

        // Disciplinary
        Route::get('disciplinary', [DisciplinaryController::class, 'index'])->name('disciplinary.index');
        Route::post('disciplinary', [DisciplinaryController::class, 'store'])->name('disciplinary.store');
        Route::get('disciplinary/{disciplinaryCase}', [DisciplinaryController::class, 'show'])->name('disciplinary.show');
        Route::post('disciplinary/{disciplinaryCase}/respond', [DisciplinaryController::class, 'respond'])->name('disciplinary.respond');
        Route::post('disciplinary/{disciplinaryCase}/close', [DisciplinaryController::class, 'close'])->name('disciplinary.close');
        Route::post('disciplinary/{disciplinaryCase}/appeal', [DisciplinaryController::class, 'appeal'])->name('disciplinary.appeal');

        // Performance review cycles
        Route::get('performance/cycles', [ReviewCycleController::class, 'index'])->name('performance.cycles.index');
        Route::post('performance/cycles', [ReviewCycleController::class, 'store'])->name('performance.cycles.store');
        Route::post('performance/cycles/{reviewCycle}/activate', [ReviewCycleController::class, 'activate'])->name('performance.cycles.activate');
        Route::post('performance/cycles/{reviewCycle}/close', [ReviewCycleController::class, 'close'])->name('performance.cycles.close');
        Route::get('performance/cycles/{reviewCycle}', [ReviewCycleController::class, 'show'])->name('performance.cycles.show');

        // Performance reviews
        Route::get('performance/my-reviews', [PerformanceReviewController::class, 'myReviews'])->name('performance.my-reviews');
        Route::get('performance/reviews/{performanceReview}', [PerformanceReviewController::class, 'show'])->name('performance.reviews.show');
        Route::post('performance/reviews/{performanceReview}/self-assess', [PerformanceReviewController::class, 'selfAssess'])->name('performance.reviews.self-assess');
        Route::post('performance/reviews/{performanceReview}/manager-review', [PerformanceReviewController::class, 'managerReview'])->name('performance.reviews.manager-review');
        Route::post('performance/reviews/{performanceReview}/hr-approve', [PerformanceReviewController::class, 'hrApprove'])->name('performance.reviews.hr-approve');

        // Training, certifications, documents
        Route::get('training', [TrainingController::class, 'index'])->name('training.index');
        Route::post('training', [TrainingController::class, 'store'])->name('training.store');
        Route::patch('training/{trainingProgram}', [TrainingController::class, 'update'])->name('training.update');
        Route::post('training/{trainingProgram}/enroll', [TrainingController::class, 'enroll'])->name('training.enroll');
        Route::patch('training/attendance/{trainingAttendance}', [TrainingController::class, 'updateAttendance'])->name('training.attendance.update');
        Route::get('my-certifications', [TrainingController::class, 'myCertifications'])->name('training.my-certs');
        Route::post('certifications', [TrainingController::class, 'storeCertification'])->name('certifications.store');
        Route::post('documents', [TrainingController::class, 'storeDocument'])->name('documents.store');
        Route::post('documents/{staffDocument}/verify', [TrainingController::class, 'verifyDocument'])->name('documents.verify');

        // Approval Matrices (maker-checker configuration)
        Route::get('approvals/matrix', [\App\Http\Controllers\Hr\ApprovalMatrixController::class, 'index'])->name('approvals.matrix');
        Route::post('approvals/matrix', [\App\Http\Controllers\Hr\ApprovalMatrixController::class, 'store'])->name('approvals.matrix.store');
        Route::patch('approvals/matrix/{approvalMatrix}', [\App\Http\Controllers\Hr\ApprovalMatrixController::class, 'update'])->name('approvals.matrix.update');
        Route::delete('approvals/matrix/{approvalMatrix}', [\App\Http\Controllers\Hr\ApprovalMatrixController::class, 'destroy'])->name('approvals.matrix.destroy');
        Route::post('approvals/matrix/{approvalMatrix}/toggle', [\App\Http\Controllers\Hr\ApprovalMatrixController::class, 'toggle'])->name('approvals.matrix.toggle');

        // Approval Requests (inbox + actions)
        Route::get('approvals/requests', [\App\Http\Controllers\Hr\ApprovalRequestController::class, 'index'])->name('approvals.requests');
        Route::get('approvals/requests/{approvalRequest}', [\App\Http\Controllers\Hr\ApprovalRequestController::class, 'show'])->name('approvals.requests.show');
        Route::post('approvals/requests/{approvalRequest}/approve', [\App\Http\Controllers\Hr\ApprovalRequestController::class, 'approve'])->name('approvals.requests.approve');
        Route::post('approvals/requests/{approvalRequest}/reject', [\App\Http\Controllers\Hr\ApprovalRequestController::class, 'reject'])->name('approvals.requests.reject');
        Route::post('approvals/requests/{approvalRequest}/cancel', [\App\Http\Controllers\Hr\ApprovalRequestController::class, 'cancel'])->name('approvals.requests.cancel');

        // Staff ID Cards
        Route::get('id-cards', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'index'])->name('id-cards.index');
        Route::post('id-cards', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'store'])->name('id-cards.store');
        Route::post('id-cards/bulk', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'bulkGenerate'])->name('id-cards.bulk');
        Route::get('id-cards/{staffIdCard}/download', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'download'])->name('id-cards.download');
        Route::post('id-cards/{staffIdCard}/report-lost', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'reportLost'])->name('id-cards.report-lost');
        Route::post('id-cards/{staffIdCard}/replace', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'replace'])->name('id-cards.replace');
        Route::get('id-cards/verify/{cardNumber}', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'verify'])->name('id-cards.verify')->withoutMiddleware(['auth']);

        // ID Card Templates
        Route::get('id-cards/templates', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'templates'])->name('id-cards.templates');
        Route::post('id-cards/templates', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'templateStore'])->name('id-cards.templates.store');
        Route::patch('id-cards/templates/{cardTemplate}', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'templateUpdate'])->name('id-cards.templates.update');
        Route::delete('id-cards/templates/{cardTemplate}', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'templateDestroy'])->name('id-cards.templates.destroy');
        Route::post('id-cards/templates/{cardTemplate}/set-default', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'templateSetDefault'])->name('id-cards.templates.set-default');
        Route::post('id-cards/templates/{cardTemplate}/upload-logo', [\App\Http\Controllers\Hr\StaffIdCardController::class, 'templateUploadLogo'])->name('id-cards.templates.upload-logo');

        // Attendance
        Route::get('attendance', [\App\Http\Controllers\Hr\AttendanceController::class, 'index'])->name('attendance.index');
        Route::get('attendance/export', [\App\Http\Controllers\Hr\AttendanceController::class, 'exportMonthly'])->name('attendance.export');
        Route::get('attendance/policies', [\App\Http\Controllers\Hr\AttendanceController::class, 'policies'])->name('attendance.policies');
        Route::post('attendance/policies', [\App\Http\Controllers\Hr\AttendanceController::class, 'storePolicy'])->name('attendance.policies.store');
        Route::post('attendance/mark', [\App\Http\Controllers\Hr\AttendanceController::class, 'markAttendance'])->name('attendance.mark');
        Route::post('attendance/bulk-mark', [\App\Http\Controllers\Hr\AttendanceController::class, 'bulkMark'])->name('attendance.bulk-mark');
        Route::get('attendance/{staffProfile}', [\App\Http\Controllers\Hr\AttendanceController::class, 'staffDetail'])->name('attendance.staff');

        // Expense Claims
        Route::get('expense-claims', [\App\Http\Controllers\Hr\ExpenseClaimController::class, 'index'])->name('expense-claims.index');
        Route::post('expense-claims', [\App\Http\Controllers\Hr\ExpenseClaimController::class, 'store'])->name('expense-claims.store');
        Route::post('expense-claims/{expenseClaim}/submit', [\App\Http\Controllers\Hr\ExpenseClaimController::class, 'submit'])->name('expense-claims.submit');
        Route::post('expense-claims/{expenseClaim}/approve', [\App\Http\Controllers\Hr\ExpenseClaimController::class, 'approve'])->name('expense-claims.approve');
        Route::post('expense-claims/{expenseClaim}/reject', [\App\Http\Controllers\Hr\ExpenseClaimController::class, 'reject'])->name('expense-claims.reject');
        Route::post('expense-claims/{expenseClaim}/paid', [\App\Http\Controllers\Hr\ExpenseClaimController::class, 'markPaid'])->name('expense-claims.paid');

        // Public Holidays
        Route::get('holidays', [\App\Http\Controllers\Hr\PublicHolidayController::class, 'index'])->name('holidays.index');
        Route::post('holidays', [\App\Http\Controllers\Hr\PublicHolidayController::class, 'store'])->name('holidays.store');
        Route::patch('holidays/{publicHoliday}', [\App\Http\Controllers\Hr\PublicHolidayController::class, 'update'])->name('holidays.update');
        Route::delete('holidays/{publicHoliday}', [\App\Http\Controllers\Hr\PublicHolidayController::class, 'destroy'])->name('holidays.destroy');
        Route::post('holidays/{publicHoliday}/toggle', [\App\Http\Controllers\Hr\PublicHolidayController::class, 'toggle'])->name('holidays.toggle');

        // Announcements
        Route::get('announcements', [\App\Http\Controllers\Hr\AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('announcements/manage', [\App\Http\Controllers\Hr\AnnouncementController::class, 'manage'])->name('announcements.manage');
        Route::post('announcements', [\App\Http\Controllers\Hr\AnnouncementController::class, 'store'])->name('announcements.store');
        Route::post('announcements/{announcement}/publish', [\App\Http\Controllers\Hr\AnnouncementController::class, 'publish'])->name('announcements.publish');
        Route::post('announcements/{announcement}/pin', [\App\Http\Controllers\Hr\AnnouncementController::class, 'togglePin'])->name('announcements.pin');
        Route::delete('announcements/{announcement}', [\App\Http\Controllers\Hr\AnnouncementController::class, 'destroy'])->name('announcements.destroy');

        // Salary Advances
        Route::get('salary-advances', [\App\Http\Controllers\Hr\SalaryAdvanceController::class, 'index'])->name('salary-advances.index');
        Route::post('salary-advances', [\App\Http\Controllers\Hr\SalaryAdvanceController::class, 'store'])->name('salary-advances.store');
        Route::post('salary-advances/{salaryAdvance}/approve', [\App\Http\Controllers\Hr\SalaryAdvanceController::class, 'approve'])->name('salary-advances.approve');
        Route::post('salary-advances/{salaryAdvance}/reject', [\App\Http\Controllers\Hr\SalaryAdvanceController::class, 'reject'])->name('salary-advances.reject');
        Route::post('salary-advances/{salaryAdvance}/disburse', [\App\Http\Controllers\Hr\SalaryAdvanceController::class, 'disburse'])->name('salary-advances.disburse');
    });

    // ── CRM MODULE ────────────────────────────────────────────────────────────
    Route::prefix('crm')->name('crm.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Crm\CrmController::class, 'dashboard'])->name('dashboard');
        Route::get('leads', [\App\Http\Controllers\Crm\CrmController::class, 'leads'])->name('leads');
        Route::post('leads', [\App\Http\Controllers\Crm\CrmController::class, 'storeLead'])->name('leads.store');
        Route::get('leads/{lead}', [\App\Http\Controllers\Crm\CrmController::class, 'showLead'])->name('leads.show');
        Route::patch('leads/{lead}', [\App\Http\Controllers\Crm\CrmController::class, 'updateLead'])->name('leads.update');
        Route::get('interactions', [\App\Http\Controllers\Crm\CrmController::class, 'interactions'])->name('interactions');
        Route::post('interactions', [\App\Http\Controllers\Crm\CrmController::class, 'storeInteraction'])->name('interactions.store');
        Route::post('follow-ups', [\App\Http\Controllers\Crm\CrmController::class, 'storeFollowUp'])->name('follow-ups.store');
        Route::post('follow-ups/{followUp}/complete', [\App\Http\Controllers\Crm\CrmController::class, 'completeFollowUp'])->name('follow-ups.complete');
        Route::get('pipeline/settings', [\App\Http\Controllers\Crm\CrmController::class, 'stageSettings'])->name('pipeline.settings');
        Route::post('pipeline/stages', [\App\Http\Controllers\Crm\CrmController::class, 'storeStage'])->name('pipeline.stages.store');
        Route::delete('pipeline/stages/{stage}', [\App\Http\Controllers\Crm\CrmController::class, 'destroyStage'])->name('pipeline.stages.destroy');
        Route::get('customer360/{accountId}', [\App\Http\Controllers\Crm\CrmController::class, 'customer360'])->name('customer360');
    });

    // ── ASSET MANAGEMENT MODULE ───────────────────────────────────────────────
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Assets\AssetController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Assets\AssetController::class, 'store'])->name('store');
        Route::get('categories', [\App\Http\Controllers\Assets\AssetController::class, 'categories'])->name('categories');
        Route::post('categories', [\App\Http\Controllers\Assets\AssetController::class, 'storeCategory'])->name('categories.store');
        Route::get('procurement', [\App\Http\Controllers\Assets\ProcurementController::class, 'index'])->name('procurement');
        Route::post('procurement', [\App\Http\Controllers\Assets\ProcurementController::class, 'store'])->name('procurement.store');
        Route::post('procurement/{procurement}/submit', [\App\Http\Controllers\Assets\ProcurementController::class, 'submit'])->name('procurement.submit');
        Route::post('procurement/{procurement}/received', [\App\Http\Controllers\Assets\ProcurementController::class, 'markReceived'])->name('procurement.received');
        Route::get('{asset}', [\App\Http\Controllers\Assets\AssetController::class, 'show'])->name('show');
        Route::post('{asset}/assign', [\App\Http\Controllers\Assets\AssetController::class, 'assign'])->name('assign');
        Route::post('{asset}/return', [\App\Http\Controllers\Assets\AssetController::class, 'returnAsset'])->name('return');
        Route::post('{asset}/maintenance', [\App\Http\Controllers\Assets\AssetController::class, 'logMaintenance'])->name('maintenance');
        Route::post('maintenance/{log}/complete', [\App\Http\Controllers\Assets\AssetController::class, 'completeMaintenance'])->name('maintenance.complete');
    });

    // ── PAYROLL MODULE ────────────────────────────────────────────────────────
    Route::prefix('payroll')->name('payroll.')->group(function () {

        // Setup: pay grades, components, configs, bank details
        Route::get('setup', [PayrollSetupController::class, 'index'])->name('setup.index');
        Route::post('setup/pay-grades', [PayrollSetupController::class, 'storePayGrade'])->name('setup.pay-grades.store');
        Route::patch('setup/pay-grades/{payGrade}', [PayrollSetupController::class, 'updatePayGrade'])->name('setup.pay-grades.update');
        Route::post('setup/pay-components', [PayrollSetupController::class, 'storePayComponent'])->name('setup.pay-components.store');
        Route::patch('setup/pay-components/{payComponent}', [PayrollSetupController::class, 'updatePayComponent'])->name('setup.pay-components.update');
        Route::post('setup/pay-configs', [PayrollSetupController::class, 'storePayConfig'])->name('setup.pay-configs.store');
        Route::post('setup/bank-details', [PayrollSetupController::class, 'storeBankDetail'])->name('setup.bank-details.store');

        // Payroll runs
        Route::get('runs', [PayrollRunController::class, 'index'])->name('runs.index');
        Route::post('runs', [PayrollRunController::class, 'store'])->name('runs.store');
        Route::get('runs/{payrollRun}', [PayrollRunController::class, 'show'])->name('runs.show');
        Route::post('runs/{payrollRun}/process', [PayrollRunController::class, 'process'])->name('runs.process');
        Route::post('runs/{payrollRun}/approve', [PayrollRunController::class, 'approve'])->name('runs.approve');
        Route::post('runs/{payrollRun}/mark-paid', [PayrollRunController::class, 'markPaid'])->name('runs.mark-paid');
        Route::post('runs/{payrollRun}/cancel', [PayrollRunController::class, 'cancel'])->name('runs.cancel');

        // Payslips
        Route::get('my-payslips', [PayslipController::class, 'myPayslips'])->name('my-payslips');
        Route::get('payslip/{payrollItem}', [PayslipController::class, 'show'])->name('payslip.show');
        Route::get('payslip/{payrollItem}/download', [PayslipController::class, 'download'])->name('payslip.download');
    });

    // ── DOCUMENTS MODULE ──────────────────────────────────────────────────────
    Route::prefix('documents')->name('documents.')->group(function () {
        // ── Explicit paths first (before {document} wildcard) ──────────────
        Route::get('/', [DocumentController::class, 'index'])->name('index');
        Route::get('/upload', [DocumentController::class, 'create'])->name('create');
        Route::post('/', [DocumentController::class, 'store'])->name('store');
        Route::get('/my-actions', [DmsController::class, 'myActions'])->name('my-actions');
        Route::post('/workflow-action/{action}/act', [DmsController::class, 'act'])->name('workflow.act');
        Route::post('/folders', [DmsController::class, 'foldersStore'])->name('folders.store');
        Route::delete('/notes/{documentNote}', [DmsController::class, 'deleteNote'])->name('notes.destroy');

        // CBN Checklists
        Route::prefix('checklists')->name('checklists.')->group(function () {
            Route::get('/', [DocumentChecklistController::class, 'index'])->name('index');
            Route::post('/', [DocumentChecklistController::class, 'store'])->name('store');
            Route::patch('/{checklist}', [DocumentChecklistController::class, 'update'])->name('update');
            Route::delete('/{checklist}', [DocumentChecklistController::class, 'destroy'])->name('destroy');
        });

        // DMS — Workflow templates
        Route::prefix('workflows')->name('workflows.')->group(function () {
            Route::get('/', [DmsController::class, 'workflowsIndex'])->name('index');
            Route::post('/', [DmsController::class, 'workflowsStore'])->name('store');
            Route::post('/{documentWorkflow}/toggle', [DmsController::class, 'workflowsToggle'])->name('toggle');
            Route::delete('/{documentWorkflow}', [DmsController::class, 'workflowsDestroy'])->name('destroy');
        });

        // ── Wildcard {document} routes LAST ───────────────────────────────
        Route::get('/{document}', [DocumentController::class, 'show'])->name('show');
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('download');
        Route::get('/{document}/preview', [DocumentController::class, 'preview'])->name('preview');
        Route::post('/{document}/version', [DocumentController::class, 'newVersion'])->name('version');
        Route::post('/{document}/review', [DocumentController::class, 'review'])->name('review');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('destroy');
        Route::post('/{document}/workflow/initiate', [DmsController::class, 'initiate'])->name('workflow.initiate');
        Route::post('/{document}/sign', [DmsController::class, 'sign'])->name('sign');
        Route::post('/{document}/notes', [DmsController::class, 'addNote'])->name('notes.store');
    });

    // ── SUPPORT MODULE ────────────────────────────────────────────────────────
    Route::prefix('support')->name('support.')->group(function () {
        Route::get('/dashboard', [SupportDashboardController::class, 'index'])->name('dashboard');

        // Tickets
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [SupportTicketController::class, 'index'])->name('index');
            Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
            Route::post('/', [SupportTicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [SupportTicketController::class, 'reply'])->name('reply');
            Route::post('/{ticket}/assign', [SupportTicketController::class, 'assign'])->name('assign');
            Route::post('/{ticket}/escalate', [SupportTicketController::class, 'escalate'])->name('escalate');
            Route::post('/{ticket}/resolve', [SupportTicketController::class, 'resolve'])->name('resolve');
            Route::post('/{ticket}/close', [SupportTicketController::class, 'close'])->name('close');
        });

        // Teams
        Route::prefix('teams')->name('teams.')->group(function () {
            Route::get('/', [SupportTeamController::class, 'index'])->name('index');
            Route::post('/', [SupportTeamController::class, 'store'])->name('store');
            Route::patch('/{supportTeam}', [SupportTeamController::class, 'update'])->name('update');
            Route::post('/{supportTeam}/members', [SupportTeamController::class, 'addMember'])->name('add-member');
            Route::delete('/{supportTeam}/members/{user}', [SupportTeamController::class, 'removeMember'])->name('remove-member');
            Route::post('/{supportTeam}/toggle', [SupportTeamController::class, 'toggle'])->name('toggle');
        });

        // SLA Policies
        Route::prefix('sla')->name('sla.')->group(function () {
            Route::get('/', [SupportTeamController::class, 'slaIndex'])->name('index');
            Route::post('/', [SupportTeamController::class, 'slaStore'])->name('store');
            Route::patch('/{supportSlaPolicy}', [SupportTeamController::class, 'slaUpdate'])->name('update');
        });

        // Categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [SupportTeamController::class, 'categoriesIndex'])->name('index');
            Route::post('/', [SupportTeamController::class, 'categoriesStore'])->name('store');
            Route::post('/{supportCategory}/toggle', [SupportTeamController::class, 'categoryToggle'])->name('toggle');
        });

        // Knowledge Base
        Route::prefix('kb')->name('kb.')->group(function () {
            Route::get('/', [SupportTeamController::class, 'kbIndex'])->name('index');
            Route::post('/', [SupportTeamController::class, 'kbStore'])->name('store');
            Route::post('/{supportKbArticle}/publish', [SupportTeamController::class, 'kbPublish'])->name('publish');
            Route::delete('/{supportKbArticle}', [SupportTeamController::class, 'kbDestroy'])->name('destroy');
        });
    });

    // ── VISITOR MANAGEMENT MODULE ─────────────────────────────────────────────
    Route::prefix('visitor')->name('visitor.')->group(function () {
        Route::get('/', [VisitorController::class, 'dashboard'])->name('dashboard');
        Route::get('/visits', [VisitorController::class, 'visits'])->name('visits');
        Route::get('/visitors', [VisitorController::class, 'visitors'])->name('visitors');
        Route::post('/visitors', [VisitorController::class, 'visitorStore'])->name('visitor-store');
        Route::post('/visitors/{visitor}/blacklist', [VisitorController::class, 'visitorBlacklist'])->name('visitor-blacklist');
        Route::post('/check-in', [VisitorController::class, 'checkIn'])->name('check-in');
        Route::post('/pre-register', [VisitorController::class, 'preRegister'])->name('pre-register');
        Route::post('/visits/{visit}/check-out', [VisitorController::class, 'checkOut'])->name('check-out');
        Route::post('/visits/{visit}/deny', [VisitorController::class, 'denyEntry'])->name('deny');
        Route::get('/visits/{visit}', [VisitorController::class, 'visitShow'])->name('visit-show');
        Route::post('/visits/{visit}/activities', [VisitorController::class, 'logActivity'])->name('log-activity');
        Route::get('/meetings', [VisitorController::class, 'meetings'])->name('meetings');
        Route::post('/meetings', [VisitorController::class, 'meetingStore'])->name('meeting-store');
        Route::post('/meetings/{meeting}/status', [VisitorController::class, 'meetingUpdateStatus'])->name('meeting-status');
        Route::get('/rooms', [VisitorController::class, 'rooms'])->name('rooms');
        Route::post('/rooms', [VisitorController::class, 'roomStore'])->name('room-store');
        Route::post('/rooms/{room}/toggle', [VisitorController::class, 'roomToggle'])->name('room-toggle');
        Route::get('/watchlist', [VisitorController::class, 'watchlist'])->name('watchlist');
        Route::post('/watchlist', [VisitorController::class, 'watchlistStore'])->name('watchlist-store');
    });

    // ── COMMS MODULE ──────────────────────────────────────────────────────────
    Route::prefix('comms')->name('comms.')->group(function () {
        // Outbox (compose & sent)
        Route::prefix('messages')->name('messages.')->group(function () {
            Route::get('/', [CommsMessageController::class, 'index'])->name('index');
            Route::get('/compose', [CommsMessageController::class, 'create'])->name('create');
            Route::post('/', [CommsMessageController::class, 'store'])->name('store');
            Route::get('/{commsMessage}/edit', [CommsMessageController::class, 'edit'])->name('edit');
            Route::patch('/{commsMessage}', [CommsMessageController::class, 'update'])->name('update');
            Route::post('/{commsMessage}/publish', [CommsMessageController::class, 'publish'])->name('publish');
            Route::post('/{commsMessage}/archive', [CommsMessageController::class, 'archive'])->name('archive');
            Route::get('/{commsMessage}/recipients', [CommsMessageController::class, 'recipients'])->name('recipients');
        });

        // Inbox
        Route::get('/inbox', [CommsInboxController::class, 'index'])->name('inbox.index');
        Route::get('/inbox/{message}', [CommsInboxController::class, 'show'])->name('inbox.show');
        Route::post('/inbox/{message}/acknowledge', [CommsInboxController::class, 'acknowledge'])->name('inbox.acknowledge');
    });

    // ── FIXED DEPOSITS ────────────────────────────────────────────────────────
    Route::get('fixed-deposits', [FixedDepositController::class, 'index'])->name('fixed-deposits.index');
    Route::get('fixed-deposits/create', [FixedDepositController::class, 'create'])->name('fixed-deposits.create');
    Route::post('fixed-deposits', [FixedDepositController::class, 'store'])->name('fixed-deposits.store');
    Route::get('fixed-deposits/{fixedDeposit}', [FixedDepositController::class, 'show'])->name('fixed-deposits.show');
    Route::post('fixed-deposits/{fixedDeposit}/liquidate', [FixedDepositController::class, 'liquidate'])->name('fixed-deposits.liquidate');

    // FD Products
    Route::get('fd-products', [FixedDepositProductController::class, 'index'])->name('fd-products.index');
    Route::post('fd-products', [FixedDepositProductController::class, 'store'])->name('fd-products.store');
    Route::patch('fd-products/{fixedDepositProduct}', [FixedDepositProductController::class, 'update'])->name('fd-products.update');

    // ── STANDING ORDERS ───────────────────────────────────────────────────────
    Route::get('standing-orders', [StandingOrderController::class, 'index'])->name('standing-orders.index');
    Route::get('standing-orders/create', [StandingOrderController::class, 'create'])->name('standing-orders.create');
    Route::post('standing-orders', [StandingOrderController::class, 'store'])->name('standing-orders.store');
    Route::delete('standing-orders/{standingOrder}', [StandingOrderController::class, 'destroy'])->name('standing-orders.destroy');
    Route::patch('standing-orders/{standingOrder}/pause', [StandingOrderController::class, 'pause'])->name('standing-orders.pause');

    // ── OVERDRAFT FACILITIES ──────────────────────────────────────────────────
    Route::get('overdrafts', [OverdraftController::class, 'index'])->name('overdrafts.index');
    Route::post('overdrafts', [OverdraftController::class, 'store'])->name('overdrafts.store');
    Route::patch('overdrafts/{overdraft}', [OverdraftController::class, 'update'])->name('overdrafts.update');

    // ── ACCOUNT LIENS & PND ───────────────────────────────────────────────────
    Route::post('accounts/{account}/liens', [AccountLienController::class, 'store'])->name('accounts.liens.store');
    Route::patch('accounts/liens/{lien}/lift', [AccountLienController::class, 'lift'])->name('accounts.liens.lift');
    Route::post('accounts/{account}/pnd', [AccountLienController::class, 'pnd'])->name('accounts.liens.pnd');

    // ── ACCOUNT LIFECYCLE ─────────────────────────────────────────────────────
    Route::post('accounts/{account}/close', [AccountController::class, 'close'])->name('accounts.close');
    Route::post('accounts/{account}/reactivate', [AccountController::class, 'reactivate'])->name('accounts.reactivate');

    // ── TELLER OPERATIONS ─────────────────────────────────────────────────────
    Route::prefix('teller')->name('teller.')->group(function () {
        Route::get('/', [TellerController::class, 'index'])->name('index');
        Route::get('/monitor', [\App\Http\Controllers\Teller\TellerMonitoringController::class, 'index'])->name('monitor');
        Route::post('/open', [TellerController::class, 'openSession'])->name('open');
        Route::patch('/sessions/{session}/close', [TellerController::class, 'closeSession'])->name('close');
        Route::post('/cash-deposit', [TellerController::class, 'cashDeposit'])->name('deposit');
        Route::post('/cash-withdrawal', [TellerController::class, 'cashWithdrawal'])->name('withdrawal');
        Route::get('/lookup-account', [TellerController::class, 'lookupAccount'])->name('lookup'); // JSON
    });

    // ── CHEQUE MANAGEMENT ─────────────────────────────────────────────────────
    Route::prefix('cheques')->name('cheques.')->group(function () {
        Route::get('/', [ChequeController::class, 'index'])->name('index');
        // Cheque books
        Route::post('/books', [ChequeController::class, 'store'])->name('books.store');
        Route::patch('/books/{chequeBook}/cancel', [ChequeController::class, 'cancel'])->name('books.cancel');
        // Cheque transactions
        Route::post('/', [ChequeController::class, 'storeCheque'])->name('store');
        Route::patch('/{cheque}/process', [ChequeController::class, 'process'])->name('process');
    });

    // ── FIXED ASSETS ──────────────────────────────────────────────────────────
    Route::prefix('fixed-assets')->name('fixed-assets.')->group(function () {
        Route::get('/', [FixedAssetController::class, 'index'])->name('index');
        Route::get('/create', [FixedAssetController::class, 'create'])->name('create');
        Route::post('/', [FixedAssetController::class, 'store'])->name('store');
        Route::get('/{fixedAsset}', [FixedAssetController::class, 'show'])->name('show');
        Route::patch('/{fixedAsset}/dispose', [FixedAssetController::class, 'dispose'])->name('dispose');
        Route::patch('/{fixedAsset}/revalue', [FixedAssetController::class, 'revalue'])->name('revalue');
        // Categories
        Route::post('/categories', [FixedAssetController::class, 'storeCategory'])->name('categories.store');
    });

    // ── COMPLIANCE / REGULATORY REPORTS ───────────────────────────────────────
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('/', [RegulatoryReportController::class, 'dashboard'])->name('dashboard');
        Route::get('/ndic', [RegulatoryReportController::class, 'ndicDepositors'])->name('ndic');
        Route::get('/ndic/download', [RegulatoryReportController::class, 'ndicDownload'])->name('ndic.download');
        Route::get('/nfiu-ctr', [RegulatoryReportController::class, 'nfiuCtr'])->name('nfiu-ctr');
        Route::get('/nfiu-ctr/download', [RegulatoryReportController::class, 'nfiuCtrDownload'])->name('nfiu-ctr.download');
    });

    // ── CREDIT POLICY / RULE ENGINE ───────────────────────────────────────────
    Route::prefix('credit/policies')->name('credit.policies.')->group(function () {
        Route::get('/', [CreditPolicyController::class, 'index'])->name('index');
        Route::get('/create', [CreditPolicyController::class, 'create'])->name('create');
        Route::post('/', [CreditPolicyController::class, 'store'])->name('store');
        Route::get('/{creditPolicy}', [CreditPolicyController::class, 'show'])->name('show');
        Route::patch('/{creditPolicy}', [CreditPolicyController::class, 'update'])->name('update');
        Route::post('/{creditPolicy}/rules', [CreditPolicyController::class, 'storeRule'])->name('rules.store');
        Route::delete('/rules/{rule}', [CreditPolicyController::class, 'destroyRule'])->name('rules.destroy');
        Route::post('/evaluate/{loan}', [CreditPolicyController::class, 'evaluate'])->name('evaluate');
    });

    // ── TRANSFER PROVIDERS ──────────────────────────────────────────────────
    Route::prefix('transfer-providers')->name('transfer-providers.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TransferProviderController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\TransferProviderController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\TransferProviderController::class, 'store'])->name('store');
        Route::get('/{provider}/edit', [\App\Http\Controllers\TransferProviderController::class, 'edit'])->name('edit');
        Route::patch('/{provider}', [\App\Http\Controllers\TransferProviderController::class, 'update'])->name('update');
        Route::patch('/{provider}/toggle', [\App\Http\Controllers\TransferProviderController::class, 'toggleActive'])->name('toggle');
        Route::patch('/{provider}/default', [\App\Http\Controllers\TransferProviderController::class, 'setDefault'])->name('default');
    });

    // ── NIP OUTWARD TRANSFERS ─────────────────────────────────────────────────
    Route::prefix('nip')->name('nip.')->group(function () {
        Route::get('/', [NipController::class, 'index'])->name('index');
        Route::get('/create', [NipController::class, 'create'])->name('create');
        Route::post('/', [NipController::class, 'store'])->name('store');
        Route::get('/{transfer}', [NipController::class, 'show'])->name('show');
        Route::post('/name-enquiry', [NipController::class, 'nameEnquiry'])->name('name-enquiry'); // JSON
    });

    // ── MANDATE MANAGEMENT ────────────────────────────────────────────────────
    Route::prefix('mandates')->name('mandates.')->group(function () {
        Route::get('/', [MandateController::class, 'index'])->name('index');
        Route::get('/create', [MandateController::class, 'create'])->name('create');
        Route::post('/', [MandateController::class, 'store'])->name('store');
        Route::get('/approvals', [MandateController::class, 'approvals'])->name('approvals');
        Route::get('/{mandate}', [MandateController::class, 'show'])->name('show');
        Route::patch('/{mandate}', [MandateController::class, 'update'])->name('update');
        Route::post('/{mandate}/signatories', [MandateController::class, 'storeSig'])->name('signatories.store');
        Route::delete('/signatories/{signatory}', [MandateController::class, 'destroySig'])->name('signatories.destroy');
        Route::post('/approvals/{approval}/approve', [MandateController::class, 'approve'])->name('approve');
        Route::post('/approvals/{approval}/reject', [MandateController::class, 'reject'])->name('reject');
    });

    // ── CHAT MODULE ───────────────────────────────────────────────────────────
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('index');
        Route::get('/conversations', [ChatController::class, 'conversations'])->name('conversations'); // JSON
        Route::post('/conversations', [ChatController::class, 'storeConversation'])->name('conversations.store');
        Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages'])->name('messages');
        Route::post('/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('send');
        Route::patch('/messages/{message}', [ChatController::class, 'editMessage'])->name('messages.edit');
        Route::delete('/messages/{message}', [ChatController::class, 'deleteMessage'])->name('messages.delete');
        Route::post('/conversations/{conversation}/participants', [ChatController::class, 'addParticipants'])->name('participants.add');
        Route::delete('/conversations/{conversation}/participants/{user}', [ChatController::class, 'removeParticipant'])->name('participants.remove');
        Route::get('/attachments/{attachment}/download', [ChatController::class, 'downloadAttachment'])->name('attachment.download');
        Route::get('/unread-count', [ChatController::class, 'unreadCount'])->name('unread-count'); // JSON
    });

    // ── PORTAL LOAN APPLICATIONS REVIEW ───────────────────────────────────────
    Route::prefix('loan-applications')->name('loan-applications.')->group(function () {
        Route::get('/', [LoanApplicationReviewController::class, 'index'])->name('index');
        Route::get('/{id}', [LoanApplicationReviewController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [LoanApplicationReviewController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [LoanApplicationReviewController::class, 'reject'])->name('reject');
        Route::post('/{id}/convert', [LoanApplicationReviewController::class, 'convert'])->name('convert');
    });

    // ── PORTAL KYC UPGRADE REVIEW ─────────────────────────────────────────────
    Route::prefix('kyc-review')->name('kyc-review.')->group(function () {
        Route::get('/', [KycUpgradeReviewController::class, 'index'])->name('index');
        Route::post('/manual-adjust', [KycUpgradeReviewController::class, 'manualAdjust'])->name('manual-adjust');
        Route::get('/{id}', [KycUpgradeReviewController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [KycUpgradeReviewController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [KycUpgradeReviewController::class, 'reject'])->name('reject');
    });

    // ── PAR DASHBOARD ─────────────────────────────────────────────────────────
    Route::get('par-dashboard', [ParDashboardController::class, 'index'])->name('par-dashboard.index');

    // ── FEATURE FLAGS ─────────────────────────────────────────────────────────
    Route::get('feature-flags', [FeatureFlagController::class, 'index'])->name('feature-flags.index');
    Route::post('feature-flags', [FeatureFlagController::class, 'update'])->name('feature-flags.update');
    Route::patch('feature-flags/{key}', [FeatureFlagController::class, 'toggle'])->name('feature-flags.toggle');

    // Per-customer feature flag overrides
    Route::get('/customers/{customer}/feature-flags', [FeatureFlagController::class, 'customerFlags'])->name('customers.feature-flags');
    Route::post('/customers/{customer}/feature-flags', [FeatureFlagController::class, 'customerFlagsUpdate'])->name('customers.feature-flags.update');

    // ── TRANSACTION MONITOR ───────────────────────────────────────────────────
    Route::get('transaction-monitor', [TransactionMonitorController::class, 'index'])->name('transaction-monitor.index');
    Route::post('transaction-monitor/{transaction}/reverse', [TransactionMonitorController::class, 'reverse'])->name('transaction-monitor.reverse');

    // ── PORTAL DISPUTES ───────────────────────────────────────────────────────
    Route::prefix('portal-disputes')->name('portal-disputes.')->group(function () {
        Route::get('/', [PortalDisputeAdminController::class, 'index'])->name('index');
        Route::get('/{id}', [PortalDisputeAdminController::class, 'show'])->name('show');
        Route::post('/{id}/respond', [PortalDisputeAdminController::class, 'respond'])->name('respond');
    });

    // ── REFERRAL REWARDS ─────────────────────────────────────────────────────
    Route::prefix('referral-rewards')->name('referral-rewards.')->group(function () {
        Route::get('/', [ReferralRewardController::class, 'index'])->name('index');
        Route::post('/{id}/approve', [ReferralRewardController::class, 'approve'])->name('approve');
        Route::post('/{id}/pay', [ReferralRewardController::class, 'pay'])->name('pay');
        Route::post('/{id}/reject', [ReferralRewardController::class, 'reject'])->name('reject');
    });

    // ── PORTAL INVESTMENT PRODUCTS ────────────────────────────────────────────
    Route::prefix('investment-products')->name('investment-products.')->group(function () {
        Route::get('/', [InvestmentProductController::class, 'index'])->name('index');
        Route::post('/', [InvestmentProductController::class, 'store'])->name('store');
        Route::put('/{id}', [InvestmentProductController::class, 'update'])->name('update');
        Route::patch('/{id}/toggle', [InvestmentProductController::class, 'toggleActive'])->name('toggle');
        Route::delete('/{id}', [InvestmentProductController::class, 'destroy'])->name('destroy');
    });

    // ── PORTAL ANALYTICS DASHBOARD ───────────────────────────────────────────
    Route::get('portal-analytics', [PortalAnalyticsController::class, 'index'])->name('portal-analytics.index');
    Route::get('portal-analytics/data', [PortalAnalyticsController::class, 'data'])->name('portal-analytics.data');

    // ── FEE ENGINE ────────────────────────────────────────────────────────────
    Route::prefix('fee-rules')->name('fee-rules.')->group(function () {
        Route::get('/', [FeeRuleController::class, 'index'])->name('index');
        Route::post('/', [FeeRuleController::class, 'store'])->name('store');
        Route::put('/{id}', [FeeRuleController::class, 'update'])->name('update');
        Route::delete('/{id}', [FeeRuleController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [FeeRuleController::class, 'toggle'])->name('toggle');
    });

    // ── AML / COMPLIANCE ENGINE ───────────────────────────────────────────────
    Route::prefix('aml')->name('aml.')->group(function () {
        Route::get('/', [AmlController::class, 'index'])->name('index');
        Route::get('/alerts/{id}', [AmlController::class, 'show'])->name('show');
        Route::post('/alerts/{id}/review', [AmlController::class, 'review'])->name('review');
        Route::post('/screen', [AmlController::class, 'screenName'])->name('screen');
        Route::get('/limits', [AmlController::class, 'limits'])->name('limits');
        Route::post('/limits', [AmlController::class, 'updateLimit'])->name('limits.update');
        Route::get('/rules', [AmlController::class, 'rules'])->name('rules');
        Route::post('/rules', [AmlController::class, 'storeRule'])->name('rules.store');
        Route::post('/rules/{id}', [AmlController::class, 'updateRule'])->name('rules.update');
        Route::post('/rules/{id}/toggle', [AmlController::class, 'toggleRule'])->name('rules.toggle');
        Route::delete('/rules/{id}', [AmlController::class, 'destroyRule'])->name('rules.destroy');
        Route::get('/sanctions', [AmlController::class, 'sanctionsScreen'])->name('sanctions');
        Route::get('/str', [AmlController::class, 'strIndex'])->name('str.index');
        Route::post('/str', [AmlController::class, 'strCreate'])->name('str.create');
        Route::post('/str/{id}/submit', [AmlController::class, 'strSubmit'])->name('str.submit');
    });

    // ── WEBHOOKS ──────────────────────────────────────────────────────────────
    Route::prefix('webhooks')->name('webhooks.')->group(function () {
        Route::get('/', [WebhookEndpointController::class, 'index'])->name('index');
        Route::post('/', [WebhookEndpointController::class, 'store'])->name('store');
        Route::delete('/{id}', [WebhookEndpointController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle', [WebhookEndpointController::class, 'toggle'])->name('toggle');
        Route::get('/{id}/logs', [WebhookEndpointController::class, 'logs'])->name('logs');
    });

    // ── FINANCIAL AUDIT LOG ───────────────────────────────────────────────────
    Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
    Route::get('audit-log/export', [AuditLogController::class, 'export'])->name('audit-log.export');

    // ── IP WHITELIST ──────────────────────────────────────────────────────────
    Route::resource('ip-whitelist', IpWhitelistController::class)->only(['index', 'store', 'destroy']);
    Route::patch('ip-whitelist/{id}/toggle', [IpWhitelistController::class, 'toggle'])->name('ip-whitelist.toggle');

    // ── COOPERATIVE SHARES ────────────────────────────────────────────────────
    Route::prefix('cooperative/shares')->name('cooperative.shares.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Cooperative\ShareController::class, 'index'])->name('index');
        Route::get('/products/create', [\App\Http\Controllers\Cooperative\ShareController::class, 'createProduct'])->name('products.create');
        Route::post('/products', [\App\Http\Controllers\Cooperative\ShareController::class, 'storeProduct'])->name('products.store');
        Route::get('/products/{id}', [\App\Http\Controllers\Cooperative\ShareController::class, 'showProduct'])->name('products.show');
        Route::get('/members', [\App\Http\Controllers\Cooperative\ShareController::class, 'members'])->name('members');
        Route::get('/members/{customerId}', [\App\Http\Controllers\Cooperative\ShareController::class, 'showMember'])->name('members.show');
        Route::get('/purchase', [\App\Http\Controllers\Cooperative\ShareController::class, 'purchaseForm'])->name('purchase');
        Route::post('/purchase', [\App\Http\Controllers\Cooperative\ShareController::class, 'purchase'])->name('purchase.store');
        Route::get('/redeem', [\App\Http\Controllers\Cooperative\ShareController::class, 'redeemForm'])->name('redeem');
        Route::post('/redeem', [\App\Http\Controllers\Cooperative\ShareController::class, 'redeem'])->name('redeem.store');
        Route::get('/certificate/{memberShareId}', [\App\Http\Controllers\Cooperative\ShareController::class, 'certificate'])->name('certificate');
    });

    // ── COOPERATIVE DIVIDENDS ──────────────────────────────────────────────────
    Route::prefix('cooperative/dividends')->name('cooperative.dividends.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Cooperative\DividendController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Cooperative\DividendController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Cooperative\DividendController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Cooperative\DividendController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\Cooperative\DividendController::class, 'approve'])->name('approve');
        Route::post('/{id}/process', [\App\Http\Controllers\Cooperative\DividendController::class, 'process'])->name('process');
        Route::post('/{id}/cancel', [\App\Http\Controllers\Cooperative\DividendController::class, 'cancel'])->name('cancel');
    });

    // ── COOPERATIVE CONTRIBUTIONS ─────────────────────────────────────────────
    Route::prefix('cooperative/contributions')->name('cooperative.contributions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Cooperative\ContributionController::class, 'index'])->name('index');
        Route::get('/schedules/create', [\App\Http\Controllers\Cooperative\ContributionController::class, 'createSchedule'])->name('schedules.create');
        Route::post('/schedules', [\App\Http\Controllers\Cooperative\ContributionController::class, 'storeSchedule'])->name('schedules.store');
        Route::get('/schedules/{id}', [\App\Http\Controllers\Cooperative\ContributionController::class, 'showSchedule'])->name('schedules.show');
        Route::get('/collect', [\App\Http\Controllers\Cooperative\ContributionController::class, 'collect'])->name('collect');
        Route::post('/collect', [\App\Http\Controllers\Cooperative\ContributionController::class, 'storeCollect'])->name('collect.store');
        Route::get('/bulk-collect', [\App\Http\Controllers\Cooperative\ContributionController::class, 'bulkCollect'])->name('bulk-collect');
        Route::post('/bulk-collect', [\App\Http\Controllers\Cooperative\ContributionController::class, 'storeBulkCollect'])->name('bulk-collect.store');
        Route::get('/members/{customerId}', [\App\Http\Controllers\Cooperative\ContributionController::class, 'memberHistory'])->name('member-history');
        Route::get('/report', [\App\Http\Controllers\Cooperative\ContributionController::class, 'report'])->name('report');
    });

    // ── COOPERATIVE MEMBER EXITS ──────────────────────────────────────────────
    Route::prefix('cooperative/exits')->name('cooperative.exits.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Cooperative\MemberExitController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Cooperative\MemberExitController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Cooperative\MemberExitController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Cooperative\MemberExitController::class, 'show'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\Cooperative\MemberExitController::class, 'approve'])->name('approve');
        Route::post('/{id}/settle', [\App\Http\Controllers\Cooperative\MemberExitController::class, 'settle'])->name('settle');
    });
});

// ── SUPER ADMIN — Platform Control Tower ─────────────────────────────────────
Route::middleware(['auth', 'super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [SuperAdminController::class, 'index'])->name('index');
    Route::get('/tenant/{tenantId}', [SuperAdminController::class, 'tenantDrill'])->name('tenant-drill');
    Route::get('/export', [SuperAdminController::class, 'exportAll'])->name('export');
});

// ── SUPER ADMIN — Subscription Management ────────────────────────────────────
Route::middleware(['auth', 'super_admin'])->group(function () {
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SubscriptionController::class, 'index'])->name('index');
        Route::get('/plans', [\App\Http\Controllers\SubscriptionController::class, 'plans'])->name('plans');
        Route::get('/{tenantId}', [\App\Http\Controllers\SubscriptionController::class, 'show'])->name('show');
        Route::post('/{tenantId}/plan', [\App\Http\Controllers\SubscriptionController::class, 'changePlan'])->name('change-plan');
        Route::post('/{tenantId}/suspend', [\App\Http\Controllers\SubscriptionController::class, 'suspend'])->name('suspend');
        Route::post('/{tenantId}/unsuspend', [\App\Http\Controllers\SubscriptionController::class, 'unsuspend'])->name('unsuspend');
        Route::post('/{tenantId}/cancel', [\App\Http\Controllers\SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/{tenantId}/payment', [\App\Http\Controllers\SubscriptionController::class, 'recordPayment'])->name('record-payment');
    });
});

// ── TENANT ONBOARDING / SELF-SERVICE SETUP ───────────────────────────────────
Route::prefix('setup')->name('setup.')->group(function () {
    Route::get('/', [\App\Http\Controllers\TenantOnboardingController::class, 'start'])->name('start');
    Route::get('/step1', [\App\Http\Controllers\TenantOnboardingController::class, 'institutionDetails'])->name('step1');
    Route::post('/step1', [\App\Http\Controllers\TenantOnboardingController::class, 'storeStep1'])->name('step1.store');
    Route::get('/step2', [\App\Http\Controllers\TenantOnboardingController::class, 'branding'])->name('step2');
    Route::post('/step2', [\App\Http\Controllers\TenantOnboardingController::class, 'storeBranding'])->name('step2.store');
    Route::get('/step3', [\App\Http\Controllers\TenantOnboardingController::class, 'adminUser'])->name('step3');
    Route::post('/step3', [\App\Http\Controllers\TenantOnboardingController::class, 'storeAdminUser'])->name('step3.store');
    Route::get('/step4', [\App\Http\Controllers\TenantOnboardingController::class, 'subscription'])->name('step4');
    Route::post('/step4', [\App\Http\Controllers\TenantOnboardingController::class, 'storeSubscription'])->name('step4.store');
    Route::get('/review', [\App\Http\Controllers\TenantOnboardingController::class, 'review'])->name('review');
    Route::post('/complete', [\App\Http\Controllers\TenantOnboardingController::class, 'complete'])->name('complete');
});

// ── TENANT SUSPENDED PAGE ─────────────────────────────────────────────────────
Route::middleware(['auth', 'tenant'])->get('/suspended', function () {
    return view('tenant.suspended');
})->name('tenant.suspended');

// Public webhook endpoint (no auth — secured by signature in production)
Route::post('api/webhook/{tenantSlug}/inbound', [InboundTransferController::class, 'webhook'])->name('webhook.inbound');

// NIBSS NIP callback (no auth — secured by IP whitelist in production)
Route::post('api/nip/callback', [NipController::class, 'callback'])->name('nip.callback');

// USSD endpoint (public — Africa's Talking POST callback)
Route::post('ussd', [UssdController::class, 'handle'])->name('ussd.handle');

// 2FA challenge routes are in routes/auth.php

// 2FA profile management (authenticated)
Route::middleware(['auth'])->group(function () {
    Route::get('profile/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
});

require __DIR__.'/auth.php';
