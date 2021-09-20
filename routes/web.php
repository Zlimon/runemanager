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
Route::get('/account/compare', 'AccountController@compare')->name('account-compare');
Route::get('/account/{account}', 'AccountController@show')->name('account-show');

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

Auth::routes();
