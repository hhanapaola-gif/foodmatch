<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Banner;
use Illuminate\Http\JsonResponse;

class BannerController extends Controller
{
    public function __construct(
        private Banner $banner
    )
    {
    }

    /**
     * @return JsonResponse
     */
    public function getBanners(): JsonResponse
    {
        $banners = $this->banner
            ->with(['product.rating', 'product.branch_product', 'plan.category', 'plan.days', 'plan.branch'])
            ->active()
            ->get()
            ->map(function (Banner $banner) {
                // Build a plain array (not an Eloquent model) so these curated
                // overrides can't be re-clobbered by the raw eager-loaded
                // relations Eloquent would otherwise merge back in on toArray().
                $data = $banner->toArray();
                $data['product'] = $banner->product ? Helpers::product_data_formatting($banner->product) : null;
                // Embed the full plan shape (not just plan_id) so the mobile app can
                // navigate straight to the plan detail screen without a second request.
                $data['plan'] = $banner->plan ? PlanController::formatPlan($banner->plan) : null;
                return $data;
            });

        return response()->json($banners, 200);
    }
}
