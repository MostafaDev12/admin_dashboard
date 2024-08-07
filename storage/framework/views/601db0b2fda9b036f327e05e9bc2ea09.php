<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('translation.analytics'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('css'); ?>

  
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
    <?php $__env->startComponent('components.breadcrumb'); ?>
        <?php $__env->slot('li_1'); ?>
            Dashboards
        <?php $__env->endSlot(); ?>
        <?php $__env->slot('title'); ?>
            website content
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>


<div class="page-content">
    <div class="container-fluid">
  
                      <input type="hidden" id="headerdata" value="<?php echo e(__("Website Contents")); ?>" style="box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px;">
                           <div class="col-lg-12 content_row"  >
                              <div class="card">
                                  <div class="card-header">
                                         <?php echo $__env->make('includes.admin.form-success', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                         <div class="btn-area"></div>
                                  </div>
                                  <div class="card-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
                                       <form action="<?php echo e(route('admin-gs-update')); ?>" id="geniusform" method="POST" enctype="multipart/form-data">
        <?php echo e(csrf_field()); ?>


        <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <div class="row" style="padding-top: 100px; padding-left: 10px; padding-bottom: 20px; max-width: 100%">
            <div class="col-6">
                <div class="mb-3">
                    <label for="firstNameinput" class="form-label"><?php echo e(__('Website Title')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write Your Site Title Here')); ?>"
                        name="title" value="<?php echo e($gs->title); ?>" required="">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="lastNameinput" class="form-label"><?php echo e(__('vonage_key')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write vonage_key Here')); ?>"
                        name="vonage_key" value="<?php echo e($gs->vonage_key); ?>">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="compnayNameinput" class="form-label"><?php echo e(__('vonage_secret')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write vonage_secret Here')); ?>"
                        name="vonage_secret" value="<?php echo e($gs->vonage_secret); ?>">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="phonenumberInput" class="form-label"><?php echo e(__('sms_title')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write sms_title Here')); ?>"
                        name="sms_title" value="<?php echo e($gs->sms_title); ?>">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="emailidInput" class="form-label"><?php echo e(__('sms_message')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write sms_message Here')); ?>"
                        name="sms_message" value="<?php echo e($gs->sms_message); ?>">
                </div>
            </div><!--end col-->  
             <div class="col-6">
                <div class="mb-3">
                    <label for="address" class="form-label"><?php echo e(__('address')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write address Here')); ?>"
                        name="address" value="<?php echo e($gs->address); ?>">
                </div>
            </div><!--end col-->  
             <div class="col-6">
                <div class="mb-3">
                    <label for="phone" class="form-label"><?php echo e(__('phone')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write phone Here')); ?>"
                        name="phone" value="<?php echo e($gs->phone); ?>">
                </div>
            </div><!--end col-->  
             <div class="col-6">
                <div class="mb-3">
                    <label for="fax" class="form-label"><?php echo e(__('fax')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write fax Here')); ?>"
                        name="fax" value="<?php echo e($gs->fax); ?>">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo e(__('email')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write email Here')); ?>"
                        name="email" value="<?php echo e($gs->email); ?>">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="from_name" class="form-label"><?php echo e(__('from_name')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write from_name Here')); ?>"
                        name="from_name" value="<?php echo e($gs->from_name); ?>">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="from_email" class="form-label"><?php echo e(__('from_email')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write from_email Here')); ?>"
                        name="from_email" value="<?php echo e($gs->from_email); ?>">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="footer" class="form-label"><?php echo e(__('why us footer')); ?></label>
                    <input type="text" class="form-control" placeholder="<?php echo e(__('Write footer Here')); ?>"
                        name="footer" value="<?php echo e($gs->footer); ?>">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <br>
                <div class="mb-3">
                    <div class="form-check form-switch form-switch-lg" dir="ltr">
                        <input type="checkbox" class="form-check-input" value="1" name="is_elections"
                            id="customSwitchsizelg" <?php echo e($gs->is_elections == 1 ? 'checked' : ''); ?>>
                        <label class="form-check-label" for="customSwitchsizelg">is_capcha</label>
                    </div>
                </div>
            </div>
            
            <!--end col-->

            <div class="col-lg-12">
                <div class="text-end">
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div><!--end col-->
        </div><!--end row-->
    </form>

                                  </div>
                              </div>
                          </div>
                        </div>
                      </div>
   
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\modern\resources\views/admin/generalsetting/websitecontent.blade.php ENDPATH**/ ?>