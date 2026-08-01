@extends('layouts.admin.app')

@section('title', 'Informe de Planes')

@section('content')
    <div class="content container-fluid">

        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="tio-chart-bar-1 nav-icon" style="font-size:24px"></i>
                <span class="page-header-title">Informe de Planes</span>
            </h2>
            <a href="{{ route('admin.report.export-plan-report', array_filter(['from' => request('from'), 'to' => request('to'), 'status' => request('status')])) }}"
               class="btn btn-danger btn-sm ms-auto">
                <i class="tio-download"></i> Descargar PDF
            </a>
        </div>

        {{-- Filters --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="input-group w-auto">
                        <input type="date" name="from" class="form-control"
                               value="{{ request('from', $from->format('Y-m-d')) }}">
                        <div class="input-group-prepend input-group-append">
                            <span class="input-group-text">–</span>
                        </div>
                        <input type="date" name="to" class="form-control"
                               value="{{ request('to', $to->format('Y-m-d')) }}">
                    </div>
                    <select name="status" class="custom-select w-auto">
                        <option value="">Todos los estados</option>
                        <option value="pending"   {{ $status === 'pending'   ? 'selected' : '' }}>Pendiente</option>
                        <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.report.plan-report') }}" class="btn btn-outline-secondary">Restablecer</a>
                </form>
            </div>
        </div>

        {{-- Summary card --}}
        <div class="row mb-3">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body h-100">
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm avatar-soft-primary avatar-circle mr-3">
                            <span class="avatar-initials"><i class="tio-dollar-outlined"></i></span>
                        </div>
                        <div class="media-body">
                            <span class="d-block font-size-sm">Ingresos por Planes</span>
                            <h3 class="mb-0">{{ \App\CentralLogics\Helpers::set_symbol($totalRevenue) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-body h-100">
                    <div class="media align-items-center">
                        <div class="avatar avatar-sm avatar-soft-success avatar-circle mr-3">
                            <span class="avatar-initials"><i class="tio-receipt"></i></span>
                        </div>
                        <div class="media-body">
                            <span class="d-block font-size-sm">Total de Pedidos</span>
                            <h3 class="mb-0">{{ $orders->total() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Cliente (ID)</th>
                                <th>Plan</th>
                                <th>Categoría</th>
                                <th>Fecha inicio</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Pago</th>
                                <th>Fecha creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>{{ $order->id }}</td>
                                    <td>{{ $order->user_id }}</td>
                                    <td>{{ $order->plan?->title ?? '-' }}</td>
                                    <td>{{ $order->plan?->category?->name ?? '-' }}</td>
                                    <td>{{ $order->start_date?->format('d/m/Y') }}</td>
                                    <td>{{ \App\CentralLogics\Helpers::set_symbol($order->total_price) }}</td>
                                    <td>
                                        @php
                                            $badge = match($order->status) {
                                                'confirmed' => 'success',
                                                'cancelled' => 'danger',
                                                default     => 'warning',
                                            };
                                        @endphp
                                        @php
                                            $statusLabel = match($order->status) {
                                                'pending'   => 'Pendiente',
                                                'confirmed' => 'Confirmado',
                                                'cancelled' => 'Cancelado',
                                                default     => ucfirst($order->status),
                                            };
                                        @endphp
                                        <span class="badge badge-soft-{{ $badge }}">{{ $statusLabel }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No se encontraron pedidos de planes para el período seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
