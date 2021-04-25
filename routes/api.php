<?php

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

Route::prefix('/user')->group(function() {
	Route::post('/register', 'Api\UserController@register')->name('user-register');
	Route::post('/login', 'Api\UserController@login')->name('user-login');
});

Route::middleware('auth:api')->group(function() {
	Route::get('/user', 'Api\UserController@user')->name('user-show');
	Route::post('/authenticate', 'Api\AccountController@store')->name('authenticate'); // Authenticate user

	Route::prefix('/account')->middleware('user.account')->group(function() {
        Route::put('/{accountUsername}/login', 'Api\AccountController@loginLogout')->name('account-login'); // Make account online
        Route::put('/{accountUsername}/logout', 'Api\AccountController@loginLogout')->name('account-logout'); // Make account offline

		Route::put('/{account}/loot/{collection}', 'Api\AccountLootController@update')->name('account-loot-update'); // Put loot data - updates collection model
		Route::post('/{account}/collection/{collection}', 'Api\AccountCollectionController@update')->name('account-collection-update'); // Post collection data - replaces collection model

        Route::post('/{account}/skill/{skill}', 'Api\AccountSkillController@update')->name('account-skill-update');

        Route::post('/{account}/equipment', 'Api\AccountEquipmentController@update')->name('account-equipment-update');
        Route::patch('/{account}/equipment', 'Api\AccountEquipmentController@updateDisplay')->name('account-equipment-update-display');

        Route::post('/{account}/bank', 'Api\AccountBankController@update')->name('account-bank-update');
        Route::patch('/{account}/bank', 'Api\AccountBankController@updateDisplay')->name('account-bank-update-display');

        Route::post('/{account}/quests', 'Api\AccountQuestController@update')->name('account-quests-update');
        Route::patch('/{account}/quests', 'Api\AccountQuestController@updateDisplay')->name('account-quests-update-display');
    });
});

Route::prefix('/account')->group(function() {
	Route::get('/{account}', 'Api\AccountController@show')->name('account-show');

	Route::get('/{account}/skill', 'Api\AccountSkillController@index')->name('account-skills-show');
	Route::get('/{account}/skill/{skill}', 'Api\AccountSkillController@show')->name('account-skill-show');

	Route::get('/{account}/boss', 'Api\AccountBossController@index')->name('account-bosses-show');
	Route::get('/{account}/boss/{boss}', 'Api\AccountBossController@show')->name('account-boss-show');

	Route::get('/{accountUsername}/collection', 'Api\AccountCollectionController@index')->name('account-collections-show');
	Route::get('/{accountUsername}/collection/{collectionName}', 'Api\AccountCollectionController@show')->name('account-collection-show');

    Route::get('/{accountUsername}/equipment', 'Api\AccountEquipmentController@show')->name('account-equipment-show');
    Route::get('/{accountUsername}/bank', 'Api\AccountBankController@show')->name('account-bank-show');
    Route::get('/{accountUsername}/quests', 'Api\AccountQuestController@show')->name('account-quests-show');
});

Route::prefix('/hiscore')->group(function() {
	Route::get('/skill/{skill}', 'Api\HiscoreController@skill')->name('hiscore-skill-show');
	Route::get('/boss/{boss}', 'Api\HiscoreController@boss')->name('hiscore-boss-show');
    Route::get('/npc/{npc}', 'Api\HiscoreController@npc')->name('hiscore-npc-show');
    Route::get('/clue/{clue}', 'Api\HiscoreController@clue')->name('hiscore-clue-show');
});

Route::prefix('/collection')->group(function() {
	Route::get('/{collectionCategory}', 'Api\CollectionController@index');
});

Route::prefix('/broadcast')->group(function() {
	Route::get('/{broadcastType}', 'Api\BroadcastController@index')->name('broadcast-show-all');
	Route::get('/account/{accountUsername}/{broadcastType}', 'Api\BroadcastController@account')->name('broadcast-account-show');
    Route::get('/recent/{broadcastType}', 'Api\BroadcastController@recent')->name('broadcast-recent-show');
});
