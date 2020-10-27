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
	Route::get('/{account}', 'Api\AccountController@show')->name('show-account');
	Route::post('/{account}/authenticate', 'Api\AccountController@store')->name('authenticate-account');

	// Route::get('/{accountUsername}/loot/{collectionName}', 'Api\AccountCollectionController@show')->name('show-account-collection');
	Route::put('/{accountUsername}/loot/{collectionName}', 'Api\AccountLootController@update')->name('update-account-loot');

	Route::get('/{accountUsername}/collection/{collectionName}', 'Api\AccountCollectionController@show')->name('show-account-collection');
	Route::post('/{accountUsername}/collection/{collectionName}', 'Api\AccountCollectionController@update')->name('update-account-collection');
});

Route::prefix('/collection')->group(function () {
	// Route::middleware('auth:api')->group(function () {
		Route::get('/{collectionType}', 'CollectionController@list');
	// });
});