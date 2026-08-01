
<div class="col-sm-6 col-lg-3">
    <a href="{{ route('admin.plan-order.list', ['status' => 'pending']) }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Pendientes</h5>
        <h2 class="dashboard--card__title">{{ $data['pending'] }}</h2>
        <img width="30" src="{{asset('assets/admin/img/icons/pending.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a href="{{ route('admin.plan-order.list', ['status' => 'confirmed']) }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Confirmados</h5>
        <h2 class="dashboard--card__title">{{ $data['confirmed'] }}</h2>
        <img width="30" src="{{asset('assets/admin/img/icons/confirmed.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a href="{{ route('admin.plan-order.list', ['status' => 'cancelled']) }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Cancelados</h5>
        <h2 class="dashboard--card__title">{{ $data['cancelled'] }}</h2>
        <img width="30" src="{{asset('assets/admin/img/icons/canceled.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>

<div class="col-sm-6 col-lg-3">
    <a href="{{ route('admin.plan-order.list') }}" class="dashboard--card">
        <h5 class="dashboard--card__subtitle">Ingresos Totales</h5>
        <h2 class="dashboard--card__title" style="font-size:1.4rem">
            {{ \App\CentralLogics\Helpers::set_symbol($data['revenue']) }}
        </h2>
        <img width="30" src="{{asset('assets/admin/img/icons/delivered.png')}}" class="dashboard--card__img" alt="">
    </a>
</div>
