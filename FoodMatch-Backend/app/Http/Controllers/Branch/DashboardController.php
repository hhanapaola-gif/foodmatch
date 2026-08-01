<?php

namespace App\Http\Controllers\Branch;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\Model\Plan;
use App\Model\PlanOrder;
use App\Traits\UploadSizeHelperTrait;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class DashboardController extends Controller
{
    use UploadSizeHelperTrait;

    public function __construct(
        private PlanOrder $planOrder,
        private Branch    $branch,
    )
    {}

    private function branchPlanIds(): array
    {
        return Plan::where('restaurant_id', auth('branch')->id())->pluck('id')->all();
    }

    /**
     * @return Renderable
     */
    public function dashboard(): Renderable
    {
        $planIds = $this->branchPlanIds();

        $data = $this->planOrderStatsData();

        $data['total_orders']  = (clone $this->planOrder)->whereIn('plan_id', $planIds)->count();
        $data['total_revenue'] = (clone $this->planOrder)->whereIn('plan_id', $planIds)
            ->where('status', '!=', 'cancelled')->sum('total_price');

        $data['top_plans'] = (clone $this->planOrder)
            ->with(['plan'])
            ->whereIn('plan_id', $planIds)
            ->select('plan_id', DB::raw('COUNT(id) as order_count'))
            ->groupBy('plan_id')
            ->orderByDesc('order_count')
            ->take(6)
            ->get();

        $data['recent_orders'] = (clone $this->planOrder)
            ->with(['plan', 'customer'])
            ->whereIn('plan_id', $planIds)
            ->latest()
            ->take(5)
            ->get();

        // Monthly earning chart (current year, exclude cancelled)
        $earningRaw = (clone $this->planOrder)
            ->whereIn('plan_id', $planIds)
            ->where('status', '!=', 'cancelled')
            ->select(
                DB::raw('IFNULL(SUM(total_price), 0) as sums'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )
            ->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
            ->groupBy('year', 'month')
            ->get()
            ->toArray();

        $earning = [];
        for ($i = 1; $i <= 12; $i++) {
            $earning[$i] = 0;
            foreach ($earningRaw as $row) {
                if ($row['month'] == $i) {
                    $earning[$i] = Helpers::set_price($row['sums']);
                }
            }
        }

        // Monthly order count chart (current year)
        $orderChartRaw = (clone $this->planOrder)
            ->whereIn('plan_id', $planIds)
            ->select(
                DB::raw('COUNT(id) as total'),
                DB::raw('YEAR(created_at) year, MONTH(created_at) month')
            )
            ->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
            ->groupBy('year', 'month')
            ->get()
            ->toArray();

        $order_statistics_chart = [];
        for ($i = 1; $i <= 12; $i++) {
            $order_statistics_chart[$i] = 0;
            foreach ($orderChartRaw as $row) {
                if ($row['month'] == $i) {
                    $order_statistics_chart[$i] = $row['total'];
                }
            }
        }

        // Donut chart: plan order status distribution
        $donut = [
            'pending'   => (clone $this->planOrder)->whereIn('plan_id', $planIds)->where('status', 'pending')->count(),
            'confirmed' => (clone $this->planOrder)->whereIn('plan_id', $planIds)->where('status', 'confirmed')->count(),
            'cancelled' => (clone $this->planOrder)->whereIn('plan_id', $planIds)->where('status', 'cancelled')->count(),
        ];

        return view('branch-views.dashboard', compact('data', 'earning', 'order_statistics_chart', 'donut'));
    }

    /**
     * @return Renderable
     */
    public function settings(): Renderable
    {
        return view('branch-views.settings');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsUpdate(Request $request): RedirectResponse
    {
        $check = $this->validateUploadedFile($request, ['image']);
        if ($check !== true) {
            return $check;
        }

        $request->validate([
            'name' => 'required|max:255',
            'phone' => 'required|regex:/^[0-9+\-\s]{6,20}$/',
            'image' => 'image|max:'. $this->maxImageSizeKB .'|mimes:' . implode(',', array_column(IMAGEEXTENSION, 'key')),
        ]);

        $branch = $this->branch->find(auth('branch')->id());

        if ($request->has('image')) {
            $imageName = Helpers::update('branch/', $branch->image, APPLICATION_IMAGE_FORMAT, $request->file('image'));
        } else {
            $imageName = $branch['image'];
        }

        $branch->name = $request->name;
        $branch->image = $imageName;
        $branch->phone = $request->phone;
        $branch->save();

        Toastr::success(translate('Branch updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function settingsPasswordUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|same:confirm_password|min:8|max:255',
            'confirm_password' => 'required|max:255',
        ]);

        $branch = $this->branch->find(auth('branch')->id());
        $branch->password = bcrypt($request['password']);
        $branch->save();

        Toastr::success(translate('Branch password updated successfully!'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStats(Request $request): JsonResponse
    {
        session()->put('statistics_type', $request['statistics_type']);
        $data = $this->planOrderStatsData();

        return response()->json([
            'view' => view('branch-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    /**
     * @return array
     */
    public function planOrderStatsData(): array
    {
        $planIds    = $this->branchPlanIds();
        $today      = session()->has('statistics_type') && session('statistics_type') === 'today';
        $this_month = session()->has('statistics_type') && session('statistics_type') === 'this_month';

        $base = fn () => (clone $this->planOrder)
            ->whereIn('plan_id', $planIds)
            ->when($today,      fn ($q) => $q->whereDate('created_at', Carbon::today()))
            ->when($this_month, fn ($q) => $q->whereMonth('created_at', Carbon::now()));

        return [
            'pending'   => (clone $base())->where('status', 'pending')->count(),
            'confirmed' => (clone $base())->where('status', 'confirmed')->count(),
            'cancelled' => (clone $base())->where('status', 'cancelled')->count(),
            'all'       => (clone $base())->count(),
            'revenue'   => (clone $base())->where('status', '!=', 'cancelled')->sum('total_price'),
        ];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function orderStatistics(Request $request): JsonResponse
    {
        $planIds  = $this->branchPlanIds();
        $dateType = $request->type;

        $orderData = array();
        if ($dateType == 'yearOrder') {
            $rows = (clone $this->planOrder)
                ->whereIn('plan_id', $planIds)
                ->select(DB::raw('COUNT(id) as total'), DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
                ->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
                ->groupBy('year', 'month')
                ->get()->toArray();

            for ($i = 1; $i <= 12; $i++) {
                $orderData[$i] = 0;
                foreach ($rows as $r) {
                    if ($r['month'] == $i) $orderData[$i] = $r['total'];
                }
            }
            $keyRange = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        } elseif ($dateType == 'MonthOrder') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $keyRange    = range(1, $daysInMonth);

            $rows = (clone $this->planOrder)
                ->whereIn('plan_id', $planIds)
                ->select(DB::raw('COUNT(id) as total'), DB::raw('DAY(created_at) day'))
                ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->groupBy('day')
                ->get()->toArray();

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $orderData[$i] = 0;
                foreach ($rows as $r) {
                    if ($r['day'] == $i) $orderData[$i] = $r['total'];
                }
            }

        } elseif ($dateType == 'WeekOrder') {
            $from  = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $to    = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $rows  = (clone $this->planOrder)->whereIn('plan_id', $planIds)->whereBetween('created_at', [$from, $to])->get();
            $dates = CarbonPeriod::create($from, $to)->toArray();
            $keyRange  = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            $orderData = [];
            foreach ($dates as $date) {
                $orderData[] = $rows->whereBetween('created_at', [$date, Carbon::parse($date)->endOfDay()])->count();
            }
        }

        return response()->json([
            'orders_label' => $keyRange,
            'orders'       => array_values($orderData),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function earningStatistics(Request $request): JsonResponse
    {
        $planIds  = $this->branchPlanIds();
        $dateType = $request->type;

        $earningData = array();
        if ($dateType == 'yearEarn') {
            $rows = (clone $this->planOrder)
                ->whereIn('plan_id', $planIds)
                ->where('status', '!=', 'cancelled')
                ->select(DB::raw('IFNULL(SUM(total_price),0) as sums'), DB::raw('YEAR(created_at) year, MONTH(created_at) month'))
                ->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])
                ->groupBy('year', 'month')
                ->get()->toArray();

            for ($i = 1; $i <= 12; $i++) {
                $earningData[$i] = 0;
                foreach ($rows as $r) {
                    if ($r['month'] == $i) $earningData[$i] = Helpers::set_price($r['sums']);
                }
            }
            $keyRange = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        } elseif ($dateType == 'MonthEarn') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $keyRange    = range(1, $daysInMonth);

            $rows = (clone $this->planOrder)
                ->whereIn('plan_id', $planIds)
                ->where('status', '!=', 'cancelled')
                ->select(DB::raw('IFNULL(SUM(total_price),0) as sums'), DB::raw('DAY(created_at) day'))
                ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->groupBy('day')
                ->get()->toArray();

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $earningData[$i] = 0;
                foreach ($rows as $r) {
                    if ($r['day'] == $i) $earningData[$i] = $r['sums'];
                }
            }

        } elseif ($dateType == 'WeekEarn') {
            $from  = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $to    = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $rows  = (clone $this->planOrder)->whereIn('plan_id', $planIds)->where('status', '!=', 'cancelled')->whereBetween('created_at', [$from, $to])->get();
            $dates = CarbonPeriod::create($from, $to)->toArray();
            $keyRange    = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
            $earningData = [];
            foreach ($dates as $date) {
                $earningData[] = $rows->whereBetween('created_at', [$date, Carbon::parse($date)->endOfDay()])->sum('total_price');
            }
        }

        return response()->json([
            'earning_label' => $keyRange,
            'earning'       => array_values($earningData),
        ]);
    }
}
