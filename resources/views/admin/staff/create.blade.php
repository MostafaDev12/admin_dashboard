@extends('layouts.load')
@section('content')

<div class="content-area">
    <div class="add-product-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="product-description">
                    <div class="body-area">
@include('includes.admin.form-error')
    <form id="geniusformdata" action="{{ route('admin-staff-store') }}" method="POST" enctype="multipart/form-data">
                        {{csrf_field()}}
                    <div class="row">
                          <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">{{ __('الصوره') }}</h4>
                                </div><!-- end card header -->

                                <div class="card-body">
                                    <p class="text-muted">{{ __('رفع الصوره') }}</p>
                                    <div class="avatar-xl mx-auto">
                                        <input type="file" class="filepond filepond-input-circle form-control " name="photo" accept="image/png, image/jpeg, image/gif" />
                                    </div>

                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div> <!-- end col -->
                     <!-- <div class="col-lg-4">
                        <div class="left-area">
                            <h4 class="heading">{{ __('Staff Profile Image') }} *</h4>
                        </div>
                      </div>
                      <div class="col-lg-7">
                        <div class="img-upload">
                            <div id="image-preview" class="img-preview" style="background: url({{ asset('assets/images/noimage.png') }});">
                                <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('Upload Image') }}</label>
                                <input type="file" name="photo" class="img-upload" id="image-upload">
                              </div>
                        </div>
                      </div>-->
                    </div>

                    <div class="row">
                        <div class="col-lg-4">
                            <div class="left-area">
                                    <h4 class="heading">{{ __('الاسم') }} *</h4>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <input type="text" class="form-control rounded-pill" name="name" placeholder="{{ __(" الاسم  ") }}" required="" value="">
                        </div>
                    </div>

                    <div class="row" style="padding-top: 10px">
                        <div class="col-lg-4">
                            <div class="left-area">
                                    <h4 class="heading">{{ __("الايميل") }} *</h4>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <input type="email" class="form-control rounded-pill" name="email" placeholder="{{ __(" الايميل  ") }}" required="" value="">
                        </div>
                    </div>

                    <div class="row" style="padding-top: 10px">
                        <div class="col-lg-4">
                            <div class="left-area">
                                    <h4 class="heading">{{ __("رقم الهاتف") }} *</h4>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <input type="text" class="form-control rounded-pill" name="phone" placeholder="{{ __(" رقم الهاتف  ") }}" required="" value="">
                        </div>
                    </div>
                    <div class="row" style="padding-top: 10px">
                            <div class="col-lg-4">
                                <div class="left-area">
                                        <h4 class="heading">{{ __("الصلاخيات") }} *</h4>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                    <select  class="form-select rounded-pill mb-3"  name="role_id" required="">
                                        <option value="">{{ __('Select Role') }}</option>
                                          @foreach(DB::table('roles')->get() as $data)
                                            <option value="{{ $data->id }}">{{ $data->name }}</option>
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
                                <input type="password" class="form-control rounded-pill" name="password" placeholder="{{ __("كلمه المرور") }}" required="" value="">
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
                            <button class="addProductSubmit-btn btn btn-sm btn-secondary" type="submit">{{ __(" حفظ  ") }}</button>
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
