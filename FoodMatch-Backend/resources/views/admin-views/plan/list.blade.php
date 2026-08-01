@extends('layouts.admin.app')

@section('title', translate('Plans'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="tio-calendar-month nav-icon" style="font-size:24px"></i>
                <span class="page-header-title">
                    {{translate('Plan Management')}}
                </span>
            </h2>
        </div>

        <div class="row g-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row align-items-center gy-2">
                            <div class="col-sm-4 col-md-6 col-lg-8">
                                <h5 class="d-flex align-items-center gap-2 mb-0">
                                    {{translate('Plan List')}}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $plans->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-8 col-md-6 col-lg-4">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form action="{{ url()->current() }}" method="GET" class="flex-grow-1">
                                        <div class="input-group">
                                            <input id="datatableSearch_" type="search" name="search"
                                                   value="{{ $search }}"
                                                   class="form-control"
                                                   placeholder="{{translate('Search by title')}}"
                                                   aria-label="Search"
                                                   autocomplete="off">
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-primary">
                                                    {{translate('Search')}}
                                                </button>
                                            </div>
                                        </div>
                                    </form>
                                    <a href="{{ route('admin.plan.create') }}" class="btn btn-primary text-nowrap">
                                        <i class="tio-add"></i> {{translate('Add Plan')}}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{translate('SL')}}</th>
                                    <th>{{translate('Image')}}</th>
                                    <th>{{translate('Title')}}</th>
                                    <th>{{translate('Category')}}</th>
                                    <th>{{translate('Type')}}</th>
                                    <th>{{translate('Base Price')}}</th>
                                    <th>{{translate('Status')}}</th>
                                    <th class="text-center">{{translate('Action')}}</th>
                                </tr>
                                </thead>

                                <tbody>
                                @foreach($plans as $key => $plan)
                                    <tr>
                                        <td>{{ $plans->firstItem() + $key }}</td>
                                        <td>
                                            <img class="img-vertical-150"
                                                 src="{{ $plan->imageFullPath }}"
                                                 alt="{{ translate('plan image') }}">
                                        </td>
                                        <td>
                                            <div class="max-w300 min-w-120px line-limit-2 text-wrap">
                                                {{ $plan->title }}
                                            </div>
                                        </td>
                                        <td>{{ $plan->category->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-soft-{{ $plan->type === 'weekly' ? 'info' : 'primary' }}">
                                                {{ ucfirst($plan->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $prices = array_filter([$plan->breakfast_price, $plan->lunch_price, $plan->dinner_price]);
                                                $minPrice = $prices ? min($prices) : 0;
                                            @endphp
                                            {{ \App\CentralLogics\Helpers::set_symbol($minPrice) }}+
                                        </td>
                                        <td>
                                            <label class="switcher">
                                                <input class="switcher_input status-change"
                                                       type="checkbox"
                                                       {{ $plan->is_active == 1 ? 'checked' : '' }}
                                                       id="{{ $plan->id }}"
                                                       data-url="{{ route('admin.plan.status', [$plan->id, 0]) }}">
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                   href="{{ route('admin.plan.edit', [$plan->id]) }}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                        data-id="plan-{{ $plan->id }}"
                                                        data-message="{{ translate('Want to delete this plan?') }}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.plan.delete') }}"
                                                  method="post"
                                                  id="plan-{{ $plan->id }}">
                                                @csrf @method('delete')
                                                <input type="hidden" name="id" value="{{ $plan->id }}">
                                            </form>
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
                                <img class="w-120px mb-3"
                                     src="{{ asset('/public/assets/admin/svg/illustrations/sorry.svg') }}"
                                     alt="Image Description">
                                <p class="mb-0">{{ translate('No_data_to_show') }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";

        $(".status-change").change(function () {
            var selectedValue = $(this).val();
            status_change(selectedValue);
        });
    </script>
@endpush
