<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\LoginController;
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