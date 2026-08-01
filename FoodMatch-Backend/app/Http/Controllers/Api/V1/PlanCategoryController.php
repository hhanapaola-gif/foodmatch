<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Model\PlanCategory;
use Illuminate\Http\JsonResponse;

class PlanCategoryController extends Controller
{
    public function __construct(private PlanCategory $planCategory) {}

    /**
     * Return all active plan categories.
     * GET /api/v1/plan-categories
     */
    public function index(): JsonResponse
    {
        $categories = $this->planCategory
            ->active()
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->map(fn (PlanCategory $cat) => [
                'id'    => $cat->id,
                'name'  => $cat->name,
                'image' => $cat->imageFullPath,
            ]);

        return response()->json($categories, 200);
    }
}
