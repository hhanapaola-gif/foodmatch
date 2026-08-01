<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\Plan;
use App\Model\SavedPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedPlanController extends Controller
{
    /**
     * POST /api/v1/plans/{id}/save
     * Save a plan for the authenticated user.
     */
    public function save(int $id): JsonResponse
    {
        $userId = auth('api')->user()->id;

        SavedPlan::firstOrCreate([
            'user_id' => $userId,
            'plan_id' => $id,
        ]);

        return response()->json([
            'status' => true,
            'saved'  => true,
        ], 200);
    }

    /**
     * DELETE /api/v1/plans/{id}/unsave
     * Remove a saved plan for the authenticated user.
     */
    public function unsave(int $id): JsonResponse
    {
        $userId = auth('api')->user()->id;

        SavedPlan::where('user_id', $userId)
            ->where('plan_id', $id)
            ->delete();

        return response()->json([
            'status' => true,
            'saved'  => false,
        ], 200);
    }

    /**
     * GET /api/v1/user/saved-plans
     * List all saved plans for the authenticated user with full plan data.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth('api')->user()->id;

        $savedPlans = SavedPlan::where('user_id', $userId)
            ->with(['plan.category', 'plan.days'])
            ->latest('created_at')
            ->get();

        $plans = $savedPlans->map(function (SavedPlan $row) {
            if (!$row->plan) {
                return null;
            }
            $plan = $row->plan;

            return [
                'id'              => $plan->id,
                'title'           => $plan->title,
                'description'     => $plan->description,
                'type'            => $plan->type,
                'min_days'        => $plan->min_days,
                'breakfast_price' => (float) $plan->breakfast_price,
                'lunch_price'     => (float) $plan->lunch_price,
                'dinner_price'    => (float) $plan->dinner_price,
                'image'           => $plan->image_full_path,
                'category_name'   => $plan->category?->name,
                'category_id'     => $plan->category_id,
                'restaurant_id'   => $plan->restaurant_id,
                'days_of_service' => $plan->days->pluck('day_of_week')->sort()->values(),
                'is_active'       => (bool) $plan->is_active,
            ];
        })->filter()->values();

        return response()->json([
            'status'      => true,
            'saved_plans' => $plans,
        ], 200);
    }

    /**
     * GET /api/v1/user/saved-plan-ids
     * Returns only plan IDs the user has saved — lightweight check for the heart icon.
     */
    public function savedIds(): JsonResponse
    {
        $userId = auth('api')->user()->id;

        $ids = SavedPlan::where('user_id', $userId)
            ->pluck('plan_id')
            ->toArray();

        return response()->json([
            'status'   => true,
            'plan_ids' => $ids,
        ], 200);
    }
}
