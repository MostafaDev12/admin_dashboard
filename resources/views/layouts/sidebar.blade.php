<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/cangrow.png') }}" alt="" height="60">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('build/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('build/images/cangrow.png') }}" alt="" height="60">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">

            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span>@lang('translation.menu')</span></li>
                <li class="nav-item">
                    <a class="nav-link  " href="{{ route('admin.dashboard') }}" aria-controls="sidebarDashboards">
                        <i class="las la-tachometer-alt"></i> <span>@lang('translation.dashboards')</span>
                    </a>
                </li> <!-- end Dashboard Menu -->

                <li class="menu-title"><i class="ri-more-fill"></i> <span>@lang('translation.pages')</span></li>


                @if (Auth::guard('admin')->user()->sectionCheck('slider'))
                    <li class="nav-item">
                        <a class="nav-link  " href="{{ route('admin-slider-index') }}" aria-controls="sidebarsliders">
                            <i class="las la-tachometer-alt"></i> <span>@lang('translation.sliders')</span>
                        </a>
                    </li>
                @endif
                @if (Auth::guard('admin')->user()->sectionCheck('page_settings'))
                    <li class="nav-item">
                        <a class="nav-link  " href="{{ route('admin-ps-about_us') }}" aria-controls="sidebarabout_us">
                            <i class="las la-tachometer-alt"></i> <span>@lang('translation.about_us')</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link  " href="{{ route('admin-ps-portfolio') }}" aria-controls="sidebarportfolio">
                            <i class="las la-tachometer-alt"></i> <span>@lang('translation.portfolio')</span>
                        </a>
                    </li>
                @endif

                @if (Auth::guard('admin')->user()->sectionCheck('partners'))
                    <li class="nav-item">
                        <a class="nav-link  " href="{{ route('admin-partners-index') }}"
                            aria-controls="sidebarpartners">
                            <i class="las la-tachometer-alt"></i> <span>@lang('translation.partners')</span>
                        </a>
                    </li>
                @endif

                @if (Auth::guard('admin')->user()->sectionCheck('services'))
                    <li class="nav-item">
                        <a class="nav-link  " href="{{ route('admin-services-index') }}"
                            aria-controls="sidebarservices">
                            <i class="las la-tachometer-alt"></i> <span>@lang('translation.services')</span>
                        </a>
                    </li>
                @endif
                @if (Auth::guard('admin')->user()->sectionCheck('models'))
                    <li class="nav-item">
                        <a class="nav-link  " href="{{ route('admin-models-index') }}" aria-controls="sidebarmodels">
                            <i class="las la-tachometer-alt"></i> <span>@lang('translation.models')</span>
                        </a>
                    </li>
                @endif
                @if (Auth::guard('admin')->user()->sectionCheck('general_settings'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#general" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="general">
                            <i class="las la-cog"></i> <span data-key="t-General_Settings"> @lang('translation.settings')</span>
                        </a>
                        <div class="collapse menu-dropdown" id="general">
                            <ul class="nav nav-sm flex-column">

                                <li class="nav-item">
                                    <a href="{{ route('admin-gs-logo') }}" class="nav-link" data-key="t-Logo">
                                        @lang('translation.logo')
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin-gs-contents') }}" class="nav-link"
                                        data-key="t-Website_Contents"> @lang('translation.content') </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin-gs-contact_messages') }}" class="nav-link"
                                        data-key="t-contact_messages"> @lang('translation.contact_messages') </a>
                                </li>
                                @if (Auth::guard('admin')->user()->sectionCheck('super'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin-role-index') }}" class="nav-link"
                                            data-key="t-Manage_Roles"> @lang('translation.role_mangment') </a>
                                    </li>
                                @endif

                                @if (Auth::guard('admin')->user()->sectionCheck('manage_staffs'))
                                    <li class="nav-item">
                                        <a href="{{ route('admin-staff-index') }}" class="nav-link"
                                            data-key="t-Manage_Stauff"> @lang('translation.staff_mangment') </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li> <!-- end Dashboard Menu -->
                @endif
                @if (Auth::guard('admin')->user()->sectionCheck('super'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('admin-cache-clear') }}">
                            <i class="las la-flask"></i> <span data-key="t-Clear_Cache"> @lang('translation.cache_clear') </span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
