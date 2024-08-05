<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('/accounts')->group(function() {
        Route::get('/', [App\Http\Controllers\AccountController::class, 'index'])->name('accounts.index');
    });

    Route::prefix('/hiscores')->group(function() {
        Route::get('/skills/{skill}', [\App\Http\Controllers\HiscoreController::class, 'showSkill'])->name('hiscores.skill.show');
    });

    Route::prefix('/admin')->group(function() {
        Route::prefix('/hiscores')->group(function() {
            Route::get('/create', [\App\Http\Controllers\Admin\HiscoreController::class, 'create'])->name('admin.hiscores.create');
        });
    });
});
