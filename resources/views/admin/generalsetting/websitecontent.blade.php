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
            website content
        @endslot
    @endcomponent


<div class="page-content">
    <div class="container-fluid">
  
                      <input type="hidden" id="headerdata" value="{{ __("Website Contents") }}" style="box-shadow: rgba(0, 0, 0, 0.19) 0px 10px 20px, rgba(0, 0, 0, 0.23) 0px 6px 6px;">
                           <div class="col-lg-12 content_row"  >
                              <div class="card">
                                  <div class="card-header">
                                         @include('includes.admin.form-success')
                                         <div class="btn-area"></div>
                                  </div>
                                  <div class="card-body" style="box-shadow: rgba(60, 64, 67, 0.3) 0px 1px 2px 0px, rgba(60, 64, 67, 0.15) 0px 2px 6px 2px;">
                                       <form action="{{ route('admin-gs-update') }}" id="geniusform" method="POST" enctype="multipart/form-data">
        {{ csrf_field() }}

        @include('includes.admin.form-both')

        <div class="row" style="padding-top: 100px; padding-left: 10px; padding-bottom: 20px; max-width: 100%">
            <div class="col-6">
                <div class="mb-3">
                    <label for="firstNameinput" class="form-label">{{ __('Website Title') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write Your Site Title Here') }}"
                        name="title" value="{{ $gs->title }}" required="">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="lastNameinput" class="form-label">{{ __('vonage_key') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write vonage_key Here') }}"
                        name="vonage_key" value="{{ $gs->vonage_key }}">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="compnayNameinput" class="form-label">{{ __('vonage_secret') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write vonage_secret Here') }}"
                        name="vonage_secret" value="{{ $gs->vonage_secret }}">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="phonenumberInput" class="form-label">{{ __('sms_title') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write sms_title Here') }}"
                        name="sms_title" value="{{ $gs->sms_title }}">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <div class="mb-3">
                    <label for="emailidInput" class="form-label">{{ __('sms_message') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write sms_message Here') }}"
                        name="sms_message" value="{{ $gs->sms_message }}">
                </div>
            </div><!--end col-->  
             <div class="col-6">
                <div class="mb-3">
                    <label for="address" class="form-label">{{ __('address') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write address Here') }}"
                        name="address" value="{{ $gs->address }}">
                </div>
            </div><!--end col-->  
             <div class="col-6">
                <div class="mb-3">
                    <label for="phone" class="form-label">{{ __('phone') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write phone Here') }}"
                        name="phone" value="{{ $gs->phone }}">
                </div>
            </div><!--end col-->  
             <div class="col-6">
                <div class="mb-3">
                    <label for="fax" class="form-label">{{ __('fax') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write fax Here') }}"
                        name="fax" value="{{ $gs->fax }}">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('email') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write email Here') }}"
                        name="email" value="{{ $gs->email }}">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="from_name" class="form-label">{{ __('from_name') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write from_name Here') }}"
                        name="from_name" value="{{ $gs->from_name }}">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="from_email" class="form-label">{{ __('from_email') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write from_email Here') }}"
                        name="from_email" value="{{ $gs->from_email }}">
                </div>
            </div><!--end col-->
             <div class="col-6">
                <div class="mb-3">
                    <label for="footer" class="form-label">{{ __('why us footer') }}</label>
                    <input type="text" class="form-control" placeholder="{{ __('Write footer Here') }}"
                        name="footer" value="{{ $gs->footer }}">
                </div>
            </div><!--end col-->
            <div class="col-6">
                <br>
                <div class="mb-3">
                    <div class="form-check form-switch form-switch-lg" dir="ltr">
                        <input type="checkbox" class="form-check-input" value="1" name="is_elections"
                            id="customSwitchsizelg" {{ $gs->is_elections == 1 ? 'checked' : '' }}>
                        <label class="form-check-label" for="customSwitchsizelg">is_capcha</label>
                    </div>
                </div>
            </div>
            {{--  --}}
            <!--end col-->

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
