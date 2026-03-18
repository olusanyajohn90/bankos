# bankOS — Functional Requirements Document (FRD)

**Version:** 1.0  
**Date:** March 9, 2026  
**Product:** bankOS — Multi-Tenant Core Banking & Lending SaaS  
**Platform:** Laravel (PHP 8.3+) / MySQL / Blade + Tailwind CSS  
**Derived From:** bankOS Master Product Specification v2.0  

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [System Architecture & Technology Stack](#2-system-architecture--technology-stack)
3. [Multi-Tenancy Requirements](#3-multi-tenancy-requirements)
4. [User Roles & Access Control](#4-user-roles--access-control)
5. [Authentication & Onboarding](#5-authentication--onboarding)
6. [Dashboard Module](#6-dashboard-module)
7. [Customers Module](#7-customers-module)
8. [Accounts & Savings Module](#8-accounts--savings-module)
9. [Transactions & Collections Module](#9-transactions--collections-module)
10. [Loans Module](#10-loans-module)
11. [Operations Module](#11-operations-module)
12. [Administration Module](#12-administration-module)
13. [Borrower Channels](#13-borrower-channels)
14. [Agent Banking Module](#14-agent-banking-module)
15. [Risk & Compliance Module](#15-risk--compliance-module)
16. [Additional Products](#16-additional-products)
17. [Security Requirements](#17-security-requirements)
18. [Data Models](#18-data-models)
19. [UI Design System](#19-ui-design-system)
20. [Regulatory Compliance](#20-regulatory-compliance)
21. [Scheduled Jobs](#21-scheduled-jobs)
22. [Non-Functional Requirements](#22-non-functional-requirements)

---

## 1. Introduction

### 1.1 Purpose
This Functional Requirements Document (FRD) defines the complete functional and technical requirements for **bankOS**, a multi-tenant core banking and lending SaaS platform designed for the Nigerian and broader African financial services market.

### 1.2 Product Identity

| Attribute | Value |
|---|---|
| **Name** | bankOS (formerly LendCore) |
| **Tagline** | Modern banking & lending infrastructure for Africa |
| **Sub-heading** | The operating system for microfinance banks, digital lenders, and co-operative societies |
| **Value Proposition** | Originate, assess, disburse, and collect — all from one platform |
| **Primary Market** | Nigeria; extensible to wider Africa |
| **Primary Currency** | NGN (also USD, GBP, EUR, CAD, CNY) |

### 1.3 Target Segments

| # | Segment | Description |
|---|---|---|
| 1 | Small Cooperatives | Low-cost group lending |
| 2 | Licensed MFBs | Full CBN compliance |
| 3 | Digital Lenders | High-velocity micro-lending |

### 1.4 Regulatory Framework
- CBN MFB Guidelines
- FCCPC Digital Lending Regulations 2025
- Nigeria Data Protection Regulation (NDPR)
- IFRS 9 Expected Credit Loss (ECL)

---

## 2. System Architecture & Technology Stack

### 2.1 Backend

| Component | Technology |
|---|---|
| **Framework** | Laravel (latest stable) |
| **Language** | PHP 8.3+ |
| **Database** | MySQL — relational tables, UUIDs, all queries tenant-scoped |
| **Multi-Tenancy** | `tenant_id` scoping (or `hyn/multi-tenant` package) |
| **Queue** | Laravel Queue workers (Redis or database driver) |
| **Scheduler** | Laravel Task Scheduler (`cron`) for 9 background jobs |
| **API Auth** | Laravel Sanctum (token-based REST JSON API) |

### 2.2 Frontend

| Component | Technology |
|---|---|
| **Templates** | Laravel Blade + Tailwind CSS v3 + Alpine.js (or Vue.js for complex components) |
| **Icons** | Lucide (SVG icon set) |
| **Charts** | Chart.js or Recharts (via CDN in Blade) |
| **Theme** | Light / Dark / System toggle. Blue base with Crimson, Indigo, Purple variants |

### 2.3 Mobile Application

| Component | Technology |
|---|---|
| **Framework** | React Native (iOS + Android) |
| **API** | Consumes Laravel Sanctum API |

### 2.4 Auth & Security Stack

| Component | Technology |
|---|---|
| **Authentication** | Laravel Breeze or Fortify |
| **Session/API** | Laravel Sanctum (web session + API tokens) |
| **Authorization** | Spatie Laravel Permission (RBAC per tenant) |
| **2FA** | SMS OTP or TOTP Authenticator |

### 2.5 Infrastructure

| Component | Technology |
|---|---|
| **CDN/Security** | Cloudflare (WAF + proxy + Web Analytics) |
| **Workflow Engine** | Camunda BPM (loan approval, KYC, user approval) |
| **SMS** | Termii / Africa's Talking |
| **WhatsApp** | Twilio / Meta API |
| **Email** | SendGrid / Mailgun |
| **Push** | Firebase Cloud Messaging (FCM) |
| **FX Rates** | External FX API (scheduled daily at 06:00) |
| **Credit Bureau** | CRC / XDS / FirstCentral API |
| **Identity** | NIBSS API (BVN/NIN verification) |

---

## 3. Multi-Tenancy Requirements

### FR-MT-001: Tenant Data Isolation
- Every database table SHALL include a `tenant_id` foreign key column
- All database queries SHALL be automatically scoped by `tenant_id`
- No tenant SHALL be able to access another tenant's data

### FR-MT-002: Tenant Types
- The system SHALL support three tenant types: `bank`, `lender`, `cooperative`
- Each type MAY have different feature sets and compliance requirements

### FR-MT-003: Account Number Prefixing
- Each tenant SHALL have a unique 3-digit `account_prefix`
- All account numbers SHALL be prefixed with the tenant's prefix

### FR-MT-004: Tenant Login Isolation
- Login SHALL require an Institution Code (`short_name`) in addition to Email and Password
- The Institution Code SHALL route the user to the correct tenant context

### FR-MT-005: GL Auto-Provisioning
- On tenant creation, the system SHALL auto-provision GL accounts from the standard Nigerian Chart of Accounts

---

## 4. User Roles & Access Control

All roles are scoped per tenant via Spatie Permission. Super Admin operates across all tenants.

### FR-ROLE-001: Super Admin
- Manage tenants, global settings, audit logs, scheduled jobs, exchange rates, system-wide reports
- **Priority:** High | **Segment:** All

### FR-ROLE-002: Compliance Officer
- **MUST exist before any other role is created per tenant**
- KYC review, workflow approvals, regulatory reports
- **Priority:** High | **Segment:** MFBs + Cooperatives

### FR-ROLE-003: Tenant Admin (Bank Admin)
- Manage institution users, branches, products, reports, GL
- Created automatically on tenant setup
- **Priority:** High | **Segment:** All

### FR-ROLE-004: Loan Officer
- Loan origination, pre-qualification, disbursement, customer management
- **Priority:** High | **Segment:** All

### FR-ROLE-005: Teller
- Cash deposits, withdrawals, inbound transfer posting, daily balancing
- **Priority:** Medium | **Segment:** MFBs + Cooperatives

### FR-ROLE-006: Agent
- Field collections, account opening, loan repayments via POS
- Has float wallet and GPS check-in
- **Priority:** High | **Segment:** Cooperatives + MFBs

### FR-ROLE-007: Auditor
- Read-only access to audit logs, reports, GL
- Cannot create or modify any record
- **Priority:** Medium | **Segment:** All

---

## 5. Authentication & Onboarding

### 5.1 Login Page (`/login`)

**FR-AUTH-001: Login UI**
- Split-screen layout: left marketing panel, right login card
- Background: light grey with floating blue/₦ decorative elements

**FR-AUTH-002: Left Panel Content**
- Large heading: "Modern banking & lending infrastructure for Africa"
- Sub-paragraph with 3 feature bullets: CBN/NIBSS compliance, AI credit assessment, real-time dashboards
- Bottom tag strip: Compliance | AI Credit | Dashboards

**FR-AUTH-003: Login Form Fields**
- Institution Code (placeholder: "e.g. firstbank")
- Email address
- Password (show/hide toggle)
- Remember me checkbox
- Forgot password? link
- CTA: "Sign in" (full-width blue button)
- Footer: "Don't have an account? Contact your admin"

**FR-AUTH-004: Two-Factor Authentication**
- After primary auth, prompt for OTP (SMS or TOTP) if enabled on the account

### 5.2 New Tenant Wizard (`/tenants/new`)

**FR-AUTH-005: Tenant Creation Form**
Three-section scrollable form:

**Section 1 — Basic Information:**
- Institution Name* | Tenant Type* (Bank/Lender/Cooperative) | Short Name* (unique slug)
- Domain (bank.example.com) | Account Prefix* (3 digits) | Primary Currency (NGN)
- Supported Currencies (default: NGN, USD, GBP) | Contact Email | Contact Phone | Address | Logo URL

**Section 2 — Default Administrator:**
- Admin Email* | First Name* | Last Name*
- Helper text: "A BANK_ADMIN user will be created with a temporary password. They must change it on first login."

**Section 3 — Regulatory & Compliance:**
- CBN License Number | NIBSS Institution Code | Routing Number

**FR-AUTH-006: Tenant Creation Side Effects**
- GL accounts auto-provisioned from standard Nigerian Chart of Accounts

### 5.3 New User Form (`/users/new`)

**FR-AUTH-007: User Creation**
- Warning banner (amber): "Compliance Officer must be created before any other role can be assigned"
- Fields: First Name*, Last Name*, Email*, Phone, Role* (only Compliance Officer available until CO exists), Branch ID (optional UUID), Password (auto-generated if empty)
- CTA: "Submit for Approval" → triggers maker-checker workflow
- On approval: user receives welcome email with login credentials

---

## 6. Dashboard Module (`/dashboard`)

### 6.1 KPI Cards

**FR-DASH-001: KPI Card Layout**
- Two rows of 5 cards each (10 total)
- Each card: label, large bold value, coloured Lucide icon top-right
- White surface, rounded corners, subtle border

**FR-DASH-002: KPI Card Definitions**

| # | KPI | Format | Icon | Colour |
|---|---|---|---|---|
| 1 | Total Customers | Integer count | Users | Blue |
| 2 | Total Accounts | Integer count | CreditCard | Blue |
| 3 | Total Deposits | ₦ sum | ArrowDownCircle | Blue |
| 4 | Total Withdrawals | ₦ sum | ArrowUpCircle | Red/Orange |
| 5 | Active Loans | Integer count | Building2 | Blue |
| 6 | Total Savings | ₦ sum | PiggyBank | Blue |
| 7 | Accrued Interest | ₦ sum | Percent | Blue |
| 8 | Interest Posted | ₦ sum | TrendingUp | Blue |
| 9 | Pending Transactions | Integer count | Clock | Amber |
| 10 | Pending KYC | Integer count | FileText | Blue |

### 6.2 Charts

**FR-DASH-003: Transaction Volume Chart**
- 30-day line/area chart
- Series: Deposits (blue), Withdrawals (red), Transfers (blue)
- Legend below, width ~60% content area

**FR-DASH-004: Loan Portfolio by Status**
- Donut chart
- Segments: PENDING / ACTIVE / OVERDUE / CLOSED / WRITTEN_OFF
- Legend right, width ~40%

**FR-DASH-005: Account Distribution**
- Full-width bar chart
- Two series per account type: Count (blue) + Total Balance (blue)
- X-axis: account types

**FR-DASH-006: PAR Aging Widget**
- Stacked bar or table showing Portfolio at Risk by bucket
- Buckets: 1–30 days / 31–60 / 61–90 / 90+ days
- Shows count and ₦ value per bucket

---

## 7. Customers Module

### 7.1 Customer List (`/customers`)

**FR-CUST-001: Customer List Page**
- Title: "Customers", subtitle: "Manage customer records"
- Top right: "+ New Customer" button (blue)
- Controls: Search (by name/email/number), All Types dropdown, Export button

**FR-CUST-002: Customer Table Columns**

| Column | Description |
|---|---|
| Customer # | Auto-generated reference |
| Name | Full name |
| Type | Individual / Corporate |
| Phone | Phone number |
| Status | Badge: ACTIVE / INACTIVE / PENDING / SUSPENDED |
| Created | Date created |
| Action | View icon → customer detail page |

### 7.2 New Customer Wizard (3 Steps)

**FR-CUST-003: Step 1 — Identity Verification**
- Customer Type dropdown: Individual / Corporate
- BVN field (11-digit) + Validate button → returns: photo, full name, DOB, gender, phone (blue verified card)
- NIN field (11-digit) + Validate button → similar return
- Validation: at least one of BVN or NIN required

**FR-CUST-004: Step 2 — Personal Details & Documents**
- Personal Details (pre-filled from BVN): First Name*, Middle Name, Last Name*, DOB*, Gender*, Email*, Phone*, Occupation, Marital Status, Street Address, Nationality, State, City/LGA, Postal Code
- Photo ID Document: ID Type*, ID Number*, file upload (max 5 MB)
- Address Verification: file upload (max 5 MB, Utility Bill or Bank Statement)

**FR-CUST-005: Step 3 — Review & Submit**
- Customer Information summary
- Identity Verification status: BVN/NIN Verified badges
- Documents status
- KYC Assessment: KYC Tier badge (LEVEL 1/2/3), Workflow badge (Auto-approved / Manual review)
- Submit triggers auto-approval or KYC review queue

### 7.3 KYC Review Queue (`/kyc`)

**FR-CUST-006: KYC Review Table**

| Column | Description |
|---|---|
| Customer | Name + link |
| Document Type | NIN / Passport / Driver's License / Utility Bill |
| Document # | ID number |
| File | Thumbnail / download link |
| Status | PENDING / APPROVED / REJECTED / EXPIRED |
| Expiry | Document expiry date |
| Uploaded | Upload datetime |
| Actions | Approve / Reject / Request More Info |

---

## 8. Accounts & Savings Module

### 8.1 Accounts List (`/accounts`)

**FR-ACCT-001: Accounts List Page**
- Title: "Accounts". Top right: "+ New Account"
- Filters: All Types, All Statuses. Export.

**FR-ACCT-002: Accounts Table Columns**

| Column | Description |
|---|---|
| Account # | Account number |
| Account Name | Display name |
| Type | Savings / Current / Loan |
| Currency | NGN / USD / GBP / EUR |
| Available Balance | ₦ formatted |
| Ledger Balance | ₦ formatted |
| Status | ACTIVE / DORMANT / CLOSED |

### 8.2 Open Account Form (`/accounts/new`)

**FR-ACCT-003: Account Creation**
- Customer: searchable combobox
- Account Details: Type (Savings default), Name, Currency (NGN default), Account Number (optional, auto-generate)
- Savings Product: dropdown, Initial Deposit, Target Amount (optional)
- CTA: "Open Account" (full-width blue)

### 8.3 Savings Products (`/savings/products`)

**FR-ACCT-004: Savings Product Configuration**

| Field | Description |
|---|---|
| Product Name / Code | Display name + short code (e.g. SAV-PREM) |
| Description / Currency | Text, NGN default |
| Interest Rate / Frequency | Annual %, posting: Monthly/Quarterly/Annually |
| Min Balance / Min Opening | ₦ thresholds |
| Withdrawal Rules | Max withdrawal/day, max withdrawals/month |
| Lock-in Period | Days before early withdrawal penalty |
| Early Withdrawal Penalty | % fee |
| Monthly Maintenance Fee | ₦ flat fee/month |
| Min Balance Penalty | ₦ fee when below minimum |
| Product Type | Standard Savings / Goal-based / Fixed Deposit |
| Goal Target / Maturity | For goal-based and fixed deposit products |

### 8.4 Goal-based Savings & Fixed Deposits

**FR-ACCT-005: Goal-based Savings**
- Customer sets target amount and target date
- Progress bar shown on account page
- Partial withdrawals blocked until goal met (configurable)

**FR-ACCT-006: Fixed Deposit**
- Customer locks a sum for fixed tenure at guaranteed rate
- Maturity date calculated
- Early exit triggers penalty
- Auto-renewal option

### 8.5 Internal Wallet

**FR-ACCT-007: Customer Wallet**
- Every customer has a system wallet (NGN) separate from bank accounts
- Wallet funded via card, bank transfer, USSD, or agent
- Wallet balance usable for loan repayments, savings top-ups, inter-customer transfers
- Wallet transaction log: funding, debit, transfer, fee events

---

## 9. Transactions & Collections Module

### 9.1 Transactions List (`/transactions`)

**FR-TXN-001: Transactions List Page**
- Title: "Transactions". Top right: "+ New Transaction"
- Filters: All Types, All Statuses. Export.

| Column | Description |
|---|---|
| Reference | Transaction reference |
| Type | Deposit / Withdrawal / Transfer / Repayment / Disbursement |
| Amount | ₦ formatted |
| Description | Narration |
| Status | PENDING / SUCCESS / FAILED / REVERSED |
| Date | DateTime |

### 9.2 New Transaction Form (`/transactions/new`)

**FR-TXN-002: Transaction Tabs**
- **Tab 1 — Deposit:** Account ID, Amount, Currency, Description → "Process Deposit"
- **Tab 2 — Withdrawal:** Account ID, Amount, Currency, Description → "Process Withdrawal"
- **Tab 3 — Transfer:** Source Account ID, Destination Account ID, Amount, Currency, Description → "Process Transfer"

### 9.3 Inbound Transfers (`/inbound-transfers`)

**FR-TXN-003: Inbound Transfers (Read-only)**

| Column | Description |
|---|---|
| Session ID | NIBSS/switch session ID |
| Sender | Sender name |
| Account | Destination account number |
| Amount | ₦ formatted |
| Channel | BRANCH / MOBILE / POS / INTERNET_BANKING |
| Status | PENDING / POSTED / FAILED |
| Source | Payment switch source |
| Posting Type | Manual / Auto |
| Narration | Payment description |
| Received | Datetime received |
| Posted | Datetime posted |

### 9.4 Posting Files (`/posting-files`)

**FR-TXN-004: Bulk Posting**
- Upload CSV/Excel via drag-and-drop zone
- Buttons: Upload & Validate, Download Template
- Required columns: `identifier_type` (BVN/NIN/LOAN_ACCOUNT_NUMBER), `identifier_value`, `amount`, `transaction_date`
- Optional: `payment_channel`, `narration`
- Duplicate detection on re-upload
- Supports tens of thousands of records

### 9.5 Notifications Engine

**FR-TXN-005: Notification Channels**
- SMS (Termii/Africa's Talking), WhatsApp (Twilio/Meta), Email (SendGrid/Mailgun), Push (FCM)

**FR-TXN-006: Trigger Events**
- Loan disbursed, repayment received, repayment overdue, KYC approved/rejected, account opened, OTP, password reset

**FR-TXN-007: Notification Configuration**
- Per-tenant customisable message templates
- Notification log: channel, recipient, event, status, sent_at
- Customer opt-in/opt-out per channel

### 9.6 Real-time Repayment Channels

**FR-TXN-008: Payment Channels**

| Channel | Integration |
|---|---|
| Card | Paystack / Flutterwave card charge API |
| Mobile Money | MTN MoMo / Airtel Money / OPay API |
| USSD | USSD menu repayment flow (*944# or custom shortcode) |
| Agent POS | QR scan or loan ref + amount on POS device |
| Direct Debit | NIBSS eMandates — auto-debit on due date |

- All channels post to same Inbound Transfers queue with channel tag for reconciliation

---

## 10. Loans Module

### 10.1 Loans List (`/loans`)

**FR-LOAN-001: Loans List Page**
- Title: "Loans". Top right: "+ New Loan" + "Products" buttons
- Filters: status, type, date range. Export.

| Column | Description |
|---|---|
| Loan # | Auto-generated loan reference |
| Customer | Borrower name |
| Product | Loan product name |
| Principal | ₦ formatted |
| Balance | ₦ outstanding |
| Rate | Annual interest rate % |
| Status | PENDING / ACTIVE / OVERDUE / CLOSED / WRITTEN_OFF |
| Created | Application date |

### 10.2 New Loan Application (`/loans/new`)

**FR-LOAN-002: Loan Application Form**
Two-column layout: left form + right Pre-Qualification panel

**Left Column:**
- Customer: searchable combobox
- Loan Details: Product, Principal Amount, Tenure (days), Purpose, Source Channel (Web/Mobile/Branch/USSD/Agent)
- Loan Account: select from customer's existing accounts (or auto-create)
- Collateral (if required): Description textarea, Value (₦)

**FR-LOAN-003: Pre-Qualification Panel (Right)**
- Checks: KYC status, age ≥ 18, BVN verified, within loan limits, DTI ≤ product max, no existing overdue
- Shows pass/fail per check
- "Run Pre-Qualification" button

**FR-LOAN-004: AI Credit Score**
- Displayed after pre-qual: Score 1–100, band (Excellent/Good/Fair/Poor), approval recommendation
- CTA: "Submit Application" → enters approval workflow

### 10.3 Loan Products (`/loans/products`)

**FR-LOAN-005: Loan Product Configuration**

| Field | Description |
|---|---|
| Name / Code | Display name + short code (e.g. PL-001) |
| Interest Rate | Annual % (e.g. 24.0) |
| Interest Method | Reducing Balance / Flat |
| Amortization | Equal Installment / Bullet / Balloon |
| Min / Max Amount | ₦ loan size limits |
| Min / Max Tenure | Days |
| Max DTI Ratio | Decimal (e.g. 0.40 = 40%) |
| Processing Fee | % of principal |
| Insurance Fee | % of principal |
| Grace Period | Days after due date before penalty |
| Group Lending | Enable group/joint-liability flag |
| AI Assessment | Require AI credit score (configurable thresholds) |
| Early Repayment | Allow/forbid + penalty % |
| Collateral Types | Multi-select: Land Title, Vehicle, Movable Asset, Cash Deposit, Guarantor, None |

### 10.4 Loan Restructuring & Moratorium

**FR-LOAN-006: Restructuring Options**
- Accessible from loan detail page → "Restructure" action button
- Options: Extend Tenure, Reduce Rate, Grant Grace Period, Partial Write-off (₦ + reason), Full Write-off
- All actions require L2/L3 approval via workflow
- Audit trail: who approved, amount affected, old vs new terms

**FR-LOAN-007: Moratorium**
- Freeze repayment schedule for defined period (e.g. flood relief)
- Interest accrual during moratorium: product-configurable

### 10.5 Group / Centre / Meeting Management

**FR-LOAN-008: Group Lending**
- Group: named group of borrowers (5–30 members), assigned to Loan Officer and Branch
- Centre: umbrella for multiple groups (Grameen-style), meeting day/time/location
- Meeting: scheduled with attendance register, collection amounts per member
- Group Loan: single disbursement, varying amounts to members, repayment at meetings
- Solidarity guarantee: peer liability configurable per product
- Group dashboard: total exposure, PAR, attendance rate, next meeting date

---

## 11. Operations Module

### 11.1 Workflows (`/workflows`)

**FR-OPS-001: Workflow Interface**
- Tab 1: My Tasks — assigned to current user (badge count)
- Tab 2: Available Tasks — claimable by current user's role
- View Instances → table: Process, Business Key, Status, Started, Ended

**FR-OPS-002: Workflow Types**
- User Approval, Loan Approval (L1/L2/L3 by amount), KYC Review, Restructuring Approval, Write-off Approval

### 11.2 Reports (`/reports`)

**FR-OPS-003: Reports Hub**
- Grid of report cards, each with icon, title, description, "View Report →" link

| Report | Description |
|---|---|
| Account Statement | Transaction history for specific account (Account #, Start/End Date) |
| Trial Balance | GL debits/credits. CoA Code, Name, Category, Debit, Credit. "Balanced" badge |
| Loan Portfolio | Distribution donut + summary (Status, Count, ₦ Amount, % of Portfolio) |
| Interest Accrual | KPI cards: Total Accrued, Total Posted, Accounts with Pending Interest |
| PAR & Aging | Buckets: 1–30 / 31–60 / 61–90 / 90+ days. Count + ₦ + % per bucket |
| IFRS 9 ECL | ECL by stage (1/2/3), provision amount, coverage ratio, movement schedule |

---

## 12. Administration Module

### 12.1 Branches (`/branches`)

**FR-ADM-001: Branch Management**
- Table: Branch, Code, Location, Phone, Email, Manager, Status, Created
- "+ New Branch" → modal: Name*, Code*, Branch Code*, Sort Code, Routing Number, Phone, Email, Address

### 12.2 GL Accounts (`/gl-accounts`)

**FR-ADM-002: Chart of Accounts**
- Subtitle: "Hierarchical view of all General Ledger accounts"
- Filter: All Levels dropdown
- Table: Account, Account #, Category, Level, Balance, Branch/Teller
- Auto-provisioned from standard Nigerian CoA on tenant creation
- Used by Trial Balance report and double-entry posting engine

### 12.3 Users (`/users`)

**FR-ADM-003: User Management**
- Filters: All Roles, All Statuses. Export.
- Table: Name (+email), Role, Phone, Branch, Status, Last Login, View
- New User form with Submit for Approval flow (see Section 5.3)

### 12.4 Tenants (`/tenants`)

**FR-ADM-004: Tenant Management**
- Table: Tenant Name (+slug), Type, Prefix, Currency, Domain, Contact Email, Status, Created, Edit/View
- New Tenant form (see Section 5.2)

### 12.5 Exchange Rates (`/exchange-rates`)

**FR-ADM-005: FX Management**
- Current Rates: Pair (NGN/CAD, NGN/CNY, NGN/EUR, NGN/GBP, NGN/USD), Buy Rate, Sell Rate, Mid Rate, Effective Date
- Currency Converter panel: enter NGN amount, see all equivalents live
- "Refresh Rates" button (triggers FX Rate Update job)

### 12.6 Audit Logs (`/audit-logs`)

**FR-ADM-006: Audit Trail**
- Read-only, immutable system trail
- Filter by action / entity type. Export.
- Columns: Action (LOGIN/UPDATE/CREATE/DELETE), Entity Type, Entity ID, Description, User ID, IP Address, Timestamp

### 12.7 Scheduled Jobs (`/scheduled-jobs`)

**FR-ADM-007: Jobs Dashboard**
- Card grid (4 per row): name, description, Active badge, Schedule, Last Run, Records, Errors, History + Run Now
- Job History: Status, Started, Ended, Duration (ms), Records, Errors. Export.
- Execution Detail modal: Job Name, Duration, Started, Ended, Records, Errors, Metadata JSON

### 12.8 Settings (`/settings`)

**FR-ADM-008: User Settings**
- Profile: Name, Email, Role, Tenant (read-only)
- Appearance: Mode toggle (Light/Dark/System) + Colour palette (Blue/Crimson/Indigo/Purple)
- Security: Change Password (Current, New, Confirm)

---

## 13. Borrower Channels

### 13.1 Native Mobile App (iOS + Android)

**FR-CHAN-001: Mobile App**
- Built in React Native, consuming bankOS Laravel Sanctum API
- Login: Institution Code + Email + Password + 2FA OTP
- Home: account balances, quick actions (Pay, Transfer, Apply for Loan, Top up)
- Loan application: simplified flow, pre-fills from profile, camera doc upload
- Repayment: select loan, enter amount, choose channel (card/wallet/mobile money)
- Statement: scrollable transaction history with date filter
- Push notifications for all trigger events
- Biometric login (Face ID / Fingerprint)
- Offline mode: cached balance, queued repayments synced on reconnect

### 13.2 USSD Channel

**FR-CHAN-002: USSD Menu**
- Shortcode: *944# (or tenant-configured)
- Menu: 1. Check Balance → 2. Mini Statement → 3. Repay Loan → 4. Apply for Loan → 5. Transfer → 0. Exit
- Sessions via Africa's Talking or Twilio USSD API
- All actions logged to audit trail with MSISDN + session ID

---

## 14. Agent Banking Module

### 14.1 Agent Onboarding & Management

**FR-AGENT-001: Agent Record**
- Fields: Name, NIN, BVN, Phone, Address, GPS home location, assigned Branch, Status (ACTIVE/SUSPENDED)

**FR-AGENT-002: Float Wallet**
- Pre-funded float balance (NGN) for cash-out and loan disbursements

**FR-AGENT-003: Transaction Limits**
- Configurable per-agent daily: cash-in, cash-out, transfer limits

**FR-AGENT-004: Commissions**
- Per-transaction commission rates (flat or %) credited to agent wallet

**FR-AGENT-005: GPS Visit Logging**
- Agent check-in at customer location with lat/long recorded

**FR-AGENT-006: Agent Dashboard**
- Today's transactions, float balance, commission earned, pending visits

### 14.2 Field Officer Offline Mode

**FR-AGENT-007: Offline Capability**
- Offline actions: record repayments, check in at visits, add meeting attendance
- Stored locally (SQLite), synced to server on reconnect
- Conflict resolution: server timestamp wins; duplicate detection by reference number

---

## 15. Risk & Compliance Module

### 15.1 IFRS 9 Expected Credit Loss (ECL)

**FR-RISK-001: Loan Staging**
- Stage 1: Performing (12-month ECL)
- Stage 2: Significant Increase in Credit Risk (Lifetime ECL)
- Stage 3: Credit-impaired (Lifetime ECL)

**FR-RISK-002: ECL Calculation**
- Inputs: PD (Probability of Default), LGD (Loss Given Default), EAD (Exposure at Default), Discount Factor
- Provisioning journal: automatic GL postings to Loan Loss Provision accounts on EOD/EOM
- Scenario modelling: base/optimistic/pessimistic weights configurable by tenant admin

**FR-RISK-003: IFRS 9 Report**
- Stage distribution, ECL amounts, coverage ratios, period-over-period movement

### 15.2 Credit Bureau Integration

**FR-RISK-004: Bureau Connections**
- Supported: CRC, XDS (Creditinfo), FirstCentral
- Pull: credit inquiry on loan application → score, facilities, delinquency history
- Push: loan disbursements, repayments, defaults (monthly batch or real-time)
- Consent: customer authorises pull during application (stored with timestamp)
- Bureau report stored on loan record, visible to Loan Officer and Compliance Officer

### 15.3 Overdue Scoring & Smart Collection

**FR-RISK-005: Overdue Detection**
- Daily job calculates Days Past Due (DPD) per loan
- Composite overdue score: DPD, historical behaviour, loan size, customer segment

**FR-RISK-006: Smart Routing**
- High-risk → Compliance Officer queue
- Medium → Loan Officer
- Low → automated SMS nudge

**FR-RISK-007: Escalation Ladder**

| DPD | Action |
|---|---|
| 1 day | SMS/WhatsApp nudge |
| 7 days | Loan Officer call task |
| 30 days | Formal demand letter |
| 90 days | Legal team queue |

**FR-RISK-008: Collection Log**
- Every contact attempt recorded: channel, agent, outcome, next action

---

## 16. Additional Products

### 16.1 Embedded Insurance

**FR-PROD-001: Credit Life Insurance**
- Covers outstanding loan balance on borrower death/total permanent disability

**FR-PROD-002: Asset Insurance**
- Covers collateral asset against damage/theft

**FR-PROD-003: Insurance Integration**
- Partner API (Leadway, AXA Mansard, AIICO)
- Premium calculated on loan origination
- Options: single upfront premium or monthly deduction from savings

**FR-PROD-004: Claims Process**
- Loan officer flags event → claim form → insurer API notified
- Policy log on customer record: insurer, policy number, cover amount, premium, status

---

## 17. Security Requirements

**FR-SEC-001: Two-Factor Authentication**
- SMS OTP or TOTP Authenticator (Google/Authy)
- Per-user opt-in or tenant-enforced

**FR-SEC-002: Device Binding**
- New device login triggers verification email/SMS before access

**FR-SEC-003: Session Timeout**
- Configurable per tenant (default 30 min idle)
- Session invalidated on logout

**FR-SEC-004: Brute-force Protection**
- 5 failed login attempts → temporary account lock
- Unlock via email link

**FR-SEC-005: Password Policy**
- Min 8 chars, uppercase, lowercase, number
- Expires every 90 days (configurable)
- Auto-generated on user invite, forced change on first login

**FR-SEC-006: Role-based UI**
- Menu items and action buttons shown/hidden based on Spatie permissions

**FR-SEC-007: Transport & Application Security**
- HTTPS only, Cloudflare WAF, CSP headers
- SQL injection and XSS protection via Laravel's built-in escaping

---

## 18. Data Models

### 18.1 Core Entities

| Entity | Table | PK | Key Fields |
|---|---|---|---|
| **Tenant** | `tenants` | UUID | name, short_name, type, account_prefix, primary_currency, domain, status |
| **Customer** | `customers` | UUID | tenant_id, customer_number, type, name fields, bvn, nin, kyc_tier, status |
| **Account** | `accounts` | UUID | tenant_id, customer_id, account_number, type, currency, balances, status |
| **Loan** | `loans` | UUID | tenant_id, customer_id, account_id, product_id, principal, balance, rate, status |
| **Group** | `groups` | UUID | tenant_id, centre_id, name, loan_officer_id, branch_id, meeting_day, status |
| **Scheduled Job Run** | `scheduled_job_runs` | UUID | job_name, status, started_at, ended_at, records, errors, metadata |

### 18.2 Multi-Language Support

**FR-LANG-001: Localization**
- Primary: English
- Additional (stretch): Hausa, Yoruba, Igbo, Nigerian Pidgin
- Implementation: Laravel localisation files (`lang/*.json`)
- Language switcher in user settings
- USSD and SMS templates available in all supported languages

---

## 19. UI Design System

### 19.1 Colour Palette

| Token | Value | Usage |
|---|---|---|
| Primary (Blue) | — | Buttons, active nav, badges, links, table headers |
| Primary Light | #E6FAF8 | Active nav bg, card accents, success backgrounds |
| Crimson | #DC2626 | Alternate accent theme |
| Indigo | #4F46E5 | Alternate accent theme |
| Purple | #7C3AED | Alternate accent theme |
| Success | #10B981 | ACTIVE badges, positive KPIs |
| Warning | #F59E0B | PENDING badges, amber banners |
| Danger | #EF4444 | FAILED/OVERDUE badges, error states |
| Surface | #FFFFFF | Cards, modals, sidebar |
| Background | #F9FAFB | Page background |
| Border | #E5E7EB | Card borders, table dividers |
| Text Primary | #1F2937 | — |
| Text Secondary | #6B7280 | — |
| Text Muted | #9CA3AF | — |

### 19.2 Component Patterns

| Component | Specification |
|---|---|
| **Status Badges** | Rounded pill. ACTIVE=blue, PENDING=amber, FAILED/INACTIVE=red, DORMANT=grey |
| **Empty State** | Lucide icon centred, bold heading, grey sub-text |
| **Tables** | White bg, grey headers, horizontal dividers, hover highlight, pagination |
| **Pagination** | "Showing X-Y of N", per-page selector (10/25/50), nav buttons |
| **Modals** | Centred overlay, white card, title + subtitle, × close, Cancel + Create buttons |
| **Forms** | Label above input, full-width, red asterisk, grey helper text, inline validation |
| **Wizard** | Numbered steps connected by line. Active=blue, Complete=checkmark, Future=grey |
| **File Upload** | Dashed border, upload icon, drag-and-drop, file limits shown |
| **Notifications** | Amber bg for info/warning, red for errors |
| **Breadcrumb** | "← Back" link top-right of sub-pages |

---

## 20. Regulatory Compliance

| ID | Requirement | Priority | Segment |
|---|---|---|---|
| RC-01 | CBN MFB Guidelines: Tier KYC, transaction limits, audit trail | High | MFBs |
| RC-02 | NIBSS Integration: BVN/NIN, inbound transfers, eMandates | High | MFBs + Digital |
| RC-03 | FCCPC Digital Lending 2025: APR display, no abusive recovery, 48h data handover | High | Digital |
| RC-04 | NDPR: Lawful data processing, consent, retention policy, right to deletion | High | All |
| RC-05 | IFRS 9 ECL: Staging, provisioning, scenario modelling, GL journals | High | MFBs |
| RC-06 | Maker-Checker: Dual auth for create/approve/write-off above thresholds | High | All |
| RC-07 | Audit Trail: Immutable log of all mutations, exportable | High | All |
| RC-08 | Credit Bureau: Monthly push to CRC/XDS/FirstCentral, pull with consent | Medium | MFBs + Digital |
| RC-09 | Transparent Pricing: APR, fees, total repayment shown before acceptance | High | Digital |

---

## 21. Scheduled Jobs

| # | Job | Schedule | Description |
|---|---|---|---|
| 1 | Interest Accrual | 12:30 AM daily | Daily savings interest accrual for all accounts |
| 2 | Interest Posting | Midnight, 1st of month | Monthly interest credit to savings accounts |
| 3 | Overdue Detection | 2:00 AM daily | Flag overdue loans, calculate penalties |
| 4 | End of Day | 1:00 AM daily | EOD processing for all tenants |
| 5 | End of Month | 2:00 AM, 1st of month | EOM processing (interest, fees, statements) |
| 6 | MV Refresh | Every 15 minutes | Refresh dashboard materialised views |
| 7 | File Validation | Every 15 seconds | Validate queued posting files |
| 8 | File Posting | Every 15 seconds | Post validated posting file transactions |
| 9 | FX Rate Update | 6:00 AM daily | Pull exchange rates from external API |

---

## 22. Non-Functional Requirements

### 22.1 Performance
- Dashboard KPI cards and charts SHALL load within 3 seconds
- Materialised views SHALL refresh every 15 minutes for dashboard performance
- Posting file processing SHALL support tens of thousands of records per batch

### 22.2 Scalability
- Multi-tenant architecture SHALL support adding new tenants without infrastructure changes
- Queue workers SHALL handle async jobs without blocking web requests

### 22.3 Availability
- Application SHALL be accessible 24/7 with scheduled maintenance windows
- Offline mode for mobile and agent apps SHALL queue transactions for sync

### 22.4 Security
- All data in transit SHALL be encrypted via HTTPS/TLS
- Sensitive fields (BVN, NIN) SHALL be stored hashed
- Application SHALL comply with OWASP Top 10 security standards

### 22.5 Audit & Compliance
- All data mutations SHALL be logged to an immutable audit trail
- Audit logs SHALL be exportable for regulatory review
- System SHALL support maker-checker workflows for sensitive operations

---

*Document generated from bankOS Master Product Specification v2.0 — March 2026*
