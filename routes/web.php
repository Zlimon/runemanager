<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\CollectionHiscoreController;
use App\Http\Controllers\CollectionLogHiscoreController;
use App\Http\Controllers\DiaryHiscoreController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GroupBankController;
use App\Http\Controllers\LootHiscoreController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\OverallHiscoreController;
use App\Http\Controllers\SkillHiscoreController;
use App\Http\Controllers\UserDarkModeController;
use App\Http\Controllers\UserResourcePackController;
use App\Http\Resources\AnnouncementResource;
use App\Models\Announcement;
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

// SPEC §8 — the live feed is intentionally public (no auth middleware).
Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');

// SPEC §10 — calendar viewing is public; mutation routes are auth-gated below.
Route::get('/calendar', [CalendarEventController::class, 'index'])->name('calendar.index');

// SPEC §9 — announcements are viewable in all modes; mutation is auth-gated below.
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

// Light/dark preference — public so the on-page toggle works on the auth screens
// too (guests persist to a cookie; logged-in users to their account).
Route::put('/dark-mode', [UserDarkModeController::class, 'update'])->name('dark-mode.update');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            // SPEC §9.2 — surface the latest active announcements on the homepage.
            'announcements' => AnnouncementResource::collection(
                Announcement::with('user:id,name')->active()->limit(5)->get(),
            )->resolve(),
        ]);
    })->name('dashboard');

    // Live Map — see where accounts are in real time (positions over websockets).
    Route::get('/map', [MapController::class, 'index'])->name('map.index');

    // SPEC §5.2 — shared Group Ironman bank (GROUP mode only; controller 404s otherwise).
    Route::get('/group-bank', [GroupBankController::class, 'index'])->name('group-bank.index');

    Route::prefix('/accounts')->group(function () {
        Route::get('/', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/{account}', [AccountController::class, 'show'])->name('accounts.show');
    });

    // SPEC §9/§10/§12 — management surfaces, each behind its own permission
    // (the Owner holds them all; clan/group elevation of other users is layered
    // on later).
    Route::prefix('/admin')->group(function () {
        Route::middleware('can:manage instance')->group(function () {
            Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
            Route::get('/settings', [AdminController::class, 'settings'])->name('admin.settings');
            Route::put('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
            Route::put('/config', [AdminController::class, 'updateConfig'])->name('admin.config.update');
            Route::post('/branding', [AdminController::class, 'updateBranding'])->name('admin.branding.update');
            Route::get('/packs', [AdminController::class, 'packs'])->name('admin.packs');
            Route::post('/packs/install', [AdminController::class, 'installPack'])->name('admin.packs.install');
        });

        Route::middleware('can:manage members')->group(function () {
            Route::get('/members', [AdminController::class, 'members'])->name('admin.members');
            Route::post('/members', [AdminController::class, 'storeMember'])->name('admin.members.store');
            Route::delete('/members/{account}', [AdminController::class, 'destroyMember'])
                ->whereNumber('account')->name('admin.members.destroy');
        });
    });

    Route::middleware('can:manage calendar')->group(function () {
        Route::post('/calendar', [CalendarEventController::class, 'store'])->name('calendar.store');
        Route::delete('/calendar/{calendarEvent}', [CalendarEventController::class, 'destroy'])
            ->name('calendar.destroy');
    });

    Route::middleware('can:manage announcements')->group(function () {
        Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])
            ->name('announcements.destroy');
    });

    // SPEC §6.2 — per-user appearance: browse packs + set a personal override
    // (instance-global default is set by the owner in admin settings).
    Route::get('/themes', [UserResourcePackController::class, 'index'])->name('themes.index');
    Route::put('/user/resource-pack', [UserResourcePackController::class, 'update'])
        ->name('user.resource-pack.update');

    Route::prefix('/hiscores')->group(function () {
        // SPEC §7.1 — Overall (total level + total XP).
        Route::get('/overall', [OverallHiscoreController::class, 'index'])->name('hiscores.overall.index');
        // SPEC §7 — Achievement Diaries (total tiers completed).
        Route::get('/diaries', [DiaryHiscoreController::class, 'index'])->name('hiscores.diaries.index');
        // SPEC §7 — Collection Log (total slots unlocked, via TempleOSRS).
        Route::get('/collection-log', [CollectionLogHiscoreController::class, 'index'])->name('hiscores.collection-log.index');
        Route::get('/skills/{skill}', [SkillHiscoreController::class, 'index'])->name('hiscores.skills.index');
        Route::get('/bosses/{collection}', [CollectionHiscoreController::class, 'index'])
            ->defaults('category', 'boss')
            ->name('hiscores.bosses.index');
        Route::get('/clues/{collection}', [CollectionHiscoreController::class, 'index'])
            ->defaults('category', 'clue')
            ->name('hiscores.clues.index');
        // SPEC §7 — loot directory (grouped sources) + per-source board.
        Route::get('/loot', [LootHiscoreController::class, 'index'])->name('hiscores.loot.index');
        Route::get('/loot/{type}/{source}', [LootHiscoreController::class, 'show'])
            ->where('source', '.*')
            ->name('hiscores.loot.show');
    });
});
