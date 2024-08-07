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
        <?php echo e(__('translation.sliders')); ?>

        <?php $__env->endSlot(); ?>
    <?php echo $__env->renderComponent(); ?>
 
          <input type="hidden" id="headerdata" value="<?php echo e(__('translation.sliders')); ?>">
                         <div class="col-lg-12"  >
                            <div class="card">
                                <div class="card-header">
                                   	<?php echo $__env->make('includes.admin.form-success', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                   	  	<div class="btn-area"></div>
                                </div>
                                <div class="card-body">
                                    <table id="geniustable" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                              <th><?php echo e(__('translation.photo')); ?></th>
                                          
                                          <th><?php echo e(__('translation.actions')); ?></th>
                                            </tr>
                                        </thead>

                                    </table>
                                </div>
                            </div>
                        </div>





<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

  <div class="modal-header d-block text-center">
    <h4 class="modal-title d-inline-block"><?php echo e(__("Confirm Delete")); ?></h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
  </div>

      <!-- Modal body -->
      <div class="modal-body">
            <p class="text-center"><?php echo e(__('You are about to delete this slider.')); ?></p>
            <p class="text-center"><?php echo e(__('Do you want to proceed?')); ?></p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo e(__('Cancel')); ?></button>
            <a class="btn btn-danger btn-ok"><?php echo e(__('Delete')); ?></a>
      </div>

    </div>
  </div>
</div>



<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>




    <script type="text/javascript">

    var table = $('#geniustable').DataTable({
         ordering: false,
               processing: true,
               serverSide: true,
               ajax: '<?php echo e(route('admin-slider-datatables')); ?>',
               columns: [
                        { data: 'photo', name: 'photo' },
                         
                        { data: 'action', searchable: false, orderable: false }

                     ],
                language : {
                  processing: '<img src="<?php echo e(asset('assets/images/'.$gs->admin_loader)); ?>">'
                }
            });

        $(function() {
        $(".btn-area").append('<div class="col-sm-4 table-contents">'+
          '<a class="add-btn  btn btn-sm btn-secondary" href="<?php echo e(route('admin-slider-create')); ?>">'+
          '<i class="fas fa-plus"></i> <?php echo e(__("translation.add_slider")); ?>'+
          '</a>'+
          '</div>');
      });



</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\laragon\www\modern\resources\views/admin/slider/index.blade.php ENDPATH**/ ?>