<?php

use App\Http\Controllers\Admin\Api\AccountController;
use App\Http\Controllers\Admin\Api\CalendarController;
use App\Http\Controllers\Admin\Api\NewsController;
use App\Http\Controllers\Admin\Api\ResourcePackController;
use App\Http\Controllers\Admin\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function() {
    Route::prefix('/admin')->group(function() {
        Route::prefix('/user')->group(function() {
            Route::post('/search', [UserController::class, 'search'])->name('admin-user-search');
            Route::put('/{user}/update', [UserController::class, 'update'])->name('admin-user-update');
        });

        Route::prefix('/account')->group(function() {
            Route::post('/store', [AccountController::class, 'store'])->name('admin-account-store');
            Route::post('/search', [AccountController::class, 'search'])->name('admin-account-search');
            Route::post('/{account}/update', [AccountController::class, 'update'])->name('admin-account-update');
        });

        Route::prefix('/news')->group(function() {
            Route::post('/create', [NewsController::class, 'store'])->name('admin-newspost-store');
            Route::put('/{newsPost}/update', [NewsController::class, 'update'])->name('admin-newspost-update');
            Route::delete('/{newsPost}/destroy', [NewsController::class, 'destroy'])->name('admin-newspost-destroy');
        });

        Route::prefix('/calendar')->group(function() {
            Route::post('/create', [CalendarController::class, 'store'])->name('admin-calendar');
            Route::post('/{calendar}/update', [CalendarController::class, 'update'])->name('admin-calendar-update');
            Route::patch('/{calendar}/schedule', [CalendarController::class, 'updateSchedule'])->name('admin-calendar-update-schedule');
            Route::delete('/{calendar}/destroy', [CalendarController::class, 'destroy'])->name('admin-destroy');
        });

        Route::prefix('/settings')->group(function() {
            Route::prefix('/resource-pack')->group(function() {
                Route::post('/search', [ResourcePackController::class, 'search'])->name('admin-settings-resourcepack-search');
                Route::patch('/{resourcePack}/switch', [ResourcePackController::class, 'switch'])->name('admin-settings-resourcepack-switch');
                Route::patch('/{resourcePack}/update', [ResourcePackController::class, 'update'])->name('admin-settings-resourcepack-update');
            });
        });
    });
});
