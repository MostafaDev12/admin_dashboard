@extends('layouts.new-app')
@section('title')
  @include("partials.title-meta", ["title"=> 'سلايدر'])

@stop

@section('page-title')
   @include("partials.page-title", ["pagetitle"=> "لوحه التحكم","title"=> "سلايدر"])
@stop

@section('content')

<div class="page-content">
  <div class="container-fluid">

					<input type="hidden" id="headerdata" value="{{ __("سلايدر") }}" style="box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px;">
                         <div class="col-lg-12 content_row"  >
                            <div class="card">
                                <div class="card-header">
                                   	@include('includes.admin.form-success')
                                   	<div class="btn-area"></div>
                                </div>
                                <div class="card-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
                                    <table id="geniustable2" class="table nowrap dt-responsive align-middle table-hover table-bordered" style="width:100% ; box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px; ">
                                        <thead>
                                            <tr style="color: white !important; background-color: #0e395e !important;">
                                              <th  data-key="t-image">{{ __("الصوره") }}</th>
                                             
                                                <th data-key="t-Options">{{ __("خيارات") }}</th>
                                            </tr>
                                        </thead>

                                    </table>
                                </div>
                            </div>
                        </div>
                      </div>
                    </div>
{{-- ADD / EDIT MODAL --}}

			<div class="modal fade" id="modal1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
					<div class="modal-dialog modal-dialog-centered" role="document">
						<div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">

                            </div>
						</div>
					</div>

				</div>

{{-- ADD / EDIT MODAL ENDS --}}

{{-- DELETE MODAL --}}

<div class="modal fade" id="confirm-delete1" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

<div class="modal-header d-block text-center">
		<h4 class="modal-title d-inline-block"  data-key="t-delete_Confirm">{{ __("تاكيد الحذف") }}</h4>
		<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
	</div>

      <!-- Modal body -->
      <div class="modal-body">
            <p class="text-center" data-key="t-delete_customer">{{ __("أنت على وشك حذف هذا .") }}</p>
            <p class="text-center" data-key="t-delete_process">{{ __("هل تريد المتابعة؟") }}</p>
      </div>

      <!-- Modal footer -->
      <div class="modal-footer justify-content-center">
            <button type="button" class="btn btn-default" data-dismiss="modal" data-key="t-cancel">{{ __("الغاء") }}</button>
            <a class="btn btn-danger btn-ok"  data-key="t-delete">{{ __("حذف") }}</a>
      </div>

    </div>
  </div>
</div>

{{-- DELETE MODAL ENDS --}}

{{-- MESSAGE MODAL --}}
<div class="sub-categori">
	<div class="modal" id="vendorform" tabindex="-1" role="dialog" aria-labelledby="vendorformLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="vendorformLabel"  data-key="t-Send_Message">{{ __("   ارسل رساله") }}</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
				</div>
			<div class="modal-body">
				<div class="container-fluid p-0">
					<div class="row">
						<div class="col-md-12">
							<div class="contact-form">
								<form id="emailreply1">
									{{csrf_field()}}
									<ul>
										<li>
											<input type="email" class="input-field eml-val" id="eml1" name="to" placeholder="{{ __("الايميل") }} *" value="" required="">
										</li>
										<li>
											<input type="text" class="input-field" id="subj1" name="subject" placeholder="{{ __("الموضوع") }} *" required="">
										</li>
										<li>
											<textarea class="input-field textarea" name="message" id="msg1" placeholder="{{ __(" الرساله  ") }} *" required=""></textarea>
										</li>
									</ul>
									<button class="submit-btn" id="emlsub1" type="submit"  data-key="t-Send_Message">{{ __(" ارسل رساله  ") }}</button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>

{{-- MESSAGE MODAL ENDS --}}
@endsection

@section('scripts')

{{-- DATA TABLE --}}

    <script type="text/javascript">

		var table = $('#geniustable2').DataTable({
			   ordering: true,
               processing: true,
               serverSide: true,
               ajax: '{{ route('admin-slider-datatables') }}',
               columns: [
                        { data: 'photo', name: 'photo' },
                         

            			{ data: 'action', searchable: false, orderable: false }
                     ],


            });
		  	$(function() {
           $(".btn-area").append('<div class="col-sm-4 text-right">'+
        	'<a class="add-btn btn btn-sm btn-secondary" data-href="{{route('admin-slider-create')}}" id="add-data" data-bs-toggle="modal" data-bs-target="#modal1">'+
          '<i class="fas fa-plus"></i> {{ __('اضافه صوره') }}'+
          '</a>'+
          '</div>');
      });
    </script>

{{-- DATA TABLE --}}

@endsection
