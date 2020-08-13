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