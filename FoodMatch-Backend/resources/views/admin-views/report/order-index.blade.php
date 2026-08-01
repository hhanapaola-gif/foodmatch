@extends('layouts.admin.app')

@section('title', 'Informe de Pedidos')

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{ asset('assets/admin/img/icons/order_report.png') }}" alt="">
                <span class="page-header-title">Informe de Pedidos de Planes</span>
            </h2>
            <div class="ml-auto">
                <a href="{{ route('admin.plan-order.list') }}" class="btn btn-outline-primary btn-sm">
                    <i class="tio-receipt mr-1"></i> Ver todos los pedidos
                </a>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="row g-2 mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body h-100 text-center">
                    <h3 class="mb-0">{{ $total }}</h3>
                    <small class="text-muted">Total de Pedidos ({{ date('Y') }})</small>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body h-100 text-center">
                    <h3 class="mb-0 text-success">{{ $confirmed }}</h3>
                    <small class="text-muted">Confirmados</small>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body h-100 text-center">
                    <h3 class="mb-0 text-warning">{{ $pending }}</h3>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body h-100 text-center">
                    <h3 class="mb-0 text-danger">{{ $cancelled }}</h3>
                    <small class="text-muted">Cancelados</small>
                </div>
            </div>
        </div>

        {{-- Monthly chart --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0">Pedidos mensuales — {{ date('Y') }}</h6>
                <a href="{{ route('admin.plan-order.list') }}" class="btn btn-sm btn-white">
                    <i class="tio-receipt mr-1"></i> Ver pedidos
                </a>
            </div>
            <div class="card-body">
                <div class="chartjs-custom" style="height:300px">
                    <canvas id="orderReportChart"></canvas>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-3 justify-content-center">
                    <span><span style="display:inline-block;width:12px;height:12px;background:green;border-radius:2px;margin-right:4px"></span>Confirmados</span>
                    <span><span style="display:inline-block;width:12px;height:12px;background:#ec9a3c;border-radius:2px;margin-right:4px"></span>Pendientes</span>
                    <span><span style="display:inline-block;width:12px;height:12px;background:darkred;border-radius:2px;margin-right:4px"></span>Cancelados</span>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('script_2')
    <script src="{{ asset('assets/admin') }}/vendor/chart.js/dist/Chart.min.js"></script>
    <script>
        "use strict";
        var ctx = document.getElementById('orderReportChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                datasets: [
                    {
                        label: 'Confirmados',
                        data: [
                            {{ $monthly['confirmed'][1] }}, {{ $monthly['confirmed'][2] }},
                            {{ $monthly['confirmed'][3] }}, {{ $monthly['confirmed'][4] }},
                            {{ $monthly['confirmed'][5] }}, {{ $monthly['confirmed'][6] }},
                            {{ $monthly['confirmed'][7] }}, {{ $monthly['confirmed'][8] }},
                            {{ $monthly['confirmed'][9] }}, {{ $monthly['confirmed'][10] }},
                            {{ $monthly['confirmed'][11] }}, {{ $monthly['confirmed'][12] }}
                        ],
                        borderColor: 'green',
                        backgroundColor: 'rgba(0,128,0,0.05)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                    },
                    {
                        label: 'Pendientes',
                        data: [
                            {{ $monthly['pending'][1] }}, {{ $monthly['pending'][2] }},
                            {{ $monthly['pending'][3] }}, {{ $monthly['pending'][4] }},
                            {{ $monthly['pending'][5] }}, {{ $monthly['pending'][6] }},
                            {{ $monthly['pending'][7] }}, {{ $monthly['pending'][8] }},
                            {{ $monthly['pending'][9] }}, {{ $monthly['pending'][10] }},
                            {{ $monthly['pending'][11] }}, {{ $monthly['pending'][12] }}
                        ],
                        borderColor: '#ec9a3c',
                        backgroundColor: 'rgba(236,154,60,0.05)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                    },
                    {
                        label: 'Cancelados',
                        data: [
                            {{ $monthly['cancelled'][1] }}, {{ $monthly['cancelled'][2] }},
                            {{ $monthly['cancelled'][3] }}, {{ $monthly['cancelled'][4] }},
                            {{ $monthly['cancelled'][5] }}, {{ $monthly['cancelled'][6] }},
                            {{ $monthly['cancelled'][7] }}, {{ $monthly['cancelled'][8] }},
                            {{ $monthly['cancelled'][9] }}, {{ $monthly['cancelled'][10] }},
                            {{ $monthly['cancelled'][11] }}, {{ $monthly['cancelled'][12] }}
                        ],
                        borderColor: 'darkred',
                        backgroundColor: 'rgba(139,0,0,0.05)',
                        borderWidth: 2,
                        pointRadius: 3,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true, precision: 0 },
                        gridLines: { color: '#e7eaf3', drawBorder: false }
                    }],
                    xAxes: [{
                        gridLines: { display: false }
                    }]
                },
                tooltips: { mode: 'index', intersect: false },
                legend: { display: false }
            }
        });
    </script>
@endpush
