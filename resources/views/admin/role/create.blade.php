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


 <div class="col-lg-12" style="padding-top: 80px; padding-left: 10px; padding-bottom: 20px; max-width: 100%">
                            <div class="card">
                                <div class="card-header">
                                     <h4 class="heading">{{ __(' اضافه صلاحيه  ') }} <a class="add-btn" href="{{route('admin-role-index')}}"><i class="fas fa-arrow-left"></i> {{ __(' الرجوع') }}</a></h4>
                                   	@include('includes.admin.form-success')


                                </div>
                                <div class="card-body">
                                     <form id="geniusform" action="{{route('admin-role-create')}}" method="POST" enctype="multipart/form-data">
                        {{csrf_field()}}
                      @include('includes.admin.form-both')

                        <div class="row">
                          <div class="col-lg-2">
                            <div class="left-area">
                                <h4 class="heading">{{ __("الاسم") }} *</h4>
                                
                            </div>
                          </div>
                          <div class="col-lg-10">
                            <input type="text" class="form-control" name="name" placeholder="{{ __('الاسم') }}" required="" value="">
                          </div>
                        </div>

                        <hr>
                        <h5 class="text-center">{{ __('الصلاحيات') }}</h5>
                        <hr>


                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="users" role="switch" name="section[]"  id="users" >
                            <label class="form-check-label" for="users">{{ __('المستخدمين') }}  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="general_settings" role="switch" name="section[]"  id="general_settings" >
                            <label class="form-check-label" for="general_settings">{{ __('   الاعدادات') }}  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="manage_staffs" role="switch" name="section[]"  id="manage_staffs" >
                            <label class="form-check-label" for="manage_staffs">{{ __('Manage Staffs') }}  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="super" role="switch" name="section[]"  id="super" >
                            <label class="form-check-label" for="super">{{ __('Manage Role & Cache clear') }}  </label>
                        </div>

                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="candidates" role="switch" name="section[]"  id="candidates" >
                            <label class="form-check-label" for="candidates">{{ __('المرشحين') }}  </label>
                        </div>


                        <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="campaigns" role="switch" name="section[]"  id="campaigns" >
                            <label class="form-check-label" for="campaigns">{{ __('الحملات') }}  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="country" role="switch" name="section[]"  id="country" >
                            <label class="form-check-label" for="country">{{ __('المحافظات') }}  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="tasks" role="switch" name="section[]"  id="tasks" >
                            <label class="form-check-label" for="tasks">{{ __('المهام') }}  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="complaints_suggestions" role="switch" name="section[]"  id="complaints_suggestions" >
                            <label class="form-check-label" for="complaints_suggestions">{{ __('المهام') }}  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="volunteers" role="switch" name="section[]"  id="volunteers" >
                            <label class="form-check-label" for="volunteers">{{ __('المتطوعين فى الحمله') }}  </label>
                        </div>


   <!-- Switches Color -->
                        <div class="form-check form-switch form-check-inline">
                            <input class="form-check-input" type="checkbox"  value="complaints_suggestions" role="switch" name="section[]"  id="complaints_suggestions" >
                            <label class="form-check-label" for="complaints_suggestions">{{ __('الشكاوى والمقترحات') }}  </label>
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
                            <button class="addProductSubmit-btn btn btn-secondary" type="submit">{{ __('   حفظ') }}</button>
                          </div>
                        </div>
                      </form>
                                </div>
                            </div>
                        </div>




@endsection
