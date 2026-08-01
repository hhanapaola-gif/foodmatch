@extends('layouts.branch.app')

@section('title', 'Configuración del Negocio')

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('assets/admin/img/icons/business_setup2.png')}}" alt="">
                <span class="page-header-title">
                    Configuración del Negocio
                </span>
            </h2>
        </div>

        <form action="{{ route('branch.business-settings.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="input-label">Nombre de la sucursal</label>
                                <input value="{{$branch['name']}}" type="text" name="name"  maxlength="255" class="form-control"
                                       placeholder="Ej: Sucursal Centro" tabindex="1">
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="input-label">Tiempo de preparación<span class="text-danger mx-1">*</span>
                                    <i class="tio-info-outined"
                                       data-toggle="tooltip"
                                       data-placement="top"
                                       title="El tiempo de preparación de comida se mostrará al cliente.">
                                    </i>
                                </label>
                                <input value="{{ $branch['preparation_time'] }}" type="number" name="preparation_time" class="form-control"
                                       placeholder="Ej: 30" min="1" required tabindex="2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn--container mt-4">
                <button type="reset" class="btn btn-secondary" tabindex="3">Restablecer</button>
                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}" class="btn btn-primary call-demo" tabindex="4">Guardar</button>
            </div>
        </form>
    </div>
@endsection
