<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.logos'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>

<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Dashboards
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            website logos
        <?php $__env->endSlot(); ?> 
    <?php echo $__env->renderComponent(); ?>

 
       
            <div class="row  ">
               
                
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">English Logo</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin-gs-update')); ?>" method="POST"
                            enctype="multipart/form-data">
                            <?php echo e(csrf_field()); ?>


                            <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="<?php echo e($gs->logo_en ? asset('assets/images/' . $gs->logo_en) : asset('assets/images/noimage.png')); ?>"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="logo_en"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary"><?php echo e(__('Submit')); ?></button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
   
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Arabic Logo</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin-gs-update')); ?>" method="POST"
                            enctype="multipart/form-data">
                            <?php echo e(csrf_field()); ?>


                            <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="<?php echo e($gs->logo_ar ? asset('assets/images/' . $gs->logo_ar) : asset('assets/images/noimage.png')); ?>"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="logo_ar"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary"><?php echo e(__('Submit')); ?></button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
 
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">france Logo</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin-gs-update')); ?>" method="POST"
                            enctype="multipart/form-data">
                            <?php echo e(csrf_field()); ?>


                            <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="<?php echo e($gs->logo_fr ? asset('assets/images/' . $gs->logo_fr) : asset('assets/images/noimage.png')); ?>"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="logo_fr"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary"><?php echo e(__('Submit')); ?></button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
 
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">favicon</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="<?php echo e(route('admin-gs-update')); ?>" method="POST"
                            enctype="multipart/form-data">
                            <?php echo e(csrf_field()); ?>


                            <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="<?php echo e($gs->favicon ? asset('assets/images/' . $gs->favicon) : asset('assets/images/noimage.png')); ?>"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="favicon"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary"><?php echo e(__('Submit')); ?></button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
 

            </div>
         
   
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
   
    <script src="<?php echo e(URL::asset('build/js/app.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\modern\resources\views/admin/generalsetting/logo.blade.php ENDPATH**/ ?>