<?php

use Illuminate\Support\Facades\Route;

//Route::group(['middleware' => ['permission:access admin']], function () {
Route::middleware('auth')->group(function() {
    Route::prefix('/admin')->group(function() {
		Route::get('/', 'Admin\AdminController@index')->name('admin-index');
		/*==========News Controller=============*/
		Route::get('/news', 'Admin\NewsController@index')->name('admin-news');
		Route::get('/news/create', 'Admin\NewsController@create')->name('admin-create-newspost');
		Route::get('/news/{newsPost}/show', 'Admin\NewsController@show')->name('admin-show-newspost');
		Route::get('/news/{newsPost}/edit', 'Admin\NewsController@edit')->name('admin-edit-newspost');
		Route::delete('/news/{newsPost}delete', 'Admin\NewsController@destroy')->name('admin-delete-newspost');

		Route::post('/news/category/create', 'Admin\NewsController@createCategory')->name('admin-create-newspost-category');
		//Route::resource('/admin/news', 'AdminNewsController');
		/*==========User Controller=============*/
		Route::get('/user', 'Admin\UserController@index')->name('admin-user');

		Route::get('/user/{user}/show', 'Admin\UserController@show')->name('admin-show-user');
		Route::get('/user/{user}/edit', 'Admin\UserController@edit')->name('admin-edit-user');
		Route::patch('/user/{user}/edit', 'Admin\UserController@update')->name('admin-update-user');
		/*==========Member Controller=============*/
		Route::get('/account', 'Admin\AccountController@index')->name('admin-account');

		Route::get('/account/create', 'Admin\AccountController@create')->name('admin-create-account');
		Route::post('/account/create', 'Admin\AccountController@store')->name('admin-store-account');
		Route::get('/account/{account}/show', 'Admin\AccountController@show')->name('admin-show-account');
		Route::patch('/account/{account}/show', 'Admin\AccountController@update')->name('admin-update-account');
		/*==========Calendar Controller=============*/
		Route::get('/calendar', 'Admin\CalendarController@index')->name('admin-calendar');
		Route::delete('/calendar/truncate', 'Admin\CalendarController@truncate')->name('admin-calendar-truncate');
		/*==========Settings Controller=============*/
		Route::get('/settings', 'Admin\SettingsController@index')->name('admin-settings');
		Route::post('/settings', 'Admin\SettingsController@store')->name('admin-settings-store');
		Route::get('/settings/resource-pack', 'Admin\SettingsController@resourcePack')->name('admin-settings-resourcepack');
		/*==========Rank Controller=============*/
//        Route::get('/admin/rank', 'AdminRoleController@index')->name('admin-rank');
    });
});
