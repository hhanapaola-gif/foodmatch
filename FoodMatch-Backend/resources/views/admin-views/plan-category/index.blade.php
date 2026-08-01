@extends('layouts.admin.app')

@section('title', translate('Plan Categories'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="tio-category nav-icon" style="font-size:24px"></i>
                <span class="page-header-title">
                    {{ translate('Plan Category Setup') }}
                </span>
            </h2>
        </div>

        {{-- CREATE FORM --}}
        <div class="row g-2 mb-4">
            <div class="col-12">
                <form action="{{ route('admin.plan-category.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- Left: name + priority + status --}}
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label class="input-label">{{ translate('name') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                               value="{{ old('name') }}"
                                               placeholder="{{ translate('Category name') }}"
                                               required maxlength="255">
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="input-label">{{ translate('priority') }}</label>
                                                <input type="number" name="priority" class="form-control"
                                                       value="{{ old('priority', 0) }}" min="0">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-0">
                                                <label class="input-label d-block">{{ translate('status') }}</label>
                                                <label class="switcher mt-1">
                                                    <input type="checkbox" name="is_active" class="switcher_input"
                                                           {{ old('is_active', 1) ? 'checked' : '' }}>
                                                    <span class="switcher_control"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right: image upload --}}
                                <div class="col-lg-4">
                                    <div class="bg-soft-secondary rounded p-3 h-100">
                                        <div class="mb-3">
                                            <h5 class="mb-0">{{ translate('Image') }}</h5>
                                        </div>
                                        <div class="text-center">
                                            <div class="upload-file_custom ratio-1 h-100px mx-auto">
                                                <input type="file" name="image"
                                                       class="upload-file__input single_file_input"
                                                       accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*"
                                                       data-maxFileSize="{{ readableUploadMaxFileSize('image') }}">
                                                <label class="upload-file__wrapper w-100 h-100 m-0">
                                                    <div class="upload-file-textbox text-center">
                                                        <img class="svg" src="{{ asset('assets/admin/img/document-upload.svg') }}" alt="">
                                                        <h6 class="mt-1 tc-clr fw-medium fs-10 lh-base text-center">
                                                            <span class="text-c2">{{ translate('Click to upload') }}</span><br>
                                                            {{ translate('Or drag and drop') }}
                                                        </h6>
                                                    </div>
                                                    <img class="upload-file-img" loading="lazy" src="" data-default-src="" alt="" style="display:none;">
                                                </label>
                                                <div class="overlay-review">
                                                    <div class="d-flex gap-1 justify-content-center align-items-center h-100">
                                                        <button type="button" class="btn icon-btn view_btn"><i class="tio-invisible"></i></button>
                                                        <button type="button" class="btn icon-btn edit_btn"><i class="tio-edit"></i></button>
                                                        <button type="button" class="remove_btn btn icon-btn"><i class="tio-delete text-danger"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="mb-0 fs-12 mt-2 text-center">
                                            {{ implode(', ', array_column(IMAGEEXTENSION, 'key')) }}
                                            {{ readableUploadMaxFileSize('image') }}
                                            <span class="font-medium text-title">(1:1)</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-3 mt-4">
                                <button type="reset" class="btn btn-secondary">{{ translate('reset') }}</button>
                                <button type="submit" class="btn btn-primary">{{ translate('submit') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- LIST TABLE --}}
        <div class="row g-2">
            <div class="col-12">
                <div class="card">
                    <div class="card-top px-card pt-4">
                        <div class="row align-items-center gy-2">
                            <div class="col-sm-5 col-md-7">
                                <h5 class="d-flex align-items-center gap-2 mb-0">
                                    {{ translate('Plan Category List') }}
                                    <span class="badge badge-soft-dark rounded-50 fz-12">{{ $categories->total() }}</span>
                                </h5>
                            </div>
                            <div class="col-sm-7 col-md-5">
                                <form action="{{ url()->current() }}" method="GET">
                                    <div class="input-group">
                                        <input type="search" name="search" value="{{ $search }}"
                                               class="form-control"
                                               placeholder="{{ translate('Search by name') }}"
                                               autocomplete="off">
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary">{{ translate('Search') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="py-4">
                        <div class="table-responsive datatable-custom">
                            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('SL') }}</th>
                                    <th>{{ translate('Image') }}</th>
                                    <th>{{ translate('Name') }}</th>
                                    <th>{{ translate('Priority') }}</th>
                                    <th>{{ translate('Plans') }}</th>
                                    <th>{{ translate('Status') }}</th>
                                    <th class="text-center">{{ translate('Action') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($categories as $key => $category)
                                    <tr>
                                        <td>{{ $categories->firstItem() + $key }}</td>
                                        <td>
                                            <img class="img-vertical-150"
                                                 src="{{ $category->imageFullPath }}"
                                                 alt="{{ translate('category image') }}">
                                        </td>
                                        <td>
                                            <div class="max-w200 min-w-100px line-limit-2 text-wrap">
                                                {{ $category->name }}
                                            </div>
                                        </td>
                                        <td>{{ $category->priority }}</td>
                                        <td>
                                            <span class="badge badge-soft-info">{{ $category->plans()->count() }}</span>
                                        </td>
                                        <td>
                                            <label class="switcher">
                                                <input class="switcher_input status-change"
                                                       type="checkbox"
                                                       {{ $category->is_active == 1 ? 'checked' : '' }}
                                                       data-url="{{ route('admin.plan-category.status', [$category->id, 0]) }}">
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-center gap-2">
                                                <a class="btn btn-outline-info btn-sm edit square-btn"
                                                   href="{{ route('admin.plan-category.edit', $category->id) }}">
                                                    <i class="tio-edit"></i>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-outline-danger btn-sm delete square-btn form-alert"
                                                        data-id="plan-cat-{{ $category->id }}"
                                                        data-message="{{ translate('Want to delete this category?') }}">
                                                    <i class="tio-delete"></i>
                                                </button>
                                            </div>
                                            <form action="{{ route('admin.plan-category.delete') }}"
                                                  method="post"
                                                  id="plan-cat-{{ $category->id }}">
                                                @csrf @method('delete')
                                                <input type="hidden" name="id" value="{{ $category->id }}">
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4 px-3">
                            <div class="d-flex justify-content-lg-end">
                                {!! $categories->links() !!}
                            </div>
                        </div>

                        @if(count($categories) == 0)
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
