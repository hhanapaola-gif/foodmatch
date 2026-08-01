@extends('layouts.admin.app')

@section('title', 'Informe de Ganancias')

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{ asset('assets/admin/img/icons/online-survey.png') }}" alt="">
                <span class="page-header-title">Informe de Ganancias — Planes</span>
            </h2>
        </div>

        {{-- Header info card --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="media flex-column flex-sm-row flex-wrap align-items-sm-center gap-4">
                    <div class="avatar avatar-xl">
                        <img class="avatar-img" src="{{ asset('assets/admin') }}/svg/illustrations/earnings.png" alt="ganancias">
                    </div>
                    <div class="media-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                            <div>
                                <h2 class="page-header-title mb-2">Resumen de Ganancias por Planes</h2>
                                <div class="mb-1">
                                    <span>Administrador:</span>
                                    <a href="#">{{ auth('admin')->user()->f_name . ' ' . auth('admin')->user()->l_name }}</a>
                                </div>
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span>Período:</span>
                                    <span>({{ $from->format('Y-m-d') }} — {{ $to->format('Y-m-d') }})</span>
                                </div>
                            </div>
                            <div>
                                <a class="btn btn-icon btn-primary px-2 rounded-circle" href="{{ route('admin.dashboard') }}">
                                    <i class="tio-home-outlined"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Date filter --}}
        <div class="card mb-3">
            <div class="card-body">
                <form action="{{ url()->current() }}" method="get">
                    <div class="row g-2 align-items-end">
                        <div class="col-12">
                            <h4 class="form-label mb-2">Filtrar por rango de fechas</h4>
                        </div>
                        <div class="col-sm-4">
                            <input type="date" name="from" value="{{ $startDate }}" class="form-control" required>
                        </div>
                        <div class="col-sm-4">
                            <input type="date" name="to" value="{{ $endDate }}" class="form-control" required>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-primary btn-block">Mostrar</button>
                        </div>
                    </div>
                </form>

                {{-- Summary metrics --}}
                <div class="row g-2 mt-3">
                    <div class="col-sm-6">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="media">
                                    <i class="tio-dollar-outlined nav-icon mr-3"></i>
                                    <div class="media-body">
                                        <h4 class="mb-1">Ingresos por planes</h4>
                                        <span class="font-size-sm text-success">
                                            <i class="tio-trending-up"></i>
                                            {{ \App\CentralLogics\Helpers::set_symbol($total_sold) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card card-sm">
                            <div class="card-body">
                                <div class="media">
                                    <i class="tio-receipt nav-icon mr-3"></i>
                                    <div class="media-body">
                                        <h4 class="mb-1">Pedidos confirmados</h4>
                                        <span class="font-size-sm text-success">
                                            <i class="tio-trending-up"></i>
                                            {{ \App\Model\PlanOrder::whereBetween('created_at', [$from, $to])->where('status', 'confirmed')->count() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Annual chart --}}
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between flex-wrap gap-2">
                <h6 class="mb-0">
                    Ingresos totales ({{ date('Y') }}):
                    <span class="h4 mb-0 ml-1">
                        {{ \App\CentralLogics\Helpers::set_symbol(
                            \App\Model\PlanOrder::whereBetween('created_at', [date('Y-01-01'), date('Y-12-31')])->sum('total_price')
                        ) }}
                    </span>
                </h6>
                <a href="{{ route('admin.plan-order.list') }}" class="btn btn-white btn-sm">
                    <i class="tio-receipt mr-1"></i> Ver pedidos
                </a>
            </div>
            <div class="card-body">
                <div style="height:360px">
                    <canvas id="earningChart"></canvas>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script_2')
    <script src="{{ asset('assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script>
        "use strict";
        var ctx = document.getElementById('earningChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                datasets: [{
                    label: 'Ingresos',
                    data: [
                        {{ $monthly_earning[1] }}, {{ $monthly_earning[2] }},
                        {{ $monthly_earning[3] }}, {{ $monthly_earning[4] }},
                        {{ $monthly_earning[5] }}, {{ $monthly_earning[6] }},
                        {{ $monthly_earning[7] }}, {{ $monthly_earning[8] }},
                        {{ $monthly_earning[9] }}, {{ $monthly_earning[10] }},
                        {{ $monthly_earning[11] }}, {{ $monthly_earning[12] }}
                    ],
                    borderColor: 'green',
                    backgroundColor: 'rgba(0,128,0,0.08)',
                    borderWidth: 2,
                    pointRadius: 3,
                    fill: true,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            max: {{ $max_earning }},
                            fontColor: '#97a4af',
                        },
                        gridLines: { color: '#e7eaf3', drawBorder: false }
                    }],
                    xAxes: [{
                        gridLines: { display: false },
                        ticks: { fontColor: '#97a4af' }
                    }]
                },
                tooltips: { mode: 'index', intersect: false },
                legend: { display: false }
            }
        });
    </script>
@endpush
