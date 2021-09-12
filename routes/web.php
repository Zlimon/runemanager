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
Route::get('/calendar', 'PageController@calendar')->name('calendar');

/*==========Member Controller=============*/
Route::get('/account', 'AccountController@index')->name('account');
Route::post('/account', 'AccountController@search')->name('account-search');
Route::get('/account/create', 'AccountController@create')->name('account-create');
Route::get('/account/{accountUsername}', 'AccountController@show')->name('account-show');

/*==========Group Controller=============*/
Route::get('/group', 'GroupController@index')->name('group');
Route::post('/group', 'GroupController@search')->name('group-search');
Route::get('/group/create', 'GroupController@create')->name('group-create');
Route::get('/group/{groupName}', 'GroupController@show')->name('group-show');

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
		Route::post('/user', 'Admin\UserController@search')->name('admin-search-user');
		Route::get('/user/{user}/show', 'Admin\UserController@show')->name('admin-show-user');
		Route::get('/user/{user}/edit', 'Admin\UserController@edit')->name('admin-edit-user');
		Route::patch('/user/{user}/edit', 'Admin\UserController@update')->name('admin-update-user');
		/*==========Member Controller=============*/
		Route::get('/account', 'Admin\AccountController@index')->name('admin-account');
		Route::post('/account', 'Admin\AccountController@search')->name('admin-search-account');
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
//		Route::get('/admin/rank', 'AdminRoleController@index')->name('admin-rank');
//	});
    });
});

Auth::routes();
