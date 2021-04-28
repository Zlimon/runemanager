<?php

use App\Account;
use App\Collection;
use App\Http\Controllers\Api\HiscoreController;
use App\Http\Resources\AccountBossResource;
use App\Http\Resources\AccountCollectionResource;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountSkillResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\SkillResource;
use App\Skill;
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
        return new SkillResource($account->skill($skill)->first());
    })->name('account-skill-show');

	Route::get('/{account}/boss', function (Account $account) {
        return new AccountBossResource($account);
    })->name('account-bosses-show');
	Route::get('/{account}/boss/{boss}', function (Account $account, $bossName) {
        // Allow only selecting bosses
        $boss = Collection::where(
            function ($query) use ($bossName) {
                $query->where('category_id', '=', 2)
                    ->orWhere('category_id', '=', 3);
            })->where('name', $bossName)->firstOrFail();

        return new CollectionResource($account->collection($boss)->first());
    })->name('account-boss-show');

	Route::get('/{account}/collection', function (Account $account) {
        return new AccountCollectionResource($account);
    })->name('account-collections-show');
	Route::get('/{account}/collection/{collection:alias}', function (Account $account, $collectionName) {
	    $collection = Collection::whereName($collectionName)->orWhere('alias', $collectionName)->first();

	    return new CollectionResource($account->collection($collection)->first());
    })->name('account-collection-show');

    Route::get('/{account}/equipment', 'Api\AccountEquipmentController@show')->name('account-equipment-show');
    Route::get('/{account}/bank', 'Api\AccountBankController@show')->name('account-bank-show');
    Route::get('/{account}/quests', 'Api\AccountQuestController@show')->name('account-quests-show');
});

Route::prefix('/hiscore')->group(function() {
    Route::get('/skill/total', 'Api\HiscoreController@total')->name('hiscore-total-show');
	Route::get('/skill/{skill}', 'Api\HiscoreController@skill')->name('hiscore-skill-show');
	Route::get('/boss/{collection}', function(Collection $collection) {
	    Collection::where('name', $collection->name)->where(function ($query) {
            $query->where('category_id', 2)
                ->orWhere('category_id', 3);
        })->firstOrFail();

	    return (new HiscoreController())->collection($collection);
    })->name('hiscore-boss-show');
    Route::get('/npc/{collection}', function(Collection $collection) {
	    Collection::where('name', $collection->name)->where('category_id', 4)->firstOrFail();

	    return (new HiscoreController())->collection($collection);
    })->name('hiscore-npc-show');
    Route::get('/clue/{collection}', function(Collection $collection) {
	    Collection::where('name', $collection->name)->where('category_id', 5)->firstOrFail();

	    return (new HiscoreController())->collection($collection);
    })->name('hiscore-clue-show');
});

Route::prefix('/collection')->group(function() {
	Route::get('/{collectionCategory}', 'Api\CollectionController@index');
});

Route::prefix('/broadcast')->group(function() {
	Route::get('/{broadcastType}', 'Api\BroadcastController@index')->name('broadcast-show-all');
	Route::get('/account/{accountUsername}/{broadcastType}', 'Api\BroadcastController@account')->name('broadcast-account-show');
    Route::get('/recent/{broadcastType}', 'Api\BroadcastController@recent')->name('broadcast-recent-show');
});
