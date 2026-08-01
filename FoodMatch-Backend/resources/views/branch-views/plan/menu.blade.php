@extends('layouts.branch.app')

@section('title', 'Menú del Plan')

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <span class="page-header-title">Menú PDF — {{ $plan->title }}</span>
            </h2>
        </div>

        <form action="{{ route('branch.plan.menu-update', [$plan->id]) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Menú PDF</h5>
                </div>
                <div class="card-body">
                    @if($plan->menu_pdf)
                        <div class="mb-2 d-flex align-items-center gap-2">
                            <i class="tio-file-text text-danger fs-22"></i>
                            <a href="{{ asset($plan->menu_pdf) }}" target="_blank">{{ basename($plan->menu_pdf) }}</a>
                        </div>
                    @endif
                    <div class="form-group mb-0">
                        <label class="input-label">{{ $plan->menu_pdf ? 'Reemplazar PDF' : 'Subir PDF' }}<span class="text-danger mx-1">*</span></label>
                        <input type="file" name="menu_pdf" class="form-control" accept=".pdf" required>
                        <small class="text-muted">Solo PDF, máximo 10MB. Se mostrará al cliente cuando seleccione este plan.</small>
                    </div>
                </div>
            </div>

            <div class="btn--container mt-4">
                <a href="{{ route('branch.plan.list') }}" class="btn btn-secondary">Volver</a>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
@endsection
