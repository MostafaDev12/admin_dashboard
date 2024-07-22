@extends('layouts.new-app')

@section('title')
    @include('partials.title-meta', ['title' => 'home Contents'])

@stop

@section('page-title')
    @include('partials.page-title', ['pagetitle' => 'Dashboards', 'title' => 'home Contents'])
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
                                    
                                $home_content =  json_decode($gs->home_content); 
                                @endphp

                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="firstNameinput"
                                            class="form-label">{{ __('انضم إلى المجتمع للعطاء
                                            التعليم للأطفال') }}</label>
                                        <input type="text" class="form-control"
                                            placeholder="{{ __('انضم إلى المجتمع للعطاء
                                            التعليم للأطفال') }}" name="home_content[first_banner]"
                                            value="{{ $home_content->first_banner ?? '' }}">
                                    </div>
                                </div><!--end col-->


                                <div class="col-6">
                                    <div class="mb-3">
                                        <label for="firstNameinput"
                                            class="form-label">{{ __('لماذا نحن') }}</label>
                                        <input type="text" class="form-control"
                                            placeholder="{{ __('لماذا نحن') }}"
                                            name="home_content[secend_banner]"
                                            value="{{ $home_content->secend_banner ?? '' }}">
                                    </div>
                                </div><!--end col-->

  
                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">   البانر الاول</h4>
                                        </div><!-- end card header -->

                                        <div class="card-body">
                                            <div class="img-upload">

                                                <div id="image-preview" class="img-preview"
                                                    style="background: url({{ $gs->first_banner ? $gs->first_banner : asset('assets/images/noimage.png') }}); width: 242px; height: 142px; text-align: center; background-size: cover; background-position: center;">


                                                    <!--   <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('Upload Image') }}</label>
              <input type="file" name="image_url" class="img-upload" id="image-upload">
              -->
                                                </div>

                                            </div>

                                            <div class="avatar-xl mx-auto">
                                                <input type="file" class="filepond filepond-input-circle form-control "
                                                    name="first_banner" accept="image/png, image/jpeg, image/gif, image/webp" />
                                            </div>

                                        </div>
                                        <!-- end card body -->
                                    </div>
                                    <!-- end card -->
                                </div> <!-- end col -->


                                <div class="col-lg-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <h4 class="card-title mb-0">   البانر الثانى</h4>
                                        </div><!-- end card header -->

                                        <div class="card-body">
                                            <div class="img-upload">

                                                <div id="image-preview" class="img-preview"
                                                    style="background: url({{ $gs->secend_banner ? $gs->secend_banner : asset('assets/images/noimage.png') }}); width: 242px; height: 142px; text-align: center; background-size: cover; background-position: center;">


                                                    <!--   <label for="image-upload" class="img-label" id="image-label"><i class="icofont-upload-alt"></i>{{ __('Upload Image') }}</label>
              <input type="file" name="image_url" class="img-upload" id="image-upload">
              -->
                                                </div>

                                            </div>

                                            <div class="avatar-xl mx-auto">
                                                <input type="file" class="filepond filepond-input-circle form-control "
                                                    name="secend_banner" accept="image/png, image/jpeg, image/gif, image/webp" />
                                            </div>

                                        </div>
                                        <!-- end card body -->
                                    </div>
                                    <!-- end card -->
                                </div> <!-- end col -->

                           

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
 
@endsection
