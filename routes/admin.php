<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\GeneralSettingController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

 
Route::prefix('admin')->group(function() {


  Route::middleware('auth.admin')->group(function () {
   
     //------------ ADMIN DASHBOARD & PROFILE SECTION ------------
  Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
  Route::get('/profile',  [DashboardController::class, 'profile'])->name('admin.profile');
  Route::post('/profile/update', [DashboardController::class, 'profileupdate'])->name('admin.profile.update');
  Route::get('/password', [DashboardController::class, 'passwordreset'])->name('admin.password');
  Route::post('/password/update',  [DashboardController::class, 'changepass'])->name('admin.password.update');
  //------------ ADMIN DASHBOARD & PROFILE SECTION ENDS ------------

  

  Route::group(['middleware' => 'permissions:super'], function () {


    Route::get('/cache/clear', function () {
      Artisan::call('cache:clear');
      Artisan::call('config:clear');
      Artisan::call('route:clear');
      Artisan::call('view:clear');
      return redirect()->route('admin.dashboard')->with('cache', 'System Cache Has Been Removed.');
    })->name('admin-cache-clear');



    // ------------ ROLE SECTION ----------------------

    Route::get('/role/datatables',  [RoleController::class, 'datatables'])->name('admin-role-datatables');
    Route::get('/role',  [RoleController::class, 'index'])->name('admin-role-index');
    Route::get('/role/create',  [RoleController::class, 'create'])->name('admin-role-create');
    Route::post('/role/create',  [RoleController::class, 'store'])->name('admin-role-store');
    Route::get('/role/edit/{id}',  [RoleController::class, 'edit'])->name('admin-role-edit');
    Route::post('/role/edit/{id}', [RoleController::class, 'update'])->name('admin-role-update');
    Route::get('/role/delete/{id}',  [RoleController::class, 'destroy'])->name('admin-role-delete');

    // ------------ ROLE SECTION ENDS ----------------------


  });



  Route::group(['middleware' => 'permissions:manage_staffs'], function () {

    Route::get('/staff/datatables',  [StaffController::class, 'datatables'])->name('admin-staff-datatables');
    Route::get('/staff',  [StaffController::class, 'index'])->name('admin-staff-index');
    Route::get('/staff/create',   [StaffController::class, 'create'])->name('admin-staff-create');
    Route::post('/staff/create',  [StaffController::class, 'store'])->name('admin-staff-store');
    Route::get('/staff/edit/{id}',  [StaffController::class, 'edit'])->name('admin-staff-edit');
    Route::post('/staff/update/{id}', [StaffController::class, 'update'])->name('admin-staff-update');
    Route::get('/staff/show/{id}', [StaffController::class, 'show'])->name('admin-staff-show');
    Route::get('/staff/delete/{id}',  [StaffController::class, 'destroy'])->name('admin-staff-delete');
  });


  Route::group(['middleware' => 'permissions:general_settings'], function () {

    Route::get('/general-settings/logo', [GeneralSettingController::class, 'logo'])->name('admin-gs-logo');
    Route::get('/general-settings/contents', [GeneralSettingController::class, 'contents'])->name('admin-gs-contents');
    Route::post('/general-settings/update/all', [GeneralSettingController::class, 'generalupdate'])->name('admin-gs-update');
    Route::get('/general-settings/Adminloader', [GeneralSettingController::class, 'load2'])->name('admin-gs-load2');

    Route::get('/general-settings/admin/loader/{status}', [GeneralSettingController::class, 'isadminloader'])->name('admin-gs-is-admin-loader');
  });



});
 

//------------ ADMIN LOGIN SECTION ------------

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
Route::post('/login', [LoginController::class, 'login'])->name('admin.login.submit');
Route::get('/forgot', [LoginController::class, 'showForgotForm'])->name('admin.forgot');
Route::post('/forgot', [LoginController::class, 'forgot'])->name('admin.forgot.submit');
Route::get('/logout', [LoginController::class, 'logout'])->name('admin.logout');


//Update User Details
Route::post('/update-profile/{id}', [DashboardController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [DashboardController::class, 'updatePassword'])->name('updatePassword');

});