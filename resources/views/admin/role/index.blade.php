@extends('layouts.master')
@section('title')
    @lang('translation.analytics')
@endsection
@section('css')

  
@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            roles
        @endslot
    @endcomponent
 
          <input type="hidden" id="headerdata" value="{{ __('الصلاحيه') }}">
                         <div class="col-lg-12"  >
                            <div class="card">
                                <div class="card-header">
                                   	@include('includes.admin.form-success')
                                   	  	<div class="btn-area"></div>
                                </div>
                                <div class="card-body">
                                    <table id="geniustable" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100%">
                                        <thead>
                                            <tr>
                                              <th>{{ __('الاسم') }}</th>
                                          <th width="50%">{{ __('الصلاحيات') }}</th>
                                          <th>{{ __('خيارات') }}</th>
                                            </tr>
                                        </thead>

                                    </table>
                                </div>
                            </div>
                        </div>



{{-- DELETE MODAL --}}

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

  <div class="modal-header d-block text-center">
    <h4 class="modal-title d-inline-block">{{ __("Confirm Delete") }}</h4>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
  </div>

      <!-- Modal body -->
      <div class="modal-body">
            <p class="text-center">{{ __('You are about to delete this Role.') }}</p>
            <p class="text-center">{{ __('Do you want to proceed?') }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
            <a class="btn btn-danger btn-ok">{{ __('Delete') }}</a>
      </div>

    </div>
  </div>
</div>

{{-- DELETE MODAL ENDS --}}

@endsection

@section('script')


{{-- DATA TABLE --}}

    <script type="text/javascript">

    var table = $('#geniustable').DataTable({
         ordering: false,
               processing: true,
               serverSide: true,
               ajax: '{{ route('admin-role-datatables') }}',
               columns: [
                        { data: 'name', name: 'name' },
                        { data: 'section', name: 'section' },
                        { data: 'action', searchable: false, orderable: false }

                     ],
                language : {
                  processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">'
                }
            });

        $(function() {
        $(".btn-area").append('<div class="col-sm-4 table-contents">'+
          '<a class="add-btn  btn btn-sm btn-secondary" href="{{route('admin-role-create')}}">'+
          '<i class="fas fa-plus"></i> {{ __("اضافه صلاحيه ") }}'+
          '</a>'+
          '</div>');
      });

{{-- DATA TABLE ENDS--}}

</script>

@endsection
