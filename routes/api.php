<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\CollectionLogController;
use App\Http\Controllers\Api\EquipmentController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\LootingBagController;
use App\Http\Controllers\Api\QuestController;
use App\Http\Controllers\UserResourcePackController;
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

            Route::get('/looting-bag', [LootingBagController::class, 'show'])->name('api.accounts.looting-bag.show');
            Route::get('/bank', [BankController::class, 'show'])->name('api.accounts.bank.show');
            Route::get('/equipment', [EquipmentController::class, 'show'])->name('api.accounts.equipment.show');
            Route::get('/quests', [QuestController::class, 'show'])->name('api.accounts.quests.show');

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
        Route::put('/bank', [BankController::class, 'update'])->name('api.plugin.bank');
        Route::put('/equipment', [EquipmentController::class, 'update'])->name('api.plugin.equipment');
        Route::put('/quests', [QuestController::class, 'update'])->name('api.plugin.quests');
        Route::put('/looting-bag', [LootingBagController::class, 'update'])->name('api.plugin.looting-bag');
    });

    // Resource pack is a user preference (not OSRS-account-scoped), so it
    // sits outside the plugin.account middleware. Plugin pushes a pack name;
    // backend resolves to an id and writes users.resource_pack_id.
    Route::put('/plugin/resource-pack', [UserResourcePackController::class, 'updateFromPlugin'])
        ->name('api.plugin.resource-pack');
});
