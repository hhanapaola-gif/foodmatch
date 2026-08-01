<div class="col-6 col-sm-3">
    <a href="{{ route('branch.plan-order.list') }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Todos</h5>
        <h2 class="dashboard--card__title">{{ $data['all'] }}</h2>
    </a>
</div>
<div class="col-6 col-sm-3">
    <a href="{{ route('branch.plan-order.list', ['status' => 'pending']) }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Pendientes</h5>
        <h2 class="dashboard--card__title">{{ $data['pending'] }}</h2>
    </a>
</div>
<div class="col-6 col-sm-3">
    <a href="{{ route('branch.plan-order.list', ['status' => 'confirmed']) }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Confirmados</h5>
        <h2 class="dashboard--card__title">{{ $data['confirmed'] }}</h2>
    </a>
</div>
<div class="col-6 col-sm-3">
    <a href="{{ route('branch.plan-order.list', ['status' => 'cancelled']) }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Cancelados</h5>
        <h2 class="dashboard--card__title">{{ $data['cancelled'] }}</h2>
    </a>
</div>
