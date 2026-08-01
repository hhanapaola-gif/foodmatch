@extends('layouts.branch.app')

@section('title', 'Mis Planes')

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <img width="20" class="avatar-img" src="{{asset('assets/admin/img/icons/business_setup2.png')}}" alt="">
                <span class="page-header-title">Mis Planes</span>
            </h2>
            <span class="badge badge-soft-dark rounded-50 fz-14">{{ $plans->total() }}</span>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <div class="card">
                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Plan</th>
                                    <th>Categoría</th>
                                    <th>Tipo</th>
                                    <th>Menú PDF</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($plans as $key => $plan)
                                    <tr>
                                        <td>{{ $plans->firstItem() + $key }}</td>
                                        <td>{{ $plan->title }}</td>
                                        <td>{{ $plan->category->name ?? '-' }}</td>
                                        <td>{{ $plan->type === 'monthly' ? 'Mensual' : 'Semanal' }}</td>
                                        <td>
                                            @if($plan->menu_pdf)
                                                <span class="badge badge-soft-success">Cargado</span>
                                            @else
                                                <span class="badge badge-soft-warning">Sin cargar</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                   href="{{ route('branch.plan.menu', [$plan->id]) }}">
                                                    <i class="tio-file-text"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $plans->links() !!}
                            </div>
                        </div>

                        @if(count($plans) == 0)
                            <div class="text-center p-4">
                                <img class="w-120px mb-3" src="{{asset('/public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">
                                <p class="mb-0">No tienes planes asignados todavía.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
