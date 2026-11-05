<?php

use App\Http\Controllers\Admin\CmsAuthController;
use App\Http\Controllers\Admin\CmsController;
use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::get('/admin/login', [CmsAuthController::class, 'form'])->name('admin.login');
Route::post('/admin/login', [CmsAuthController::class, 'login'])->middleware('throttle:5,1')->name('admin.login.submit');
Route::post('/admin/logout', [CmsAuthController::class, 'logout'])->middleware('auth.admin')->name('cms.logout');
Route::match(['get', 'post'], '/admin/forgot', fn () => redirect()->route('admin.login')->withErrors(['email' => 'Ask the site owner to reset your account using website:admin.']));
Route::middleware(['auth.admin', 'can:manage-cms'])->prefix('admin/cms')->name('cms.')->group(function () {
    Route::get('/users', [\App\Http\Controllers\Admin\CmsUserController::class, 'index'])->name('users');
    Route::post('/users', [\App\Http\Controllers\Admin\CmsUserController::class, 'store'])->name('users.store');
    Route::put('/users/{admin}', [\App\Http\Controllers\Admin\CmsUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{admin}', [\App\Http\Controllers\Admin\CmsUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/', [CmsController::class, 'dashboard'])->name('dashboard');
    Route::get('/pages', [CmsController::class, 'pages'])->name('pages');
    Route::post('/pages', [CmsController::class, 'createPage'])->name('pages.store');
    Route::get('/pages/{page}', [CmsController::class, 'page'])->name('pages.edit');
    Route::put('/pages/{page}', [CmsController::class, 'updatePage'])->name('pages.update');
    Route::delete('/pages/{page}', [CmsController::class, 'deletePage'])->name('pages.destroy');
    Route::get('/globals', [CmsController::class, 'globals'])->name('globals');
    Route::get('/sections/{section}', [CmsController::class, 'section'])->name('sections.edit');
    Route::put('/sections/{section}', [CmsController::class, 'updateSection'])->name('sections.update');
    Route::post('/sections/{section}/items', [CmsController::class, 'addItem'])->name('items.store');
    Route::get('/items/{item}', [CmsController::class, 'item'])->name('items.edit');
    Route::put('/items/{item}', [CmsController::class, 'updateItem'])->name('items.update');
    Route::delete('/items/{item}', [CmsController::class, 'deleteItem'])->name('items.destroy');
    Route::get('/media', [CmsController::class, 'media'])->name('media');
    Route::post('/media', [CmsController::class, 'upload'])->name('media.store');
    Route::put('/media/{media}', [CmsController::class, 'updateMedia'])->name('media.update');
    Route::delete('/media/{media}', [CmsController::class, 'deleteMedia'])->name('media.destroy');
    Route::get('/submissions', [CmsController::class, 'submissions'])->name('submissions');
    Route::put('/submissions/{submission}', [CmsController::class, 'submission'])->name('submissions.update');
    Route::delete('/submissions/{submission}', [CmsController::class, 'deleteSubmission'])->name('submissions.destroy');
});
Route::get('/admin', fn () => redirect()->route('cms.dashboard'))->middleware('auth.admin')->name('admin.dashboard');
Route::get('/sitemap.xml', [WebsiteController::class, 'sitemap'])->name('website.sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nDisallow: /admin\nSitemap: ".route('website.sitemap')."\n", 200, ['Content-Type' => 'text/plain']));
Route::post('/appointment', [WebsiteController::class, 'contact'])->middleware('throttle:10,1')->name('website.contact');
Route::get('/', [WebsiteController::class, 'show'])->name('front.index');
Route::get('/{path}', [WebsiteController::class, 'show'])->where('path', '(?!admin(?:/|$)|api(?:/|$)).*')->name('website.page');
