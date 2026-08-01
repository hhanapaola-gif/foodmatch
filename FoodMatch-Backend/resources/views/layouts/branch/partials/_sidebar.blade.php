<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered  ">
        <div class="navbar-vertical-container">
            <div class="navbar-vertical-footer-offset">
                <div class="navbar-brand-wrapper justify-content-between">
                    @php($restaurantLogo=\App\Model\BusinessSetting::where(['key'=>'logo'])->first()->value)
                    <a class="navbar-brand" href="{{route('branch.dashboard')}}" aria-label="Front">
                        <img class="navbar-brand-logo" style="object-fit: contain;"
                             onerror="this.src='{{asset('assets/admin/img/160x160/img2.jpg')}}'"
                             src="{{asset('storage/restaurant/'.$restaurantLogo)}}"
                             alt="Logo">
                        <img class="navbar-brand-logo-mini" style="object-fit: contain;"
                             onerror="this.src='{{asset('assets/admin/img/160x160/img2.jpg')}}'"
                             src="{{asset('storage/restaurant/'.$restaurantLogo)}}" alt="Logo">
                    </a>

                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip" data-placement="right" title="" data-original-title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align" data-template="<div class=&quot;tooltip d-none d-sm-block&quot; role=&quot;tooltip&quot;><div class=&quot;arrow&quot;></div><div class=&quot;tooltip-inner&quot;></div></div>" data-toggle="tooltip" data-placement="right" title="" data-original-title="Expand"></i>
                    </button>

                    <div class="navbar-nav-wrap-content-left d-none d-xl-block">
                        <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                            <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip" data-placement="right" title="" data-original-title="Collapse"></i>
                            <i class="tio-last-page navbar-vertical-aside-toggle-full-align"></i>
                        </button>
                    </div>
                </div>

                <div class="navbar-vertical-content text-capitalize">
                    <div class="sidebar--search-form py-3">
                        <div class="search--form-group">
                            <button type="button" class="btn"><i class="tio-search"></i></button>
                            <input type="text" class="js-form-search form-control form--control" id="search-bar-input" placeholder="Buscar menú...">
                        </div>
                    </div>

                    <ul class="navbar-nav navbar-nav-lg nav-tabs">

                        {{-- ── PANEL ──────────────────────────────────────────── --}}
                        <li class="navbar-vertical-aside-has-menu {{Request::is('branch')?'show':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('branch.dashboard')}}" title="Panel">
                                <i class="tio-home-vs-1-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Panel</span>
                            </a>
                        </li>

                        {{-- ── GESTIÓN DE PEDIDOS ─────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Gestión de Pedidos</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        @php($branchPlanIds = \App\Model\Plan::where('restaurant_id', auth('branch')->id())->pluck('id'))
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('branch/plan-order*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-shopping-cart nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Pedidos de Planes</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('branch/plan-order*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('branch/plan-order/list') && !Request::get('status') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('branch.plan-order.list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Todos
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::whereIn('plan_id', $branchPlanIds)->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::get('status') === 'pending' ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('branch.plan-order.list', ['status' => 'pending']) }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Pendientes
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::whereIn('plan_id', $branchPlanIds)->where('status', 'pending')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::get('status') === 'confirmed' ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('branch.plan-order.list', ['status' => 'confirmed']) }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Confirmados
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::whereIn('plan_id', $branchPlanIds)->where('status', 'confirmed')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::get('status') === 'cancelled' ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('branch.plan-order.list', ['status' => 'cancelled']) }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Cancelados
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::whereIn('plan_id', $branchPlanIds)->where('status', 'cancelled')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('branch/plan*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('branch.plan.list')}}">
                                <i class="tio-file-text nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Mis Planes</span>
                            </a>
                        </li>

                        {{-- ── CONFIGURACIÓN ──────────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Configuración</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="navbar-vertical-aside-has-menu {{Request::is('branch/business-settings*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('branch.business-settings.index')}}">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Configuración del Negocio</span>
                            </a>
                        </li>

                        <li class="nav-item pt-10"></li>
                    </ul>
                </div>
            </div>
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none"></div>

@push('script_2')
    <script>
        $(window).on('load' , function() {
            if($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 10);
            }
        });

        //Sidebar Menu Search
        var $rows = $('.navbar-vertical-content .navbar-nav > li');
        $('#search-bar-input').keyup(function() {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();

            $rows.show().filter(function() {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
    </script>
@endpush
