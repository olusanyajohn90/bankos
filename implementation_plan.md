# BankOS — Phase 3: Operations & Administration

This plan covers the implementation of the Phase 3 modules: **Workflows** and **Reports**.

## Goal Description
Implement an internal Operations & Administration hub. This includes a Workflow Inbox where bank officers and admins can view and claim pending approval tasks, and a Reports Hub containing the 6 primary banking reports outlined in the FRD.

## Proposed Changes

### 1. Workflows Module

Instead of integrating a heavy external BPM engine (Camunda) for the MVP, we will implement a lightweight, polymorphic `workflow_instances` table. This acts as a unified "Inbox" bridging all approvals across the system.

#### Database Migrations
#### [NEW] `2026_03_10_create_workflow_instances_table.php`
- `id` (uuid)
- `tenant_id` (uuid)
- `process_name` (string) - e.g., 'Loan Approval', 'KYC Review', 'Restructure Approval'
- `subject_type`, `subject_id` - Polymorphic relation to the underlying model (Loan, KycDocument, etc.)
- `status` (enum: pending, approved, rejected, cancelled)
- `assigned_role` (string) - The Spatie role name required to action this task (e.g., 'tenant_admin', 'compliance_officer')
- `started_at`, `ended_at` (timestamps)

#### Models
#### [NEW] `app/Models/WorkflowInstance.php`

#### Controllers
#### [NEW] `app/Http/Controllers/WorkflowController.php`
- `index()` - Returns the Inbox view with tabs for "My Tasks" (tasks matching the user's roles), "Available Tasks", and "All Instances".

#### Views
#### [NEW] `resources/views/workflows/index.blade.php`
- Renders the Workflow Inbox grid with tabs. Action buttons will dynamically link to the existing respective review pages (e.g., `/loans/{id}` or `/customers/kyc`).

*Implementation Note: We will also need to fire eloquent events or insert hooks in our existing controllers (LoanController, KycDocumentController, LoanRestructureController, etc.) to create a `WorkflowInstance` when a request is made, and mark it as `ended` when it is approved/rejected.*

---

### 2. Reports Module

We will build a central Reports Hub and dedicated views/queries for the 6 core banking reports.

#### Controllers
#### [NEW] `app/Http/Controllers/ReportController.php`
- `index()` - The Reports Hub grid with icons and descriptions.
- `accountStatement(Request $request)` - Filters: Account Number, Start Date, End Date.
- `trialBalance()` - Joins GL accounts with sums of debits/credits.
- `loanPortfolio()` - Aggregates loans by status.
- `interestAccrual()` - Aggregates expected vs posted interest.
- `parAging()` - Portfolio At Risk: groups overdue loans into 1-30, 31-60, 61-90, 90+ days.
- `ifrs9()` - Basic ECL (Expected Credit Loss) staging report based on the PAR aging buckets.

#### Views
#### [NEW] `resources/views/reports/index.blade.php` (Hub)
#### [NEW] `resources/views/reports/account_statement.blade.php`
#### [NEW] `resources/views/reports/trial_balance.blade.php`
#### [NEW] `resources/views/reports/loan_portfolio.blade.php`
#### [NEW] `resources/views/reports/par_aging.blade.php`
#### [NEW] `resources/views/reports/ifrs9.blade.php`

---

### 3. Automated End of Day (EOD) Processing & Savings Interest

To support automated interest calculations and reporting on savings accounts, we will implement an EOD processing command and a new specialized report.

#### Commands & Services
#### [NEW] `app/Console/Commands/RunEndOfDay.php`
- Artisan command `bankos:eod` to trigger daily processing.
- Will be scheduled in Laravel's Task Scheduler to run daily.
#### [NEW] `app/Services/EndOfDayService.php`
- Contains core logic to:
  1. Iterate over active `Account`s linked to a `SavingsProduct`.
  2. Calculate daily interest accrued (`interest_rate / 365 * current_balance`).
  3. Post interest transactions to the accounts based on product settings.

#### Controllers & Views
#### [MODIFY] `app/Http/Controllers/ReportController.php`
- `savingsInterest()` - Aggregate report showing interest paid and added to accounts by EOD.
#### [NEW] `resources/views/reports/savings_interest.blade.php`
- Dedicated view for the Savings Interest report.

---

### 4. Administration Management Modules

We need to build out the standard CRUD interfaces for the system's core entities.

#### Controllers
#### [NEW] `app/Http/Controllers/BranchController.php`
- Manage bank branches.
#### [NEW] `app/Http/Controllers/GlAccountController.php`
- Manage the chart of accounts for the institution.
#### [NEW] `app/Http/Controllers/UserController.php`
- Manage administrative users (staff) and assign Spatie roles.
#### [NEW] `app/Http/Controllers/TenantController.php`
- For Super Admins: Manage multi-tenant institutions.

#### Views
#### [NEW] `resources/views/branches/index.blade.php`, `create/edit`
#### [NEW] `resources/views/gl_accounts/index.blade.php`, `create/edit`
#### [NEW] `resources/views/users/index.blade.php`, `create/edit`
#### [NEW] `resources/views/tenants/index.blade.php`, `create/edit`

#### Navigation & Routing
#### [MODIFY] `routes/web.php`
- Add resources for `branches`, `gl-accounts`, `users`, and `tenants`.
#### [MODIFY] `resources/views/components/sidebar.blade.php`
- Update the `href="#"` links to point to the new resource index routes.

---

## Verification Plan

### Automated Tests
- N/A for UI reports, but we will test the queries manually.

### Manual Verification
1. **Workflows**: Generate a new KYC request and a new Loan application. Go to the Workflows inbox. Verify that the matching tasks appear under "My Tasks" if logged in as an Admin/Compliance officer.
2. **Actioning Tasks**: Click "Review" on a task, complete the approval on the target page, and verify the task disappears from the pending inbox.
3. **Reports**: Open each of the 6 reports and verify the data matches the underlying database state (e.g., Trial Balance debits equal credits).
