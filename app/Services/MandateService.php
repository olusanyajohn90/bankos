<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountMandate;
use App\Models\MandateApproval;
use App\Models\MandateApprovalAction;
use App\Models\MandateSignatory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MandateService
{
    /**
     * Determine if a transaction on the given account at the given amount
     * requires multi-signatory approval.
     */
    public function requiresApproval(Account $account, float $amount): bool
    {
        $mandate = $account->mandate;

        if (! $mandate) {
            return false;
        }

        // If a sole-signing threshold is defined and the amount is within it, no approval needed.
        if ($mandate->max_amount_sole !== null && $amount <= (float) $mandate->max_amount_sole) {
            return false;
        }

        if ($mandate->signing_rule === 'sole') {
            return false;
        }

        return true;
    }

    /**
     * Open a new approval request for a transaction that requires multi-signatory sign-off.
     */
    public function initiateApproval(
        Account $account,
        string $description,
        float $amount,
        array $metadata = [],
        ?User $requestedBy = null
    ): MandateApproval {
        $mandate = $account->mandate;

        $required = match ($mandate->signing_rule) {
            'sole', 'any_one'               => 1,
            'any_two', 'a_and_b', 'a_and_any_b' => 2,
            'all'                           => $mandate->signatories()->active()->count(),
            default                         => 1,
        };

        return MandateApproval::create([
            'tenant_id'          => $account->tenant_id,
            'account_id'         => $account->id,
            'mandate_id'         => $mandate->id,
            'description'        => $description,
            'amount'             => $amount,
            'reference'          => 'MAPPR-' . strtoupper(Str::random(10)) . '-' . now()->format('YmdHis'),
            'required_approvals' => $required,
            'approvals_received' => 0,
            'status'             => 'pending',
            'requested_by'       => $requestedBy?->id,
            'expires_at'         => now()->addHours(48),
            'metadata'           => $metadata ?: null,
        ]);
    }

    /**
     * Record an approval action by a signatory user.
     * Returns true when the approval threshold has been met.
     */
    public function approve(MandateApproval $approval, User $actioner, ?string $notes = null): bool
    {
        // Find the signatory record linked to this user
        $signatory = MandateSignatory::where('mandate_id', $approval->mandate_id)
            ->where('user_id', $actioner->id)
            ->where('is_active', true)
            ->first();

        // Record the action
        MandateApprovalAction::create([
            'approval_id'  => $approval->id,
            'signatory_id' => $signatory?->id,
            'action'       => 'approved',
            'notes'        => $notes,
            'actioned_by'  => $actioner->id,
            'actioned_at'  => now(),
        ]);

        $approval->increment('approvals_received');
        $approval->refresh();

        // Evaluate class-based rules
        if (in_array($approval->mandate->signing_rule, ['a_and_b', 'a_and_any_b'])) {
            $approvedActions = $approval->actions()
                ->where('action', 'approved')
                ->with('signatory')
                ->get();

            $classes = $approvedActions
                ->filter(fn($a) => $a->signatory !== null)
                ->pluck('signatory.signatory_class')
                ->unique()
                ->values();

            if ($approval->mandate->signing_rule === 'a_and_b') {
                $met = $classes->contains('A') && $classes->contains('B');
            } else {
                // a_and_any_b: need class-A, then any from B or C
                $met = $classes->contains('A') && $classes->intersect(['B', 'C'])->isNotEmpty();
            }
        } else {
            $met = $approval->approvals_received >= $approval->required_approvals;
        }

        if ($met) {
            $approval->update([
                'status'       => 'approved',
                'completed_at' => now(),
            ]);
            return true;
        }

        return false;
    }

    /**
     * Reject an approval request.
     */
    public function reject(MandateApproval $approval, User $actioner, ?string $notes = null): void
    {
        $signatory = MandateSignatory::where('mandate_id', $approval->mandate_id)
            ->where('user_id', $actioner->id)
            ->where('is_active', true)
            ->first();

        MandateApprovalAction::create([
            'approval_id'  => $approval->id,
            'signatory_id' => $signatory?->id,
            'action'       => 'rejected',
            'notes'        => $notes,
            'actioned_by'  => $actioner->id,
            'actioned_at'  => now(),
        ]);

        $approval->update([
            'status'       => 'rejected',
            'completed_at' => now(),
        ]);
    }

    /**
     * Expire pending approvals past their expiry time.
     * Returns the count of records expired.
     */
    public function expireOld(): int
    {
        return MandateApproval::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);
    }

    /**
     * Get all pending approvals where the given user is a signatory on the account mandate.
     */
    public function getPendingForUser(User $user): Collection
    {
        return MandateApproval::where('status', 'pending')
            ->whereHas('mandate.signatories', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('is_active', true);
            })
            ->with(['account.customer', 'mandate', 'requestedBy'])
            ->get();
    }
}
