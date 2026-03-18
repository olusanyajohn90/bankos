<?php

namespace App\Services;

use App\Models\Customer;

class AiReviewService
{
    /**
     * Generate a comprehensive (simulated) AI review based on customer profile and history.
     * In a production environment, this would build a prompt and call the OpenAI API.
     */
    public function generateReview(Customer $customer): string
    {
        $customer->load(['accounts', 'loans']);

        $totalBalance = $customer->accounts->sum('available_balance');
        $activeLoans = $customer->loans->where('status', 'active');
        $totalLoanBalance = $activeLoans->sum('principal') - $activeLoans->sum('amount_paid');
        
        $kycStatus = $customer->kyc_status;
        $isCorporate = $customer->type === 'corporate';
        $age = \Carbon\Carbon::parse($customer->date_of_birth)->age;

        $review = [];

        // 1. Executive Summary
        $review[] = "### 🤖 AI Executive Summary";
        $sentiment = "Neutral";
        if ($totalBalance > 50000 && $totalLoanBalance == 0) $sentiment = "Low Risk / High Potential";
        if ($totalLoanBalance > $totalBalance) $sentiment = "Elevated Risk - Highly Leveraged";
        if ($kycStatus !== 'approved') $sentiment = "Pending Compliance Action";

        $review[] = "**Overall Risk & Profile Sentiment:** {$sentiment}";
        $review[] = "The customer is a {$age}-year-old " . ($isCorporate ? "corporate entity" : "individual") . " with their primary KYC status currently marked as **{$kycStatus}**. ";

        // 2. Financial Health
        $review[] = "### 💰 Financial & Transactional Health";
        if ($customer->accounts->isEmpty()) {
            $review[] = "- **Warning:** No active accounts found. The customer is currently unbanked within the platform. Opportunity to push account origination.";
        } else {
            $review[] = "- **Liquidity:** The customer maintains a total available balance of ₦" . number_format($totalBalance, 2) . " across {$customer->accounts->count()} account(s).";
            if ($totalBalance < 5000) {
                $review[] = "- **Recommendation:** Cross-sell high-yield savings products to increase deposit stickiness.";
            } else {
                $review[] = "- **Actionable Insight:** Customer is highly liquid. Consider introducing wealth management or fixed deposit products.";
            }
        }

        // 3. Credit Profile
        $review[] = "### 💳 Credit & Lending Profile";
        if ($customer->loans->isEmpty()) {
            $review[] = "- **Untapped Potential:** No borrowing history found. Based on standard demographic metrics, they may be eligible for a Tier-1 micro-loan or salary advance. Run soft credit check to pre-qualify.";
        } else {
            $review[] = "- **Active Exposure:** Customer has {$activeLoans->count()} active loan(s) with an outstanding balance of ₦" . number_format($totalLoanBalance, 2) . ".";
            if ($totalLoanBalance > $totalBalance) {
                $review[] = "- **Risk Alert:** Outstanding debt exceeds current available liquidity exactly. Monitor upcoming repayment cycles closely.";
            } else {
                $review[] = "- **Positive Indicator:** Liquidity safely covers outstanding credit liabilities.";
            }
        }

        // 4. Compliance & KYC
        $review[] = "### 🛡️ Compliance Recommendations";
        if ($kycStatus === 'approved') {
            $review[] = "- KYC is fully verified. No compliance blockers for expanded transaction limits.";
        } else {
            $review[] = "- **Blocker:** Customer KYC is pending or rejected. Restrict outward transfers until BVN/NIN verification is processed by the compliance desk.";
        }

        return implode("\n\n", $review);
    }
}
