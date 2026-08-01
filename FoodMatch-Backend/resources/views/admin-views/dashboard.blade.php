@extends('layouts.admin.app')

@section('title', 'Panel de control')

@push('css_or_js')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" src="{{asset('assets/admin')}}/vendor/apex/apexcharts.css"></link>
@endpush

@section('content')
    <div class="content container-fluid">

        {{-- Welcome header --}}
        <div class="mb-4">
            <h1 class="page-header-title c1">Bienvenido, {{ auth('admin')->user()->f_name }}.</h1>
            <p class="text-dark font-weight-semibold">Panel de control — FoodMatch</p>
        </div>

        {{-- Business Analytics --}}
        <div class="card card-body mb-3">
            <div class="row justify-content-between align-items-center g-2 mb-3">
                <div class="col-auto">
                    <h4 class="d-flex align-items-center gap-10 mb-0">
                        <img width="20" class="avatar-img rounded-0"
                             src="{{asset('assets/admin/img/icons/business_analytics.png')}}" alt="">
                        Análisis del Negocio
                    </h4>
                </div>
                <div class="col-auto">
                    <select class="custom-select min-w200" name="statistics_type"
                            onchange="order_stats_update(this.value)">
                        <option value="overall" {{ session('statistics_type') === 'overall' ? 'selected' : '' }}>
                            Estadísticas Generales
                        </option>
                        <option value="today" {{ session('statistics_type') === 'today' ? 'selected' : '' }}>
                            Estadísticas de Hoy
                        </option>
                        <option value="this_month" {{ session('statistics_type') === 'this_month' ? 'selected' : '' }}>
                            Estadísticas del Mes
                        </option>
                    </select>
                </div>
            </div>
            <div class="row g-2" id="order_stats">
                @include('admin-views.partials._dashboard-order-stats', ['data' => $data])
            </div>
        </div>

        {{-- Summary row --}}
        <div class="row g-2 mb-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card card-body h-100 text-center">
                    <div class="avatar avatar-lg avatar-soft-primary avatar-circle mx-auto mb-2">
                        <span class="avatar-initials"><i class="tio-receipt"></i></span>
                    </div>
                    <h3 class="mb-0">{{ $data['total_orders'] }}</h3>
                    <small class="text-muted">Total de Pedidos</small>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-body h-100 text-center">
                    <div class="avatar avatar-lg avatar-soft-success avatar-circle mx-auto mb-2">
                        <span class="avatar-initials"><i class="tio-calendar-month"></i></span>
                    </div>
                    <h3 class="mb-0">{{ $data['total_plans'] }}</h3>
                    <small class="text-muted">Planes Disponibles</small>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-body h-100 text-center">
                    <div class="avatar avatar-lg avatar-soft-warning avatar-circle mx-auto mb-2">
                        <span class="avatar-initials"><i class="tio-poi-user"></i></span>
                    </div>
                    <h3 class="mb-0">{{ $data['customer'] }}</h3>
                    <small class="text-muted">Clientes Registrados</small>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card card-body h-100 text-center">
                    <div class="avatar avatar-lg avatar-soft-info avatar-circle mx-auto mb-2">
                        <span class="avatar-initials"><i class="tio-dollar-outlined"></i></span>
                    </div>
                    <h3 class="mb-0" style="font-size:1.3rem">
                        {{ \App\CentralLogics\Helpers::set_symbol($data['total_revenue']) }}
                    </h3>
                    <small class="text-muted">Ingresos Totales</small>
                </div>
            </div>
        </div>

        {{-- Charts grid --}}
        <div class="grid-chart mb-3">

            {{-- Pedidos por período --}}
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap gap-2 align-items-center">
                        <h4 class="d-flex align-items-center gap-10 mb-0">
                            <img width="20" class="avatar-img rounded-0"
                                 src="{{asset('assets/admin/img/icons/earning_statistics.png')}}" alt="">
                            Pedidos de Planes
                        </h4>
                        <ul class="option-select-btn">
                            <li>
                                <label>
                                    <input type="radio" name="statistics" hidden checked>
                                    <span data-order-type="yearOrder" onclick="orderStatisticsUpdate(this)">Este año</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics" hidden>
                                    <span data-order-type="MonthOrder" onclick="orderStatisticsUpdate(this)">Este mes</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics" hidden>
                                    <span data-order-type="WeekOrder" onclick="orderStatisticsUpdate(this)">Esta semana</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div id="updatingOrderData" class="custom-chart mt-2">
                        <div id="order-statistics-line-chart"></div>
                    </div>
                </div>
            </div>

            {{-- Donut: plan order status --}}
            <div class="card h-100 order-last order-lg-0">
                <div class="card-header">
                    <h4 class="mb-0">Estado de Pedidos</h4>
                </div>
                <div class="card-body">
                    <div class="mt-2">
                        <div class="position-relative pie-chart">
                            <div id="dognut-pie"></div>
                            <div class="total--orders">
                                <h3>{{ $donut['pending'] + $donut['confirmed'] + $donut['cancelled'] }}</h3>
                                <span>Pedidos</span>
                            </div>
                        </div>
                        <div class="apex-legends">
                            <div class="before-bg-pending">
                                <span>Pendientes ({{ $donut['pending'] }})</span>
                            </div>
                            <div class="before-bg-ongoing">
                                <span>Confirmados ({{ $donut['confirmed'] }})</span>
                            </div>
                            <div class="before-bg-17202A">
                                <span>Cancelados ({{ $donut['cancelled'] }})</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ingresos por período --}}
            <div class="card h100">
                <div class="card-body">
                    <div class="d-flex justify-content-between flex-wrap gap-2 align-items-center">
                        <h4 class="d-flex align-items-center gap-10 mb-0">
                            <img width="20" class="avatar-img rounded-0"
                                 src="{{asset('assets/admin/img/icons/earning_statistics.png')}}" alt="">
                            Ingresos por Planes
                        </h4>
                        <ul class="option-select-btn">
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden checked>
                                    <span data-earn-type="yearEarn" onclick="earningStatisticsUpdate(this)">Este año</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden>
                                    <span data-earn-type="MonthEarn" onclick="earningStatisticsUpdate(this)">Este mes</span>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="statistics2" hidden>
                                    <span data-earn-type="WeekEarn" onclick="earningStatisticsUpdate(this)">Esta semana</span>
                                </label>
                            </li>
                        </ul>
                    </div>
                    <div id="updatingData" class="custom-chart mt-2">
                        <div id="line-adwords"></div>
                    </div>
                </div>
            </div>

            {{-- Pedidos recientes --}}
            <div class="card h100 recent-orders">
                <div class="card-header d-flex justify-content-between gap-10">
                    <h5 class="mb-0">Pedidos Recientes</h5>
                    <a href="{{ route('admin.plan-order.list') }}" class="btn-link">Ver todos</a>
                </div>
                <div class="card-body">
                    <ul class="common-list">
                        @foreach($data['recent_orders'] as $recent)
                            <li class="pt-0 d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                <div class="order-info">
                                    <h5>
                                        <a href="{{ route('admin.plan-order.show', $recent->id) }}" class="text-dark">
                                            Pedido #{{ $recent->id }}
                                        </a>
                                    </h5>
                                    <p>{{ $recent->created_at?->format('d/m/Y H:i') }}</p>
                                    @if($recent->customer)
                                        <small class="text-muted">
                                            {{ trim($recent->customer->f_name . ' ' . $recent->customer->l_name) }}
                                        </small>
                                    @endif
                                </div>
                                @php
                                    $sc = match($recent->status) {
                                        'confirmed' => 'text-success',
                                        'cancelled' => 'text-danger',
                                        default     => 'text-warning',
                                    };
                                    $sl = match($recent->status) {
                                        'pending'   => 'Pendiente',
                                        'confirmed' => 'Confirmado',
                                        'cancelled' => 'Cancelado',
                                        default     => ucfirst($recent->status),
                                    };
                                @endphp
                                <span class="status {{ $sc }}">{{ $sl }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>

        {{-- Top plans & top customers --}}
        <div class="row g-2">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Planes más pedidos</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($data['top_plans'] as $tp)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $tp->plan?->title ?? 'Plan #'.$tp->plan_id }}</span>
                                    <span class="badge badge-soft-primary badge-pill">{{ $tp->order_count }} pedidos</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center py-4">Sin datos aún</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="mb-0">Clientes más activos</h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($data['top_customers'] as $tc)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        @if($tc->customer)
                                            {{ trim($tc->customer->f_name . ' ' . $tc->customer->l_name) ?: $tc->customer->phone ?? 'Cliente #'.$tc->user_id }}
                                        @else
                                            Cliente #{{ $tc->user_id }}
                                        @endif
                                    </span>
                                    <span class="badge badge-soft-success badge-pill">{{ $tc->order_count }} pedidos</span>
                                </li>
                            @empty
                                <li class="list-group-item text-muted text-center py-4">Sin datos aún</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script')
    <script src="{{asset('assets/admin')}}/vendor/chart.js/dist/Chart.min.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chart.js.extensions/chartjs-extensions.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/chartjs-plugin-datalabels/dist/chartjs-plugin-datalabels.min.js"></script>
    <script src="{{asset('assets/admin')}}/vendor/apex/apexcharts.min.js"></script>
@endpush

@push('script_2')
<script>
    // Order statistics line chart (initial render — current year)
    var OSDCoptions = {
        chart: { height: 328, type: 'line', zoom: { enabled: false }, toolbar: { show: false } },
        stroke: { curve: 'smooth', width: 3 },
        colors: ['rgba(255, 111, 112, 0.5)', '#107980'],
        series: [{ name: "Pedidos",
            data: [
                {{$order_statistics_chart[1]}}, {{$order_statistics_chart[2]}}, {{$order_statistics_chart[3]}},
                {{$order_statistics_chart[4]}}, {{$order_statistics_chart[5]}}, {{$order_statistics_chart[6]}},
                {{$order_statistics_chart[7]}}, {{$order_statistics_chart[8]}}, {{$order_statistics_chart[9]}},
                {{$order_statistics_chart[10]}}, {{$order_statistics_chart[11]}}, {{$order_statistics_chart[12]}}
            ]
        }],
        markers: { size: 2, strokeWidth: 0, hover: { size: 5 } },
        grid: { show: true, padding: { bottom: 0 }, borderColor: "rgba(180,208,224,0.5)", strokeDashArray: 7,
                xaxis: { lines: { show: true } } },
        labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        xaxis: { tooltip: { enabled: false } },
        legend: { show: false }
    };
    var chartLine = new ApexCharts(document.querySelector('#order-statistics-line-chart'), OSDCoptions);
    chartLine.render();
</script>

<script>
    // Donut chart
    var options = {
        series: [{{ $donut['pending'] }}, {{ $donut['confirmed'] }}, {{ $donut['cancelled'] }}],
        chart: { width: 256, type: 'donut' },
        labels: ['Pendientes', 'Confirmados', 'Cancelados'],
        dataLabels: { enabled: false },
        colors: ['#FF6F70', '#27AE60', '#17202A'],
        fill:   { colors: ['#FF6F70', '#27AE60', '#17202A'] },
        legend: { show: false },
        responsive: [{ breakpoint: 1650, options: { chart: { width: 250 } } }],
    };
    var chart = new ApexCharts(document.querySelector("#dognut-pie"), options);
    chart.render();
</script>

<script>
    // Earning line chart (initial render — current year)
    var earningOptions = {
        chart: { height: 328, type: 'line', zoom: { enabled: false }, toolbar: { show: false } },
        stroke: { curve: 'straight', width: 3 },
        colors: ['rgba(255, 111, 112, 0.5)', '#107980'],
        series: [{ name: "Ingresos",
            data: [
                {{$earning[1]}}, {{$earning[2]}}, {{$earning[3]}}, {{$earning[4]}},
                {{$earning[5]}}, {{$earning[6]}}, {{$earning[7]}}, {{$earning[8]}},
                {{$earning[9]}}, {{$earning[10]}}, {{$earning[11]}}, {{$earning[12]}}
            ]
        }],
        markers: { size: 2, strokeWidth: 0, hover: { size: 5 } },
        grid: { show: true, padding: { bottom: 0 }, borderColor: "rgba(180,208,224,0.5)", strokeDashArray: 7,
                xaxis: { lines: { show: true } } },
        labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        xaxis: { tooltip: { enabled: false } },
        legend: { show: false }
    };
    var chartLine2 = new ApexCharts(document.querySelector('#line-adwords'), earningOptions);
    chartLine2.render();
</script>

<script>
    // AJAX: update stats filter
    function order_stats_update(type) {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
        $.ajax({
            url: "{{route('admin.order-stats')}}",
            type: "post",
            data: { statistics_type: type },
            beforeSend: function () { $('#loading').show() },
            success: function (data) { $('#order_stats').html(data.view) },
            complete: function () { $('#loading').hide() }
        });
    }

    // AJAX: update order statistics chart
    function orderStatisticsUpdate(t) {
        let value = $(t).attr('data-order-type');
        $.ajax({
            url: '{{route('admin.order-statistics')}}',
            type: 'GET',
            data: { type: value },
            beforeSend: function () { $('#loading').show() },
            success: function (response_data) {
                document.getElementById("order-statistics-line-chart").remove();
                let graph = document.createElement('div');
                graph.setAttribute("id", "order-statistics-line-chart");
                document.getElementById("updatingOrderData").appendChild(graph);

                var opts = {
                    series: [{ name: "Pedidos", data: response_data.orders }],
                    chart: { height: 316, type: 'line', zoom: { enabled: false }, toolbar: { show: false } },
                    dataLabels: { enabled: false },
                    colors: ['rgba(255, 111, 112, 0.5)', '#107980'],
                    stroke: { curve: 'smooth', width: 3 },
                    xaxis: { categories: response_data.orders_label },
                    grid: { show: true, padding: { bottom: 0 }, borderColor: "rgba(180,208,224,0.5)", strokeDashArray: 7 },
                    yaxis: { tickAmount: 4 }
                };
                var c = new ApexCharts(document.querySelector("#order-statistics-line-chart"), opts);
                c.render();
            },
            complete: function () { $('#loading').hide() }
        });
    }

    // AJAX: update earning chart
    function earningStatisticsUpdate(t) {
        let value = $(t).attr('data-earn-type');
        $.ajax({
            url: '{{route('admin.earning-statistics')}}',
            type: 'GET',
            data: { type: value },
            beforeSend: function () { $('#loading').show() },
            success: function (response_data) {
                document.getElementById("line-adwords").remove();
                let graph = document.createElement('div');
                graph.setAttribute("id", "line-adwords");
                document.getElementById("updatingData").appendChild(graph);

                var opts = {
                    chart: { height: 328, type: 'line', zoom: { enabled: false }, toolbar: { show: false } },
                    stroke: { curve: 'straight', width: 2 },
                    colors: ['rgba(255, 111, 112, 0.5)', '#107980'],
                    series: [{ name: "Ingresos", data: response_data.earning }],
                    markers: { size: 6, strokeWidth: 0, hover: { size: 9 } },
                    grid: { show: true, padding: { bottom: 0 }, borderColor: "rgba(180,208,224,0.5)", strokeDashArray: 7 },
                    labels: response_data.earning_label,
                    xaxis: { tooltip: { enabled: false } },
                    legend: { position: 'top', horizontalAlign: 'right', offsetY: -20 }
                };
                var c = new ApexCharts(document.querySelector('#line-adwords'), opts);
                c.render();
            },
            complete: function () { $('#loading').hide() }
        });
    }
</script>
@endpush
