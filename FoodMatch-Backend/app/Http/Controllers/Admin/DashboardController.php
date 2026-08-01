<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Admin;
use App\Model\Branch;
use App\Model\Category;
use App\Model\Plan;
use App\Model\PlanOrder;
use App\User;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;

class DashboardController extends Controller
{
    public function __construct(
        private PlanOrder $planOrder,
        private Admin     $admin,
        private User      $user,
        private Plan      $plan,
        private Category  $category,
        private Branch    $branch
    ) {}

    public function dashboard(): Renderable
    {
        // Plan order metrics
        $data = $this->planOrderStatsData();

        $data['customer']      = $this->user->count();
        $data['total_plans']   = $this->plan->count();
        $data['total_orders']  = $this->planOrder->count();
        $data['category']      = $this->category->where('parent_id', 0)->count();
        $data['branch']        = $this->branch->count();
        $data['total_revenue'] = $this->planOrder->where('status', '!=', 'cancelled')->sum('total_price');

        // Top plans by order count
        $data['top_plans'] = $this->planOrder
            ->with(['plan'])
            ->select('plan_id', DB::raw('COUNT(id) as order_count'))
            ->groupBy('plan_id')
            ->orderByDesc('order_count')
            ->take(6)
            ->get();

        // Top customers by plan order count
        $data['top_customers'] = $this->planOrder
            ->with(['customer'])
            ->select('user_id', DB::raw('COUNT(id) as order_count'))
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderByDesc('order_count')
            ->take(6)
            ->get();

        // Recent 5 plan orders
        $data['recent_orders'] = $this->planOrder
            ->with(['plan', 'customer'])
            ->latest()
            ->take(5)
            ->get();

        // Monthly earning chart (current year, exclude cancelled)
        $earningRaw = $this->planOrder
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
        $orderChartRaw = $this->planOrder
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
            'pending'   => $this->planOrder->where('status', 'pending')->count(),
            'confirmed' => $this->planOrder->where('status', 'confirmed')->count(),
            'cancelled' => $this->planOrder->where('status', 'cancelled')->count(),
        ];

        return view('admin-views.dashboard', compact('data', 'earning', 'order_statistics_chart', 'donut'));
    }

    public function orderStats(Request $request): JsonResponse
    {
        session()->put('statistics_type', $request['statistics_type']);
        $data = $this->planOrderStatsData();

        return response()->json([
            'view' => view('admin-views.partials._dashboard-order-stats', compact('data'))->render()
        ], 200);
    }

    public function planOrderStatsData(): array
    {
        $today      = session()->has('statistics_type') && session('statistics_type') === 'today';
        $this_month = session()->has('statistics_type') && session('statistics_type') === 'this_month';

        $base = fn () => $this->planOrder
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

    public function orderStatistics(Request $request): JsonResponse
    {
        $dateType  = $request->type;
        $orderData = [];

        if ($dateType === 'yearOrder') {
            $rows = $this->planOrder
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

        } elseif ($dateType === 'MonthOrder') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $keyRange    = range(1, $daysInMonth);

            $rows = $this->planOrder
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

        } elseif ($dateType === 'WeekOrder') {
            $from   = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $to     = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $rows   = $this->planOrder->whereBetween('created_at', [$from, $to])->get();
            $dates  = CarbonPeriod::create($from, $to)->toArray();
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

    public function earningStatistics(Request $request): JsonResponse
    {
        $dateType    = $request->type;
        $earningData = [];

        if ($dateType === 'yearEarn') {
            $rows = $this->planOrder
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

        } elseif ($dateType === 'MonthEarn') {
            $daysInMonth = Carbon::now()->daysInMonth;
            $keyRange    = range(1, $daysInMonth);

            $rows = $this->planOrder
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

        } elseif ($dateType === 'WeekEarn') {
            $from  = Carbon::now()->startOfWeek(Carbon::SUNDAY);
            $to    = Carbon::now()->endOfWeek(Carbon::SATURDAY);
            $rows  = $this->planOrder->where('status', '!=', 'cancelled')->whereBetween('created_at', [$from, $to])->get();
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
