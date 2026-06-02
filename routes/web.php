<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\SkillHiscoreController;
use App\Http\Controllers\UserResourcePackController;
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

// SPEC §8 — the live feed is intentionally public (no auth middleware).
Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::prefix('/accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/{account}', [AccountController::class, 'show'])->name('accounts.show');
    });

    // Per-user resource pack override (instance-global pack is set by the
    // resourcepack:switch artisan, not this endpoint).
    Route::put('/user/resource-pack', [UserResourcePackController::class, 'update'])
        ->name('user.resource-pack.update');

    Route::prefix('/hiscores')->group(function () {
        Route::get('/skills/{skill}', [SkillHiscoreController::class, 'index'])->name('hiscores.skills.index');
        Route::get('/bosses/{boss}', function () {
            $boss = $this->router->current()->parameters['boss'];

            if (! isset($boss)) {
                abort(404);
            }

            return App::call('\App\Http\Controllers\CollectionHiscoreController@index', ['category' => 'boss', 'collection' => $boss]);
        })->name('hiscores.bosses.index');
        Route::get('/clues/{clue}', function () {
            $clue = $this->router->current()->parameters['clue'];

            if (! isset($clue)) {
                abort(404);
            }

            return App::call('\App\Http\Controllers\CollectionHiscoreController@index', ['category' => 'clue', 'collection' => $clue]);
        })->name('hiscores.clues.index');
    });
});
