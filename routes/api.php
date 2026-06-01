<?php

use App\Http\Controllers\Admin\Api\HiscoreController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\CollectionLogController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LootingBagController;
use App\Http\Controllers\Api\NpcController;
use App\Http\Controllers\Api\QuestController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('api.login');

Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

    Route::prefix('/accounts')->group(function () {
        Route::post('/search', [AccountController::class, 'search'])->name('api.accounts.search');
        Route::put('/{account}/update', [AccountController::class, 'update'])->name('api.accounts.update');

        Route::prefix('/{account}')->group(function () {
            //            Route::get('/hiscores', [App\Http\Controllers\Api\AccountController::class, 'hiscores'])->name('api.accounts.hiscores');
            //            Route::get('/collectionlog', [App\Http\Controllers\Api\AccountController::class, 'collectionlog'])->name('api.accounts.collectionlog');

            Route::prefix('/inventory')->group(function () {
                Route::get('/', [InventoryController::class, 'show'])->name('api.accounts.inventory.show');
            });

            Route::prefix('/looting-bag')->group(function () {
                Route::get('/', [LootingBagController::class, 'show'])->name('api.accounts.looting-bag.show');
                Route::put('/update', [LootingBagController::class, 'update'])->name('api.accounts.looting-bag.update');
            });

            Route::prefix('/bank')->group(function () {
                Route::get('/', [BankController::class, 'show'])->name('api.accounts.bank.show');
                Route::put('/update', [BankController::class, 'update'])->name('api.accounts.bank.update');
            });

            Route::prefix('/equipment')->group(function () {
                Route::get('/', [EquipmentController::class, 'show'])->name('api.accounts.equipment.show');
                Route::put('/update', [EquipmentController::class, 'update'])->name('api.accounts.equipment.update');
            });

            Route::prefix('/quests')->group(function () {
                Route::get('/', [QuestController::class, 'show'])->name('api.accounts.quests.show');
                Route::put('/update', [QuestController::class, 'update'])->name('api.accounts.quests.update');
            });

            Route::prefix('/collectionlog')->group(function () {
                Route::post('/', [CollectionLogController::class, 'index'])->name('api.accounts.collectionlog.index');
                Route::get('/{tab}/{collection}', [CollectionLogController::class, 'show'])->name('api.accounts.collectionlog.show');
            });
        });
    });

    /*
     * Plugin push endpoints. The OSRS account is resolved by the plugin.account
     * middleware from the X-Account-Hash + X-Account-Username headers, not from
     * the URL — usernames change but the hash is stable.
     */
    Route::middleware('plugin.account')->prefix('/plugin')->group(function () {
        Route::put('/inventory', [InventoryController::class, 'update'])->name('api.plugin.inventory');
    });

    Route::post('/npc/search', [NpcController::class, 'search'])->name('api.npc.search');

    Route::prefix('/admin')->group(function () {
        Route::prefix('/hiscores')->group(function () {
            Route::post('/store', [HiscoreController::class, 'store'])->name('admin.api.hiscores.store');
        });
    });
});
