@extends('layouts.branch.app')

@section('title', 'Pedido de Plan #' . $order->id)

@section('content')
    <div class="content container-fluid">

        {{-- Breadcrumb --}}
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <div>
                <h2 class="h1 mb-0">Pedido de Plan <span class="text-primary">#{{ $order->id }}</span></h2>
                <small class="text-muted">Creado el {{ $order->created_at?->format('d/m/Y H:i') }}</small>
            </div>
            <div class="ml-auto">
                <a href="{{ route('branch.plan-order.list') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="tio-arrow-backward mr-1"></i> Volver a la lista
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row g-3">

            {{-- Left column --}}
            <div class="col-lg-8">

                {{-- Plan info --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="tio-calendar-month mr-2"></i>Información del Plan</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="d-block text-muted font-size-sm">Plan</label>
                                <strong>{{ $order->plan?->title ?? '-' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <label class="d-block text-muted font-size-sm">Categoría</label>
                                <strong>{{ $order->plan?->category?->name ?? '-' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <label class="d-block text-muted font-size-sm">Fecha de inicio</label>
                                <strong>{{ $order->start_date?->format('d/m/Y') ?? '-' }}</strong>
                            </div>
                            <div class="col-sm-6">
                                <label class="d-block text-muted font-size-sm">Precio total</label>
                                <strong class="text-success font-size-16">
                                    {{ \App\CentralLogics\Helpers::set_symbol($order->total_price) }}
                                </strong>
                            </div>
                            @if($order->selected_days && count($order->selected_days))
                                <div class="col-12">
                                    <label class="d-block text-muted font-size-sm">Días seleccionados</label>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @foreach($order->selected_days as $day)
                                            <span class="badge badge-soft-primary">{{ $day }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Address & Notes --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="tio-map-outlined mr-2"></i>Entrega</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="d-block text-muted font-size-sm">Dirección</label>
                                <p class="mb-0">{{ $order->address ?: 'No especificada' }}</p>
                            </div>
                            @if($order->notes)
                                <div class="col-12">
                                    <label class="d-block text-muted font-size-sm">Notas</label>
                                    <p class="mb-0">{{ $order->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Order details rows (if any) --}}
                @if($order->details && $order->details->count())
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="tio-receipt mr-2"></i>Detalle de comidas</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Comida</th>
                                            <th>Tipo</th>
                                            <th>Precio</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->details as $detail)
                                            <tr>
                                                <td>{{ isset($detail->delivery_date) ? \Carbon\Carbon::parse($detail->delivery_date)->format('d/m/Y') : '-' }}</td>
                                                <td>{{ $detail->meal_name ?? '-' }}</td>
                                                <td>{{ $detail->meal_type ?? '-' }}</td>
                                                <td>{{ \App\CentralLogics\Helpers::set_symbol($detail->price ?? 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

            </div>

            {{-- Right column --}}
            <div class="col-lg-4">

                {{-- Status card --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">Estado del Pedido</h5>
                    </div>
                    <div class="card-body">
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
                        <p class="mb-2">
                            Estado actual:
                            <span class="badge badge-soft-{{ $badge }} ml-1">{{ $label }}</span>
                        </p>
                        <p class="mb-3">
                            Pago:
                            <span class="badge badge-soft-{{ $order->payment_status === 'paid' ? 'success' : 'warning' }} ml-1">
                                {{ $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}
                            </span>
                        </p>
                        <form method="POST" action="{{ route('branch.plan-order.status', $order->id) }}">
                            @csrf
                            <div class="form-group">
                                <label class="font-size-sm">Cambiar estado</label>
                                <select name="status" class="custom-select custom-select-sm">
                                    <option value="pending"   {{ $order->status === 'pending'   ? 'selected' : '' }}>Pendiente</option>
                                    <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-sm w-100">Actualizar estado</button>
                        </form>
                    </div>
                </div>

                {{-- Customer card --}}
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="tio-poi-user mr-2"></i>Cliente</h5>
                    </div>
                    <div class="card-body">
                        @if($order->customer)
                            <p class="font-weight-bold mb-1">
                                {{ trim($order->customer->f_name . ' ' . $order->customer->l_name) ?: 'Sin nombre' }}
                            </p>
                            @if($order->customer->phone)
                                <p class="mb-1"><i class="tio-smartphone mr-1"></i>{{ $order->customer->phone }}</p>
                            @endif
                            @if($order->customer->email)
                                <p class="mb-0"><i class="tio-email-outlined mr-1"></i>{{ $order->customer->email }}</p>
                            @endif
                        @else
                            <p class="text-muted mb-0">ID de usuario: {{ $order->user_id }}</p>
                        @endif
                    </div>
                </div>

                {{-- Payment summary --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Resumen de pago</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Método de pago</span>
                            <span>{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : '-' }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>Total</span>
                            <span class="text-success">{{ \App\CentralLogics\Helpers::set_symbol($order->total_price) }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
@endsection
