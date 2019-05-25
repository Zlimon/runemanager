<?php

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
Route::get('/', 'PagesController@index')->name('index');
Route::get('/update-log', 'PagesController@updateLog')->name('update-log');
Route::get('/hiscore/{skill}', 'PagesController@hiscore')->name('show-skill');
Route::get('/home', 'HomeController@index')->name('home');

/*==========Member Controller=============*/
Route::get('/member', 'AccountsController@index')->name('member');
Route::post('/member', 'AccountsController@search')->name('search-member');
Route::get('/member/create', 'AccountsController@create')->name('create-member');
Route::post('/member/create', 'AccountsController@verifyAccount')->name('store-member');
Route::get('/member/{id}', 'AccountsController@show')->name('show-member');

/*==========User Controller=============*/
Route::get('/user/edit', 'UsersController@edit')->name('edit-user');
Route::patch('/user/edit', 'UsersController@update')->name('update-user');

/*==========Tasks Controller=============*/
Route::get('/task', 'TasksController@index')->name('task');
Route::post('/task', 'TasksController@store')->name('store-task');
Route::patch('/task', 'TasksController@update')->name('update-task');

/*==========News Controller=============*/
Route::get('/news', 'NewsController@index')->name('news');
Route::get('/news/{id}', 'NewsController@show')->name('show-newspost');

/*==========Admin Controller=============*/
	Route::get('/admin', 'AdminController@index')->name('admin-index')->middleware('role:admin');
	/*==========News Controller=============*/
	Route::get('/admin/news', 'AdminNewsController@index')->name('admin-news')->middleware('role:admin');
	Route::get('/admin/news/create', 'AdminNewsController@create')->name('admin-create-newspost');
	Route::post('/admin/news/create', 'AdminNewsController@store')->name('admin-store-newspost');
	Route::get('/admin/news/{id}/show', 'AdminNewsController@show')->name('admin-show-newspost');
	Route::get('/admin/news/{id}/edit', 'AdminNewsController@edit')->name('admin-edit-newspost');
	Route::patch('/admin/news/{id}/edit', 'AdminNewsController@update')->name('admin-update-newspost');
	Route::delete('/admin/news/{id}/delete', 'AdminNewsController@destroy')->name('admin-delete-newspost');
	//Route::resource('/admin/news', 'AdminNewsController');
	/*==========User Controller=============*/
	Route::get('/admin/user', 'AdminUserController@index')->name('admin-user')->middleware('role:admin');
	Route::post('/admin/user', 'AdminUserController@search')->name('admin-search-user')->middleware('role:admin');
	Route::get('/admin/user/{id}/show', 'AdminUserController@show')->name('admin-show-user')->middleware('role:admin');
	Route::get('/admin/user/{id}/edit', 'AdminUserController@edit')->name('admin-edit-user')->middleware('role:admin');
	Route::patch('/admin/user/{id}/edit', 'AdminUserController@update')->name('admin-update-user')->middleware('role:admin');
	/*==========Member Controller=============*/
	Route::get('/admin/member', 'AdminAccountController@index')->name('admin-member')->middleware('role:admin');
	Route::post('/admin/member', 'AdminAccountController@search')->name('admin-search-member')->middleware('role:admin');
	Route::get('/admin/member/create', 'AdminAccountController@create')->name('admin-create-member');
	Route::post('/admin/member/create', 'AdminAccountController@store')->name('admin-store-member');
	Route::get('/admin/member/{id}/show', 'AdminAccountController@show')->name('admin-show-member')->middleware('role:admin');
	Route::patch('/admin/member/{id}/show', 'AdminAccountController@update')->name('admin-update-member')->middleware('role:admin');

Auth::routes();