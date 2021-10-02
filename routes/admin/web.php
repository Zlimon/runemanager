<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function() {
    Route::prefix('/admin')->group(function() {
		Route::get('/', 'Admin\AdminController@index')->name('admin-index');

		Route::get('/user', 'Admin\UserController@index')->name('admin-user');
		Route::get('/user/{user}/show', 'Admin\UserController@show')->name('admin-user-show');
		Route::get('/user/{user}/edit', 'Admin\UserController@edit')->name('admin-user-edit');

		Route::get('/account', 'Admin\AccountController@index')->name('admin-account');
		Route::get('/account/create', 'Admin\AccountController@create')->name('admin-account-create');
		Route::get('/account/{account}/show', 'Admin\AccountController@show')->name('admin-account-show');

		Route::get('/news', 'Admin\NewsController@index')->name('admin-newspost');
		Route::get('/news/create', 'Admin\NewsController@create')->name('admin-newspost-create');
		Route::get('/news/{newsPost}/show', 'Admin\NewsController@show')->name('admin-newspost-show');
		Route::get('/news/{newsPost}/edit', 'Admin\NewsController@edit')->name('admin-newspost-edit');

		Route::post('/news/category/create', 'Admin\NewsController@createCategory')->name('admin-newspost-category-create');

		Route::get('/calendar', 'Admin\CalendarController@index')->name('admin-calendar');
		Route::delete('/calendar/truncate', 'Admin\CalendarController@truncate')->name('admin-calendar-truncate');

		Route::get('/settings', 'Admin\SettingsController@index')->name('admin-settings');
		Route::post('/settings', 'Admin\SettingsController@store')->name('admin-settings-store');
		Route::get('/settings/resource-pack', 'Admin\SettingsController@resourcePack')->name('admin-settings-resourcepack');
    });
});
