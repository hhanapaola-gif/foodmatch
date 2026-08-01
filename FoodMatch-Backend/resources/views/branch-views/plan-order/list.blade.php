@extends('layouts.branch.app')

@section('title', 'Pedidos de Planes')

@section('content')
    <div class="content container-fluid">

        {{-- Header --}}
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="tio-receipt nav-icon" style="font-size:24px"></i>
                <span class="page-header-title">Pedidos de Planes</span>
            </h2>
            <span class="badge badge-soft-dark badge-pill font-size-14 ml-2">{{ $counts['all'] }}</span>
        </div>

        {{-- Status summary cards --}}
        <div class="row g-2 mb-3">
            <div class="col-6 col-sm-3">
                <a href="{{ route('branch.plan-order.list') }}" class="dashboard--card {{ !$status ? 'active' : '' }}">
                    <h5 class="dashboard--card__subtitle">Todos</h5>
                    <h2 class="dashboard--card__title">{{ $counts['all'] }}</h2>
                </a>
            </div>
            <div class="col-6 col-sm-3">
                <a href="{{ route('branch.plan-order.list', ['status' => 'pending']) }}" class="dashboard--card {{ $status === 'pending' ? 'active' : '' }}">
                    <h5 class="dashboard--card__subtitle">Pendientes</h5>
                    <h2 class="dashboard--card__title">{{ $counts['pending'] }}</h2>
                </a>
            </div>
            <div class="col-6 col-sm-3">
                <a href="{{ route('branch.plan-order.list', ['status' => 'confirmed']) }}" class="dashboard--card {{ $status === 'confirmed' ? 'active' : '' }}">
                    <h5 class="dashboard--card__subtitle">Confirmados</h5>
                    <h2 class="dashboard--card__title">{{ $counts['confirmed'] }}</h2>
                </a>
            </div>
            <div class="col-6 col-sm-3">
                <a href="{{ route('branch.plan-order.list', ['status' => 'cancelled']) }}" class="dashboard--card {{ $status === 'cancelled' ? 'active' : '' }}">
                    <h5 class="dashboard--card__subtitle">Cancelados</h5>
                    <h2 class="dashboard--card__title">{{ $counts['cancelled'] }}</h2>
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <div class="card mb-3">
            <div class="card-body py-2">
                <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
                    <div>
                        <label class="d-block font-size-sm mb-1">Buscar</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="ID, cliente, teléfono..."
                               value="{{ $search }}">
                    </div>
                    <div>
                        <label class="d-block font-size-sm mb-1">Estado</label>
                        <select name="status" class="custom-select">
                            <option value="">Todos</option>
                            <option value="pending"   {{ $status === 'pending'   ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                            <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label class="d-block font-size-sm mb-1">Plan</label>
                        <select name="plan_id" class="custom-select">
                            <option value="">Todos</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ (string) $planId === (string) $plan->id ? 'selected' : '' }}>
                                    {{ $plan->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="d-block font-size-sm mb-1">Desde</label>
                        <input type="date" name="from" class="form-control"
                               value="{{ $from ? $from->format('Y-m-d') : '' }}">
                    </div>
                    <div>
                        <label class="d-block font-size-sm mb-1">Hasta</label>
                        <input type="date" name="to" class="form-control"
                               value="{{ $to ? $to->format('Y-m-d') : '' }}">
                    </div>
                    <button class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('branch.plan-order.list') }}" class="btn btn-outline-secondary">Restablecer</a>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Pedido #</th>
                                <th>Cliente</th>
                                <th>Plan</th>
                                <th>Categoría</th>
                                <th>Fecha inicio</th>
                                <th>Dirección</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Pago</th>
                                <th>Creado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('branch.plan-order.show', $order->id) }}" class="font-weight-bold">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td>
                                        @if($order->customer)
                                            <span class="d-block font-weight-semibold">
                                                {{ trim($order->customer->f_name . ' ' . $order->customer->l_name) ?: 'Sin nombre' }}
                                            </span>
                                            <small class="text-muted">{{ $order->customer->phone ?? $order->customer->email ?? '-' }}</small>
                                        @else
                                            <span class="text-muted">ID: {{ $order->user_id }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $order->plan?->title ?? '-' }}</td>
                                    <td>{{ $order->plan?->category?->name ?? '-' }}</td>
                                    <td>{{ $order->start_date?->format('d/m/Y') ?? '-' }}</td>
                                    <td>
                                        @if($order->address)
                                            <span class="text-truncate d-inline-block" style="max-width:160px" title="{{ $order->address }}">
                                                {{ $order->address }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="font-weight-bold">
                                        {{ \App\CentralLogics\Helpers::set_symbol($order->total_price) }}
                                    </td>
                                    <td>
                                        @php
                                            $badge = match($order->status) {
                                                'confirmed' => 'success',
                                                'cancelled' => 'danger',
                                                default     => 'warning',
                                            };
                                            $label = match($order->status) {
                                                'pending'   => 'Pendiente',
                                                'confirmed' => 'Confirmado',
                                                'cancelled' => 'Cancelado',
                                                default     => ucfirst($order->status),
                                            };
                                        @endphp
                                        <span class="badge badge-soft-{{ $badge }}">{{ $label }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $payLabel = $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente';
                                        @endphp
                                        <span class="badge badge-soft-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                            {{ $payLabel }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex gap-1 align-items-center">
                                            <a href="{{ route('branch.plan-order.show', $order->id) }}"
                                               class="btn btn-sm btn-outline-primary" title="Ver detalle">
                                                <i class="tio-visible"></i>
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('branch.plan-order.status', $order->id) }}"
                                                  class="d-inline-flex gap-1">
                                                @csrf
                                                <select name="status" class="custom-select custom-select-sm w-auto">
                                                    <option value="pending"   {{ $order->status === 'pending'   ? 'selected' : '' }}>Pendiente</option>
                                                    <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                                </select>
                                                <button class="btn btn-sm btn-primary">Guardar</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-5">
                                        <i class="tio-receipt" style="font-size:48px; opacity:.3"></i>
                                        <p class="mt-2 mb-0">No se encontraron pedidos de planes.</p>
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
