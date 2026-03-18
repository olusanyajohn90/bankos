<?php

/**
 * Workflow Process Definitions
 *
 * Each process defines a chain of approval steps.
 * Steps are applied sequentially — the total number of steps used for a given
 * submission is determined by the 'amount' option in WorkflowService::create().
 *
 * Step config keys:
 *   role       — Spatie role name that can action this step
 *   label      — Human-readable step name shown in the UI
 *   sla_hours  — Hours until this step is considered overdue
 *   max_amount — (optional) If the submitted amount is <= this value, no further steps are needed
 *
 * Delegated Authority (amount-based routing):
 *   • Amount ≤ step[0]['max_amount']  → 1 step (step 0 only)
 *   • Amount ≤ step[1]['max_amount']  → 2 steps
 *   • Amount > all max_amounts        → full chain (all steps)
 *
 * To expand to real roles (loan_officer, branch_manager, credit_committee),
 * update the 'role' keys below after creating those Spatie roles.
 */

return [

    'processes' => [

        'KYC Review' => [
            'steps' => [
                [
                    'role'      => 'compliance_officer',
                    'label'     => 'Compliance Review',
                    'sla_hours' => 48,
                ],
            ],
        ],

        'Loan Approval' => [
            'steps' => [
                [
                    'role'       => 'tenant_admin',
                    'label'      => 'L1 — Credit Officer Review',
                    'sla_hours'  => 24,
                    'max_amount' => 500_000,  // Loans ≤ ₦500k: one level only
                ],
                [
                    'role'       => 'tenant_admin',
                    'label'      => 'L2 — Senior Management Approval',
                    'sla_hours'  => 48,
                    'max_amount' => 2_000_000, // Loans ≤ ₦2M: two levels
                ],
                [
                    'role'      => 'tenant_admin',
                    'label'     => 'L3 — Credit Committee Sign-off',
                    'sla_hours' => 72,
                    // No max_amount → all loans above ₦2M reach this step
                ],
            ],
        ],

        'Loan Top-up' => [
            'steps' => [
                [
                    'role'       => 'tenant_admin',
                    'label'      => 'L1 — Credit Review',
                    'sla_hours'  => 24,
                    'max_amount' => 500_000,
                ],
                [
                    'role'      => 'tenant_admin',
                    'label'     => 'L2 — Management Approval',
                    'sla_hours' => 48,
                ],
            ],
        ],

        'Loan Restructure' => [
            'steps' => [
                [
                    'role'      => 'tenant_admin',
                    'label'     => 'L1 — Credit Review',
                    'sla_hours' => 24,
                ],
                [
                    'role'      => 'tenant_admin',
                    'label'     => 'L2 — Management Approval',
                    'sla_hours' => 48,
                ],
            ],
        ],

    ],

];
