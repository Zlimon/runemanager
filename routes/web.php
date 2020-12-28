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
Route::get('/hiscore/{hiscoreType}/{hiscore}', 'PageController@hiscore')->name('hiscore');

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
	Route::group(['middleware' => ['permission:access admin']], function () {
		Route::get('/admin', 'AdminController@index')->name('admin-index');
		/*==========News Controller=============*/
		Route::get('/admin/news', 'AdminNewsController@index')->name('admin-news');
		Route::get('/admin/news/create', 'AdminNewsController@create')->name('admin-create-newspost');
		Route::post('/admin/news/create', 'AdminNewsController@store')->name('admin-store-newspost');
		Route::get('/admin/news/{id}/show', 'AdminNewsController@show')->name('admin-show-newspost');
		Route::get('/admin/news/{id}/edit', 'AdminNewsController@edit')->name('admin-edit-newspost');
		Route::patch('/admin/news/{id}/edit', 'AdminNewsController@update')->name('admin-update-newspost');
		Route::delete('/admin/news/{id}/delete', 'AdminNewsController@destroy')->name('admin-delete-newspost');
		//Route::resource('/admin/news', 'AdminNewsController');
		/*==========User Controller=============*/
		Route::get('/admin/user', 'AdminUserController@index')->name('admin-user');
		Route::post('/admin/user', 'AdminUserController@search')->name('admin-search-user');
		Route::get('/admin/user/{id}/show', 'AdminUserController@show')->name('admin-show-user');
		Route::get('/admin/user/{id}/edit', 'AdminUserController@edit')->name('admin-edit-user');
		Route::patch('/admin/user/{id}/edit', 'AdminUserController@update')->name('admin-update-user');
		/*==========Member Controller=============*/
		Route::get('/admin/member', 'AdminAccountController@index')->name('admin-member');
		Route::post('/admin/member', 'AdminAccountController@search')->name('admin-search-member');
		Route::get('/admin/member/create', 'AdminAccountController@create')->name('admin-create-member');
		Route::post('/admin/member/create', 'AdminAccountController@store')->name('admin-store-member');
		Route::get('/admin/member/{id}/show', 'AdminAccountController@show')->name('admin-show-member');
		Route::patch('/admin/member/{id}/show', 'AdminAccountController@update')->name('admin-update-member');
		/*==========Rank Controller=============*/
		Route::get('/admin/rank', 'AdminRoleController@index')->name('admin-rank');
	});

Auth::routes();
