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
            roles
        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>


 <div class="col-lg-12" style="padding-top: 80px; padding-left: 10px; padding-bottom: 20px; max-width: 100%">
                            <div class="card">
                                <div class="card-header">
                                     <h4 class="heading"><?php echo e(__(' اضافه صلاحيه  ')); ?> <a class="add-btn" href="<?php echo e(route('admin-role-index')); ?>"><i class="fas fa-arrow-left"></i> <?php echo e(__(' الرجوع')); ?></a></h4>
                                   	<?php echo $__env->make('includes.admin.form-success', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


                                </div>
                                <div class="card-body">
                                     <form id="geniusform" action="<?php echo e(route('admin-role-create')); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo e(csrf_field()); ?>

                      <?php echo $__env->make('includes.admin.form-both', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <div class="row">
                          <div class="col-lg-2">
                            <div class="left-area">
                                <h4 class="heading"><?php echo e(__("الاسم")); ?> *</h4>
                                
                            </div>
                          </div>
                          <div class="col-lg-10">
                            <input type="text" class="form-control" name="name" placeholder="<?php echo e(__('الاسم')); ?>" required="" value="">
                          </div>
                        </div>

                        <hr>
                        <h5 class="text-center"><?php echo e(__('الصلاحيات')); ?></h5>
                        <hr>


                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="users" role="switch" name="section[]"  id="users" >
                            <label class="form-check-label" for="users"><?php echo e(__('المستخدمين')); ?>  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="general_settings" role="switch" name="section[]"  id="general_settings" >
                            <label class="form-check-label" for="general_settings"><?php echo e(__('   الاعدادات')); ?>  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="manage_staffs" role="switch" name="section[]"  id="manage_staffs" >
                            <label class="form-check-label" for="manage_staffs"><?php echo e(__('Manage Staffs')); ?>  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="super" role="switch" name="section[]"  id="super" >
                            <label class="form-check-label" for="super"><?php echo e(__('Manage Role & Cache clear')); ?>  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="candidates" role="switch" name="section[]"  id="candidates" >
                            <label class="form-check-label" for="candidates"><?php echo e(__('المرشحين')); ?>  </label>
                        </div>


                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="campaigns" role="switch" name="section[]"  id="campaigns" >
                            <label class="form-check-label" for="campaigns"><?php echo e(__('الحملات')); ?>  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="country" role="switch" name="section[]"  id="country" >
                            <label class="form-check-label" for="country"><?php echo e(__('المحافظات')); ?>  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="tasks" role="switch" name="section[]"  id="tasks" >
                            <label class="form-check-label" for="tasks"><?php echo e(__('المهام')); ?>  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="complaints_suggestions" role="switch" name="section[]"  id="complaints_suggestions" >
                            <label class="form-check-label" for="complaints_suggestions"><?php echo e(__('المهام')); ?>  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="volunteers" role="switch" name="section[]"  id="volunteers" >
                            <label class="form-check-label" for="volunteers"><?php echo e(__('المتطوعين فى الحمله')); ?>  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="complaints_suggestions" role="switch" name="section[]"  id="complaints_suggestions" >
                            <label class="form-check-label" for="complaints_suggestions"><?php echo e(__('الشكاوى والمقترحات')); ?>  </label>
                        </div>



    <br>
    <br>
    <br>
                        <div class="row">
                          <div class="col-lg-5">
                            <div class="left-area">

                            </div>
                          </div>
                          <div class="col-lg-7">
                            <button class="addProductSubmit-btn btn btn-secondary" type="submit"><?php echo e(__('   حفظ')); ?></button>
                          </div>
                        </div>
                      </form>
                                </div>
                            </div>
                        </div>




<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\wamp64\www\modern\resources\views/admin/role/create.blade.php ENDPATH**/ ?>