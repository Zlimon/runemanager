<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function() {
    Route::prefix('/admin')->group(function() {
        Route::post('/news/create', 'Admin\Api\NewsController@store')->name('admin-store-newspost');
        Route::put('/news/{newsPost}/update', 'Admin\Api\NewsController@update')->name('admin-update-newspost');

        Route::post('/calendar/create', 'Admin\Api\CalendarController@store');
        Route::post('/calendar/{calendar}/update', 'Admin\Api\CalendarController@update');
        Route::patch('/calendar/{calendar}/schedule', 'Admin\Api\CalendarController@updateSchedule');
        Route::delete('/calendar/{calendar}/destroy', 'Admin\Api\CalendarController@destroy');

		Route::post('/settings/resource-pack', 'Admin\Api\ResourcePackController@search')->name('admin-settings-resourcepack-search');
		Route::patch('/settings/resource-pack/{resourcePack}/switch', 'Admin\Api\ResourcePackController@switch')->name('admin-settings-resourcepack-switch');
		Route::patch('/settings/resource-pack/{resourcePack}/update', 'Admin\Api\ResourcePackController@update')->name('admin-settings-resourcepack-update');
    });
});
