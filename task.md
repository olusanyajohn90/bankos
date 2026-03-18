# BankOS — Multi-Tenant Core Banking Application Build

## Phase 1: Foundation & Infrastructure
- [x] Create implementation plan and get user approval
- [x] Scaffold Laravel project with required packages
- [x] Configure multi-tenancy (`tenant_id` scoping via middleware/traits)
- [x] Set up authentication (Breeze + Sanctum + Institution Code login)
- [x] Set up Spatie Permission (RBAC) with all 7 roles
- [x] Create database migrations for core tables (tenants, customers, accounts, loans, transactions, users, branches, gl_accounts)
- [x] Implement base layout (Blade + Tailwind + sidebar + header + theme toggle)
- [x] Seed initial data (Super Admin, default tenant, GL Chart of Accounts)

## Phase 2: Core Modules
- [x] Dashboard module (KPI cards + 4 charts)
- [x] Customers module (list + 3-step wizard + KYC queue)
- [x] Accounts & Savings module (list + open account + savings products)
- [x] Transactions module (list + new transaction with tabs)
- [x] Loans module (list + application + products + pre-qualification)

## Phase 3: Operations & Administration
- [x] Workflows module (task inbox + instances)
  - [x] Create `workflow_instances` table and `WorkflowInstance` model
  - [x] Build `WorkflowController` to handle the inbox views
  - [x] Implement UI for 'My Tasks' and 'Available Tasks'
  - [x] Hook existing request flows (Loan, KYC, Restructure) to create WorkflowInstances
- [x] Reports module (6 required Core Reports)
- [x] End of Day (EOD) Processing and Savings Interest Report
- [x] Implement Additional Core Reports (7 reports)
  - [x] Daily Transaction Journal
  - [x] General Ledger (GL) Movements
  - [x] Overdrawn Accounts Report
  - [x] Dormant Accounts
  - [x] Suspicious Activity (AML) Report
  - [x] Loan Disbursement & Repayment Log
  - [x] Branch Performance Report
- [x] Branches + GL Accounts + Users + Tenants management
- [x] Custom Roles & Permissions Management (Tenant Scope)
- [x] Exchange Rates + Audit Logs + Scheduled Jobs + Settings

## Phase 4: Advanced Features
- [x] AI Profile Review (Cortex™ Engine)
- [x] Loan restructuring & moratorium
- [x] Loan top-ups (request, approve, DB tracking, history viewing)
- [x] Group/Centre/Meeting management
- [x] Notifications engine (SMS/WhatsApp/Email/Push)
- [x] Posting files (bulk upload + validate + post)
- [x] Inbound transfers

## Phase 5: Channels & Compliance
- [x] Agent Banking module (agents, float management, visit logs, CRUD views)
- [x] IFRS 9 ECL module (staging logic, PD×LGD×EAD, run + view provisions)
- [x] Credit Bureau integration (CRC/XDS/FirstCentral stubs, query + show)
- [x] Overdue scoring & smart collection (composite score, collection log)
- [x] Embedded insurance (credit_life/health/asset policies, status management)
- [x] Internal wallet (wallet accounts via existing Account model type='wallet')
- [x] USSD channel (Africa's Talking menu scaffold — balance, statement, loan)
- [x] Mobile app API endpoints (Sanctum: login, balance, statement, loans, repay)
