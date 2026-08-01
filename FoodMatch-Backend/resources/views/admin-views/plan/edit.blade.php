@extends('layouts.admin.app')

@section('title', translate('Edit Plan'))

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="tio-calendar-month nav-icon" style="font-size:24px"></i>
                <span class="page-header-title">
                    {{ translate('Edit Plan') }}
                </span>
            </h2>
            <a href="{{ route('admin.plan.list') }}" class="btn btn-outline-secondary btn-sm ms-auto">
                <i class="tio-arrow-backward"></i> {{ translate('Back to List') }}
            </a>
        </div>

        <form action="{{ route('admin.plan.update', $plan->id) }}" method="post" enctype="multipart/form-data">
            @csrf

            <div class="row g-3">
                <!-- LEFT COLUMN: Main fields -->
                <div class="col-lg-8">

                    <!-- Basic Information -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Basic Information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="input-label">{{ translate('title') }} <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control"
                                       value="{{ old('title', $plan->title) }}"
                                       placeholder="{{ translate('Plan title') }}"
                                       required maxlength="255">
                            </div>

                            <div class="form-group">
                                <label class="input-label">{{ translate('description') }}</label>
                                <textarea name="description" class="form-control" rows="4"
                                          placeholder="{{ translate('Enter plan description') }}">{{ old('description', $plan->description) }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('category') }} <span class="text-danger">*</span></label>
                                        <select name="category_id" class="custom-select js-select2-custom" required>
                                            <option selected disabled value="">{{ translate('Select category') }}</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ old('category_id', $plan->category_id) == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('type') }} <span class="text-danger">*</span></label>
                                        <select name="type" class="custom-select js-select2-custom" required>
                                            <option selected disabled value="">{{ translate('Select type') }}</option>
                                            <option value="weekly"  {{ old('type', $plan->type) == 'weekly'  ? 'selected' : '' }}>{{ translate('Weekly') }}</option>
                                            <option value="monthly" {{ old('type', $plan->type) == 'monthly' ? 'selected' : '' }}>{{ translate('Monthly') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group mb-0">
                                        <label class="input-label">Restaurante / Sucursal</label>
                                        <select name="restaurant_id" class="custom-select js-select2-custom">
                                            <option value="">Sin restaurante asignado</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}"
                                                    {{ old('restaurant_id', $plan->restaurant_id) == $branch->id ? 'selected' : '' }}>
                                                    {{ $branch->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Configuration') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                {{-- Weekly only: manual min days --}}
                                <div class="col-md-6" id="min-days-group">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('min_days') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="min_days" class="form-control"
                                               value="{{ old('min_days', $plan->type !== 'monthly' ? $plan->min_days : '') }}"
                                               min="1">
                                        <small class="text-muted">{{ translate('Total delivery days for the plan') }}</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('meals_per_day') }} <span class="text-danger">*</span></label>
                                        <input type="number" name="meals_per_day" class="form-control"
                                               value="{{ old('meals_per_day', $plan->meals_per_day) }}"
                                               min="1" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Pricing') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('breakfast_price') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ \App\CentralLogics\Helpers::currency_symbol() }}</span>
                                            </div>
                                            <input type="number" name="breakfast_price" class="form-control"
                                                   value="{{ old('breakfast_price', $plan->breakfast_price) }}"
                                                   step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('lunch_price') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ \App\CentralLogics\Helpers::currency_symbol() }}</span>
                                            </div>
                                            <input type="number" name="lunch_price" class="form-control"
                                                   value="{{ old('lunch_price', $plan->lunch_price) }}"
                                                   step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label class="input-label">{{ translate('dinner_price') }} <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ \App\CentralLogics\Helpers::currency_symbol() }}</span>
                                            </div>
                                            <input type="number" name="dinner_price" class="form-control"
                                                   value="{{ old('dinner_price', $plan->dinner_price) }}"
                                                   step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Schedule (Available Days)') }}</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $dayAbbrs  = ['mon','tue','wed','thu','fri','sat','sun'];
                                $dayLabels = [
                                    'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
                                    'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday', 'sun' => 'Sunday'
                                ];
                                // $planDays comes from controller as int[] (1=Mon…7=Sun)
                                $intToAbbr = [1=>'mon',2=>'tue',3=>'wed',4=>'thu',5=>'fri',6=>'sat',7=>'sun'];
                                $savedServiceDays = array_map(fn($n) => $intToAbbr[$n] ?? '', $planDays);
                                $activeServiceDays = old('service_days', $savedServiceDays);
                            @endphp
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($dayAbbrs as $abbr)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="service_days[]" value="{{ $abbr }}"
                                               id="day-{{ $abbr }}"
                                               {{ in_array($abbr, $activeServiceDays) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="day-{{ $abbr }}">
                                            {{ translate($dayLabels[$abbr]) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <p id="service-days-hint" class="mb-0 mt-2 text-muted fs-12" style="display:none;">
                                {{ translate('Selected days × 4 = total monthly delivery days') }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Media & Status -->
                <div class="col-lg-4">

                    <!-- Plan Image -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Plan Image') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center">
                                <div class="upload-file_custom ratio-1 h-150px mx-auto">
                                    <input type="file" name="image"
                                           class="upload-file__input single_file_input"
                                           accept=".{{ implode(',.', array_column(IMAGEEXTENSION, 'key')) }}, |image/*"
                                           data-maxFileSize="{{ readableUploadMaxFileSize('image') }}">
                                    <label class="upload-file__wrapper w-100 h-100 m-0">
                                        <div class="upload-file-textbox text-center" {{ $plan->image ? 'style=display:none' : '' }}>
                                            <img class="svg" src="{{ asset('assets/admin/img/document-upload.svg') }}" alt="img">
                                            <h6 class="mt-1 tc-clr fw-medium fs-10 lh-base text-center">
                                                <span class="text-c2">{{ translate('Click to upload') }}</span>
                                                <br>{{ translate('Or drag and drop') }}
                                            </h6>
                                        </div>
                                        <img class="upload-file-img" loading="lazy"
                                             src="{{ $plan->imageFullPath }}"
                                             data-default-src="{{ $plan->imageFullPath }}"
                                             alt="" style="{{ $plan->image ? '' : 'display: none;' }}">
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

                    <!-- Menu PDF -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Menu PDF') }}</h5>
                        </div>
                        <div class="card-body">
                            @if($plan->menu_pdf)
                                <div class="mb-2 d-flex align-items-center gap-2">
                                    <i class="tio-file-text text-danger fs-22"></i>
                                    <span class="text-muted fs-12">{{ $plan->menu_pdf }}</span>
                                </div>
                            @endif
                            <div class="form-group mb-0">
                                <label class="input-label">{{ translate('Replace PDF') }}</label>
                                <input type="file" name="menu_pdf" class="form-control" accept=".pdf">
                                <small class="text-muted">{{ translate('PDF only, max 10MB') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ translate('Status') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <label class="input-label mb-0">{{ translate('Active') }}</label>
                                <label class="switcher">
                                    <input type="checkbox" name="is_active" class="switcher_input"
                                           {{ $plan->is_active ? 'checked' : '' }}>
                                    <span class="switcher_control"></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-end gap-3">
                        <a href="{{ route('admin.plan.list') }}" class="btn btn-secondary">
                            {{ translate('cancel') }}
                        </a>
                        <button type="submit" class="btn btn-primary">
                            {{ translate('update') }}
                        </button>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection

@push('script_2')
    <script>
        "use strict";
        $('.js-select2-custom').each(function () {
            $.HSCore.components.HSSelect2.init($(this));
        });

        function syncPlanTypeFields() {
            var type = $('select[name="type"]').val();
            if (type === 'monthly') {
                $('#min-days-group').hide();
                $('#service-days-hint').show();
            } else {
                $('#min-days-group').show();
                $('#service-days-hint').hide();
            }
        }

        $('select[name="type"]').on('change', syncPlanTypeFields);

        // Run on page load to reflect the saved type
        syncPlanTypeFields();
    </script>
@endpush
