<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container text-capitalize">
            <div class="navbar-vertical-footer-offset">
                <div class="navbar-brand-wrapper justify-content-between">
                    <!-- Logo -->
                    @php($restaurant_logo=\App\Model\BusinessSetting::where(['key'=>'logo'])->first()->value)
                    <a class="navbar-brand" href="{{route('admin.dashboard')}}" aria-label="Front">
                        <img class="navbar-brand-logo" style="object-fit: contain;"
                             onerror="this.src='{{asset('assets/admin/img/160x160/img2.jpg')}}'"
                             src="{{asset('storage/restaurant/'.$restaurant_logo)}}"
                             alt="Logo">
                        <img class="navbar-brand-logo-mini" style="object-fit: contain;"
                             onerror="this.src='{{asset('assets/admin/img/160x160/img2.jpg')}}'"
                             src="{{asset('storage/restaurant/'.$restaurant_logo)}}" alt="Logo">
                    </a>
                    <!-- End Logo -->

                    <!-- Navbar Vertical Toggle -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip" data-placement="right" title="" data-original-title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align" data-template="<div class=&quot;tooltip d-none d-sm-block&quot; role=&quot;tooltip&quot;><div class=&quot;arrow&quot;></div><div class=&quot;tooltip-inner&quot;></div></div>" data-toggle="tooltip" data-placement="right" title="" data-original-title="Expand"></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->

                    <div class="navbar-nav-wrap-content-left d-none d-xl-block">
                        <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                            <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip" data-placement="right" title="" data-original-title="Collapse"></i>
                            <i class="tio-last-page navbar-vertical-aside-toggle-full-align"></i>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="navbar-vertical-content">
                    <div class="sidebar--search-form py-3">
                        <div class="search--form-group">
                            <button type="button" class="btn"><i class="tio-search"></i></button>
                            <input type="text" class="js-form-search form-control form--control" id="search-bar-input" placeholder="Buscar menú...">
                        </div>
                    </div>

                    <ul class="navbar-nav navbar-nav-lg nav-tabs">

                        {{-- ── PANEL ──────────────────────────────────────────── --}}
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin')?'show':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="{{route('admin.dashboard')}}" title="Panel">
                                <i class="tio-home-vs-1-outlined nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Panel</span>
                            </a>
                        </li>

                        {{-- ── GESTIÓN DE PEDIDOS ─────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Gestión de Pedidos</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        {{-- Pedidos de Planes --}}
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/plan-order*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-shopping-cart nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Pedidos</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/plan-order*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/plan-order/list') && !Request::get('status') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.plan-order.list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Todos
                                            <span class="badge badge-soft-info badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::get('status') === 'pending' ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.plan-order.list', ['status' => 'pending']) }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Pendientes
                                            <span class="badge badge-soft-warning badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::where('status', 'pending')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::get('status') === 'confirmed' ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.plan-order.list', ['status' => 'confirmed']) }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Confirmados
                                            <span class="badge badge-soft-success badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::where('status', 'confirmed')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::get('status') === 'cancelled' ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.plan-order.list', ['status' => 'cancelled']) }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate sidebar--badge-container">
                                            Cancelados
                                            <span class="badge badge-soft-danger badge-pill ml-1">
                                                {{ \App\Model\PlanOrder::where('status', 'cancelled')->count() }}
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- ── PLANES ─────────────────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Planes</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        {{-- Planes (CRUD) --}}
                        <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/plan/create') || Request::is('admin/plan/list') || Request::is('admin/plan/edit*') || Request::is('admin/plan-category*') ? 'active' : '' }}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-calendar-month nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Gestión de Planes</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{ Request::is('admin/plan/create') || Request::is('admin/plan/list') || Request::is('admin/plan/edit*') || Request::is('admin/plan-category*') ? 'block' : 'none' }}">
                                <li class="nav-item {{ Request::is('admin/plan-category*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.plan-category.index') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Categorías de Planes</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/plan/create') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.plan.create') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Agregar Plan</span>
                                    </a>
                                </li>
                                <li class="nav-item {{ Request::is('admin/plan/list') || Request::is('admin/plan/edit*') ? 'active' : '' }}">
                                    <a class="nav-link" href="{{ route('admin.plan.list') }}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Lista de Planes</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- ── MARKETING ──────────────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Marketing</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        {{-- Banners --}}
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/banner*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-image nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Banners</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('admin/banner*')?'block':'none'}}">
                                <li class="nav-item {{Request::is('admin/banner/add-new')?'active':''}}">
                                    <a class="nav-link" href="{{route('admin.banner.add-new')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Agregar banner</span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/banner/list')?'active':''}}">
                                    <a class="nav-link" href="{{route('admin.banner.list')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Lista</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- ── REPORTES ───────────────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Reportes</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        <li class="nav-item {{Request::is('admin/report/earning')?'active':''}}">
                            <a class="nav-link" href="{{route('admin.report.earning')}}">
                                <i class="tio-chart-pie-1 nav-icon"></i>
                                <span class="text-truncate">Informe de Ganancias</span>
                            </a>
                        </li>

                        <li class="nav-item {{Request::is('admin/report/order')?'active':''}}">
                            <a class="nav-link" href="{{route('admin.report.order')}}">
                                <i class="tio-chart-bar-2 nav-icon"></i>
                                <span class="text-truncate">Informe de Pedidos</span>
                            </a>
                        </li>

                        <li class="nav-item {{Request::is('admin/report/plan-report')?'active':''}}">
                            <a class="nav-link" href="{{route('admin.report.plan-report')}}">
                                <i class="tio-chart-bar-1 nav-icon"></i>
                                <span class="text-truncate">Informe de Planes</span>
                            </a>
                        </li>

                        {{-- ── CLIENTES ───────────────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Clientes</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        {{-- Lista de clientes --}}
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/customer/list') || Request::is('admin/customer/view*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('admin.customer.list')}}">
                                <i class="tio-poi-user nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Clientes</span>
                            </a>
                        </li>

                        {{-- ── CONFIGURACIÓN ──────────────────────────────────── --}}
                        <li class="nav-item">
                            <small class="nav-subtitle">Configuración</small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>

                        {{-- Configuración del Negocio --}}
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/business-settings/restaurant*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link" href="{{route('admin.business-settings.restaurant.restaurant-setup')}}">
                                <i class="tio-settings nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Configuración del Negocio</span>
                            </a>
                        </li>

                        {{-- Sucursales --}}
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/branch*')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-shop nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">Sucursales</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                                style="display: {{Request::is('admin/branch*')?'block':'none'}}">
                                <li class="nav-item {{Request::is('admin/branch/add-new')?'active':''}}">
                                    <a class="nav-link" href="{{route('admin.branch.add-new')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Agregar sucursal</span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/branch/list')?'active':''}}">
                                    <a class="nav-link" href="{{route('admin.branch.list')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">Lista</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item pt-10">
                            <div class=""></div>
                        </li>
                    </ul>
                </div>
                <!-- End Content -->
            </div>
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none"></div>

@push('script_2')
    <script>
        $(window).on('load', function() {
            if($(".navbar-vertical-content li.active").length) {
                $('.navbar-vertical-content').animate({
                    scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
                }, 10);
            }
        });

        //Sidebar Menu Search
        var $rows = $('.navbar-vertical-content  .navbar-nav > li');
        $('#search-bar-input').keyup(function() {
            var val = $.trim($(this).val()).replace(/ +/g, ' ').toLowerCase();
            $rows.show().filter(function() {
                var text = $(this).text().replace(/\s+/g, ' ').toLowerCase();
                return !~text.indexOf(val);
            }).hide();
        });
    </script>
@endpush
