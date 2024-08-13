<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
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
        Route::get('/', [\App\Http\Controllers\AccountController::class, 'index'])->name('accounts.index');
        Route::get('/{account}', [\App\Http\Controllers\AccountController::class, 'show'])->name('accounts.show');
    });

    Route::prefix('/hiscores')->group(function() {
        Route::get('/skills/{skill}', [\App\Http\Controllers\SkillHiscoreController::class, 'index'])->name('hiscores.skills.index');
        Route::get('/bosses/{boss}', function() {
            $boss = $this->router->current()->parameters['boss'];

            if (!isset($boss)) {
                abort(404);
            }

            return App::call('\App\Http\Controllers\CollectionHiscoreController@index' , ['category' => 'boss', 'collection' => $boss]);
        })->name('hiscores.bosses.index');
        Route::get('/clues/{clue}', function() {
            $clue = $this->router->current()->parameters['clue'];

            if (!isset($clue)) {
                abort(404);
            }

            return App::call('\App\Http\Controllers\CollectionHiscoreController@index' , ['category' => 'clue', 'collection' => $clue]);
        })->name('hiscores.clues.index');
    });

    Route::prefix('/admin')->group(function() {
        Route::prefix('/hiscores')->group(function() {
            Route::get('/create', [\App\Http\Controllers\Admin\HiscoreController::class, 'create'])->name('admin.hiscores.create');
        });
    });
});
