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
            roles
        @endslot
    @endcomponent


 <div class="col-lg-12" style=" ">
                            <div class="card">
                                <div class="card-header">
                               
                                 
                                </div>
                                <div class="card-body">
                                      <form id="geniusform" action="{{route('admin-role-update',$data->id)}}" method="POST" enctype="multipart/form-data">
                                      {{csrf_field()}}
                                      @include('includes.admin.form-both')


                        <div class="row">
                          <div class="col-lg-2">
                            <div class="left-area">
                                <h4 class="heading">{{ __("الاسم") }} *</h4>
                               
                            </div>
                          </div>
                          <div class="col-lg-10">
                            <input type="text" class="form-control" name="name" placeholder="{{ __('الاسم') }}" required="" value="{{$data->name}}">
                          </div>
                        </div>

                        <hr>
                        <h5 class="text-center">{{ __('الصلاحيات') }}</h5>
                        <hr>


                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="users" role="switch" name="section[]"  id="users" {{ $data->sectionCheck('users') ? 'checked' : '' }}>
                            <label class="form-check-label" for="users">{{ __('المستخدمين') }}  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="general_settings" role="switch" name="section[]"  id="general_settings" {{ $data->sectionCheck('general_settings') ? 'checked' : '' }}>
                            <label class="form-check-label" for="general_settings">{{ __('   الاعدادات') }}  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="manage_staffs" role="switch" name="section[]"  id="manage_staffs" {{ $data->sectionCheck('manage_staffs') ? 'checked' : '' }}>
                            <label class="form-check-label" for="manage_staffs">{{ __('Manage Staffs') }}  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="super" role="switch" name="section[]"  id="super" {{ $data->sectionCheck('super') ? 'checked' : '' }}>
                            <label class="form-check-label" for="super">{{ __('Manage Role & Cache clear') }}  </label>
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
                            <button class="addProductSubmit-btn btn btn-secondary" type="submit">{{ __('  حفظ') }}</button>
                          </div>
                        </div>
                      </form>
                                </div>
                            </div>
                        </div>


@endsection
