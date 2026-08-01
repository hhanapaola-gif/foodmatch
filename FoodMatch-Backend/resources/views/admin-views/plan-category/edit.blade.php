@extends('layouts.admin.app')

@section('title', translate('Edit Plan Category'))

@section('content')
    <div class="content container-fluid">
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="tio-category nav-icon" style="font-size:24px"></i>
                <span class="page-header-title">
                    {{ translate('Edit Plan Category') }}
                </span>
            </h2>
            <a href="{{ route('admin.plan-category.index') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                <i class="tio-arrow-backward"></i> {{ translate('Back to List') }}
            </a>
        </div>

        <form action="{{ route('admin.plan-category.update', $category->id) }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Category Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="input-label">{{ translate('name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control"
                                       value="{{ old('name', $category->name) }}"
                                       required maxlength="255">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('priority') }}</label>
                                        <input type="number" name="priority" class="form-control"
                                               value="{{ old('priority', $category->priority) }}" min="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label d-block">{{ translate('status') }}</label>
                                        <label class="switcher mt-1">
                                            <input type="checkbox" name="is_active" class="switcher_input"
                                                   {{ $category->is_active ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Image') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="upload-file_custom ratio-1 h-150px mx-auto">
                                    <input type="file" name="image"
                                           class="upload-file__input single_file_input"
                                           accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*"
                                           data-maxFileSize="{{ readableUploadMaxFileSize('image') }}">
                                    <label class="upload-file__wrapper w-100 h-100 m-0">
                                        <div class="upload-file-textbox text-center" {{ $category->image ? 'style=display:none' : '' }}>
                                            <img class="svg" src="{{ asset('assets/admin/img/document-upload.svg') }}" alt="">
                                            <h6 class="mt-1 tc-clr fw-medium fs-10 lh-base text-center">
                                                <span class="text-c2">{{ translate('Click to upload') }}</span><br>
                                                {{ translate('Or drag and drop') }}
                                            </h6>
                                        </div>
                                        <img class="upload-file-img" loading="lazy"
                                             src="{{ $category->imageFullPath }}"
                                             data-default-src="{{ $category->imageFullPath }}"
                                             alt="" style="{{ $category->image ? '' : 'display:none;' }}">
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

                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.plan-category.index') }}" class="btn btn-secondary">{{ translate('cancel') }}</a>
                        <button type="submit" class="btn btn-primary">{{ translate('update') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
