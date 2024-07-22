@extends('layouts.new-app')

@section('title')
    @include('partials.title-meta', ['title' => 'Website Contents'])

@stop

@section('page-title')
    @include('partials.page-title', ['pagetitle' => 'Dashboards', 'title' => 'Website Contents'])
@stop



@section('content')

    <style>
        .featured-keyword-area {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .heading-area {
            margin-bottom: 20px;
        }

        .title {
            font-size: 1.8rem;
            color: #495057;
        }

        .feature-tag-top-filds {
            margin-bottom: 20px;
        }

        .feature-area {
            border: 1px solid #ced4da;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .remove {
            cursor: pointer;
            color: #dc3545;
            float: right;
            font-size: 1.2rem;
        }

        .remove:hover {
            color: #bd2130;
        }

        .row {
            margin-bottom: 15px;
        }

        .col-lg-12 {
            width: 100%;
        }

        .input-field {
            width: 100%;
            padding: 12px;
            box-sizing: border-box;
            border: 1px solid #ced4da;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .input-field:focus {
            border-color: #007bff;
        }

        .add-fild-btn {
            display: inline-block;
            padding: 10px 20px;
            font-size: 1rem;
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .add-fild-btn:hover {
            background-color: #0056b3;
        }

        .icofont-plus {
            margin-right: 5px;
        }
    </style>

    <div class="page-content">
        <div class="container-fluid">

            <div class="col-lg-12 content_row">
                <div class="card">
                    <div class="card-header">
                        @include('includes.admin.form-success')

                    </div>
                    <div class="card-body"
                        style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
                        <form action="{{ route('admin-gs-update') }}" id="geniusform" method="POST"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('includes.admin.form-both')

                            <div class="row"
                                style="padding-top: 100px; padding-left: 10px; padding-bottom: 20px; max-width: 100%">
                                @php
                                    
                                $about_us =  json_decode($gs->about_us); 
                                @endphp

                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="firstNameinput"
                                            class="form-label">{{ __('من هي صــحبة الخــير') }}</label>
                                        <input type="text" class="form-control"
                                            placeholder="{{ __('من هي صــحبة الخــير') }}" name="about_us[who_us]"
                                            value="{{ $about_us->who_us ?? '' }}">
                                    </div>
                                </div><!--end col-->


                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="firstNameinput"
                                            class="form-label">{{ __('تعاون في الخير مع صــحبة الخــير') }}</label>
                                        <input type="text" class="form-control"
                                            placeholder="{{ __('تعاون في الخير مع صــحبة الخــير') }}"
                                            name="about_us[who_us_title]"
                                            value="{{ $about_us->who_us_title ?? '' }}">
                                    </div>
                                </div><!--end col-->


                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="firstNameinput"
                                            class="form-label">{{ __('تفاصيل عن صحبه خير') }}</label>
                                        <input type="text" class="form-control"
                                            placeholder="{{ __('تفاصيل عن صحبه خير') }}" name="about_us[who_us_details]"
                                            value="{{ $about_us->who_us_details ?? '' }}">
                                    </div>
                                </div><!--end col-->


                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">الصوره الكبيره</h4>
                                        </div><!-- end card header -->

                                        <div class="card-body">
                                            <div class="img-upload">

                                                <div id="image-preview" class="img-preview"
                                                    style="background: url({{ $gs->about_us_large_photo ? $gs->about_us_large_photo : asset('assets/images/noimage.png') }}); width: 142px; height: 142px; text-align: center; background-size: cover; background-position: center;">


                                                    <!--   <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('Upload Image') }}</label>
              <input type="file" name="image_url" class="img-upload" id="image-upload">
              -->
                                                </div>

                                            </div>

                                            <div class="avatar-xl mx-auto">
                                                <input type="file" class="filepond filepond-input-circle form-control "
                                                    name="about_us_large_photo" accept="image/png, image/jpeg, image/gif, image/webp" />
                                            </div>

                                        </div>
                                        <!-- end card body -->
                                    </div>
                                    <!-- end card -->
                                </div> <!-- end col -->


                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">الصوره الصغيره</h4>
                                        </div><!-- end card header -->

                                        <div class="card-body">
                                            <div class="img-upload">

                                                <div id="image-preview" class="img-preview"
                                                    style="background: url({{ $gs->about_us_small_photo ? $gs->about_us_small_photo : asset('assets/images/noimage.png') }}); width: 142px; height: 142px; text-align: center; background-size: cover; background-position: center;">


                                                    <!--   <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('Upload Image') }}</label>
              <input type="file" name="image_url" class="img-upload" id="image-upload">
              -->
                                                </div>

                                            </div>

                                            <div class="avatar-xl mx-auto">
                                                <input type="file" class="filepond filepond-input-circle form-control "
                                                    name="about_us_small_photo" accept="image/png, image/jpeg, image/gif, image/webp" />
                                            </div>

                                        </div>
                                        <!-- end card body -->
                                    </div>
                                    <!-- end card -->
                                </div> <!-- end col -->

                                <div class="col-lg-12">
                                    <div class="featured-keyword-area">
                                        <div class="heading-area">
                                            <h4 class="title">{{ __('مهمتنا وقصص نجاحنا') }}</h4>
                                        </div>

                                        <div class="feature-tag-top-filds" id="feature-section2">
                                            @if (!empty($gs->about_us_titles))
                                                @php
                                                    $title = json_decode($gs->about_us_titles);
                                                    $details = json_decode($gs->about_us_details);

                                                @endphp
                                                @foreach ($title as $key => $data1)
                                                    <div class="feature-area">
                                                        <span class="remove feature-remove2"><i
                                                                class="fas fa-times"></i></span>
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <input type="text" name="about_us_titles[]"
                                                                    class="input-field" placeholder="{{ __('العنوان') }}"
                                                                    value="{{ $title[$key] }}">
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <input type="text" name="about_us_details[]"
                                                                    class="input-field" placeholder="{{ __('التفاصيل') }}"
                                                                    value="{{ $details[$key] }}">
                                                            </div>


                                                        </div>

                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="feature-area">
                                                    <span class="remove feature-remove2"><i
                                                            class="fas fa-times"></i></span>
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <input type="text" name="about_us_titles[]"
                                                                class="input-field" placeholder="{{ __('العنوان') }}">
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <input type="text" name="about_us_details[]"
                                                                class="input-field" placeholder="{{ __('التفاصيل') }}">
                                                        </div>


                                                    </div>

                                                </div>
                                            @endif
                                        </div>

                                        <a href="javascript:;" id="feature-btn2" class="add-fild-btn"><i
                                                class="icofont-plus"></i> {{ __('اضافه حقول اخرى') }}</a>
                                    </div>
                                </div>


                                <div class="col-lg-12">
                                    <div class="featured-keyword-area">
                                        <div class="heading-area">
                                            <h4 class="title">{{ __('بروجرس') }}</h4>
                                        </div>

                                        <div class="feature-tag-top-filds" id="feature-section">
                                            @if (!empty($gs->about_us_progress))
                                                @php
                                                    $progress = json_decode($gs->about_us_progress);
                                                    $percent = json_decode($gs->about_us_percent);

                                                @endphp
                                                @foreach ($progress as $key => $data1)
                                                    <div class="feature-area">
                                                        <span class="remove feature-remove"><i
                                                                class="fas fa-times"></i></span>
                                                        <div class="row">
                                                            <div class="col-lg-6">
                                                                <input type="text" name="about_us_progress[]"
                                                                    class="input-field" placeholder="{{ __('العنوان') }}"
                                                                    value="{{ $title[$key] }}">
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <input type="text" name="about_us_percent[]"
                                                                    class="input-field" placeholder="{{ __('النسبه') }}"
                                                                    value="{{ $details[$key] }}">
                                                            </div>


                                                        </div>

                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="feature-area">
                                                    <span class="remove feature-remove"><i
                                                            class="fas fa-times"></i></span>
                                                    <div class="row">
                                                        <div class="col-lg-6">
                                                            <input type="text" name="about_us_progress[]"
                                                                class="input-field" placeholder="{{ __('العنوان') }}">
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <input type="text" name="about_us_percent[]"
                                                                class="input-field" placeholder="{{ __('النسبه') }}">
                                                        </div>


                                                    </div>

                                                </div>
                                            @endif
                                        </div>

                                        <a href="javascript:;" id="feature-btn" class="add-fild-btn"><i
                                                class="icofont-plus"></i> {{ __('اضافه حقول اخرى') }}</a>
                                    </div>
                                </div>


                                <br>
                                <br>
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

@endsection

@section('scripts')
    <script>
        $("#feature-btn2").on('click', function() {

            $("#feature-section2").append('' +
                '<div class="feature-area">' +
                '<span class="remove feature-remove2"><i class="fas fa-times"></i></span>' +
                '<div  class="row">' +
                '<div class="col-lg-6">' +
                '<input type="text" name="about_us_titles[]" class="input-field" placeholder="العنوان">' +
                '</div>' +
                '<div class="col-lg-6">' +
                '<input type="text" name="about_us_details[]" class="input-field" placeholder="التفاصيل">' +
                '</div>' +

                '</div>' +

                '</div>' +
                '</div>' +
                '');

        });
        $(document).on('click', '.feature-remove2', function() {

            $(this.parentNode).remove();
            if (isEmpty($('#feature-section2'))) {

                $("#feature-section2").append('' +
                    '<div class="feature-area">' +
                    '<span class="remove feature-remove2"><i class="fas fa-times"></i></span>' +
                    '<div  class="row">' +
                    '<div class="col-lg-6">' +
                    '<input type="text" name="about_us_titles[]" class="input-field" placeholder="العنوان">' +
                    '</div>' +
                    '<div class="col-lg-6">' +
                    '<input type="text" name="about_us_details[]" class="input-field" placeholder="التفاصيل">' +
                    '</div>' +

                    '</div>' +

                    '</div>' +
                    '</div>' +
                    '');
                
            }

        });


         $("#feature-btn").on('click', function() {

            $("#feature-section").append('' +
                '<div class="feature-area">' +
                '<span class="remove feature-remove"><i class="fas fa-times"></i></span>' +
                '<div  class="row">' +
                '<div class="col-lg-6">' +
                '<input type="text" name="about_us_progress[]" class="input-field" placeholder="العنوان">' +
                '</div>' +
                '<div class="col-lg-6">' +
                '<input type="text" name="about_us_percent[]" class="input-field" placeholder="النسبه">' +
                '</div>' +

                '</div>' +

                '</div>' +
                '</div>' +
                '');

        });
        $(document).on('click', '.feature-remove', function() {

            $(this.parentNode).remove();
            if (isEmpty($('#feature-section'))) {

                $("#feature-section").append('' +
                    '<div class="feature-area">' +
                    '<span class="remove feature-remove"><i class="fas fa-times"></i></span>' +
                    '<div  class="row">' +
                    '<div class="col-lg-6">' +
                    '<input type="text" name="about_us_progress[]" class="input-field" placeholder="العنوان">' +
                    '</div>' +
                    '<div class="col-lg-6">' +
                    '<input type="text" name="about_us_percent[]" class="input-field" placeholder="النسبه">' +
                    '</div>' +

                    '</div>' +

                    '</div>' +
                    '</div>' +
                    '');
                
            }

        });



    </script>
@endsection
