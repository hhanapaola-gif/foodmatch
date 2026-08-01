<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        p.subtitle { font-size: 11px; color: #666; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f3f4f6; border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        tbody td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
        tbody tr:nth-child(even) td { background: #fafafa; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 10px; }
        .badge-confirmed { background: #d1fae5; color: #065f46; }
        .badge-pending   { background: #fef3c7; color: #92400e; }
        .badge-cancelled { background: #fee2e2; color: #991b1b; }
        .summary { margin-bottom: 16px; }
        .summary-item { display: inline-block; margin-right: 24px; }
        .summary-label { font-size: 10px; color: #666; }
        .summary-value { font-size: 14px; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 10px; color: #999; text-align: right; }
    </style>
</head>
<body>
    <h1>Informe de Pedidos de Planes</h1>
    <p class="subtitle">
        Período: {{ $from->format('d/m/Y') }} — {{ $to->format('d/m/Y') }}
        @if($status) &nbsp;|&nbsp; Estado: {{ ucfirst($status) }} @endif
    </p>

    <div class="summary">
        <span class="summary-item">
            <div class="summary-label">Total de Pedidos</div>
            <div class="summary-value">{{ $orders->count() }}</div>
        </span>
        <span class="summary-item">
            <div class="summary-label">Ingresos Totales</div>
            <div class="summary-value">{{ \App\CentralLogics\Helpers::set_symbol($totalRevenue) }}</div>
        </span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID Pedido</th>
                <th>Cliente (ID)</th>
                <th>Plan</th>
                <th>Categoría</th>
                <th>Fecha Inicio</th>
                <th>Total</th>
                <th>Estado</th>
                <th>Pago</th>
                <th>Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $i => $order)
                @php
                    $statusLabel = match($order->status) {
                        'pending'   => 'Pendiente',
                        'confirmed' => 'Confirmado',
                        'cancelled' => 'Cancelado',
                        default     => ucfirst($order->status),
                    };
                    $badgeClass = match($order->status) {
                        'confirmed' => 'badge-confirmed',
                        'cancelled' => 'badge-cancelled',
                        default     => 'badge-pending',
                    };
                @endphp
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $order->id }}</td>
                    <td>{{ $order->user_id }}</td>
                    <td>{{ $order->plan?->title ?? '-' }}</td>
                    <td>{{ $order->plan?->category?->name ?? '-' }}</td>
                    <td>{{ $order->start_date?->format('d/m/Y') }}</td>
                    <td>{{ \App\CentralLogics\Helpers::set_symbol($order->total_price) }}</td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span></td>
                    <td>{{ $order->payment_status === 'paid' ? 'Pagado' : 'Pendiente' }}</td>
                    <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; color:#999; padding:16px;">
                        No se encontraron pedidos para el período seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Generado el {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
