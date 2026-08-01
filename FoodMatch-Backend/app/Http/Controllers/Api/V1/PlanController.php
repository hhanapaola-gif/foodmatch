<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Plan;
use App\Model\PlanOrder;
use App\Model\PlanOrderDetail;
use App\Services\RecommendationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private Plan            $plan,
        private PlanOrder       $planOrder,
        private PlanOrderDetail $planOrderDetail,
    ) {}

    /**
     * Return paginated list of active plans, each including category and service days.
     */
    public function index(Request $request): JsonResponse
    {
        $limit  = $request->get('limit', 10);
        $offset = $request->get('offset', 1);

        $query = $this->plan->active()->with(['category', 'days', 'branch'])
            ->when($request->get('category_id'), fn ($q) => $q->where('category_id', $request->get('category_id')));

        $total = $query->count();
        $plans = $query->latest()
            ->skip(($offset - 1) * $limit)
            ->take($limit)
            ->get()
            ->map(fn (Plan $plan) => $this->formatPlan($plan));

        return response()->json([
            'total_size' => $total,
            'limit'      => (int) $limit,
            'offset'     => (int) $offset,
            'plans'      => $plans,
        ], 200);
    }

    /**
     * Return full detail for a single active plan.
     */
    public function show(int $id): JsonResponse
    {
        $plan = $this->plan->active()->with(['category', 'days', 'branch'])->find($id);

        if (!$plan) {
            return response()->json(['message' => translate('plan_not_found')], 404);
        }

        return response()->json($this->formatPlan($plan), 200);
    }

    /**
     * Return all active plans for a given branch (restaurant).
     *
     * GET /api/v1/restaurants/{id}/plans
     */
    public function plansByRestaurant(int $branchId): JsonResponse
    {
        $plans = $this->plan
            ->active()
            ->with(['category', 'days', 'branch'])
            ->where('restaurant_id', $branchId)
            ->latest()
            ->get()
            ->map(fn (Plan $plan) => $this->formatPlan($plan));

        return response()->json([
            'status' => true,
            'plans'  => $plans,
        ], 200);
    }

    /**
     * Return up to 5 content-based recommendations for a given plan.
     *
     * GET /api/v1/plans/{id}/recommendations
     */
    public function recommendations(int $id): JsonResponse
    {
        try {
            $recommendations = (new RecommendationService())->recommendPlans($id);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => translate('plan_not_found')], 404);
        }

        return response()->json([
            'status'          => true,
            'recommendations' => $recommendations->map(fn (Plan $p) => $this->formatPlan($p)),
        ], 200);
    }

    /**
     * Create a confirmed plan order (simulated payment — demo mode).
     *
     * POST /api/v1/plans/{id}/order
     *
     * Body:
     * {
     *   "start_date":    "2026-03-30",        // YYYY-MM-DD, must be ≥ next Monday
     *   "selected_days": ["mon","tue","wed"],  // day abbreviations
     *   "meals":         ["breakfast","lunch"] // meal types selected
     * }
     *
     * Business rule: plans can only start on or after the next calendar week.
     */
    public function order(Request $request, int $id): JsonResponse
    {
        $plan = $this->plan->active()->with('days')->find($id);

        if (!$plan) {
            return response()->json(['message' => translate('plan_not_found')], 404);
        }

        $request->validate([
            'start_date'      => 'required|date_format:Y-m-d',
            'selected_days'   => 'required|array|min:1',
            'selected_days.*' => 'in:mon,tue,wed,thu,fri,sat,sun',
            'meals'           => 'required|array|min:1',
            'meals.*'         => 'in:breakfast,lunch,dinner',
            'address'         => 'nullable|string|max:500',
            'notes'           => 'nullable|string|max:1000',
        ]);

        // ── Next-Monday guard ────────────────────────────────────────────────
        $today      = Carbon::today('UTC');
        $nextMonday = $today->copy()->next(Carbon::MONDAY);
        $startDate  = Carbon::createFromFormat('Y-m-d', $request->start_date, 'UTC')->startOfDay();

        if ($startDate->lt($nextMonday)) {
            return response()->json([
                'message'       => translate('Los planes solo pueden comenzar desde la próxima semana.'),
                'earliest_date' => $nextMonday->toDateString(),
            ], 422);
        }

        // ── Price calculation ────────────────────────────────────────────────
        $dayCount   = count($request->selected_days);
        $mealPrices = [
            'breakfast' => (float) $plan->breakfast_price,
            'lunch'     => (float) $plan->lunch_price,
            'dinner'    => (float) $plan->dinner_price,
        ];

        $weeklyPrice = 0;
        foreach ($request->meals as $meal) {
            $weeklyPrice += ($mealPrices[$meal] ?? 0) * $dayCount;
        }
        // Monthly plans span 4 weeks; multiply weekly price accordingly.
        $totalPrice = $plan->type === 'monthly' ? $weeklyPrice * 4 : $weeklyPrice;

        // ── Day abbreviation → integer map ───────────────────────────────────
        $dayMap = ['mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 7];

        // ── Persist order ────────────────────────────────────────────────────
        $planOrder = $this->planOrder->create([
            'user_id'        => auth('api')->id(),
            'plan_id'        => $plan->id,
            'start_date'     => $startDate->toDateString(),
            'status'         => 'confirmed',     // demo: instantly confirmed
            'payment_status' => 'paid',          // demo: payment simulated
            'payment_method' => 'demo',
            'total_price'    => round($totalPrice, 2),
            'selected_days'  => $request->selected_days,
            'address'        => $request->input('address'),
            'notes'          => $request->input('notes'),
        ]);

        // ── Persist details (one row per day × meal) ─────────────────────────
        $details = [];
        foreach ($request->selected_days as $abbr) {
            $dow = $dayMap[$abbr] ?? null;
            if ($dow === null) continue;

            foreach ($request->meals as $meal) {
                $details[] = [
                    'plan_order_id' => $planOrder->id,
                    'meal_type'     => $meal,
                    'day_of_week'   => $dow,
                    'price'         => $mealPrices[$meal] ?? 0,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }
        $this->planOrderDetail->insert($details);

        return response()->json([
            'order_id'   => $planOrder->id,
            'status'     => $planOrder->status,
            'total'      => $planOrder->total_price,
            'start_date' => $planOrder->start_date->toDateString(),
            'message'    => translate('Pedido creado correctamente'),
        ], 201);
    }

    /**
     * Return the authenticated user's plan orders.
     *
     * GET /api/v1/user/plan-orders?status=confirmed
     */
    public function userOrders(Request $request): JsonResponse
    {
        $orders = $this->planOrder
            ->with(['plan.category', 'details'])
            ->where('user_id', auth('api')->id())
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->get()
            ->map(fn (PlanOrder $o) => [
                'id'           => $o->id,
                'plan_id'      => $o->plan_id,
                'plan_title'   => $o->plan?->title,
                'plan_image'   => $o->plan?->image_full_path,
                'category'     => $o->plan?->category?->name,
                'start_date'   => $o->start_date?->toDateString(),
                'plan_type'    => $o->plan?->type,
                'status'       => $o->status,
                'payment_status' => $o->payment_status,
                'total_price'  => $o->total_price,
                'selected_days' => $o->selected_days,
                'meals'        => $o->details->pluck('meal_type')->unique()->values(),
                'address'      => $o->address,
                'notes'        => $o->notes,
                'created_at'   => $o->created_at?->toDateTimeString(),
            ]);

        return response()->json(['orders' => $orders], 200);
    }

    /**
     * Map a Plan model to the standard API response shape.
     */
    public static function formatPlan(Plan $plan): array
    {
        return [
            'id'               => $plan->id,
            'title'            => $plan->title,
            'description'      => $plan->description,
            'type'             => $plan->type,
            'min_days'         => $plan->min_days,
            'meals_per_day'    => $plan->meals_per_day,
            'breakfast_price'  => $plan->breakfast_price,
            'lunch_price'      => $plan->lunch_price,
            'dinner_price'     => $plan->dinner_price,
            'image'            => $plan->image_full_path,
            'menu_pdf'         => $plan->menu_pdf
                                   ? (str_starts_with($plan->menu_pdf, 'storage/')
                                       ? $plan->menu_pdf
                                       : 'storage/menus/' . $plan->menu_pdf)
                                   : null,
            'category_id'      => $plan->category_id,
            'category_name'    => $plan->category?->name,
            'days_of_service'  => $plan->days->pluck('day_of_week')->sort()->values(),
            'restaurant_id'    => $plan->restaurant_id,
            'branch'           => $plan->branch ? [
                'id'   => $plan->branch->id,
                'name' => $plan->branch->name,
            ] : null,
            'is_active'        => $plan->is_active,
        ];
    }
}
