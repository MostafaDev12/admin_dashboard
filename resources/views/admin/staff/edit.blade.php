@extends('layouts.load')
@section('content')

						<div class="content-area">
							<div class="add-product-content">
								<div class="row">
									<div class="col-lg-12">
										<div class="product-description">
											<div class="body-area">
                        @include('includes.admin.form-error')
											<form id="geniusformdata" action="{{ route('admin-staff-update',$data->id) }}" method="POST" enctype="multipart/form-data">
												{{csrf_field()}}

						                        <div class="row">
						                          <div class="col-lg-4">
						                            <div class="left-area">
						                                <h4 class="heading">{{ __(' الصوره    ') }} *</h4>
						                            </div>
						                          </div>
						                          <div class="col-lg-7">
						                            <div class="img-upload">
						                                <!-- Thumbnails Images -->
						                               <!--  <div id="image-preview" class="img-preview" style="background: url({{ $data->photo ? asset('assets/images/admins/'.$data->photo) : asset('assets/images/noimage.png') }});">
						                                   </div>
						                               -->
						                                  <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('   رفع الصوره') }}</label>
						                                    <input type="file" name="photo" class="form-control " id="image-upload">
<br>
                                                        <img class="img-thumbnail" alt="200x200" width="200" src="{{ $data->photo ? asset('assets/images/admins/'.$data->photo) : asset('assets/images/noimage.png') }}">

                                                    </div>
						                          </div>
						                        </div>


												<div class="row" style="padding-top: 10px">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __('الاسم') }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="form-control rounded-pill" name="name" placeholder="{{ __(" الاسم  ") }}" required="" value="{{ $data->name }}">
													</div>
												</div>


												<div class="row" style="padding-top: 10px">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("الايميل") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="email" class="form-control rounded-pill" name="email" placeholder="{{ __("   الايميل") }}" required="" value="{{ $data->email }}">
													</div>
												</div>

												<div class="row" style="padding-top: 10px">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("رقم الهاتف") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="text" class="form-control rounded-pill" name="phone" placeholder="{{ __(" رقم الهاتف  ") }}" required="" value="{{ $data->phone }}">
													</div>
												</div>


												<div class="row" style="padding-top: 10px">
														<div class="col-lg-4">
															<div class="left-area">
																	<h4 class="heading">{{ __("الصلاحيه") }} *</h4>
															</div>
														</div>
														<div class="col-lg-7">
																<select  name="role_id" required=""  class="form-select rounded-pill mb-3">
																	<option value="">{{ __('Select Role') }}</option>
																	  @foreach(DB::table('roles')->get() as $dta)
																		<option value="{{ $dta->id }}" {{ $data->role_id == $dta->id ? 'selected' : '' }}>{{ $dta->name }}</option>
																	  @endforeach
																</select>
														</div>
												</div>

												<div class="row">
													<div class="col-lg-4">
														<div class="left-area">
																<h4 class="heading">{{ __("كلمه المرور") }} *</h4>
														</div>
													</div>
													<div class="col-lg-7">
														<input type="password" class="form-control rounded-pill" name="password" placeholder="{{ __("كلمه المرور") }}" value="">
													</div>
												</div>
<br>
<br>
						                        <div class="row">
						                          <div class="col-lg-4">
						                            <div class="left-area">

						                            </div>
						                          </div>
						                          <div class="col-lg-7">
						                            <button class="addProductSubmit-btn btn btn-sm btn-secondary" type="submit">{{ __("حفظ") }}</button>
						                          </div>
						                        </div>

											</form>


											</div>
										</div>
									</div>
								</div>
							</div>
						</div>

@endsection
