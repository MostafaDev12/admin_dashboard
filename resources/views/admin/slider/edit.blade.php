@extends('layouts.load')
@section('content')


                        					@include('includes.admin.form-error')
											<form id="geniusformdata" action="{{ route('admin-slider-edit',$data->id) }}" method="POST" enctype="multipart/form-data">
												{{csrf_field()}}


 <div class="row">
						                     <!--     <div class="col-lg-4">
						                            <div class="left-area">
						                                <h4 class="heading">{{ __("candidate Image") }} *</h4>
						                            </div>
						                          </div>
						                          <div class="col-lg-7">
						                            <div class="img-upload">

						                                <div id="image-preview" class="img-preview" style="background: url({{ $data->image_url ? $data->image_url:asset('assets/images/noimage.png') }});">


						                                    <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __("Upload Image") }}</label>
						                                    <input type="file" name="image_url" class="img-upload" id="image-upload">

						                                  </div>
						                                  <p class="text">{{ __("Prefered Size: (600x600) or Square Sized Image") }}</p>
						                            </div>
						                          </div>
						                          -->


						                    		                     <div class="col-lg-12">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">الصوره</h4>
                                        </div><!-- end card header -->

                                        <div class="card-body">

                                                 <div class="img-upload">

						                                <div id="image-preview" class="img-preview" style="background: url({{ $data->photo ? $data->photo:asset('assets/images/noimage.png') }});">


						                                 <!--   <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __("Upload Image") }}</label>
						                                    <input type="file" name="image_url" class="img-upload" id="image-upload">
						                                -->
						                                  </div>

						                            </div>


						                        </div>
                                            <div class="avatar-xl mx-auto">
                                                <input type="file" class="filepond filepond-input-circle form-control " name="photo" accept="image/png, image/jpeg, image/gif, image/webp" />
                                            </div>

                                        </div>
                                        <!-- end card body -->
                                    </div>
                                    <!-- end card -->
                                </div> <!-- end col -->


						                          


											 <br>

										      <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">الغاء</button>
                                <button type="submit" class="btn btn-primary addProductSubmit-btn">حفظ</button>
                            </div>
                        </div><!--end col-->


						                        </div>


											<!--	<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("Name") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="input-field" name="name" placeholder="{{ __("Name") }}" required="" value="{{ $data->name }}">
													</div>
												</div>
-->





											</form>



@endsection
