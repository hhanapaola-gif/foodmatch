<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\CustomerLogic;
use App\Http\Controllers\Controller;
use App\Model\Plan;
use App\Model\PlanOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;

class PlanOrderController extends Controller
{
    public function __construct(private PlanOrder $planOrder) {}

    private function branchPlanIds(): array
    {
        return Plan::where('restaurant_id', auth('branch')->id())->pluck('id')->all();
    }

    public function index(Request $request): Renderable
    {
        $planIds = $this->branchPlanIds();
        $search  = $request->search;
        $status  = $request->status;
        $planId  = $request->plan_id;
        $from    = $request->from ? Carbon::parse($request->from)->startOfDay() : null;
        $to      = $request->to   ? Carbon::parse($request->to)->endOfDay()     : null;

        $base = $this->planOrder->whereIn('plan_id', $planIds);

        $orders = (clone $base)
            ->with(['plan', 'plan.category', 'customer'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($planId, fn ($q) => $q->where('plan_id', $planId))
            ->when($from && $to, fn ($q) => $q->whereBetween('created_at', [$from, $to]))
            ->when($search, function ($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('customer', fn ($u) =>
                      $u->where('f_name', 'like', "%$search%")
                        ->orWhere('l_name', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                  );
            })
            ->latest()
            ->paginate(20)
            ->appends($request->only(['search', 'status', 'plan_id', 'from', 'to']));

        $counts = [
            'all'       => (clone $base)->count(),
            'pending'   => (clone $base)->where('status', 'pending')->count(),
            'confirmed' => (clone $base)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
        ];

        $plans = Plan::whereIn('id', $planIds)->orderBy('title')->get(['id', 'title']);

        return view('branch-views.plan-order.list', compact('orders', 'search', 'status', 'planId', 'plans', 'counts', 'from', 'to'));
    }

    public function show(int $id): Renderable
    {
        $order = $this->planOrder
            ->with(['plan', 'plan.category', 'customer', 'details'])
            ->whereIn('plan_id', $this->branchPlanIds())
            ->findOrFail($id);

        return view('branch-views.plan-order.show', compact('order'));
    }

    public function updateStatus(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,confirmed,cancelled']);

        $order = $this->planOrder->whereIn('plan_id', $this->branchPlanIds())->findOrFail($id);
        $wasCancelled = $order->status === 'cancelled';
        $order->update(['status' => $request->status]);

        if ($request->status === 'cancelled' && !$wasCancelled) {
            CustomerLogic::create_wallet_transaction($order->user_id, $order->total_price, 'order_refund', 'Reembolso pedido #' . $order->id);
        }

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
