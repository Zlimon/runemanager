<?php

use Illuminate\Support\Facades\Route;

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

/*==========Pages Controller=============*/
Route::get('/', 'PageController@index')->name('index');
Route::get('/update-log', 'PageController@updateLog')->name('update-log');
Route::get('/home', 'HomeController@index')->name('home');
Route::post('/home/{accountUsername}', 'HomeController@forceLogout')->name('account-force-logout');
Route::get('/hiscore/{hiscoreType}/{hiscoreName}', 'PageController@hiscore')->name('hiscore');

/*==========Member Controller=============*/
Route::get('/account', 'AccountController@index')->name('account');
Route::post('/account', 'AccountController@search')->name('account-search');
Route::get('/account/create', 'AccountController@create')->name('account-create');
Route::get('/account/{accountUsername}', 'AccountController@show')->name('account-show');

Route::get('/authenticate', 'AccountAuthController@index')->name('account-auth-show');
Route::post('/authenticate', 'AccountAuthController@create')->name('account-auth-create');
Route::patch('/authenticate', 'AccountAuthController@updateAccountType')->name('account-auth-update');
Route::delete('/authenticate', 'AccountAuthController@delete')->name('account-auth-delete');

/*==========User Controller=============*/
Route::get('/user/edit', 'UserController@edit')->name('user-edit');
Route::patch('/user/edit', 'UserController@update')->name('user-update');

/*==========Tasks Controller=============*/
// Route::get('/task', 'TasksController@index')->name('task');
// Route::post('/task', 'TasksController@store')->name('store-task');
// Route::patch('/task', 'TasksController@update')->name('update-task');

/*==========News Controller=============*/
Route::get('/news', 'NewsController@index')->name('news');
Route::get('/news/{id}', 'NewsController@show')->name('news-show');

/*==========Admin Controller=============*/
//	Route::group(['middleware' => ['permission:access admin']], function () {
		Route::get('/admin', 'Admin\AdminController@index')->name('admin-index');
		/*==========News Controller=============*/
		Route::get('/admin/news', 'Admin\NewsController@index')->name('admin-news');
		Route::get('/admin/news/create', 'Admin\NewsController@create')->name('admin-create-newspost');
		Route::post('/admin/news/create', 'Admin\NewsController@store')->name('admin-store-newspost');
		Route::get('/admin/news/{id}/show', 'Admin\NewsController@show')->name('admin-show-newspost');
		Route::get('/admin/news/{id}/edit', 'Admin\NewsController@edit')->name('admin-edit-newspost');
		Route::patch('/admin/news/{id}/edit', 'Admin\NewsController@update')->name('admin-update-newspost');
		Route::delete('/admin/news/{id}/delete', 'Admin\NewsController@destroy')->name('admin-delete-newspost');
		//Route::resource('/admin/news', 'AdminNewsController');
		/*==========User Controller=============*/
		Route::get('/admin/user', 'Admin\UserController@index')->name('admin-user');
		Route::post('/admin/user', 'Admin\UserController@search')->name('admin-search-user');
		Route::get('/admin/user/{user}/show', 'Admin\UserController@show')->name('admin-show-user');
		Route::get('/admin/user/{user}/edit', 'Admin\UserController@edit')->name('admin-edit-user');
		Route::patch('/admin/user/{user}/edit', 'Admin\UserController@update')->name('admin-update-user');
		/*==========Member Controller=============*/
		Route::get('/admin/account', 'Admin\AccountController@index')->name('admin-member');
		Route::post('/admin/account', 'Admin\AccountController@search')->name('admin-search-member');
		Route::get('/admin/account/create', 'Admin\AccountController@create')->name('admin-create-member');
		Route::post('/admin/account/create', 'Admin\AccountController@store')->name('admin-store-member');
		Route::get('/admin/account/{account}/show', 'Admin\AccountController@show')->name('admin-show-member');
		Route::patch('/admin/account/{account}/show', 'Admin\AccountController@update')->name('admin-update-member');
		/*==========Rank Controller=============*/
		Route::get('/admin/rank', 'AdminRoleController@index')->name('admin-rank');
//	});

Auth::routes();
