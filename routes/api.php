<?php

use App\Account;
use App\Collection;
use App\Skill;
use App\Http\Resources\AccountBossResource;
use App\Http\Resources\AccountCollectionResource;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountSkillResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\SkillResource;
use Illuminate\Support\Facades\Route;

Route::prefix('/user')->group(function() {
	Route::post('/register', 'Api\UserController@register')->name('user-register');
	Route::post('/login', 'Api\UserController@login')->name('user-login');
});

Route::middleware('auth:api')->group(function() {
	Route::get('/user', 'Api\UserController@user')->name('user-show');
	Route::put('/user/update', 'Api\UserController@update')->name('user-update');

	Route::post('/account/auth/create', 'Api\AccountAuthController@store')->name('account-auth-create');
	// TODO rework auth process
    Route::post('/account/auth/auth', 'Api\AccountController@authenticate')->name('authenticate'); // Authenticate user
	Route::prefix('/account/auth')->middleware('user.accountAuthStatus')->group(function() {
        Route::patch('/{accountAuthStatus}/update', 'Api\AccountAuthController@updateAccountType')->name('account-auth-update');
        Route::delete('/{accountAuthStatus}/destroy', 'Api\AccountAuthController@delete')->name('account-auth-delete');
    });

	Route::prefix('/account')->middleware('user.account')->group(function() {
        Route::put('/{account}/login', 'Api\AccountController@loginLogout')->name('account-login'); // Make account online
        Route::put('/{account}/logout', 'Api\AccountController@loginLogout')->name('account-logout'); // Make account offline

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
	Route::get('/{account}', function (Account $account) {
        return new AccountResource($account);
    })->name('account-show');

	Route::get('/{account}/skill', function (Account $account) {
        return new AccountSkillResource($account);
    })->name('account-skills-show');
	Route::get('/{account}/skill/{skill}', function (Account $account, Skill $skill) {
        return new SkillResource($account->skill($skill)->firstOrFail());
    })->name('account-skill-show');

	Route::get('/{account}/boss', function (Account $account) {
        return new AccountBossResource($account);
    })->name('account-bosses-show');
	Route::get('/{account}/boss/{collection}', function (Account $account, Collection $collection) {
        return new CollectionResource($account->collection($collection)->firstOrFail());
    })->name('account-boss-show');

	Route::get('/{account}/collection', function (Account $account) {
        return new AccountCollectionResource($account);
    })->name('account-collections-show');
	Route::get('/{account}/collection/{collection}', function (Account $account, Collection $collection) {
	    return new CollectionResource($account->collection($collection)->firstOrFail());
    })->name('account-collection-show');

    Route::get('/{account}/equipment', 'Api\AccountEquipmentController@show')->name('account-equipment-show');
    Route::get('/{account}/bank', 'Api\AccountBankController@show')->name('account-bank-show');
    Route::get('/{account}/quests', 'Api\AccountQuestController@show')->name('account-quests-show');
});

Route::prefix('/hiscore')->group(function() {
    Route::get('/{accountOne}/{accountTwo}/compare', 'Api\HiscoreController@compare')->name('hiscore-compare-show');
    Route::get('/skill/total', 'Api\HiscoreController@total')->name('hiscore-total-show');
	Route::get('/skill/{skill}', 'Api\HiscoreController@skill')->name('hiscore-skill-show');
	Route::get('/collection/{collection}', 'Api\HiscoreController@collection')->name('hiscore-boss-show');
});

Route::prefix('/collection')->group(function() {
	Route::get('/{collectionCategory}', 'Api\CollectionController@index');
});

Route::prefix('/broadcast')->group(function() {
	Route::get('/{broadcastType}', 'Api\BroadcastController@index')->name('broadcast-show-all');
	Route::get('/account/{account}/{broadcastType}', 'Api\BroadcastController@account')->name('broadcast-account-show');
    Route::get('/recent/{broadcastType}', 'Api\BroadcastController@recent')->name('broadcast-recent-show');
});

Route::get('/calendar', 'Admin\Api\CalendarController@index');
Route::get('/calendar/{calendar}/show', 'Admin\Api\CalendarController@show');

Route::prefix('/group')->group(function() {
    Route::get('/{group}', 'Api\GroupController@show');
	Route::get('/{group}/bank', 'Api\GroupController@bank');
});
