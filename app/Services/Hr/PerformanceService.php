<?php
namespace App\Services\Hr;

use App\Models\PerformanceReview;
use App\Models\PerformanceReviewItem;
use App\Models\ReviewCycle;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PerformanceService
{
    protected array $defaultCriteria = [
        ['criterion' => 'Quality of Work',                  'weight' => 20],
        ['criterion' => 'Productivity / Target Achievement', 'weight' => 25],
        ['criterion' => 'Teamwork & Collaboration',          'weight' => 15],
        ['criterion' => 'Punctuality & Attendance',          'weight' => 10],
        ['criterion' => 'Customer Service',                  'weight' => 15],
        ['criterion' => 'Initiative & Innovation',           'weight' => 10],
        ['criterion' => 'Compliance & Ethics',               'weight' =>  5],
    ];

    public function createReview(ReviewCycle $cycle, StaffProfile $staff, ?User $reviewer): PerformanceReview
    {
        return DB::transaction(function () use ($cycle, $staff, $reviewer) {
            $review = PerformanceReview::create([
                'tenant_id'        => $cycle->tenant_id,
                'review_cycle_id'  => $cycle->id,
                'staff_profile_id' => $staff->id,
                'reviewer_id'      => $reviewer?->id,
                'status'           => 'pending',
            ]);

            foreach ($this->defaultCriteria as $criterion) {
                PerformanceReviewItem::create([
                    'review_id' => $review->id,
                    'criterion' => $criterion['criterion'],
                    'weight'    => $criterion['weight'],
                    'max_score' => 5,
                ]);
            }
            return $review;
        });
    }

    public function submitSelfAssessment(PerformanceReview $review, array $scores, string $comments): void
    {
        DB::transaction(function () use ($review, $scores, $comments) {
            foreach ($scores as $itemId => $score) {
                PerformanceReviewItem::where('id', $itemId)->where('review_id', $review->id)
                    ->update(['self_score' => (float) $score]);
            }
            $review->update([
                'staff_comments' => $comments,
                'submitted_at'   => now(),
                'status'         => 'self_assessed',
            ]);
        });
    }

    public function submitManagerReview(PerformanceReview $review, array $scores, string $comments, User $reviewer): void
    {
        DB::transaction(function () use ($review, $scores, $comments, $reviewer) {
            foreach ($scores as $itemId => $score) {
                PerformanceReviewItem::where('id', $itemId)->where('review_id', $review->id)
                    ->update(['manager_score' => (float) $score]);
            }
            $review->load('items');
            $overallScore = $this->computeOverallScore($review);
            $rating       = $this->deriveRating($overallScore);

            $review->update([
                'manager_comments' => $comments,
                'reviewer_id'      => $reviewer->id,
                'reviewed_at'      => now(),
                'overall_score'    => $overallScore,
                'rating'           => $rating,
                'status'           => 'manager_reviewed',
            ]);
        });
    }

    public function approveByHr(PerformanceReview $review): void
    {
        $review->update(['status' => 'hr_approved']);
    }

    public function computeOverallScore(PerformanceReview $review): float
    {
        $items       = $review->items;
        $totalWeight = $items->sum('weight');
        if ($totalWeight == 0) return 0;
        $weightedSum = $items->sum(fn ($i) => ($i->manager_score ?? 0) * $i->weight);
        return round($weightedSum / $totalWeight, 2);
    }

    private function deriveRating(float $score): string
    {
        if ($score >= 4.5) return 'exceptional';
        if ($score >= 3.5) return 'exceeds_expectations';
        if ($score >= 2.5) return 'meets_expectations';
        if ($score >= 1.5) return 'below_expectations';
        return 'unsatisfactory';
    }
}
