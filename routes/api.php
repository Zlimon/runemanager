<?php

use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
])->group(function () {
    Route::get('/me', [App\Http\Controllers\Api\AuthController::class, 'me']);

    Route::prefix('/accounts')->group(function() {
        Route::post('/search', [App\Http\Controllers\Api\AccountController::class, 'search'])->name('api.accounts.search');
        Route::put('/{account}/update', [App\Http\Controllers\Api\AccountController::class, 'update'])->name('api.accounts.update');
    });

    Route::post('/npc/search', [App\Http\Controllers\Api\NpcController::class, 'search'])->name('api.npc.search');

    Route::prefix('/collectionlog')->group(function() {
        Route::prefix('/user')->group(function() {
            Route::post('/', [App\Http\Controllers\Api\CollectionLogController::class, 'user'])->name('api.collectionlog.user');
        });
    });

    Route::prefix('/admin')->group(function() {
        Route::prefix('/hiscores')->group(function() {
            Route::post('/store', [App\Http\Controllers\Admin\Api\HiscoreController::class, 'store'])->name('admin.api.hiscores.store');
        });
    });
});
