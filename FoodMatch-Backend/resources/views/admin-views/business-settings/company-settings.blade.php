@extends('layouts.admin.app')

@section('title', 'Configuración del Negocio')

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('assets/admin/img/icons/business_setup2.png')}}" alt="">
                <span class="page-header-title">Configuración del Negocio</span>
            </h2>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <form action="{{ route('admin.business-settings.restaurant.update-setup') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Logo</h5>
                        </div>
                        <div class="card-body text-center">
                            <img id="logo-preview" src="{{ $logo }}" alt="Logo actual"
                                 style="max-width:100%; max-height:180px; object-fit:contain; border:1px solid #E4E7E9; border-radius:8px; padding:8px; margin-bottom:16px;">
                            <div class="form-group text-left mb-0">
                                <label class="input-label">Subir nuevo logo</label>
                                <input type="file" name="logo" accept="image/*" class="form-control"
                                       onchange="document.getElementById('logo-preview').src = URL.createObjectURL(this.files[0])">
                                <small class="text-muted d-block mt-1">PNG o JPG, se verá reflejado en el panel y en el login apenas guardes.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Información del negocio</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="input-label">Nombre del negocio <span class="text-danger">*</span></label>
                                <input type="text" name="restaurant_name" class="form-control" maxlength="255" required
                                       value="{{ old('restaurant_name', $companyName) }}">
                            </div>
                            <div class="form-group">
                                <label class="input-label">Teléfono de contacto</label>
                                <input type="text" name="phone" class="form-control" maxlength="255"
                                       value="{{ old('phone', $phone) }}">
                            </div>
                            <div class="form-group mb-0">
                                <label class="input-label">Dirección</label>
                                <textarea name="address" class="form-control" rows="3" maxlength="1000">{{ old('address', $address) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="btn--container mt-4">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
@endsection
