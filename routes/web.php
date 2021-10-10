<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'PageController@index')->name('index');

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/home/{accountUsername}', 'HomeController@forceLogout')->name('account-force-logout');

Route::get('/news', 'NewsController@index')->name('news');
Route::get('/news/{id}', 'NewsController@show')->name('news-show');

Route::get('/hiscore/{hiscoreType}/{hiscoreName}', 'PageController@hiscore')->name('hiscore');
Route::get('/hiscore/compare/{accountOne?}/{accountTwo?}', 'PageController@compare')->name('account-compare');

Route::get('/calendar', 'PageController@calendar')->name('calendar');

Route::get('/account', 'AccountController@index')->name('account');
Route::post('/account', 'AccountController@search')->name('account-search');

Route::get('/account/{account}/show', 'AccountController@show')->name('account-show');

Route::get('/account/auth/', 'AccountAuthController@index')->name('account-auth');
Route::get('/account/auth/create', 'AccountAuthController@create')->name('account-auth-create');

Route::get('/group', 'GroupController@index')->name('group');
Route::post('/group/search', 'GroupController@search')->name('group-search');
Route::get('/group/create', 'GroupController@create')->name('group-create');
Route::get('/group/{groupName}/show', 'GroupController@show')->name('group-show');

Route::get('/user/edit', 'UserController@edit')->name('user-edit');

Auth::routes();
