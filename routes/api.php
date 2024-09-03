<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/npc/search', [App\Http\Controllers\Api\NpcController::class, 'search'])->name('api.npc.search');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('/accounts')->group(function() {
    Route::post('/search', [App\Http\Controllers\Api\AccountController::class, 'search'])->name('api.accounts.search');
});

Route::prefix('/hiscores')->group(function() {
    Route::post('/store', [App\Http\Controllers\Admin\Api\HiscoreController::class, 'store'])->name('admin.api.hiscores.store');
});

Route::prefix('/collectionlog')->group(function() {
    Route::prefix('/collectionlog')->group(function() {
        Route::prefix('/user')->group(function() {
            Route::post('/', [App\Http\Controllers\Api\CollectionLogController::class, 'user'])->name('api.collectionlog.collectionlog.user');
        });
    });
});
