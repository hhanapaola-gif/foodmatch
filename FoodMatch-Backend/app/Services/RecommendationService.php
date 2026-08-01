<?php

namespace App\Services;

use App\Model\Plan;
use Illuminate\Support\Collection;

/**
 * Content-based recommendation engine for meal plans.
 *
 * Scoring rubric (higher = more similar):
 *   +3  same branch / restaurant
 *   +2  same plan type (weekly / monthly)
 *   +3  price very close  (<15 % difference)
 *   +2  price close       (15–30 %)
 *   +1  price somewhat    (30–50 %)
 *   0–3 popularity bonus  (proportional to order count vs. most-ordered plan)
 */
class RecommendationService
{
    private const TOP_N = 5;

    public function recommendPlans(int $planId): Collection
    {
        $plan = Plan::active()->with(['category', 'days', 'branch'])->findOrFail($planId);

        $avgPrice = $this->avgPrice($plan);

        // ── Primary candidates: same category ────────────────────────────────
        $candidates = $this->queryCandidates([$planId])
            ->where('category_id', $plan->category_id)
            ->get();

        // ── Fallback: fill up to 10 candidates from other categories ─────────
        if ($candidates->count() < 3) {
            $excludeIds = $candidates->pluck('id')->push($planId)->unique()->all();
            $extras     = $this->queryCandidates($excludeIds)->limit(10)->get();
            $candidates = $candidates->merge($extras);
        }

        if ($candidates->isEmpty()) {
            return collect();
        }

        $maxOrders = max($candidates->max('orders_count'), 1);

        // ── Score & rank ──────────────────────────────────────────────────────
        return $candidates
            ->map(function (Plan $p) use ($plan, $avgPrice, $maxOrders): Plan {
                $score = 0;

                // Same branch
                if ($p->restaurant_id && $p->restaurant_id === $plan->restaurant_id) {
                    $score += 3;
                }

                // Same type (weekly / monthly)
                if ($p->type === $plan->type) {
                    $score += 2;
                }

                // Price similarity
                $candidateAvg = $this->avgPrice($p);
                if ($avgPrice > 0 && $candidateAvg > 0) {
                    $diff = abs($candidateAvg - $avgPrice) / $avgPrice;
                    if ($diff < 0.15)      $score += 3;
                    elseif ($diff < 0.30)  $score += 2;
                    elseif ($diff < 0.50)  $score += 1;
                }

                // Popularity (normalised 0–3)
                $score += (int) round(($p->orders_count / $maxOrders) * 3);

                $p->setAttribute('recommendation_score', $score);
                return $p;
            })
            ->sortByDesc('recommendation_score')
            ->take(self::TOP_N)
            ->values();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Average of all non-zero meal prices for a plan. */
    private function avgPrice(Plan $plan): float
    {
        $prices = array_filter([
            (float) $plan->breakfast_price,
            (float) $plan->lunch_price,
            (float) $plan->dinner_price,
        ], fn (float $p): bool => $p > 0);

        return $prices ? array_sum($prices) / count($prices) : 0.0;
    }

    /** Base query: active plans with popularity count, excluding given IDs. */
    private function queryCandidates(array $excludeIds)
    {
        return Plan::active()
            ->with(['category', 'days', 'branch'])
            ->selectRaw(
                'plans.*, '
                . '(SELECT COUNT(*) FROM plan_orders WHERE plan_orders.plan_id = plans.id) '
                . 'AS orders_count'
            )
            ->whereNotIn('id', $excludeIds);
    }
}
