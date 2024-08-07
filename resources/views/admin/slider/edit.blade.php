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
        {{ __("translation.edit_slider") }}
        @endslot
    @endcomponent


    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">



            </div>
            <div class="card-body">
              <form id="geniusform" action="{{route('admin-slider-update',$data->id)}}" method="POST" enctype="multipart/form-data">
                {{csrf_field()}}
                @include('includes.admin.form-both')



                    <div class="row">


                        <div class="col-xxl-12">

                            <div class="card">
                                <div class="card-body">

                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs nav-justified mb-3" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-bs-toggle="tab" href="#base-justified-home"
                                                role="tab" aria-selected="false">
                                                <img style="width: 35px;" src="{{ asset('assets/images/ar.jpg') }}">
                                                {{ __('translation.arabic') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link " data-bs-toggle="tab" href="#base-justified-product"
                                                role="tab" aria-selected="false">
                                                <img style="width: 35px;" src="{{ asset('assets/images/en.png') }}">
                                                {{ __('translation.english') }}
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-bs-toggle="tab" href="#base-justified-messages"
                                                role="tab" aria-selected="false">
                                                <img style="width: 35px;" src="{{ asset('assets/images/fr.png') }}">
                                                {{ __('translation.france') }}
                                            </a>
                                        </li>

                                    </ul>
                                    <!-- Tab panes -->
                                    <div class="tab-content  text-muted">
                                        <div class="tab-pane active" id="base-justified-home" role="tabpanel">
                                            <h6 style="text-align: center;">   {{ __('translation.arabic') }}</h6>
                                            
                                      
                                              <div class="mb-3">
                                                  <label for="title_ar" class="form-label">{{ __('translation.title') }}</label>
                                                  <input type="text" class="form-control" name="title_ar" value="{{ $data->title_ar }}" id="title_ar" placeholder="{{ __('translation.title') }}">
                                              </div>
                                               
                                              <div class="mb-3">
                                                  <label for="details_ar" class="form-label">{{ __('translation.details') }}</label>
                                                  <textarea class="form-control" name="details_ar"  id="details_ar" rows="3" placeholder="{{ __('translation.details') }}">{{ $data->details_ar }}</textarea>
                                              </div>
                                              
                                        </div>
                                        <div class="tab-pane " id="base-justified-product" role="tabpanel">
                                            <h6 style="text-align: center;"> {{ __('translation.english') }}</h6>
                                           
                                            <div class="mb-3">
                                              <label for="title_en" class="form-label">{{ __('translation.title') }}</label>
                                              <input type="text" class="form-control" name="title_en"  value="{{ $data->title_en }}"  id="title_en" placeholder="{{ __('translation.title') }}">
                                          </div>
                                           
                                          <div class="mb-3">
                                              <label for="details_en" class="form-label">{{ __('translation.details') }}</label>
                                              <textarea class="form-control" name="details_en"  id="details_en" rows="3" placeholder="{{ __('translation.details') }}">{{ $data->details_en }}</textarea>
                                          </div>
                                          
                                        </div>
                                        <div class="tab-pane" id="base-justified-messages" role="tabpanel">
                                            <h6 style="text-align: center;">{{ __('translation.france') }}</h6>
                                           

                                            <div class="mb-3">
                                              <label for="title_fr" class="form-label">{{ __('translation.title') }}</label>
                                              <input type="text" class="form-control" name="title_fr"  value="{{ $data->title_fr }}"  id="title_fr" placeholder="{{ __('translation.title') }}">
                                          </div>
                                           
                                          <div class="mb-3">
                                              <label for="details_fr" class="form-label">{{ __('translation.details') }}</label>
                                              <textarea class="form-control" name="details_fr"  id="details_fr" rows="3" placeholder="{{ __('translation.details') }}">{{ $data->details_fr }}</textarea>
                                          </div>
                                        </div>

                                    </div>
                                </div><!-- end card-body -->
                            </div><!-- end card -->
                        </div>
                    </div>
  
                        <div class="row">


                            <div class="col-xl-12 col-md-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title mb-0"> {{ __('translation.photo') }}</h4>
                                    </div><!-- end card header -->

                                    <div class="card-body">
                                        <p class="text-muted">FilePond is a JavaScript library with profile picture-shaped
                                            file
                                            upload variation.</p>
                                        <div class="currrent-logo" style="text-align: center;">
                                            <img style="width: 171px;" src="{{$data->photo ? $data->photo  :  asset('assets/images/noimage.png') }}"
                                                alt="">
                                        </div>
                                        <div class="avatar-xl mx-auto">
                                            <input type="file" class="filepond filepond-input-circle" name="photo"
                                                accept="image/png, image/jpeg, image/gif, image/webp" />
                                        </div>


                                    </div>
                                    <!-- end card body -->


                                </div>
                                <!-- end card -->
                            </div> <!-- end col -->


                        </div>



                        <br>
                        <br>
                        <br>
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="left-area">

                                </div>
                            </div>
                            <div class="col-lg-7">
                                <button class="addProductSubmit-btn btn btn-secondary"
                                    type="submit">{{ __('translation.save') }}</button>
                            </div>
                        </div>
                </form>
            </div>
        </div>
    </div>
@endsection
