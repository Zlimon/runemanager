<?php

use App\Account;
use App\Collection;
use App\Http\Controllers\Admin\Api\CalendarController;
use App\Http\Controllers\Api\AccountAuthController;
use App\Http\Controllers\Api\AccountBankController;
use App\Http\Controllers\Api\AccountCollectionController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountEquipmentController;
use App\Http\Controllers\Api\AccountLootController;
use App\Http\Controllers\Api\AccountQuestController;
use App\Http\Controllers\Api\AccountSkillController;
use App\Http\Controllers\Api\BroadcastController;
use App\Http\Controllers\Api\CollectionController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\HiscoreController;
use App\Http\Controllers\Api\UserController;
use App\Skill;
use App\Http\Resources\AccountBossResource;
use App\Http\Resources\AccountCollectionResource;
use App\Http\Resources\AccountResource;
use App\Http\Resources\AccountSkillResource;
use App\Http\Resources\CollectionResource;
use App\Http\Resources\SkillResource;
use Illuminate\Support\Facades\Route;

Route::prefix('/user')->group(function() {
    Route::post('/register', [UserController::class, 'register'])->name('user-register');
    Route::post('/login', [UserController::class, 'login'])->name('user-login');
});

Route::middleware('auth:api')->group(function() {
    Route::get('/user', [UserController::class, 'user'])->name('user');
    Route::put('/user/update', [UserController::class, 'update'])->name('user-update');

    Route::post('/account/auth/create', [AccountAuthController::class, 'store'])->name('account-auth-create');
	// TODO rework auth process
    Route::post('/account/auth/authenticate', [AccountController::class, 'authenticate'])->name('account-authenticate');
	Route::prefix('/account/auth')->middleware('user.accountAuthStatus')->group(function() {
        Route::patch('/{accountAuthStatus}/update', [AccountAuthController::class, 'updateAccountType'])->name('account-auth-update-account-type');
        Route::delete('/{accountAuthStatus}/destroy', [AccountAuthController::class, 'delete'])->name('account-auth-delete');
    });

	Route::prefix('/account')->middleware('user.account')->group(function() {
        Route::put('/{account}/login', [AccountController::class, 'loginLogout'])->name('account-login');
        Route::put('/{account}/logout', [AccountController::class, 'loginLogout'])->name('account-logout');

        Route::put('/{account}/loot/{collection}', [AccountLootController::class, 'update'])->name('account-loot-update');
        Route::post('/{account}/collection/{collection}', [AccountCollectionController::class, 'update'])->name('account-collection-update');

        Route::post('/{account}/skill/{skill}', [AccountSkillController::class, 'update'])->name('account-skill-update');

        Route::post('/{account}/equipment', [AccountEquipmentController::class, 'update'])->name('account-equipment-update');
        Route::patch('/{account}/equipment', [AccountEquipmentController::class, 'updateDisplay'])->name('account-equipment-update-display');

        Route::post('/{account}/bank', [AccountBankController::class, 'update'])->name('account-bank-update');
        Route::patch('/{account}/bank', [AccountBankController::class, 'updateDisplay'])->name('account-bank-update-display');

        Route::post('/{account}/quests', [AccountQuestController::class, 'update'])->name('account-quests-update');
        Route::patch('/{account}/quests', [AccountQuestController::class, 'updateDisplay'])->name('account-quests-update-display');
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

    Route::get('/{account}/equipment', [AccountEquipmentController::class, 'show'])->name('account-equipment-show');
    Route::get('/{account}/bank', [AccountBankController::class, 'show'])->name('account-bank-show');
    Route::get('/{account}/quests', [AccountQuestController::class, 'show'])->name('account-quests-show');
});

Route::prefix('/hiscore')->group(function() {
    Route::get('/{accountOne}/{accountTwo}/compare', [HiscoreController::class, 'compare'])->name('hiscore-compare');
    Route::get('/skill/total', [HiscoreController::class, 'total'])->name('hiscore-total');
    Route::get('/skill/{skill}', [HiscoreController::class, 'skill'])->name('hiscore-skill');
    Route::get('/collection/{collection}', [HiscoreController::class, 'collection'])->name('hiscore-boss');
});

Route::prefix('/collection')->group(function() {
    Route::get('/{collectionCategory}', [CollectionController::class, 'index'])->name('hiscore');
});

Route::prefix('/broadcast')->group(function() {
    Route::get('/{broadcastType}', [BroadcastController::class, 'index'])->name('broadcast');

    Route::get('/account/{account}/{broadcastType}', [BroadcastController::class, 'account'])->name('broadcast-account');
    Route::get('/recent/{broadcastType}', [BroadcastController::class, 'recent'])->name('broadcast-recent');
});

// TODO move out of Admin
Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
Route::get('/calendar/{calendar}/show', [CalendarController::class, 'show'])->name('calendar-show');

Route::prefix('/group')->group(function() {
    Route::get('/{group}', [GroupController::class, 'index'])->name('group');
    Route::get('/{group}/bank', [GroupController::class, 'bank'])->name('group-bank');
});
