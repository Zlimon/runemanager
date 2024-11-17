<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me']);

    Route::prefix('/accounts')->group(function () {
        Route::post('/search', [App\Http\Controllers\Api\AccountController::class, 'search'])->name('api.accounts.search');
        Route::put('/{account}/update', [App\Http\Controllers\Api\AccountController::class, 'update'])->name('api.accounts.update');

        Route::prefix('/{account}')->group(function () {
            //            Route::get('/hiscores', [App\Http\Controllers\Api\AccountController::class, 'hiscores'])->name('api.accounts.hiscores');
            //            Route::get('/collectionlog', [App\Http\Controllers\Api\AccountController::class, 'collectionlog'])->name('api.accounts.collectionlog');

            Route::prefix('/inventory')->group(function () {
                Route::get('/', [App\Http\Controllers\Api\InventoryController::class, 'show'])->name('api.accounts.inventory.show');
                Route::put('/update', [App\Http\Controllers\Api\InventoryController::class, 'update'])->name('api.accounts.inventory.update');
            });

            Route::prefix('/looting-bag')->group(function () {
                Route::get('/', [App\Http\Controllers\Api\LootingBagController::class, 'show'])->name('api.accounts.looting-bag.show');
                Route::put('/update', [App\Http\Controllers\Api\LootingBagController::class, 'update'])->name('api.accounts.looting-bag.update');
            });

            Route::prefix('/bank')->group(function () {
                Route::get('/', [App\Http\Controllers\Api\BankController::class, 'show'])->name('api.accounts.bank.show');
                Route::put('/update', [App\Http\Controllers\Api\BankController::class, 'update'])->name('api.accounts.bank.update');
            });

            Route::prefix('/equipment')->group(function () {
                Route::get('/', [App\Http\Controllers\Api\EquipmentController::class, 'show'])->name('api.accounts.equipment.show');
                Route::put('/update', [App\Http\Controllers\Api\EquipmentController::class, 'update'])->name('api.accounts.equipment.update');
            });

            Route::prefix('/quests')->group(function () {
                Route::get('/', [App\Http\Controllers\Api\QuestController::class, 'show'])->name('api.accounts.quests.show');
                Route::put('/update', [App\Http\Controllers\Api\QuestController::class, 'update'])->name('api.accounts.quests.update');
            });

            Route::prefix('/collectionlog')->group(function () {
                Route::post('/', [App\Http\Controllers\Api\CollectionLogController::class, 'index'])->name('api.accounts.collectionlog.index');
                Route::get('/{tab}/{collection}', [App\Http\Controllers\Api\CollectionLogController::class, 'show'])->name('api.accounts.collectionlog.show');
            });
        });
    });

    Route::post('/npc/search', [App\Http\Controllers\Api\NpcController::class, 'search'])->name('api.npc.search');

    Route::prefix('/admin')->group(function () {
        Route::prefix('/hiscores')->group(function () {
            Route::post('/store', [App\Http\Controllers\Admin\Api\HiscoreController::class, 'store'])->name('admin.api.hiscores.store');
        });
    });
});
