<!-- ========== App Menu ========== -->
<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/cangrow.png')); ?>" alt="" height="60">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="<?php echo e(route('admin.dashboard')); ?>" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo e(URL::asset('build/images/logo-sm.png')); ?>" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="<?php echo e(URL::asset('build/images/cangrow.png')); ?>" alt="" height="60">
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
                <li class="menu-title"><span><?php echo app('translator')->get('translation.menu'); ?></span></li>
                <li class="nav-item">
                    <a class="nav-link  " href="<?php echo e(route('admin.dashboard')); ?>" aria-controls="sidebarDashboards">
                        <i class="las la-tachometer-alt"></i> <span><?php echo app('translator')->get('translation.dashboards'); ?></span>
                    </a>
                </li> <!-- end Dashboard Menu -->

                <li class="menu-title"><i class="ri-more-fill"></i> <span><?php echo app('translator')->get('translation.pages'); ?></span></li>


                <?php if(Auth::guard('admin')->user()->sectionCheck('slider')): ?>
                    <li class="nav-item">
                        <a class="nav-link  " href="<?php echo e(route('admin-slider-index')); ?>" aria-controls="sidebarsliders">
                            <i class="las la-tachometer-alt"></i> <span><?php echo app('translator')->get('translation.sliders'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Auth::guard('admin')->user()->sectionCheck('page_settings')): ?>
                    <li class="nav-item">
                        <a class="nav-link  " href="<?php echo e(route('admin-ps-about_us')); ?>" aria-controls="sidebarabout_us">
                            <i class="las la-tachometer-alt"></i> <span><?php echo app('translator')->get('translation.about_us'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(Auth::guard('admin')->user()->sectionCheck('partners')): ?>
                    <li class="nav-item">
                        <a class="nav-link  " href="<?php echo e(route('admin-partners-index')); ?>"
                            aria-controls="sidebarpartners">
                            <i class="las la-tachometer-alt"></i> <span><?php echo app('translator')->get('translation.partners'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(Auth::guard('admin')->user()->sectionCheck('services')): ?>
                    <li class="nav-item">
                        <a class="nav-link  " href="<?php echo e(route('admin-services-index')); ?>"
                            aria-controls="sidebarservices">
                            <i class="las la-tachometer-alt"></i> <span><?php echo app('translator')->get('translation.services'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Auth::guard('admin')->user()->sectionCheck('models')): ?>
                    <li class="nav-item">
                        <a class="nav-link  " href="<?php echo e(route('admin-models-index')); ?>" aria-controls="sidebarmodels">
                            <i class="las la-tachometer-alt"></i> <span><?php echo app('translator')->get('translation.models'); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if(Auth::guard('admin')->user()->sectionCheck('general_settings')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#general" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="general">
                            <i class="las la-cog"></i> <span data-key="t-General_Settings"> <?php echo app('translator')->get('translation.settings'); ?></span>
                        </a>
                        <div class="collapse menu-dropdown" id="general">
                            <ul class="nav nav-sm flex-column">

                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin-gs-logo')); ?>" class="nav-link" data-key="t-Logo">
                                        <?php echo app('translator')->get('translation.logo'); ?>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin-gs-contents')); ?>" class="nav-link"
                                        data-key="t-Website_Contents"> <?php echo app('translator')->get('translation.content'); ?> </a>
                                </li>
                                <?php if(Auth::guard('admin')->user()->sectionCheck('super')): ?>
                                    <li class="nav-item">
                                        <a href="<?php echo e(route('admin-role-index')); ?>" class="nav-link"
                                            data-key="t-Manage_Roles"> <?php echo app('translator')->get('translation.role_mangment'); ?> </a>
                                    </li>
                                <?php endif; ?>

                                <?php if(Auth::guard('admin')->user()->sectionCheck('manage_staffs')): ?>
                                    <li class="nav-item">
                                        <a href="<?php echo e(route('admin-staff-index')); ?>" class="nav-link"
                                            data-key="t-Manage_Stauff"> <?php echo app('translator')->get('translation.staff_mangment'); ?> </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </li> <!-- end Dashboard Menu -->
                <?php endif; ?>
                <?php if(Auth::guard('admin')->user()->sectionCheck('super')): ?>
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="<?php echo e(route('admin-cache-clear')); ?>">
                            <i class="las la-flask"></i> <span data-key="t-Clear_Cache"> <?php echo app('translator')->get('translation.cache_clear'); ?> </span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>
<!-- Left Sidebar End -->
<!-- Vertical Overlay-->
<div class="vertical-overlay"></div>
<?php /**PATH C:\laragon\www\cangrow-admin\resources\views/layouts/sidebar.blade.php ENDPATH**/ ?>