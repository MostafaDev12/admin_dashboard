@extends('layouts.master')
@section('title')
    @lang('translation.logos')
@endsection
@section('css')

@endsection
@section('content')
    @component('components.breadcrumb')
        @slot('li_1')
            Dashboards
        @endslot
        @slot('title')
            website logos
        @endslot 
    @endcomponent

 
       
            <div class="row  ">
               
                
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">English Logo</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="{{ route('admin-gs-update') }}" method="POST"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('includes.admin.form-both')
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="{{ $gs->logo_en ? asset('assets/images/' . $gs->logo_en) : asset('assets/images/noimage.png') }}"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="logo_en"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary">{{ __('Submit') }}</button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
   
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Arabic Logo</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="{{ route('admin-gs-update') }}" method="POST"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('includes.admin.form-both')
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="{{ $gs->logo_ar ? asset('assets/images/' . $gs->logo_ar) : asset('assets/images/noimage.png') }}"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="logo_ar"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary">{{ __('Submit') }}</button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
 
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">france Logo</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="{{ route('admin-gs-update') }}" method="POST"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('includes.admin.form-both')
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="{{ $gs->logo_fr ? asset('assets/images/' . $gs->logo_fr) : asset('assets/images/noimage.png') }}"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="logo_fr"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary">{{ __('Submit') }}</button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
 
                <div class="col-xl-6 col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">favicon</h4>
                        </div><!-- end card header -->

                        <form class="uplogo-form" id="geniusform" action="{{ route('admin-gs-update') }}" method="POST"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('includes.admin.form-both')
                        <div class="card-body">
                            <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped file
                                upload variation.</p>
                                <div class="currrent-logo" style="text-align: center;">
                                    <img style="width: 171px;"
                                        src="{{ $gs->favicon ? asset('assets/images/' . $gs->favicon) : asset('assets/images/noimage.png') }}"
                                        alt="">
                                </div>
                            <div class="avatar-xl mx-auto">
                                <input type="file" class="filepond filepond-input-circle" name="favicon"
                                    accept="image/png, image/jpeg, image/gif, image/webp" />
                            </div>

                        <div class="submit-area mb-4">
                            <button type="submit" class="submit-btn btn btn-primary">{{ __('Submit') }}</button>
                        </div>

                        </div>
                        <!-- end card body -->

                    </form>
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
 

            </div>
         
   
@endsection
@section('script')
   
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection
