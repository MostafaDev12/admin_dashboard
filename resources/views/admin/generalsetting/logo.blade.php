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
            website logo
        @endslot 
    @endcomponent


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
                                     <div class="content-area" style="padding-top: 80px; padding-left: 10px; padding-bottom: 20px; max-width: 100%">

        <div class="add-logo-area">
            <div class="gocover"
                style="background: url({{ asset('assets/images/' . $gs->admin_loader) }}) no-repeat scroll center center rgba(45, 45, 45, 0.5);">
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-4 col-md-6">
                    <div class="special-box bg-gray">
                        <div class="heading-area">
                            <h4 class="title">
                                {{ __('Header Logo') }}
                            </h4>
                        </div>

                        <form class="uplogo-form" id="geniusform" action="{{ route('admin-gs-update') }}" method="POST"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('includes.admin.form-both')
                            <div class="currrent-logo">
                                <img style="width: 171px;"
                                    src="{{ $gs->logo ? asset('assets/images/' . $gs->logo) : asset('assets/images/noimage.png') }}"
                                    alt="">
                            </div>
                            <div class="set-logo">
                                <input class="img-upload1" type="file" name="logo"
                                    style="width:200px  !important  background-color: #f79159  !important;">
                            </div>
                            <div class='mt-3'>
                                <span class='span-img-size'>Image size 305px X 75px</span>
                            </div>
                            <br>
                            <div class="submit-area mb-4">
                                <button type="submit" class="submit-btn btn btn-primary">{{ __('Submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-xl-4 col-md-6">
                    <div class="special-box bg-gray">
                        <div class="heading-area">
                            <h4 class="title">
                                {{ __('Header white Logo') }}
                            </h4>
                        </div>

                        <form class="uplogo-form" id="geniusform" action="{{ route('admin-gs-update') }}" method="POST"
                            enctype="multipart/form-data">
                            {{ csrf_field() }}

                            @include('includes.admin.form-both')
                            <div class="currrent-logo">
                                <img style="width: 171px;"
                                    src="{{ $gs->logo_white ? asset('assets/images/' . $gs->logo_white) : asset('assets/images/noimage.png') }}"
                                    alt="">
                            </div>
                            <div class="set-logo">
                                <input class="img-upload1" type="file" name="logo_white">
                            </div>
                            <div class='mt-3'>
                                <span class='span-img-size'>Image size 305px X 75px</span>
                            </div>
                            <br>
                            <div class="submit-area mb-4">
                                <button type="submit" class="submit-btn btn btn-primary">{{ __('Submit') }}</button>
                            </div>
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
                    </div>
   
@endsection
