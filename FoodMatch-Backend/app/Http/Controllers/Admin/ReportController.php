<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\OrderDetail;
use App\Model\PlanOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Renderable;

class ReportController extends Controller
{
    public function __construct(
        private Order       $order,
        private OrderDetail $orderDetail,
    )
    {
    }

    public function orderIndex(): Renderable
    {
        $total     = PlanOrder::count();
        $confirmed = PlanOrder::where('status', 'confirmed')->count();
        $cancelled = PlanOrder::where('status', 'cancelled')->count();
        $pending   = PlanOrder::where('status', 'pending')->count();

        $monthly = ['confirmed' => [], 'cancelled' => [], 'pending' => []];
        for ($i = 1; $i <= 12; $i++) {
            $f = date('Y-' . $i . '-01');
            $t = date('Y-' . $i . '-' . date('t', mktime(0, 0, 0, $i, 1)));
            $monthly['confirmed'][$i] = PlanOrder::where('status', 'confirmed')->whereBetween('created_at', [$f, $t])->count();
            $monthly['cancelled'][$i] = PlanOrder::where('status', 'cancelled')->whereBetween('created_at', [$f, $t])->count();
            $monthly['pending'][$i]   = PlanOrder::where('status', 'pending')->whereBetween('created_at', [$f, $t])->count();
        }

        return view('admin-views.report.order-index', compact('total', 'confirmed', 'cancelled', 'pending', 'monthly'));
    }

    public function earningIndex(Request $request): Renderable
    {
        $from = $request->from
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();
        $to = $request->to
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfDay();

        $startDate = $request->from ?? $from->format('Y-m-d');
        $endDate   = $request->to   ?? $to->format('Y-m-d');

        session()->put('from_date', $from);
        session()->put('to_date',   $to);

        $total_sold = PlanOrder::where('status', '!=', 'cancelled')
            ->whereBetween('created_at', [$from, $to])->sum('total_price');

        $monthly_earning = [];
        for ($i = 1; $i <= 12; $i++) {
            $f = date('Y-' . $i . '-01');
            $t = date('Y-' . $i . '-' . date('t', mktime(0, 0, 0, $i, 1)));
            $monthly_earning[$i] = Helpers::set_price(
                PlanOrder::where('status', '!=', 'cancelled')
                    ->whereBetween('created_at', [$f, $t])->sum('total_price')
            );
        }

        $max_earning = max(array_merge([1], $monthly_earning));

        return view('admin-views.report.earning-index', compact(
            'total_sold', 'from', 'to', 'startDate', 'endDate', 'monthly_earning', 'max_earning'
        ));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function setDate(Request $request): RedirectResponse
    {
        $fromDate = Carbon::parse($request['from'])->startOfDay();
        $toDate = Carbon::parse($request['to'])->endOfDay();

        session()->put('from_date', $fromDate);
        session()->put('to_date', $toDate);

        return back();
    }

    /**
     * @return Renderable
     */
    public function deliverymanReport(): Renderable
    {
        $orders = $this->order->with(['customer', 'branch'])->paginate(25);
        return view('admin-views.report.driver-index', compact('orders'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function deliverymanFilter(Request $request): JsonResponse
    {
        $fromDate = Carbon::parse($request->formDate)->startOfDay();
        $toDate = Carbon::parse($request->toDate)->endOfDay();

        $orders = $this->order
            ->where(['delivery_man_id' => $request['delivery_man']])
            ->where(['order_status' => 'delivered'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        return response()->json([
            'view' => view('admin-views.order.partials._table', compact('orders'))->render(),
            'delivered_qty' => $orders->count()
        ]);
    }

    /**
     * @return Renderable
     */
    public function productReport(): Renderable
    {
        return view('admin-views.report.product-report');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function productReportFilter(Request $request): JsonResponse
    {
        $fromDate = Carbon::parse($request->from)->startOfDay();
        $toDate = Carbon::parse($request->to)->endOfDay();

        $orders = $this->order->when($request['branch_id'] != 'all', function ($query) use ($request) {
            $query->where('branch_id', $request['branch_id']);
        })
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->latest()
            ->get();

        $data = [];
        $totalSold = 0;
        $totalQuantity = 0;
        foreach ($orders as $order) {
            foreach ($order->details as $detail) {
                if ($request['product_id'] != 'all') {
                    if ($detail['product_id'] == $request['product_id']) {
                        $price = Helpers::variation_price(json_decode($detail->product_details, true), $detail['variations']) - $detail['discount_on_product'];
                        $orderTotal = $price * $detail['quantity'];
                        $data[] = [
                            'order_id' => $order['id'],
                            'date' => $order['created_at'],
                            'customer' => $order->customer,
                            'price' => $orderTotal,
                            'quantity' => $detail['quantity'],
                        ];
                        $totalSold += $orderTotal;
                        $totalQuantity += $detail['quantity'];
                    }

                } else {
                    $price = Helpers::variation_price(json_decode($detail->product_details, true), $detail['variations']) - $detail['discount_on_product'];
                    $orderTotal = $price * $detail['quantity'];
                    $data[] = [
                        'order_id' => $order['id'],
                        'date' => $order['created_at'],
                        'customer' => $order->customer,
                        'price' => $orderTotal,
                        'quantity' => $detail['quantity'],
                    ];
                    $totalSold += $orderTotal;
                    $totalQuantity += $detail['quantity'];
                }
            }
        }

        session()->put('export_data', $data);

        return response()->json([
            'order_count' => count($data),
            'item_qty' => $totalQuantity,
            'order_sum' => Helpers::set_symbol($totalSold),
            'view' => view('admin-views.report.partials._table', compact('data'))->render(),
        ]);
    }

    /**
     * @return mixed
     */
    public function exportProductReport(): mixed
    {
        if (session()->has('export_data')) {
            $data = session('export_data');

        } else {
            $orders = $this->order->all();
            $data = [];
            $totalSold = 0;
            $totalQuantity = 0;
            foreach ($orders as $order) {
                foreach ($order->details as $detail) {
                    $price = Helpers::variation_price(json_decode($detail->product_details, true), $detail['variations']) - $detail['discount_on_product'];
                    $orderTotal = $price * $detail['quantity'];
                    $data[] = [
                        'order_id' => $order['id'],
                        'date' => $order['created_at'],
                        'customer' => $order->customer,
                        'price' => $orderTotal,
                        'quantity' => $detail['quantity'],
                    ];
                    $totalSold += $orderTotal;
                    $totalQuantity += $detail['quantity'];
                }
            }
        }

        $pdf = Pdf::loadView('admin-views.report.partials._report', compact('data'));
        return $pdf->download('report_' . rand(00001, 99999) . '.pdf');
    }

    /**
     * @return Application|Factory|View
     */
    public function saleReport(): Factory|View|Application
    {
        return view('admin-views.report.sale-report');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function saleFilter(Request $request): JsonResponse
    {
        $fromDate = Carbon::parse($request->from)->startOfDay();
        $toDate = Carbon::parse($request->to)->endOfDay();

        if ($request['branch_id'] == 'all') {
            $orders = $this->order->whereBetween('created_at', [$fromDate, $toDate])->pluck('id')->toArray();

        } else {
            $orders = $this->order
                ->where(['branch_id' => $request['branch_id']])
                ->whereBetween('created_at', [$fromDate, $toDate])
                ->pluck('id')
                ->toArray();
        }

        $data = [];
        $totalSold = 0;
        $totalQuantity = 0;

        foreach ($this->orderDetail->whereIn('order_id', $orders)->latest()->get() as $detail) {
            $price = $detail['price'] - $detail['discount_on_product'];
            $orderTotal = $price * $detail['quantity'];
            $data[] = [
                'order_id' => $detail['order_id'],
                'date' => $detail['created_at'],
                'price' => $orderTotal,
                'quantity' => $detail['quantity'],
            ];
            $totalSold += $orderTotal;
            $totalQuantity += $detail['quantity'];
        }

        return response()->json([
            'order_count' => count($data),
            'item_qty' => $totalQuantity,
            'order_sum' => Helpers::set_symbol($totalSold),
            'view' => view('admin-views.report.partials._table', compact('data'))->render(),
        ]);
    }

    /**
     * @return mixed
     */
    public function exportSaleReport(): mixed
    {
        $data = session('export_sale_data');
        $pdf = Pdf::loadView('admin-views.report.partials._report', compact('data'));

        return $pdf->download('sale_report_' . rand(00001, 99999) . '.pdf');
    }

    public function planReport(Request $request): Renderable
    {
        $from   = $request->from   ? Carbon::parse($request->from)->startOfDay()   : Carbon::now()->startOfMonth();
        $to     = $request->to     ? Carbon::parse($request->to)->endOfDay()       : Carbon::now()->endOfDay();
        $status = $request->status ?? '';

        $orders = PlanOrder::with(['plan', 'plan.category'])
            ->whereBetween('created_at', [$from, $to])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->appends($request->only(['from', 'to', 'status']));

        $totalRevenue = PlanOrder::whereBetween('created_at', [$from, $to])
            ->when($status,
                fn ($q) => $q->where('status', $status),
                fn ($q) => $q->where('status', '!=', 'cancelled')
            )
            ->sum('total_price');

        return view('admin-views.report.plan-report', compact('orders', 'totalRevenue', 'from', 'to', 'status'));
    }

    public function exportPlanReport(Request $request): mixed
    {
        $from   = $request->from   ? Carbon::parse($request->from)->startOfDay()   : Carbon::now()->startOfMonth();
        $to     = $request->to     ? Carbon::parse($request->to)->endOfDay()       : Carbon::now()->endOfDay();
        $status = $request->status ?? '';

        $orders = PlanOrder::with(['plan', 'plan.category'])
            ->whereBetween('created_at', [$from, $to])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->get();

        $totalRevenue = PlanOrder::whereBetween('created_at', [$from, $to])
            ->when($status,
                fn ($q) => $q->where('status', $status),
                fn ($q) => $q->where('status', '!=', 'cancelled')
            )
            ->sum('total_price');

        $pdf = Pdf::loadView(
            'admin-views.report.partials._plan-report-pdf',
            compact('orders', 'totalRevenue', 'from', 'to', 'status')
        )->setPaper('a4', 'landscape');

        return $pdf->download('informe_planes_' . $from->format('Ymd') . '_' . $to->format('Ymd') . '.pdf');
    }
}
