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
Route::get('/', function () {
    return view('index');
})->name('index');
Route::get('/hiscore', 'PagesController@hiscore')->name('hiscore');
Route::get('/update-log', 'PagesController@updateLog')->name('update-log');
Route::get('/home', 'HomeController@index')->name('home');

/*==========Member Controller=============*/
Route::get('/member', 'AccountsController@index')->name('member');
Route::get('/member/create', 'AccountsController@create')->name('create-member');
Route::post('/member/create', 'AccountsController@verifyAccount')->name('store-member');
Route::get('/member/{id}', 'AccountsController@show')->name('show-member');

/*==========Skill Controller=============*/
Route::get('/skill', 'SkillsController@index')->name('skill');
Route::get('/skill/{skill}', 'SkillsController@show')->name('show-skill');

/*==========User Controller=============*/
Route::get('/user/edit', 'UsersController@edit')->name('edit-user');
Route::patch('/user/edit', 'UsersController@update')->name('update-user');

Auth::routes();