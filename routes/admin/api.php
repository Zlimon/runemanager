<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function() {
    Route::prefix('/admin')->group(function() {
        Route::post('/user/search', 'Admin\Api\UserController@search')->name('admin-user-search');
        Route::put('/user/{user}/update', 'Admin\Api\UserController@update')->name('admin-user-update');

        Route::post('/account/store', 'Admin\Api\AccountController@store')->name('admin-account-store');
        Route::post('/account/search', 'Admin\Api\AccountController@search')->name('admin-account-search');
        Route::put('/account/{account}/update', 'Admin\Api\AccountController@update')->name('admin-account-update');

        Route::post('/news/create', 'Admin\Api\NewsController@store')->name('admin-newspost-store');
        Route::put('/news/{newsPost}/update', 'Admin\Api\NewsController@update')->name('admin-newspost-update');
        Route::delete('/news/{newsPost}/destroy', 'Admin\Api\NewsController@destroy')->name('admin-newspost-destroy');

        Route::post('/calendar/create', 'Admin\Api\CalendarController@store')->name('admin-calendar');
        Route::post('/calendar/{calendar}/update', 'Admin\Api\CalendarController@update')->name('admin-calendar-update');
        Route::patch('/calendar/{calendar}/schedule', 'Admin\Api\CalendarController@updateSchedule')->name('admin-calendar-schedule-update');
        Route::delete('/calendar/{calendar}/destroy', 'Admin\Api\CalendarController@destroy')->name('admin-calendar-destroy');

		Route::post('/settings/resource-pack', 'Admin\Api\ResourcePackController@search')->name('admin-settings-resourcepack-search');
		Route::patch('/settings/resource-pack/{resourcePack}/switch', 'Admin\Api\ResourcePackController@switch')->name('admin-settings-resourcepack-switch');
		Route::patch('/settings/resource-pack/{resourcePack}/update', 'Admin\Api\ResourcePackController@update')->name('admin-settings-resourcepack-update');
    });
});
