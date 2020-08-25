<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('/hiscore')->group(function () {
	Route::get('/skill/{skill}', 'Api\HiscoreController@skill')->name('show-skill-hiscore');
	Route::get('/boss/{skill}', 'Api\HiscoreController@boss')->name('show-boss-hiscore');
});

Route::prefix('/account')->group(function () {
	Route::get('/', 'Api\AccountController@index')->name('account');
	Route::get('/{account}', 'Api\AccountController@show')->name('show-account');
});

Route::prefix('/boss')->group(function () {
	// Route::middleware('auth:api')->group(function () {
		Route::get('/', 'CollectionController@index');
		Route::get('/{boss}', 'CollectionController@show');
		Route::put('/{boss}', 'BossController@update');
	// });
});

Route::prefix('/collection')->group(function () {
	// Route::middleware('auth:api')->group(function () {
		Route::get('/', 'CollectionController@bossList');
	// });
});