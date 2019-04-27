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

/*==========Member Controller=============*/
Route::get('/member', 'MembersController@index')->name('member');
Route::get('/member/{username}', 'MembersController@show')->name('show-member');

/*==========Skills Controller=============*/
Route::get('/skill', 'SkillsController@index')->name('skill');
Route::get('/skill/{skill}', 'SkillsController@show')->name('show-skill');


Route::get('/home', 'HomeController@index')->name('home');


Auth::routes();